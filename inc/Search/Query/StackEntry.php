<?php

namespace dokuwiki\Search\Query;

/**
 * A typed entry on the QueryEvaluator's RPN evaluation stack
 *
 * Stack entries represent intermediate results during query evaluation.
 * Implementations are PageSet (concrete results), NamespacePredicate
 * (a filter), and NegatedEntry (logical NOT wrapper).
 */
interface StackEntry
{
}
