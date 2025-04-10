<?php

namespace dokuwiki\TreeBuilder;

use dokuwiki\test\mock\Doku_Renderer;
use dokuwiki\TreeBuilder\Node\AbstractNode;
use dokuwiki\TreeBuilder\Node\ExternalLink;
use dokuwiki\TreeBuilder\Node\Top;

/**
 * Abstract class to generate a tree
 */
abstract class AbstractBuilder
{
    protected bool $generated = false;

    /** @var AbstractNode[] flat list of all nodes the generator found */
    protected array $nodes = [];

    /** @var Top top level element to access the tree */
    protected Top $top;

    /** @var callable|null A callback to modify or filter out nodes */
    protected $nodeProcessor;

    /** @var callable|null A callback to decide if recursion should happen */
    protected $recursionDecision;

    /**
     * @var int configuration flags
     */
    protected int $flags = 0;

    /**
     * Generate the page tree. Needs to be called once the object is created.
     *
     * Sets the $generated flag to true.
     *
     * @return void
     */
    abstract public function generate(): void;

    /**
     * Set a callback to set additional properties on the nodes
     *
     * The callback receives a Node as parameter and must return a Node.
     * If the callback returns null, the node will not be added to the tree.
     * The callback may use the setProperty() method to set additional properties on the node.
     * The callback can also return a completely different node, which will be added to the tree instead
     * of the original node.
     *
     * @param callable|null $builder A callback to set additional properties on the nodes
     */
    public function setNodeProcessor(?callable $builder): void
    {
        if ($builder !== null && !is_callable($builder)) {
            throw new \InvalidArgumentException('Property builder must be callable');
        }
        $this->nodeProcessor = $builder;
    }

    /**
     * Set a callback to decide if recursion should happen
     *
     * The callback receives a Node as parameter and the current recursion depth.
     * The node will NOT have it's children set.
     * The callback must return true to have any children added, false to skip them.
     *
     * @param callable|null $filter
     * @return void
     */
    public function setRecursionDecision(?callable $filter): void
    {
        if ($filter !== null && !is_callable($filter)) {
            throw new \InvalidArgumentException('Recursion-filter must be callable');
        }
        $this->recursionDecision = $filter;
    }

    /**
     * Add a configuration flag
     *
     * @param int $flag
     * @return void
     */
    public function addFlag(int $flag): void
    {
        $this->flags |= $flag;
    }

    /**
     * Check if a flag is set
     *
     * @param int $flag
     * @return bool
     */
    public function hasFlag(int $flag): bool
    {
        return ($this->flags & $flag) === $flag;
    }

    /**
     * Check if a flag is NOT set
     *
     * @param int $flag
     * @return bool
     */
    public function hasNotFlag(int $flag): bool
    {
        return ($this->flags & $flag) !== $flag;
    }

    /**
     * Remove a configuration flag
     *
     * @param int $flag
     * @return void
     */
    public function removeFlag(int $flag): void
    {
        $this->flags &= ~$flag;
    }

    /**
     * Access the top element
     *
     * Use it's children to iterate over the page hierarchy
     *
     * @return Top
     */
    public function getTop(): Top
    {
        if (!$this->generated) throw new \RuntimeException('need to call generate() first');
        return $this->top;
    }

    /**
     * Get a flat list of all nodes in the tree
     *
     * This is a cached version of top->getDescendants() with the ID as key of the returned array.
     *
     * @return AbstractNode[]
     */
    public function getAll(): array
    {
        if (!$this->generated) throw new \RuntimeException('need to call generate() first');
        if ($this->nodes === []) {
            $this->nodes = [];
            foreach ($this->top->getDescendants() as $node) {
                $this->nodes[$node->getId()] = $node;
            }
        }

        return $this->nodes;
    }

    /**
     * Get a flat list of all nodes that do NOT have children
     *
     * @return AbstractNode[]
     */
    public function getLeaves(): array
    {
        if (!$this->generated) throw new \RuntimeException('need to call generate() first');
        return array_filter($this->getAll(), fn($page) => !$page->getChildren());
    }

    /**
     * Get a flat list of all nodes that DO have children
     *
     * @return AbstractNode[]
     */
    public function getBranches(): array
    {
        if (!$this->generated) throw new \RuntimeException('need to call generate() first');
        return array_filter($this->getAll(), fn($page) => (bool) $page->getChildren());
    }

    /**
     * Sort the tree
     *
     * The given comparator function will be called with two nodes as arguments and needs to
     * return an integer less than, equal to, or greater than zero if the first argument is considered
     * to be respectively less than, equal to, or greater than the second.
     *
     * Pass in one of the TreeSort comparators or your own.
     *
     * @param callable $comparator
     * @return void
     */
    public function sort(callable $comparator): void
    {
        if (!$this->generated) throw new \RuntimeException('need to call generate() first');
        $this->top->sort($comparator);
        $this->nodes = []; // reset the cache
    }

    /**
     * Render the tree on the given renderer
     *
     * This is mostly an example implementation. You probably want to implement your own.
     *
     * @param Doku_Renderer $R The current renderer
     * @param AbstractNode $top The node to start from, use null to start from the top node
     * @param int $level current nesting level, starting at 1
     * @return void
     */
    public function render(Doku_Renderer $R, $top = null, $level = 1): void
    {
        if ($top === null) $top = $this->getTop();

        $R->listu_open();
        foreach ($top->getChildren() as $node) {
            $R->listitem_open(1, $node->hasChildren());
            $R->listcontent_open();
            if ($node instanceof ExternalLink) {
                $R->externallink($node->getId(), $node->getTitle());
            } else {
                $R->internallink($node->getId(), $node->getTitle());
            }
            $R->listcontent_close();
            if ($node->hasChildren()) {
                $this->render($R, $node, $level + 1);
            }
            $R->listitem_close();
        }
        $R->listu_close();
    }

    /**
     * @param AbstractNode $node
     * @return AbstractNode|null
     */
    protected function applyNodeProcessor(AbstractNode $node): ?AbstractNode
    {
        if ($this->nodeProcessor === null) return $node;
        $result = call_user_func($this->nodeProcessor, $node);
        if (!$result instanceof AbstractNode) return null;
        return $result;
    }

    /**
     * @param AbstractNode $node
     * @return bool should children be added?
     */
    protected function applyRecursionDecision(AbstractNode $node, int $depth): bool
    {
        if ($this->recursionDecision === null) return true;
        return (bool)call_user_func($this->recursionDecision, $node, $depth);
    }

    /**
     * "prints" the tree
     *
     * @return array
     */
    public function __toString(): string
    {
        return implode("\n", $this->getAll());
    }
}
