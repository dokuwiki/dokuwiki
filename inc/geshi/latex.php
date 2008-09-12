<?php
/*************************************************************************************
 * latex.php
 * -----
 * Author: efi, Matthias Pospiech (mail@matthiaspospiech.de)
 * Copyright: (c) 2006 efi, Matthias Pospiech (mail@matthiaspospiech.de), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1\.0\.8
 * Date Started: 2006/09/23
 *
 * LaTeX language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2006/09/23 (1.0.0)
 *  -  First Release
 *
 * TODO
 * -------------------------
 * *
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
    'LANG_NAME' => 'LaTeX',
    'COMMENT_SINGLE' => array(
        1 => '%'
        ),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array(),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        ),
    'SYMBOLS' => array(
        '.', ',','\\',"~", "{", "}", "[", "]", "$"
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            ),
        'COMMENTS' => array(
            1 => 'color: #808080; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 =>  'color: #000000; font-weight: bold;'
            ),
        'BRACKETS' => array(
            ),
        'STRINGS' => array(
            0 =>  'color: #000000;'
            ),
        'NUMBERS' => array(
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            1 =>  'color: #800000; font-weight: bold;'
            ),
        'REGEXPS' => array(
            1 => 'color: #00A000; font-weight: bold;',  // Math inner
            2 => 'color: #800000; font-weight: normal;', // \keyword #202020
            3 => 'color: #2222D0; font-weight: normal;', // {...}
            4 => 'color: #2222D0; font-weight: normal;', // [Option]
            5 => 'color: #00A000; font-weight: normal;', // Mathe #CCF020
            6 => 'color: #F00000; font-weight: normal;', // Structure \begin
            7 => 'color: #F00000; font-weight: normal;', // Structure \end
            8 => 'color: #F00000; font-weight: normal;', // Structure: Labels
            //9 => 'color: #F00000; font-weight: normal;',  // Structure
            10 => 'color: #0000D0; font-weight: bold;',  // Environment
            11 => 'color: #0000D0; font-weight: bold;',  // Environment
            12 => 'color: #800000; font-weight: normal;', // Escaped char
        ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        // Math inner
        1 => array(
            GESHI_SEARCH => "(\\\\begin\\{)(equation|displaymath|eqnarray|subeqnarray|math|multline|gather|align|alignat|flalign)(\\})(.*)(\\\\end\\{)(\\2)(\\})",
            GESHI_REPLACE => '\4',
            GESHI_MODIFIERS => 'Us',
            GESHI_BEFORE => '\1\2\3',
            GESHI_AFTER => '\5\6\7'
            ),
        // \keywords
        2 => array(
            GESHI_SEARCH => "(\\\\)([a-zA-Z]+)",
            GESHI_REPLACE => '\1\2',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        // {parameters}
        3 => array(
            GESHI_SEARCH => "(\\{)(.*)(\\})",
            GESHI_REPLACE => '\2',
            GESHI_MODIFIERS => 'Us',
            GESHI_BEFORE => '\1',
            GESHI_AFTER => '\3'
            ),
        // [options]
        4 => array(
            GESHI_SEARCH => "(\[)(.+)(\])",
            GESHI_REPLACE => '\2',
            GESHI_MODIFIERS => 'Us',
            GESHI_BEFORE => '\1',
            GESHI_AFTER => '\3'
            ),
        // Math mode with $ ... $
        5 => array(
            GESHI_SEARCH => "(\\$)(.+)(\\$)",
            GESHI_REPLACE => '\1\2\3',
            GESHI_MODIFIERS => 'Us',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        // Structure begin
        6 => array(
            GESHI_SEARCH => "(\\\\begin)(?=[^a-zA-Z])",
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        // Structure end
        7 => array(
            GESHI_SEARCH => "(\\\\end)(?=[^a-zA-Z])",
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        // Structure: Label
        8 => array(
            GESHI_SEARCH => "(\\\\)(label|pageref|ref|cite)(?=[^a-zA-Z])",
            GESHI_REPLACE => '\\1\\2',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        // Structure: sections
        /*9 => array(
            GESHI_SEARCH => "(\\\\)(part|chapter|section|subsection|subsubsection|paragraph|subparagraph)(?=[^a-zA-Z])",
            GESHI_REPLACE => '\1\\2',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => '\\3'
            ),*/

        // environment begin
        10 => array(
            GESHI_SEARCH => "(\\\\begin)(\\{)(.*)(\\})",
            GESHI_REPLACE => '\\3',
            GESHI_MODIFIERS => 'U',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        // environment end
        11 => array(
            GESHI_SEARCH => "(\\\\end)(\\{)(.*)(\\})",
            GESHI_REPLACE => '\\3',
            GESHI_MODIFIERS => 'U',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),

        // environment end
        12 => array(
            GESHI_SEARCH => "(\\\\[_$%])",
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            )

// ---------------------------------------------
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'PARSER_CONTROL' => array(
        'COMMENTS' => array(
            'DISALLOWED_BEFORE' => '\\'
        ),
        'ENABLE_FLAGS' => array(
            'NUMBERS' => GESHI_NEVER,
            'SYMBOLS' => GESHI_NEVER,
        )
    )
);

?>
