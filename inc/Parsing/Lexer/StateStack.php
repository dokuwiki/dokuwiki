<?php
/**
 * Lexer adapted from Simple Test: http://sourceforge.net/projects/simpletest/
 * For an intro to the Lexer see:
 * https://web.archive.org/web/20120125041816/http://www.phppatterns.com/docs/develop/simple_test_lexer_notes
 *
 * @author Marcus Baker http://www.lastcraft.com
 */

namespace dokuwiki\Parsing\Lexer;

/**
 * States for a stack machine.
 */
class StateStack
{
    protected $stack;

    /**
     * Constructor. Starts in named state.
     * @param string $start        Starting state name.
     */
    public function __construct($start)
    {
        $this->stack = array($start);
    }

    /**
     * Accessor for current state.
     * @return string       State.
     */
    public function getCurrent()
    {
        return $this->stack[count($this->stack) - 1];
    }

    /**
     * Adds a state to the stack and sets it to be the current state.
     *
     * @param string $state        New state.
     */
    public function enter($state)
    {
        array_push($this->stack, $state);
    }

    /**
     * Leaves the current state and reverts
     * to the previous one.
     * @return boolean    false if we attempt to drop off the bottom of the list.
     */
    public function leave()
    {
        if (count($this->stack) == 1) {
            return false;
        }
        array_pop($this->stack);
        return true;
    }
}
