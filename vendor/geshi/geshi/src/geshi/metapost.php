<?php
/*************************************************************************************
 * metapost.php
 * -----------
 * Author: Maxime Chupin (notezik@gmail.com)
 * Copyright: (c) 2011 Maxime Chupin
 * Release Version: 1.0.9.1
 * Date Started: 2011/08/02
 *
 * Metapost language file for GeSHi.
 *
 * https://en.wikipedia.org/wiki/MetaPost
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'MetaPost',
    'COMMENT_SINGLE' => array(1 => '%'),
    'COMMENT_MULTI' => array(
        'verbatim'=>'etex', //TeX and LaTeX preambule
        'btex' => 'etex' //TeX invocation
    ),
    'COMMENT_REGEXP' => array(
    ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array( //type
            'boolean',
            'color', 'cmykcolor',
            'expr',
            'numeric',
            'pair', 'path', 'pen', 'picture',
            'string', 'suffix',
            'text', 'transform',
        ),
        2 => array( //file construction
            'beginfig', 'begingroup',
            'def',
            'end', 'enddef', 'endfig', 'endgroup',
            'hide',
            'image', 'input',
            'let',
            'makepen', 'makepath',
            'newinternal',
            'primary', 'primarydef',
            'save', 'secondarydef', 'shipout', 'special',
            'tertiarydef',
            'vardef'
        ),
        3 => array( //programmation structure
            'else', 'elseif', 'endfor', 'exitif', 'exitunless',
            'fi', 'for', 'forever', 'forsuffix',
            'if',
            'step',
            'until', 'upto',
        ),
        4 => array( //operations return pair
            'bot',
            'dir', 'direction of',
            'intersectionpoint', 'intiersectiontimes',
            'lft', 'llcorner', 'lrcorner',
            'penoffset of', 'point of', 'postcontrol of', 'precontrol of',
            'rt',
            'top',
            'ulcorner', 'unitvector', 'urcorner',
            'z',
        ),
        5 => array( //operations return path or picture or pen
            'bbox',
            'center', 'cutafter', 'cutbefore',
            'dashpart', 'dashpattern',
            'glyph of',
            'infont',
            'pathpart', 'penpart',
            'reverse',
            'subpath of',
        ),
        6 => array( //operations return string (or complementary)
            'closefrom',
            'fontpart',
            'readfrom',
            'str', 'substring of',
            'textpart'
        ),
        7 => array( // operations return numeric
            'abs', 'angle', 'arclength', 'arctime of', 'ASCII',
            'blackpart', 'bluepart',
            'ceiling', 'char', 'colormodel', 'cosd', 'cyanpart',
            'decimal', 'decr', 'directionpoint of', 'directiontime of',
            'div', 'dotprod',
            'floor', 'fontsize',
            'greenpart', 'greypart',
            'hex',
            'incr',
            'length',
            'magentapart', 'max', 'mexp', 'min', 'mlog', 'mod',
            'normaldeviate',
            'oct',
            'redpart', 'round',
            'sind', 'sqrt',
            'uniformdeviate',
            'xpart', 'xxpart', 'xypart',
            'yellowpart', 'ypart', 'yxpart', 'yypart',
        ),
        8 => array( // operations return boolean
            'and',
            'bounded',
            'clipped',
            'filled',
            'known',
            'not',
            'odd',
            'or',
            'rgbcolor',
            'stroked',
            'textual',
            'unknown'
        ),
        9 => array( //operations return color
            'colorpart'
        ),
        10 => array( //operations return transform
            'inverse'
        ),
        11 => array( //constructors
            'also',
            'buildcycle',
            'contour', 'controls', 'cycle',
            'doublepath',
            'setbounds',
            'to',
            'whatever'
        ),
        12 => array( //labels
            'label',
            'label.bot',
            'label.top',
            'label.llft',
            'label.lft',
            'label.ulft',
            'label.lrt',
            'label.rt',
            'label.urt',

            'labels',
            'labels.bot',
            'labels.top',
            'labels.llft',
            'labels.lft',
            'labels.ulft',
            'labels.lrt',
            'labels.rt',
            'labels.urt',

            'thelabel',
            'thelabel.bot',
            'thelabel.top',
            'thelabel.llft',
            'thelabel.lft',
            'thelabel.ulft',
            'thelabel.lrt',
            'thelabel.rt',
            'thelabel.urt',

            'dotlabel',
            'dotlabel.bot',
            'dotlabel.top',
            'dotlabel.llft',
            'dotlabel.lft',
            'dotlabel.ulft',
            'dotlabel.lrt',
            'dotlabel.rt',
            'dotlabel.urt',
        ),
        13 => array( //general transformations
            'about',
            'reflected', 'reflectedaround',
            'rotated', 'rotatedabout', 'rotatedaround',
            'scaled', 'slanted', 'shifted',
            'transformed',
            'xscaled',
            'yscaled',
            'zscaled',
        ),
        14 => array( //draw instructions
            'addto',
            'clip', 'cutdraw',
            'draw', 'drawarrow', 'drawdblarrow', 'drawdot',
            'fill', 'filldraw',
            'undraw', 'unfill', 'unfilldraw'
        ),
        15 => array( //style of drawing
            'curl',
            'dashed', 'drawoptions',
            'pickup',
            'tension',
            'withcmykcolor', 'withcolor',
            'withgreyscale', 'withpen', 'withpostscript', 'withprescript',
            'withrgbcolor',
        ),
        16 => array( //read write show
            'errhelp', 'errmessage',
            'fontmapfile', 'fontmapline',
            'interim',
            'loggingall',
            'message',
            'scantokens', 'show', 'showdependencies', 'showtoken', 'showvariable',
            'tracingall', 'tracingnone',
            'write to',
        ),
        17 => array( //Internal variables with numeric values
            'ahangle', 'ahlength',
            'bboxmargin',
            'charcode',
            'day', 'defaultcolormodel', 'defaultpen', 'defaultscale',
            'dotlabeldiam',
            'hour',
            'labeloffset',
            'linecap', 'linejoin',
            'minute', 'miterlimit', 'month', 'mpprocset',
            'pausing', 'prologues',
            'restoreclipcolor',
            'showstopping',
            'time',
            'tracingcapsules', 'tracingchoices', 'tracingcommands',
            'tracingequations', 'tracinglostchars', 'tracingmacros',
            'tracingonline', 'tracingoutput', 'tracingrestores',
            'tracingspecs', 'tracingstats', 'tracingtitles',
            'troffmode', 'truecorners',
            'warningcheck',
            'year',
        ),
        18 => array( //Internal string variables
            'filenametemplate',
            'jobname',
            'outputformat', 'outputtemplate',
        ),
        19 => array( //other predefined variables
            'background',
            'currentpen', 'currentpicture', 'cuttings',
            'defaultfont',
            'extra_beginfig', 'extra_endfig',
        ),
        20 => array( //predefined constants
            'beveled', 'black', 'blue', 'bp', 'butt',
            'cc', 'cm',
            'dd', 'ditto', 'down',
            'epsilon', 'evenly', 'EOF',
            'false', 'fullcircle',
            'green',
            'halfcircle',
            'identity',
            'left',
            'mitered', 'mm', 'mpversion',
            'nullpen', 'nullpicture',
            'origin',
            'pc', 'pencircle', 'pensquare', 'pt',
            'quartercircle',
            'red', 'right', 'rounded',
            'squared',
            'true',
            'unitsquare', 'up',
            'white', 'withdots',
        )
    ),
    'SYMBOLS' => array(
        '&', ':=', '=', '+', '-',
        '*', '**', '/', '++', '+-+',
        '<', '>', '>=', '<=', '<>',
        '#@', '@', '@#'
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true,
        5 => true,
        6 => true,
        7 => true,
        8 => true,
        9 => true,
        10 => true,
        11 => true,
        12 => true,
        13 => true,
        14 => true,
        15 => true,
        16 => true,
        17 => true,
        18 => true,
        19 => true,
        20 => true
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1  => 'color: #472;', //type
            2  => 'color: #35A;font-weight: bold;', //file construction
            3  => 'color: #A53;', //structure
            4  => 'color: #35A;', //operations return pair
            5  => 'color: #35A;', //operations return path or picture or pen
            6  => 'color: #35A;', //operations return string
            7  => 'color: #35A;', //operations return numeric
            8  => 'color: #35A;', //operations return boolean
            9  => 'color: #35A;', //operations return color
            10 => 'color: #35A;', //operations return transform
            11 => 'color: #35A;', //constructors
            12 => 'color: #35A;', //labels
            13 => 'color: #3B5;', //general transformations
            14 => 'color: #35A;', //draw instructions
            15 => 'color: #472;', //style of drawing
            16 => 'color: #000;', //read write show
            17 => 'color: #000;', //Internal variables with numeric values
            18 => 'color: #000;', //Internal string variables
            19 => 'color: #000;', //other predefined variables
            20 => 'color: #000;'  //predefined constants
        ),
        'COMMENTS' => array(
            1 => 'color: #777;',
            'MULTI' => 'color: #880;'
        ),
        'ESCAPE_CHAR' => array(
            0 => ''
        ),
        'BRACKETS' => array(
            0 => 'color: #820;'
        ),
        'STRINGS' => array(
            0 => 'color: #880;'
        ),
        'NUMBERS' => array(
            0 => 'color: #000;'
        ),
        'METHODS' => array(
            1 => '',
            2 => ''
        ),
        'SYMBOLS' => array(
            0 => 'color: #000;'
        ),
        'REGEXPS' => array(
        ),
        'SCRIPT' => array(
            0 => ''
        )
    ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
        8 => '',
        9 => '',
        10 => '',
        11 => '',
        12 => '',
        13 => '',
        14 => '',
        15 => '',
        16 => '',
        17 => '',
        18 => '',
        19 => '',
        20 => ''
    ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
    ),
    'REGEXPS' => array(
    ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
    ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
    )
);
