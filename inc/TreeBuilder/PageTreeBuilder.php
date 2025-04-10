<?php

namespace dokuwiki\TreeBuilder;

use dokuwiki\File\PageResolver;
use dokuwiki\TreeBuilder\Node\AbstractNode;
use dokuwiki\TreeBuilder\Node\Top;
use dokuwiki\TreeBuilder\Node\WikiNamespace;
use dokuwiki\TreeBuilder\Node\WikiPage;
use dokuwiki\TreeBuilder\Node\WikiStartpage;
use dokuwiki\Utf8\PhpString;

/**
 * A tree builder for wiki pages and namespaces
 *
 * This replace the classic search_* functions approach and provides a way to create a traversable tree
 * of wiki pages and namespaces.
 *
 * The created hierarchy can either use WikiNamespace nodes or represent namespaces as WikiPage nodes
 * associated with the namespace's start page.
 */
class PageTreeBuilder extends AbstractBuilder
{
    /** @var array Used to remember already seen start pages */
    protected array $startpages = [];

    /** @var int Return WikiPage(startpage) instead of WikiNamespace(id) for namespaces */
    public const FLAG_NS_AS_STARTPAGE = 1;

    /** @var int Do not return Namespaces, will also disable recursion */
    public const FLAG_NO_NS = 2;

    /** @var int Do not return pages */
    public const FLAG_NO_PAGES = 4;

    /** @var int Do not filter out hidden pages */
    public const FLAG_KEEP_HIDDEN = 8;

    /** @var int The given namespace should be added as top element */
    public const FLAG_SELF_TOP = 16;

    /** @var string The top level namespace to iterate over */
    protected string $namespace;

    /** @var int The maximum depth to iterate into, -1 for infinite */
    protected int $maxdepth;


    /**
     * Constructor
     *
     * @param string $namespace The namespace to start from
     * @param int $maxdepth The maximum depth to iterate into, -1 for infinite
     */
    public function __construct(string $namespace, int $maxdepth = -1)
    {
        $this->namespace = $namespace;
        $this->maxdepth = $maxdepth;
    }

    /** @inheritdoc */
    public function generate(): void
    {
        $this->generated = true;

        $this->top = new Top();

        // add directly to top or add the namespace under the top element?
        if ($this->hasFlag(self::FLAG_SELF_TOP)) {
            $parent = $this->createNamespaceNode($this->namespace, noNS($this->namespace));
            $parent->setParent($this->top);
        } else {
            if ($this->hasFlag(self::FLAG_NS_AS_STARTPAGE)) {
                // do not add the namespace's own startpage in this mode
                $this->startpages[$this->getStartpage($this->namespace)] = 1;
            }

            $parent = $this->top;
        }

        // if FLAG_SELF_TOP, we need to run a recursion decision on the parent
        if ($parent instanceof Top || $this->applyRecursionDecision($parent, 0)) {
            $dir = $this->namespacePath($this->namespace);
            $this->createHierarchy($parent, $dir, $this->maxdepth);
        }

        // if FLAG_SELF_TOP, we need to add the parent to the top
        if (!$parent instanceof Top) {
            $this->addNodeToHierarchy($this->top, $parent);
        }
    }

    /**
     * Recursive function to create the page hierarchy
     *
     * @param AbstractNode $parent results are added as children to this element
     * @param string $dir The directory relative to the page directory
     * @param int $depth Current depth, recursion stops at 0
     * @return void
     */
    protected function createHierarchy(AbstractNode $parent, string $dir, int $depth)
    {
        // Process namespaces (subdirectories)
        if ($this->hasNotFlag(self::FLAG_NO_NS)) {
            $this->processNamespaces($parent, $dir, $depth);
        }

        // Process pages (files)
        if ($this->hasNotFlag(self::FLAG_NO_PAGES)) {
            $this->processPages($parent, $dir);
        }
    }

