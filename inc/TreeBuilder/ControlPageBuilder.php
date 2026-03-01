<?php

namespace dokuwiki\TreeBuilder;

use dokuwiki\File\PageResolver;
use dokuwiki\TreeBuilder\Node\AbstractNode;
use dokuwiki\TreeBuilder\Node\ExternalLink;
use dokuwiki\TreeBuilder\Node\Top;
use dokuwiki\TreeBuilder\Node\WikiPage;

/**
 * A tree builder that generates a tree from a control page
 *
 * A control page is a wiki page containing a nested list of external and internal links. This builder
 * parses the control page and generates a tree of nodes representing the links.
 */
class ControlPageBuilder extends AbstractBuilder
{
    /** @var int do not include internal links */
    public const FLAG_NOINTERNAL = 1;
    /** @var int do not include external links */
    public const FLAG_NOEXTERNAL = 2;

    /** @var string */
    protected string $controlPage;
    /** @var int */
    protected int $flags = 0;

    /**
     * Parse the control page
     *
     * Check the flag constants on how to influence the behaviour
     *
     * @param string $controlPage
     * @param int $flags
     */
    public function __construct(string $controlPage)
    {
        $this->controlPage = $controlPage;
    }

    /**
     * @inheritdoc
     * @todo theoretically it should be possible to also take the recursionDecision into account, even though
     *       we don't recurse here. Passing the depth would be easy, but actually aborting going deeper is difficult.
     */
    public function generate(): void
    {
        $this->top = new Top();
        $instructions = p_cached_instructions(wikiFN($this->controlPage));
        if (!$instructions) {
            throw new \RuntimeException('No instructions for control page found');
        }

        $parents = [
            0 => $this->top
        ];
        $level = 0;

        $resolver = new PageResolver($this->controlPage);

        foreach ($instructions as $instruction) {
            switch ($instruction[0]) {
                case 'listu_open':
                    $level++; // new list level
                    break;
                case 'listu_close':
                    // if we had a node on this level, remove it from the parents
                    if (isset($parents[$level])) {
                        unset($parents[$level]);
                    }
                    $level--; // close list level
                    break;
                case 'internallink':
                case 'externallink':
                    if ($instruction[0] == 'internallink') {
                        if ($this->flags & self::FLAG_NOINTERNAL) break;

                        $newpage = new WikiPage(
                            $resolver->resolveId($instruction[1][0]),
                            $instruction[1][1]
                        );
                    } else {
                        if ($this->flags & self::FLAG_NOEXTERNAL) break;

                        $newpage = new ExternalLink(
                            $instruction[1][0],
                            $instruction[1][1]
                        );
                    }

                    if ($level) {
                        // remember this page as the parent for this level
                        $parents[$level] = $newpage;
                        // parent is the last page on the previous level
                        // levels may not be evenly distributed, so we need to check the count
                        $parent = $parents[count($parents) - 2];
                    } else {
                        // not in a list, so parent is always the top
                        $parent = $this->top;
                    }

                    $newpage->setParent($parent);
                    $newpage = $this->applyNodeProcessor($newpage);
                    if ($newpage instanceof AbstractNode) {
                        $parent->addChild($newpage);
                    }
                    break;
            }
        }

        $this->generated = true;
    }
}
