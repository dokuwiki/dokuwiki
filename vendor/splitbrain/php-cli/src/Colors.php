<?php

namespace splitbrain\phpcli;

/**
 * Class Colors
 *
 * Handles color output on (Linux) terminals
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @license MIT
 */
class Colors
{
    // these constants make IDE autocompletion easier, but color names can also be passed as strings
    const C_RESET = 'reset';
    const C_BLACK = 'black';
    const C_DARKGRAY = 'darkgray';
    const C_BLUE = 'blue';
    const C_LIGHTBLUE = 'lightblue';
    const C_GREEN = 'green';
    const C_LIGHTGREEN = 'lightgreen';
    const C_CYAN = 'cyan';
    const C_LIGHTCYAN = 'lightcyan';
    const C_RED = 'red';
    const C_LIGHTRED = 'lightred';
    const C_PURPLE = 'purple';
    const C_LIGHTPURPLE = 'lightpurple';
    const C_BROWN = 'brown';
    const C_YELLOW = 'yellow';
    const C_LIGHTGRAY = 'lightgray';
    const C_WHITE = 'white';

    /** @var array known color names */
    protected $colors = array(
        self::C_RESET => "\33[0m",
        self::C_BLACK => "\33[0;30m",
        self::C_DARKGRAY => "\33[1;30m",
        self::C_BLUE => "\33[0;34m",
        self::C_LIGHTBLUE => "\33[1;34m",
        self::C_GREEN => "\33[0;32m",
        self::C_LIGHTGREEN => "\33[1;32m",
        self::C_CYAN => "\33[0;36m",
        self::C_LIGHTCYAN => "\33[1;36m",
        self::C_RED => "\33[0;31m",
        self::C_LIGHTRED => "\33[1;31m",
        self::C_PURPLE => "\33[0;35m",
        self::C_LIGHTPURPLE => "\33[1;35m",
        self::C_BROWN => "\33[0;33m",
        self::C_YELLOW => "\33[1;33m",
        self::C_LIGHTGRAY => "\33[0;37m",
        self::C_WHITE => "\33[1;37m",
    );

    /** @var bool should colors be used? */
    protected $enabled = true;

    /**
     * Constructor
     *
     * Tries to disable colors for non-terminals
     */
    public function __construct()
    {
        if (function_exists('posix_isatty') && !posix_isatty(STDOUT)) {
            $this->enabled = false;
            return;
        }
        if (!getenv('TERM')) {
            $this->enabled = false;
            return;
        }
    }

    /**
     * enable color output
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * disable color output
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * @return bool is color support enabled?
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Convenience function to print a line in a given color
     *
     * @param string   $line    the line to print, a new line is added automatically
     * @param string   $color   one of the available color names
     * @param resource $channel file descriptor to write to
     *
     * @throws Exception
     */
    public function ptln($line, $color, $channel = STDOUT)
    {
        $this->set($color, $channel);
        fwrite($channel, rtrim($line) . "\n");
        $this->reset($channel);
    }

    /**
     * Returns the given text wrapped in the appropriate color and reset code
     *
     * @param string $text string to wrap
     * @param string $color one of the available color names
     * @return string the wrapped string
     * @throws Exception
     */
    public function wrap($text, $color)
    {
        return $this->getColorCode($color) . $text . $this->getColorCode('reset');
    }

    /**
     * Gets the appropriate terminal code for the given color
     *
     * @param string $color one of the available color names
     * @return string color code
     * @throws Exception
     */
    public function getColorCode($color)
    {
        if (!$this->enabled) {
            return '';
        }
        if (!isset($this->colors[$color])) {
            throw new Exception("No such color $color");
        }

        return $this->colors[$color];
    }

    /**
     * Set the given color for consecutive output
     *
     * @param string $color one of the supported color names
     * @param resource $channel file descriptor to write to
     * @throws Exception
     */
    public function set($color, $channel = STDOUT)
    {
        fwrite($channel, $this->getColorCode($color));
    }

    /**
     * reset the terminal color
     *
     * @param resource $channel file descriptor to write to
     *
     * @throws Exception
     */
    public function reset($channel = STDOUT)
    {
        $this->set('reset', $channel);
    }
}
