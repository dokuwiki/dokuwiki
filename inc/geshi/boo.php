<?php
/*************************************************************************************
 * boo.php
 * --------
 * Author: Marcus Griep (neoeinstein+GeSHi@gmail.com)
 * Copyright: (c) 2007 Marcus Griep (http://www.xpdm.us)
 * Release Version: 1.0.8
 * Date Started: 2007/09/10
 *
 * Boo language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/09/10 (1.0.8)
 *  -  First Release
 *
 * TODO (updated 2007/09/10)
 * -------------------------
 * Regular Expression Literal matching
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
    'LANG_NAME' => 'Boo',
    'COMMENT_SINGLE' => array(1 => '//', 2 => '#'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'''", "'", '"""', '"'),
    'HARDQUOTE' => array('"""', '"""'),
    'HARDESCAPE' => array('\"""'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        'namespace' => array(
            'namespace', 'import', 'from'
            ),
        'jump' => array(
            'yield', 'return', 'goto', 'continue', 'break'
            ),
        'conditional' => array(
            'while', 'unless', 'then', 'in', 'if', 'for', 'else', 'elif'
            ),
        'property' => array(
            'set', 'get'
            ),
        'exception' => array(
            'try', 'raise', 'failure', 'except', 'ensure'
            ),
        'visibility' => array(
            'public', 'private', 'protected', 'internal'
            ),
        'define' => array(
            'struct', 'ref', 'of', 'interface', 'event', 'enum', 'do', 'destructor', 'def', 'constructor', 'class'
            ),
        'cast' => array(
            'typeof', 'cast', 'as'
            ),
        'bimacro' => array(
            'yieldAll', 'using', 'unchecked', 'rawArayIndexing', 'print', 'normalArrayIndexing', 'lock',
            'debug', 'checked', 'assert'
            ),
        'biattr' => array(
            'required', 'property', 'meta', 'getter', 'default'
            ),
        'bifunc' => array(
            'zip', 'shellp', 'shellm', 'shell', 'reversed', 'range', 'prompt',
            'matrix', 'map', 'len', 'join', 'iterator', 'gets', 'enumerate', 'cat', 'array'
            ),
        'hifunc' => array(
            '__switch__', '__initobj__', '__eval__', '__addressof__', 'quack'
            ),
        'primitive' => array(
            'void', 'ushort', 'ulong', 'uint', 'true', 'timespan', 'string', 'single',
            'short', 'sbyte', 'regex', 'object', 'null', 'long', 'int', 'false', 'duck',
            'double', 'decimal', 'date', 'char', 'callable', 'byte', 'bool'
            ),
        'operator' => array(
            'not', 'or', 'and', 'is', 'isa',
            ),
        'modifier' => array(
            'virtual', 'transient', 'static', 'partial', 'override', 'final', 'abstract'
            ),
        'access' => array(
            'super', 'self'
            ),
        'pass' => array(
            'pass'
            )
        ),
    'SYMBOLS' => array(
         '[|', '|]', '${', '(', ')', '[', ']', '{', '}', '!', '@', '%', '&', '*', '|', '/', '<', '>', '+', '-', ';'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        'namespace' => true,
        'jump' => true,
        'conditional' => true,
        'property' => true,
        'exception' => true,
        'visibility' => true,
        'define' => true,
        'cast' => true,
        'bimacro' => true,
        'biattr' => true,
        'bifunc' => true,
        'hifunc' => true,
        'primitive' => true,
        'operator' => true,
        'modifier' => true,
        'access' => true,
        'pass' => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            'namespace' => 'color:green;font-weight:bold;',
            'jump' => 'color:navy;',
            'conditional' => 'color:blue;font-weight:bold;',
            'property' => 'color:#8B4513;',
            'exception' => 'color:teal;font-weight:bold;',
            'visibility' => 'color:blue;font-weight:bold;',
            'define' => 'color:blue;font-weight:bold;',
            'cast' => 'color:blue;font-weight:bold;',
            'bimacro' => 'color:maroon;',
            'biattr' => 'color:maroon;',
            'bifunc' => 'color:purple;',
            'hifunc' => 'color:#4B0082;',
            'primitive' => 'color:purple;font-weight:bold;',
            'operator' => 'color:#008B8B;font-weight:bold;',
            'modifier' => 'color:brown;',
            'access' => 'color:black;font-weight:bold;',
            'pass' => 'color:gray;'
            ),
        'COMMENTS' => array(
            1 => 'color: #999999; font-style: italic;',
            2 => 'color: #999999; font-style: italic;',
            'MULTI' => 'color: #008000; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #0000FF; font-weight: bold;',
            'HARD' => 'color: #0000FF; font-weight: bold;',
            ),
        'BRACKETS' => array(
            0 => 'color: #006400;'
            ),
        'STRINGS' => array(
            0 => 'color: #008000;',
            'HARD' => 'color: #008000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #00008B;'
            ),
        'METHODS' => array(
            0 => 'color: 000000;',
            1 => 'color: 000000;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #006400;'
            ),
        'REGEXPS' => array(
            #0 => 'color: #0066ff;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        'namespace' => '',
        'jump' => '',
        'conditional' => '',
        'property' => '',
        'exception' => '',
        'visibility' => '',
        'define' => '',
        'cast' => '',
        'bimacro' => '',
        'biattr' => '',
        'bifunc' => '',
        'hifunc' => '',
        'primitive' => '',
        'operator' => '',
        'modifier' => '',
        'access' => '',
        'pass' => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
            0 => '.',
            1 => '::'
        ),
    'REGEXPS' => array(
        #0 => '%(@)?\/(?:(?(1)[^\/\\\\\r\n]+|[^\/\\\\\r\n \t]+)|\\\\[\/\\\\\w+()|.*?$^[\]{}\d])+\/%'
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 4
);

?>
