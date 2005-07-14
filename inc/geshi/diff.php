<?php
/*************************************************************************************
 * diff.php
 * --------
 * Author: Conny Brunnkvist (conny@fuchsia.se)
 * Copyright: (c) 2004 Fuchsia Open Source Solutions (http://www.fuchsia.se/)
 * Release Version: 1.0.0
 * CVS Revision Version: $Revision: 1.1 $
 * Date Started: 2004/12/29
 * Last Modified: $Date: 2005/06/14 13:02:34 $
 *
 * Diff-output language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/12/29 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2004/12/29)
 * -------------------------
 * * Find out why GeSHi doesn't seem to allow matching of start (^) and end ($) 
 * * So that we can stop pretending that we are dealing with single-line comments
 * * Should be able to cover all sorts of diff-output
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
	'LANG_NAME' => 'Diff',
	'COMMENT_SINGLE' => array(
				0 => '--- ',
				1 => '+++ ',
				2 => '<',
				3 => '>',
				4 => '-',
				5 => '+',
				6 => '!',
				7 => '@@',
				8 => '*** ',
				/*9 => '***************',*/
				/*10 => ' ', // All other rows starts with a space (bug?) */
			),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array(),
	'ESCAPE_CHAR' => ' ',
	'KEYWORDS' => array(
			0 => array(
				'\ No newline at end of file',
			),
			1 => array(
				'***************' /* This only seems to works in some cases? */
			),
		),
	'SYMBOLS' => array(
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			0 => 'color: #aaaaaa; font-style: italic;',
			1 => 'color: #dd6611;',
			),
		'COMMENTS' => array(
			0 => 'color: #228822;',
			1 => 'color: #228822;',
                        2 => 'color: #991111;', 
                        3 => 'color: #00aaee;', 
                        4 => 'color: #991111;', 
                        5 => 'color: #00b000;', 
                        /*6 => 'color: #dd6611;', */
                        6 => 'color: #0011dd;', 
			7 => 'color: #aaaa88;',
			8 => 'color: #228822;',
			/*9 => 'color: #aaaa88;',*/
			/*10 => 'color: #000000;',*/
			),
		'ESCAPE_CHAR' => array(
			),
		'BRACKETS' => array(
			),
		'STRINGS' => array(
			),
		'NUMBERS' => array(
			),
		'METHODS' => array(
			),
		'SYMBOLS' => array(
			),
		'SCRIPT' => array(
			),
                'REGEXPS' => array(
			0 => 'color: #aaaaaa;',
			/*1 => 'color: #000000;',*/
                        ),
		),
	'URLS' => array(
		),
	'OOLANG' => false,
	'OBJECT_SPLITTER' => '',
	'REGEXPS' => array(
			0 => "[0-9,]+[acd][0-9,]+",
			/*1 => array( // Match all other lines - again this also doesn't work.
				GESHI_SEARCH => '(\ )(.+)',
				GESHI_REPLACE => '\\2\\3',
				GESHI_MODIFIERS => '',
				GESHI_BEFORE => '\\1',
				GESHI_AFTER => ''
			),*/
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);

?>
