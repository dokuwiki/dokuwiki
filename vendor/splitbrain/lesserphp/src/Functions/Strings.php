<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Utils\Color;
use LesserPHP\Utils\Util;

/**
 * Implements the string functions for LESS
 *
 * @link https://lesscss.org/functions/#string-functions
 */
class Strings extends AbstractFunctionCollection
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            //'escape' => [$this, 'escape'],
            'e' => [$this, 'e'],
            '%' => [$this, 'format'],
            //'replace' => [$this, 'replace'],
        ];
    }


    // escape is missing

    /**
     * String escaping.
     *
     * It expects string as a parameter and return its content as is, but without quotes. It can be used
     * to output CSS value which is either not valid CSS syntax, or uses proprietary syntax which
     * Less doesn't recognize.
     *
     * @link https://lesscss.org/functions/#string-functions-e
     * @throws Exception
     */
    public function e(array $arg): array
    {
        return $this->lessc->unwrap($arg);
    }

    /**
     * Formats a string
     *
     * @link https://lesscss.org/functions/#string-functions--format
     * @throws Exception
     */
    public function format(array $args) : array
    {
        if ($args[0] != 'list') return $args;
        $values = $args[2];
        $string = array_shift($values);
        $template = $this->lessc->compileValue($this->lessc->unwrap($string));

        $i = 0;
        if (preg_match_all('/%[dsa]/', $template, $m)) {
            foreach ($m[0] as $match) {
                $val = isset($values[$i]) ?
                    $this->lessc->reduce($values[$i]) : ['keyword', ''];

                // lessjs compat, renders fully expanded color, not raw color
                if ($color = Color::coerceColor($val)) {
                    $val = $color;
                }

                $i++;
                $rep = $this->lessc->compileValue($this->lessc->unwrap($val));
                $template = preg_replace(
                    '/' . Util::pregQuote($match) . '/',
                    $rep,
                    $template,
                    1
                );
            }
        }

        $d = $string[0] == 'string' ? $string[1] : '"';
        return ['string', $d, [$template]];
    }

    // replace is missing
}
