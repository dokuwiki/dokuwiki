<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\DirectCollection;

/**
 * A mock extending DirectCollection with custom index names for testing
 */
class MockDirectCollection extends DirectCollection
{
    /** @inheritdoc */
    public function __construct($entity = 'entity', $token = 'token')
    {
        parent::__construct($entity, $token);
    }
}
