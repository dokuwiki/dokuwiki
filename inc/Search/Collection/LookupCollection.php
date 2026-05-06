<?php

namespace dokuwiki\Search\Collection;

/**
 * Abstract collection for lookup-based indexes
 *
 * In a lookup collection each token appears at most once per entity (frequency is always 1).
 * Internally the same mechanisms as FrequencyCollection are used; only the way tokens are
 * processed on input differs (deduplication instead of counting).
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
abstract class LookupCollection extends AbstractCollection
{
    /** @inheritdoc */
    protected function countTokens(array $tokens): array
    {
        return array_fill_keys(array_unique($tokens), 1);
    }
}
