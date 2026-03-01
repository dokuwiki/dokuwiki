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

class FormatterLessJs extends FormatterClassic
{
    public $disableSingle = true;
    public $breakSelectors = true;
    public $assignSeparator = ': ';
    public $selectorSeparator = ',';
}
