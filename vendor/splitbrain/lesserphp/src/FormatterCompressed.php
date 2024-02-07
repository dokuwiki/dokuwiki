<?php
/**
 * http://leafo.net/lessphp
 *
 * LESS CSS compiler, adapted from http://lesscss.org
 *
 * Copyright 2013, Leaf Corcoran <leafot@gmail.com>
 * Copyright 2016, Marcus Schwarz <github@maswaba.de>
 * Licensed under MIT or GPLv3, see LICENSE
 */


namespace LesserPHP;

class FormatterCompressed extends FormatterClassic
{
    public $disableSingle = true;
    public $open = '{';
    public $selectorSeparator = ',';
    public $assignSeparator = ':';
    public $break = '';
    public $compressColors = true;

    public function indentStr($n = 0)
    {
        return '';
    }
}
