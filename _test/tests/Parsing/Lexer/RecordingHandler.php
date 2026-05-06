<?php

namespace dokuwiki\test\Parsing\Lexer;

use dokuwiki\Parsing\Handler;

/**
 * Handler subclass that records all handleToken calls for later assertion
 * instead of dispatching to mode objects.
 */
class RecordingHandler extends Handler
{
    /** @var array[] each entry is [method, match, state, pos] */
    public array $recorded = [];

    /** @inheritdoc */
    public function handleToken($modeName, $match, $state, $pos, $originalModeName = '')
    {
        $this->recorded[] = [$modeName, $match, $state, $pos];
        return true;
    }
}
