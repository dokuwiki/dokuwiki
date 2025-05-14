<?php

namespace dokuwiki\TreeBuilder\Node;

/**
 * A node representing the top of the tree
 *
 * This node has no parents or siblings. It is used to represent the root of the tree.
 */
class Top extends AbstractNode
{
    public function __construct()
    {
        parent::__construct('', '');
    }

    /**
     * Always returns an empty array
     * @inheritdoc
     */
    public function getSiblings(): array
    {
        return [];
    }

    /**
     * Always returns an empty array
     * @inheritdoc
     */
    public function getParents(): array
    {
        return [];
    }

    /** @inheritdoc */
    public function getHtmlLink(): string
    {
        return '';
    }
}
