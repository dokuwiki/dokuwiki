<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Lessc;
use LesserPHP\Utils\Asserts;

/**
 * Implements the list functions for LESS
 *
 * @link https://lesscss.org/functions/#list-functions
 */
class Lists extends AbstractFunctionCollection
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            //'length' => [$this, 'length'],
            'extract' => [$this, 'extract'],
            //'range' => [$this, 'range'],
            //'each' => [$this, 'each'],
        ];
    }

    // length is missing

    /**
     * Returns the value at a specified position in a list
     *
     * @link https://lesscss.org/functions/#list-functions-extract
     * @throws Exception
     */
    public function extract(array $value)
    {
        [$list, $idx] = Asserts::assertArgs($value, 2, 'extract');
        $idx = Asserts::assertNumber($idx);
        // 1 indexed
        if ($list[0] == 'list' && isset($list[2][$idx - 1])) {
            return $list[2][$idx - 1];
        }

        // FIXME what is the expected behavior here? Apparently it's not an error?
    }

    // range is missing

    // each is missing
}
