<?php
/*********************************************************************
 * МК-61/52 language file for GeSHi.
 *
 * Author: Russkiy
 * Copyright: (c) 2014 Russkiy
 * Release Version: 1.0.9.0
 * Date Started: 2014-03-11
 *
 *********************************************************************
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
 ********************************************************************/

$language_data = array (
    'LANG_NAME' => 'МК-61/52',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array(),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(),
    'SYMBOLS' => array(),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false
    ),
    'STYLES' => array(
        'KEYWORDS' => array(),
        'COMMENTS' => array(),
        'ESCAPE_CHAR' => array(),
        'BRACKETS' => array(),
        'STRINGS' => array(),
        'NUMBERS' => array(),
        'METHODS' => array(),
        'SYMBOLS' => array(),
        'SCRIPT' => array(),
        'REGEXPS' => array(
            1 => 'color:#000000;',
            2 => 'color:#A0A000;',
            3 => 'color:#00A000;',
            4 => 'color:#A00000;',
            5 => 'color:#0000A0;',
            6 => 'text-decoration: underline; color: #A000A0;',
            7 => 'font-size: 75%; color: #A0A0A0;'
        )
    ),
    'URLS' => array(),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        1 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)((F|K|К)?(пи|π|СЧ|KСЧ|КСЧ|,|\.|\/\-\/|\+\/\-|ВП))(\s|\t|$)',
            GESHI_REPLACE => '\\4',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1<span style="font-weight:lighter;font-size:90%;color:#404040;">\\3</span>', GESHI_AFTER => '\\5'
        ),
        2 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)((F|K|К)?(НОП|&lt;\-&gt;|XY|↔|X↔Y|\^|В\^|↑|В↑|Вx|Вx|Сx|\-&gt;|↻|→))(\s|\t|$)',
            GESHI_REPLACE => '\\4',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1<span style="font-weight:lighter;font-size:90%;color:#404040;">\\3</span>', GESHI_AFTER => '\\5'
        ),
        3 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)((K|К)?(П|XП|ИП|ПX|Пx)(\d|[A-E]|[a-e]|(А|В|С|Д|Е)))(\s|\t|$)',
            GESHI_REPLACE => '\\2',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1', GESHI_AFTER => '\\7'
        ),
        4 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)((F|K|К)?(10\^x|10x|e\^x|ex|lg|ln|ЧМ|arcsin|<PIPE>x<PIPE>|arccos|ЗН|arctg|ГМ|sin|\[x\]|cos|\{x\}|\(x\)|tg|max|\+|\-|\*|x|х|×|⋅|\/|\:|÷|МГ|КвКор|квкор|корень|√|x\^2|x2|x²|1\/x|x\^y|xy|МЧ|\x2F\x5C|⋀|\x5C\x2F|⋁|\(\+\)|⊕|ИНВ))(\s|\t|$)',
            GESHI_REPLACE => '\\4',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1<span style="font-weight:lighter;font-size:90%;color:#404040;">\\3</span>', GESHI_AFTER => '\\5'
        ),
        5 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)((F?)((K|К)?(В\/О|В\/0|С\/П|x&gt;\=0|x≥0|x≥0|x⩾0|x\#0|x\!\=0|x&lt;&gt;0|x≠0|БП|ПП|L2|L3|x&lt;0|x\=0|L0|L1)))(\s|\t|$)',
            GESHI_REPLACE => '\\4',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1<span style="font-weight:lighter;font-size:90%;color:#404040;">\\3</span>', GESHI_AFTER => '\\7'
        ),
        6 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)(\d{2})(\s|\t|$)',
            GESHI_REPLACE => '\\2',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1', GESHI_AFTER => '\\3'
        ),
        7 => array(
            GESHI_SEARCH => '(\s|\t|^|\G|\.)([\d\-A]\d\.)',
            GESHI_REPLACE => '\\2',
            GESHI_MODIFIERS => '', GESHI_BEFORE => '\\1', GESHI_AFTER => ''
        )
    ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array(),
    'PARSER_CONTROL' => array()
);
