<?php
/*************************************************************************************
 * c.php
 * -----
 * Author: Benny Baumann (BenBE@geshi.org)
 * Copyright: (c) 2014 Benny Baumann (http://qbnz.com/highlighter/)
 * Release Version: 1.0.9.0
 * Date Started: 2014/08/10
 *
 * PostScript language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2014/08/10 (1.0.8.13)
 *   -  First Release
 *
 * TODO (updated 2014/08/10)
 * -------------------------
 *  -  Get a list of inbuilt functions to add
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
    'LANG_NAME' => 'PostScript',
    'COMMENT_SINGLE' => array(0 => '%'),
    'COMMENT_MULTI' => array(), //array('/*' => '*/'),
    'COMMENT_REGEXP' => array(
        // Strings
        1 => "/\((?:\\\\[0-7]{3}|\\\\.|(?R)|[^)])*\)/s",
        // Hex Strings
        2 => "/<(?!<)[0-9a-f\s]*>/si",
        // ASCII-85 Strings
        3 => "/<~.*~>/si",
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'ESCAPE_REGEXP' => array(
        ),
    'NUMBERS' => array(
        0 => GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_FLT_NONSCI | GESHI_NUMBER_FLT_NONSCI_F | GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,
        1 => "\d+#[0-9a-zA-Z]+"
        ),
    'KEYWORDS' => array(
        1 => array(
            'countexecstack', 'def', 'dup', 'exch', 'exec', 'execstack', 'exit',
            'for', 'if', 'ifelse', 'loop', 'pop', 'repeat',

            'abs', 'add', 'atan', 'ceiling', 'cos', 'div', 'exp', 'floor',
            'idiv', 'ln', 'log', 'mul', 'mod', 'neg', 'rand', 'round', 'rrand',
            'sin', 'sqrt', 'srand', 'sub', 'truncate',

            'and', 'bitshift', 'eq', 'ge', 'gt', 'le', 'lt', 'ne', 'not', 'or', 'xor',
            ),
        2 => array(
            'false', 'null', 'true', 'version'
            ),
        3 => array(
            'quit', 'start', 'stop', 'stopped',

            'clear', 'cleartomark', 'copy', 'count', 'counttomark', 'index', 'roll',

            'aload', 'astore', 'begin', 'countdictstack', 'currentdict',
            'dictstack', 'end', 'errordict', 'forall', 'get',
            'getinterval', 'known', 'length', 'load', 'maxlength', 'put',
            'putinterval', 'store', 'systemdict', 'userdict', 'where',

            'anchorsearch', 'search', 'token',

            'cvi', 'cvlit', 'cvn', 'cvr', 'cvrs', 'cvs', 'cvx', 'executeonly',
            'noaccess', 'rcheck', 'readonly', 'type', 'wcheck', 'xcheck',

            'bytesavailable', 'closefile', 'currentfile', 'echo', 'file',
            'flush', 'flushfile', 'print', 'prompt', 'pstack', 'read',
            'readhexstring', 'readline', 'readstring', 'resetfile', 'restore',
            'run', 'save', 'stack', 'status', 'vmstatus', 'write',
            'writehexstring', 'writestring',

            'bind', 'usertime',

            'currentdash', 'currentflat', 'currentgray', 'currenthsbcolor',
            'currentlinecap', 'currentlinejoin', 'currentlinewidth',
            'currentmiterlimit', 'currentrgbcolor', 'currentscreen',
            'currenttransfer', 'grestore', 'grestoreall', 'gsave',
            'initgraphics', 'proc', 'setdash', 'setflat', 'setgray',
            'sethsbcolor', 'setlinecap', 'setlinejoin', 'setlinewidth',
            'setmiterlimit', 'setrgbcolor', 'setscreen', 'settransfer',

            'concat', 'concatmatrix', 'currentmatrix', 'defaultmatrix',
            'dtransform', 'identmatrix', 'idtransform', 'initmatrix',
            'invertmatrix', 'itransform', 'rotate', 'scale', 'setmatrix',
            'transform', 'translate',

            'arc', 'arcn', 'arcto', 'charpath', 'clip', 'clippath', 'closepath',
            'currentpoint', 'curveto', 'eoclip', 'eofill', 'erasepage', 'fill',
            'flattenpath', 'image', 'imagemask', 'initclip', 'lineto', 'moveto',
            'newpath', 'pathbbox', 'pathforall', 'rcurveto', 'reversepath',
            'rlineto', 'rmoveto', 'stroke', 'strokepath',

            'banddevice', 'copypage', 'framedevice', 'nulldevice', 'renderbands',
            'showpage',

            'ashow', 'awidthshow', 'currentfont', 'definefont', 'findfont',
            'fontdict', 'kshow', 'makefont', 'scalefont', 'setfont', 'show',
            'stringwidth', 'widthshow', 'FontDirectory', 'StandardEncoding',

            'cachestatus', 'setcachedevice', 'setcachelimit', 'setcharwidth',

            'dictfull', 'dictstackoverflow', 'dictstackunderflow',
            'execstackoverflow', 'handleerror', 'interrupt', 'invalidaccess',
            'invalidexit', 'invalidfileaccess', 'invalidfont', 'invalidrestore',
            'ioerror', 'limitcheck', 'nocurrentpoint', 'rangecheck',
            'stackoverflow', 'stackunderflow', 'syntaxerror', 'timeout',
            'typecheck', 'undefined', 'undefinedfilename', 'undefinedresult',
            'unmatchedmark', 'unregistered', 'VMerror'
            ),
        4 => array(
            'array', 'dict', 'mark', 'matrix', 'string'
            ),
        ),
    'SYMBOLS' => array(
        0 => array('==', '=', '/', '//'),
        1 => array('[', ']'),
        2 => array('{', '}'),
        3 => array('<<', '>>')
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000066; font-weight: bold;',
            2 => 'color: #0000ff; font-weight: bold;',
            3 => 'color: #000000; font-weight: bold;',
            4 => 'color: #993333; font-weight: bold;'
            ),
        'COMMENTS' => array(
            0 => 'color: #333333; font-style: italic;',
            1 => 'color: #339933;',
            2 => 'color: #006600;',
            3 => 'color: #666666;',
            'MULTI' => 'color: #808080; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            'HARD' => ''
            ),
        'BRACKETS' => array(
            0 => 'color: #009900;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #0000dd;',
            GESHI_NUMBER_BIN_PREFIX_0B => 'color: #208080;',
            GESHI_NUMBER_OCT_PREFIX => 'color: #208080;',
            GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
            GESHI_NUMBER_FLT_SCI_SHORT => 'color:#800080;',
            GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI_F => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI => 'color:#800080;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #339933;',
            1 => 'color: #009900;',
            2 => 'color: #009900;',
            3 => 'color: #009900;'
            ),
        'REGEXPS' => array(
            1 => 'color: #006600;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        1 => "#(?<=\\x2F)[\\w-]+#"
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 4
);
