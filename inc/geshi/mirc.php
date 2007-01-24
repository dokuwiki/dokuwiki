<?php
/*************************************************************************************
 * mirc.php
 * -----
 * Author: Alberto 'Birckin' de Areba (Birckin@hotmail.com)
 * Copyright: (c) 2006 Alberto de Areba
 * Release Version: 1.0.7.17
 * CVS Revision Version: $Revision: 866 $
 * Date Started: 2006/05/29
 * Last Modified: $LastChangedDate: 2006-11-26 21:40:26 +1300 (Sun, 26 Nov 2006) $
 *
 * mIRC Scripting language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2006/05/29 (1.0.0)
 *   -  First Release
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
	'LANG_NAME' => 'mIRC Scripting',
	'COMMENT_SINGLE' => array(1 => ';'),
  	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array(),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		1 => array(
        	'alias', 'menu', 'dialog',
			),
		2 => array(
			'if', 'elseif', 'else', 'while', 'return', 'goto',
			),
		),
	'SYMBOLS' => array(
		'(', ')', '{', '}', '[', ']', '|',
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #994444;',
			2 => 'color: #000000; font-weight: bold;',
			),
		'COMMENTS' => array(
			1 => 'color: #808080; font-style: italic;',
			),
		'ESCAPE_CHAR' => array(
			),
		'BRACKETS' => array(
			0 => 'color: #FF0000;',
			),
		'STRINGS' => array(
			),
        'NUMBERS' => array(
            0 => '',
			),
		'METHODS' => array(
			),
		'SYMBOLS' => array(
			0 => 'color: #FF0000;',
			),
		'REGEXPS' => array(
			0 => 'color: #000099;',
			1 => 'color: #990000;',
			2 => 'color: #888800;',
			3 => 'color: #888800;',
			4 => 'color: #000099;',
			5 => 'color: #000099;',
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		1 => '',
		2 => '',
		3 => 'http://www.mirc.com/{FNAME}',
		4 => ''
		),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
	'REGEXPS' => array(
		0 => '\$[^$][^ ,\(\)]*',
		1 => '(%|&).+?[^ ,\)]*',
		2 => '(#|@).+?[^ ,\)]*',
		3 => '-[a-z\d]+',
		4 => '(on|ctcp) (!|@|&)?(\d|\*):[a-zA-Z]+:',
		/*4 => array(
			GESHI_SEARCH => '((on|ctcp) (!|@|&)?(\d|\*):(Action|Active|Agent|AppActive|Ban|Chat|Close|Connect|Ctcp|CtcpReply|DccServer|DeHelp|DeOp|DeVoice|Dialog|Dns|Error|Exit|FileRcvd|FileSent|GetFail|Help|Hotlink|Input|Invite|Join|KeyDown|KeyUp|Kick|Load|Logon|MidiEnd|Mode|Mp3End|Nick|NoSound|Notice|Notify|Op|Open|Part|Ping|Pong|PlayEnd|Quit|Raw|RawMode|SendFail|Serv|ServerMode|ServerOp|Signal|Snotice|Start|Text|Topic|UnBan|Unload|Unotify|User|Mode|Voice|Wallops|WaveEnd):)',
			GESHI_REPLACE => '\\1',
			GESHI_MODIFIERS => 'i',
			GESHI_BEFORE => '',
			GESHI_AFTER => ''
			),*/
        5 => 'raw (\d|\*):',
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);

?>
