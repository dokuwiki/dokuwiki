<?php

namespace dokuwiki\Search\Query;

/**
 * A set of pages with associated scores
 *
 * Represents concrete search results where each page has a numeric score
 * (typically word frequency counts). Provides set operations for combining
 * results during query evaluation.
 */
class PageSet implements StackEntry
{
    /** @var array<string, int> page ID => score */
    protected array $pages;

    /**
     * @param array<string, int> $pages page ID => score
     */
    public function __construct(array $pages = [])
    {
        $this->pages = $pages;
    }

    /**
     * @return array<string, int> page ID => score
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * Intersect with another PageSet, summing scores for pages present in both
     *
     * @return self pages present in both sets
     */
    public function intersect(PageSet $other): self
    {
        $otherPages = $other->getPages();
        $result = [];
        foreach ($this->pages as $id => $score) {
            if (isset($otherPages[$id])) {
                $result[$id] = $score + $otherPages[$id];
            }
        }
        return new self($result);
    }

    /**
     * Unite with another PageSet, summing scores where pages overlap
     *
     * @return self pages present in either set
     */
    public function unite(PageSet $other): self
    {
        $result = $this->pages;
        foreach ($other->getPages() as $id => $score) {
            $result[$id] = ($result[$id] ?? 0) + $score;
        }
        return new self($result);
    }

    /**
     * Remove pages that exist in the other PageSet
     *
     * @return self pages in this set but not in $other
     */
    public function subtract(PageSet $other): self
    {
        return new self(array_diff_key($this->pages, $other->getPages()));
    }

    /**
     * @return bool true if this set contains no pages
     */
    public function isEmpty(): bool
    {
        return empty($this->pages);
    }
}
