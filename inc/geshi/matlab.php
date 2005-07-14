<?php
/*************************************************************************************
 * matlab.php
 * -----------
 * Author: Florian Knorn (floz@gmx.de)
 * Copyright: (c) 2004 Florian Knorn (http://www.florian-knorn.com)
 * Release Version: 1.0.0
 * CVS Revision Version: $Revision: 1.1 $
 * Date Started: 2005/02/09
 * Last Modified: $Date: 2005/06/15 12:06:28 $
 *
 * Matlab M-file language file for GeSHi. 
 *
 * CHANGES
 * -------
 * 2005/05/07 (1.0.0)
 *   -  First Release
 *
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
	'LANG_NAME' => 'M',
	'COMMENT_SINGLE' => array(1 => '%'),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'"),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		1 => array(
			'break', 'case', 'catch', 'continue', 'elseif', 'else', 'end', 'for', 
			'function', 'global', 'if', 'otherwise', 'persistent', 'return', 
			'switch', 'try', 'while','...'
			),
		),
	'SYMBOLS' => array( 
		'...' 
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		3 => false,
		4 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #0000FF;',
			),
		'COMMENTS' => array(
			1 => 'color: #228B22;',
			),
		'ESCAPE_CHAR' => array(
			),
		'BRACKETS' => array(
			),
		'STRINGS' => array(
			0 => 'color: #A020F0;'
			),
		'NUMBERS' => array(
			),
		'METHODS' => array(
			),
		'SYMBOLS' => array(
			),
		'REGEXPS' => array(
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
