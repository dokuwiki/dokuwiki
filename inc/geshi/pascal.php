<?php
/*************************************************************************************
 * pascal.php
 * ----------
 * Author: Tux (tux@inamil.cz)
 * Copyright: (c) 2004 Tux (http://tux.a4.cz/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.6
 * CVS Revision Version: $Revision: 1.1 $
 * Date Started: 2004/07/26
 * Last Modified: $Date: 2005/06/02 04:57:18 $
 *
 * Pascal language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.2)
 *  -  Added support for multiple object splitters
 * 2004/10/27 (1.0.1)
 *   -  Added support for URLs
 * 2004/08/05 (1.0.0)
 *   -  Added support for symbols
 * 2004/07/27 (0.9.1)
 *   -  Pascal is OO language. Some new words.
 * 2004/07/26 (0.9.0)
 *   -  First Release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
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
	'LANG_NAME' => 'Pascal',
	'COMMENT_SINGLE' => array(1 => '//'),
	'COMMENT_MULTI' => array('{' => '}','(*' => '*)'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
			'if', 'while', 'until', 'repeat', 'default',
			'do', 'else', 'for', 'switch', 'goto','label','asm','begin','end',
			'assembler','case', 'downto', 'to','div','mod','far','forward','in','inherited',
			'inline','interrupt','label','library','not','var','of','then','stdcall',
			'cdecl','end.','raise','try','except','name','finally','resourcestring','override','overload',
			'default','public','protected','private','property','published','stored','catch'
			),
		2 => array(
			'nil', 'false', 'break', 'true', 'function', 'procedure','implementation','interface',
			'unit','program','initialization','finalization','uses'
			),
		3 => array(
			'abs', 'absolute','and','arc','arctan','chr','constructor','destructor',
			'dispose','cos','eof','eoln','exp','get','index','ln','new','xor','write','writeln',
			'shr','sin','sqrt','succ','pred','odd','read','readln','ord','ordinal','blockread','blockwrite'
			),
		4 => array(
			'array', 'char', 'const', 'boolean',  'real', 'integer', 'longint',
			'word', 'shortint', 'record','byte','bytebool','string',
			'type','object','export','exports','external','file','longbool','pointer','set',
			'packed','ansistring','union'
			),
		),
	'SYMBOLS' => array(
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
			1 => 'color: #b1b100;',
			2 => 'color: #000000; font-weight: bold;',
			3 => '',
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
			1 => 'color: #202020;'
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
		3 => '',
		4 => ''
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => '.'
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
