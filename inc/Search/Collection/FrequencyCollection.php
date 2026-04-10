<?php

namespace dokuwiki\Search\Collection;

/**
 * Abstract collection for frequency-based indexes
 *
 * In a frequency collection the same token can appear multiple times per entity. The frequency of each
 * token per entity is tracked.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
abstract class FrequencyCollection extends AbstractCollection
{
    /** @inheritdoc */
    protected function countTokens(array $tokens): array
    {
        return array_count_values($tokens);
    }
}
