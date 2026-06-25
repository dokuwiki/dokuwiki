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

    /**
     * The Lexer tests drive the lexer directly and never go through mode
     * dispatch, so this handler needs no ModeRegistry. Skip the parent
     * constructor's registry requirement and just set up the buffers.
     */
    public function __construct()
    {
        $this->reset();
    }

    /** @inheritdoc */
    public function handleToken($modeName, $match, $state, $pos, $originalModeName = '')
    {
        $this->recorded[] = [$modeName, $match, $state, $pos];
        return true;
    }
}
