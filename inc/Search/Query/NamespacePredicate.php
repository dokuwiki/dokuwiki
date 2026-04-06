<?php

namespace dokuwiki\Search\Query;

/**
 * A namespace prefix used to filter pages by their ID
 *
 * The prefix always includes a trailing colon (e.g., "wiki:") to ensure
 * exact namespace matching — "wiki:" won't match "wikipedia:page".
 */
class NamespacePredicate implements StackEntry
{
    protected string $prefix;

    /**
     * @param string $prefix namespace prefix including trailing colon
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string the namespace prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Keep only pages whose ID starts with this namespace prefix
     */
    public function filter(PageSet $pages): PageSet
    {
        $result = [];
        foreach ($pages->getPages() as $id => $score) {
            if (str_starts_with($id, $this->prefix)) {
                $result[$id] = $score;
            }
        }
        return new PageSet($result);
    }

    /**
     * Keep only pages whose ID does NOT start with this namespace prefix
     */
    public function exclude(PageSet $pages): PageSet
    {
        $result = [];
        foreach ($pages->getPages() as $id => $score) {
            if (!str_starts_with($id, $this->prefix)) {
                $result[$id] = $score;
            }
        }
        return new PageSet($result);
    }
}
