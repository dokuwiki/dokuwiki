<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\LookupCollection;

/**
 * A mock extending LookupCollection with custom index names for testing
 */
class MockLookupCollection extends LookupCollection
{
    /** @inheritdoc */
    public function __construct($entity = 'entity', $token = 'token', $freq = 'freq', $reverse = 'reverse')
    {
        parent::__construct($entity, $token, $freq, $reverse);
    }
}
