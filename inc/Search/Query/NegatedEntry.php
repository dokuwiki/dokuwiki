<?php

namespace dokuwiki\Search\Query;

/**
 * Wraps a StackEntry to indicate logical NOT
 *
 * NOT does not compute a complement immediately. Instead, binary operators
 * (AND, OR) detect NegatedEntry operands and choose the appropriate operation:
 * AND with a NegatedEntry becomes set subtraction, avoiding the need to
 * materialize the full page universe.
 */
class NegatedEntry implements StackEntry
{
    protected StackEntry $inner;

    public function __construct(StackEntry $inner)
    {
        $this->inner = $inner;
    }

    /**
     * @return StackEntry the wrapped entry (PageSet or NamespacePredicate)
     */
    public function getInner(): StackEntry
    {
        return $this->inner;
    }
}
