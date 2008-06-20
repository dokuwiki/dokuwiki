<?php
/*************************************************************************************
 * gettext.php
 * --------
 * Author: Milian Wolff (mail@milianw.de)
 * Copyright: (c) 2008 Milian Wolff
 * Release Version: 1.0.7.22
 * Date Started: 2008/05/25
 *
 * GNU Gettext .po/.pot language file for GeSHi.
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
    'LANG_NAME' => 'GNU Gettext',
    'COMMENT_SINGLE' => array('#:', '#.', '#'),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array('msgid', 'msgstr'),
    ),
    'SYMBOLS' => array(),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000000; font-weight: bold;'
        ),
        'COMMENTS' => array(
            0 => 'color: #000099;',
            1 => 'color: #000099;',
            2 => 'color: #666666; font-style: italic;',
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
        ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
        ),
    ),
    'OOLANG' => false,
    'REGEXPS' => array(),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'TAB_WIDTH' => 4,
);

?>
