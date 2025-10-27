<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Utils\Asserts;
use LesserPHP\Utils\Util;

/**
 * Implements the  miscellaneous functions for LESS
 *
 * @link https://lesscss.org/functions/#misc-functions
 */
class Misc extends AbstractFunctionCollection
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            //'color' => [$this, 'color'],
            //'image-size' => [$this, 'imageSize'],
            //'image-width' => [$this, 'imageWidth'],
            //'image-height' => [$this, 'imageHeight'],
            'convert' => [$this, 'convert'],
            'data-uri' => [$this, 'dataUri'],
            //'default' => [$this, 'default'],
            'unit' => [$this, 'unit'],
            //'get-unit' => [$this, 'getUnit'],
            //'svg-gradient' => [$this, 'svgGradient'],
        ];
    }

    // color is missing
    // image-size is missing
    // image-width is missing
    // image-height is missing

    /**
     * Convert a number from one unit into another
     *
     * @link https://lesscss.org/functions/#misc-functions-convert
     * @throws Exception
     */
    public function convert(array $args): array
    {
        [$value, $to] = Asserts::assertArgs($args, 2, 'convert');

        // If it's a keyword, grab the string version instead
        if (is_array($to) && $to[0] == 'keyword') {
            $to = $to[1];
        }

        return Util::convert($value, $to);
    }

    /**
     * Given an url, decide whether to output a regular link or the base64-encoded contents of the file
     *
     * @param array $value either an argument list (two strings) or a single string
     * @return string        formatted url(), either as a link or base64-encoded
     */
    public function dataUri(array $value): string
    {
        $mime = ($value[0] === 'list') ? $value[2][0][2] : null;
        $url = ($value[0] === 'list') ? $value[2][1][2][0] : $value[2][0];

        $fullpath = $this->lessc->findImport($url);

        if ($fullpath && ($fsize = filesize($fullpath)) !== false) {
            // IE8 can't handle data uris larger than 32KB
            if ($fsize / 1024 < 32) {
                if (is_null($mime)) {
                    if (class_exists('finfo')) { // php 5.3+
                        $finfo = new \finfo(FILEINFO_MIME);
                        $mime = explode('; ', $finfo->file($fullpath));
                        $mime = $mime[0];
                    } elseif (function_exists('mime_content_type')) { // PHP 5.2
                        $mime = mime_content_type($fullpath);
                    }
                }

                if (!is_null($mime)) // fallback if the mime type is still unknown
                    $url = sprintf('data:%s;base64,%s', $mime, base64_encode(file_get_contents($fullpath)));
            }
        }

        return 'url("' . $url . '")';
    }

    // default is missing


    /**
     * Remove or change the unit of a dimension
     *
     * @link https://lesscss.org/functions/#misc-functions-unit
     * @throws Exception
     */
    public function unit(array $arg): array
    {
        if ($arg[0] == 'list') {
            [$number, $newUnit] = $arg[2];
            return [
                'number',
                Asserts::assertNumber($number),
                $this->lessc->compileValue($this->lessc->unwrap($newUnit))
            ];
        } else {
            return ['number', Asserts::assertNumber($arg), ''];
        }
    }

    // get-unit is missing
    // svg-gradient is missing
}