    /**
     * Process namespaces (subdirectories) and add them to the hierarchy
     *
     * @param AbstractNode $parent Parent node to add children to
     * @param string $dir Current directory path
     * @param int $depth Current depth level
     * @return void
     */
    protected function processNamespaces(AbstractNode $parent, string $dir, int $depth)
    {
        global $conf;
        $base = $conf['datadir'] . '/';

        $dirs = glob($base . $dir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $subdir) {
            $subdir = basename($subdir);
            $id = pathID($dir . '/' . $subdir);

            $node = $this->createNamespaceNode($id, $subdir);

            // Recurse into subdirectory if depth and filter allows
            if ($depth !== 0 && $this->applyRecursionDecision($node, $this->maxdepth - $depth)) {
                $this->createHierarchy($node, $dir . '/' . $subdir, $depth - 1);
            }

            // Add to hierarchy
            $this->addNodeToHierarchy($parent, $node);
        }
    }

    /**
     * Create a namespace node based on the flags
     *
     * @param string $id
     * @param string $title
     * @return AbstractNode
     */
    protected function createNamespaceNode(string $id, string $title): AbstractNode
    {
        if ($this->hasFlag(self::FLAG_NS_AS_STARTPAGE)) {
            $ns = $id;
            $id = $this->getStartpage($id); // use the start page for the namespace
            $this->startpages[$id] = 1; // mark as seen
            $node = new WikiStartpage($id, $title, $ns);
        } else {
            $node = new WikiNamespace($id, $title);
        }
        return $node;
    }

    /**
     * Process pages (files) and add them to the hierarchy
     *
     * @param AbstractNode $parent Parent node to add children to
     * @param string $dir Current directory path
     * @return void
     */
    protected function processPages(AbstractNode $parent, string $dir)
    {
        global $conf;
        $base = $conf['datadir'] . '/';

        $files = glob($base . $dir . '/*.txt');
        foreach ($files as $file) {
            $file = basename($file);
            $id = pathID($dir . '/' . $file);

            // Skip already shown start pages
            if (isset($this->startpages[$id])) {
                continue;
            }

            $page = new WikiPage($id, $file);

            // Add to hierarchy
            $this->addNodeToHierarchy($parent, $page);
        }
    }

    /**
     * Run custom node processor and add it to the hierarchy
     *
     * @param AbstractNode $parent Parent node
     * @param AbstractNode $node Node to add
     * @return void
     */
    protected function addNodeToHierarchy(AbstractNode $parent, AbstractNode $node): void
    {
        $node->setParent($parent); // set the parent even when not added, yet
        $node = $this->applyNodeProcessor($node);
        if ($node instanceof AbstractNode) {
            $parent->addChild($node);
        }
    }

    /**
     * Get the start page for the given namespace
     *
     * @param string $ns The namespace to get the start page for
     * @return string The start page id
     */
    protected function getStartpage(string $ns): string
    {
        $id = $ns . ':';
        return (new PageResolver(''))->resolveId($id);
    }

    /**
     * Get the file path for the given namespace relative to the page directory
     *
     * @param string $namespace
     * @return string
     */
    protected function namespacePath(string $namespace): string
    {
        global $conf;

        $base = $conf['datadir'] . '/';
        $dir = wikiFN($namespace . ':xxx');
        $dir = substr($dir, strlen($base));
        $dir = dirname($dir); // remove the 'xxx' part
        if ($dir === '.') $dir = ''; // dirname returns '.' for root namespace
        return $dir;
    }

    /** @inheritdoc */
    protected function applyRecursionDecision(AbstractNode $node, int $depth): bool
    {
        // automatically skip hidden elements unless disabled by flag
        if (!$this->hasNotFlag(self::FLAG_KEEP_HIDDEN) && isHiddenPage($node->getId())) {
            return false;
        }
        return parent::applyRecursionDecision($node, $depth);
    }

    /** @inheritdoc */
    protected function applyNodeProcessor(AbstractNode $node): ?AbstractNode
    {
        // automatically skip hidden elements unless disabled by flag
        if (!$this->hasNotFlag(self::FLAG_KEEP_HIDDEN) && isHiddenPage($node->getId())) {
            return null;
        }
        return parent::applyNodeProcessor($node);
    }
}
