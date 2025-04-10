<?php

namespace dokuwiki\TreeBuilder;

use dokuwiki\TreeBuilder\Node\AbstractNode;
use dokuwiki\TreeBuilder\Node\WikiNamespace;
use dokuwiki\Utf8\Sort;

/**
 * Class that provides comparators for sorting the tree nodes
 */
class TreeSort
{
    public const SORT_BY_ID = [self::class, 'sortById'];
    public const SORT_BY_TITLE = [self::class, 'sortByTitle'];
    public const SORT_BY_NS_FIRST_THEN_ID = [self::class, 'sortByNsFirstThenId'];
    public const SORT_BY_NS_FIRST_THEN_TITLE = [self::class, 'sortByNsFirstThenTitle'];

    /**
     * Comparator to sort by ID
     *
     * @param AbstractNode $a
     * @param AbstractNode $b
     * @return int
     */
    public static function sortById(AbstractNode $a, AbstractNode $b): int
    {
        // we need to compare the ID segment by segment
        $pathA = explode(':', $a->getId());
        $pathB = explode(':', $b->getId());
        $min = min(count($pathA), count($pathB));

        for ($i = 0; $i < $min; $i++) {
            if ($pathA[$i] !== $pathB[$i]) {
                return $pathA[$i] <=> $pathB[$i];
            }
        }
        return count($pathA) <=> count($pathB);
    }


    /**
     * Comparator to sort namespace first, then by ID
     *
     * @param AbstractNode $a
     * @param AbstractNode $b
     * @return int
     */
    public static function sortByNsFirstThenId(AbstractNode $a, AbstractNode $b): int
    {
        $res = self::sortByNsFirst($a, $b);
        if ($res === 0) $res = self::sortById($a, $b);
        return $res;
    }

    /**
     * Comparator to sort by title (using natural sort)
     *
     * @param AbstractNode $a
     * @param AbstractNode $b
     * @return int
     */
    public static function sortByTitle(AbstractNode $a, AbstractNode $b): int
    {
        return Sort::strcmp($a->getTitle(), $b->getTitle());
    }

    /**
     * Comparator to sort namespace first, then by title
     *
     * @param AbstractNode $a
     * @param AbstractNode $b
     * @return int
     */
    public static function sortByNsFirstThenTitle(AbstractNode $a, AbstractNode $b): int
    {
        $res = self::sortByNsFirst($a, $b);
        if ($res === 0) $res = self::sortByTitle($a, $b);
        return $res;
    }

    /**
     * Comparator to sort by namespace first
     *
     * @param AbstractNode $a
     * @param AbstractNode $b
     * @return int
     */
    protected static function sortByNsFirst(AbstractNode $a, AbstractNode $b): int
    {
        $isAaNs = ($a instanceof WikiNamespace);
        $isBaNs = ($b instanceof WikiNamespace);

        if ($isAaNs !== $isBaNs) {
            if ($isAaNs) {
                return -1;
            } elseif ($isBaNs) {
                return 1;
            }
        }
        return 0;
    }
}
