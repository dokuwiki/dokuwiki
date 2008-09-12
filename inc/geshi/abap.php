<?php
/*************************************************************************************
 * abap.php
 * --------
 * Author: Andres Picazo (andres@andrespicazo.com)
 * Copyright: (c) 2007 Andres Picazo
 * Release Version: 1\.0\.8
 * Date Started: 2004/06/04
 *
 * ABAP language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2007/06/27 (1.0.0)
 *   -  First Release
 *
 * TODO
 * ----
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
    'LANG_NAME' => 'ABAP',
    'COMMENT_SINGLE' => array(1 => '"', 2 => '*'),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => 0,
    'QUOTEMARKS' => array("'"),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'if', 'return', 'while', 'case', 'default',
            'do', 'else', 'for', 'endif', 'elseif', 'eq',
            'not', 'and'
            ),
        2 => array(
            'data', 'types', 'seletion-screen', 'parameters', 'field-symbols', 'extern', 'inline'
            ),
        3 => array(
            'report', 'write', 'append', 'select', 'endselect', 'call method', 'call function',
            'loop', 'endloop', 'raise', 'read table', 'concatenate', 'split', 'shift',
            'condense', 'describe', 'clear', 'endfunction', 'assign', 'create data', 'translate',
            'continue', 'start-of-selection', 'at selection-screen', 'modify', 'call screen',
            'create object', 'perform', 'form', 'endform',
            'reuse_alv_block_list_init', 'zbcialv', 'include'
            ),
        4 => array(
            'type ref to', 'type', 'begin of',  'end of', 'like', 'into',
            'from', 'where', 'order by', 'with key', 'string', 'separated by',
            'exporting', 'importing', 'to upper case', 'to', 'exceptions', 'tables',
            'using', 'changing'
            ),
        ),
    'SYMBOLS' => array(
        '(', ')', '{', '}', '[', ']', '=', '+', '-', '*', '/', '!', '%', '^', '&', ':'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #b1b100;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #000066;',
            4 => 'color: #993333;'
            ),
        'COMMENTS' => array(
            1 => 'color: #808080; font-style: italic;',
            2 => 'color: #339933;',
            'MULTI' => 'color: #808080; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #66cc66;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
            ),
        'METHODS' => array(
            1 => 'color: #202020;',
            2 => 'color: #202020;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => 'http://sap4.com/wiki/index.php?title={FNAMEL}',
        4 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.',
        2 => '::'
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);

?>
