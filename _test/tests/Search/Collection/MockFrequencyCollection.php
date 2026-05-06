<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\FrequencyCollection;

/**
 * A mock extending FrequencyCollection with custom index names for testing
 */
class MockFrequencyCollection extends FrequencyCollection
{
    /** @inheritdoc */
    public function __construct($entity = 'entity', $token = 'token', $freq = 'freq', $reverse = 'reverse')
    {
        parent::__construct($entity, $token, $freq, $reverse, true);
    }
}
