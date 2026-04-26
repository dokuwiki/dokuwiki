<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * Defines a mode (syntax component) in the Parser
 */
interface ModeInterface
{
    /**
     * returns a number used to determine in which order modes are added
     *
     * @return int;
     */
    public function getSort();

    /**
     * Called before any calls to connectTo
     *
     * @return void
     */
    public function preConnect();

    /**
     * Connects the mode
     *
     * @param string $mode
     * @return void
     */
    public function connectTo($mode);

    /**
     * Called after all calls to connectTo
     *
     * @return void
     */
    public function postConnect();

    /**
     * Check if given mode is accepted inside this mode
     *
     * @param string $mode
     * @return bool
     */
    public function accepts($mode);

    /**
     * Handle a matched token from the lexer.
     *
     * @param string $match The matched text
     * @param int $state The lexer state (DOKU_LEXER_ENTER, _EXIT, _MATCHED, etc.)
     * @param int $pos Byte position in the source
     * @param Handler $handler The handler (for addCall, status, etc.)
     * @return bool
     */
    public function handle($match, $state, $pos, Handler $handler);
}
