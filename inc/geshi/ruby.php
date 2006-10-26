<?php
/*************************************************************************************
 * ruby.php
 * --------
 * Author: Amit Gupta (http://blog.igeek.info/)
 * Copyright: (c) 2005 Amit Gupta (http://blog.igeek.info/)
 * Release Version: 1.0.7.15
 * CVS Revision Version: $Revision: 1.13.2.4 $
 * Date Started: 2005/09/05
 * Last Modified: $Date: 2006/09/23 02:05:48 $
 *
 * Ruby language file for GeSHi
 *
 * CHANGES
 * -------
 * 2006/01/05 (1.0.1)
 *   -  Add =begin multiline comments (Juan J. Martínez)
 *   -  Add ` string (Juan J. Martínez)
 * 2005/09/05 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2005/09/05)
 * -------------------------
 * * Add the remaining keywords, methods, classes as per
 *   v1.8.2(as listed in the online manual)
 *
 *************************************************************************************
 *
 *   This file is part of GeSHi.
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
	'LANG_NAME' => 'Ruby',
	'COMMENT_SINGLE' => array(1 => "#"),
    'COMMENT_MULTI' => array( "=begin" => "=end"),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array('"', '`'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
				'alias', 'and', 'begin', 'break', 'case', 'class',
				'def', 'defined', 'do', 'else', 'elsif', 'end',
				'ensure', 'for', 'if', 'in', 'module', 'while',
				'next', 'not', 'or', 'redo', 'rescue', 'yield',
				'retry', 'super', 'then', 'undef', 'unless',
				'until', 'when', 'BEGIN', 'END', 'include'

			),
		2 => array(
				'__FILE__', '__LINE__', 'false', 'nil', 'self', 'true', 'return'
			),
		3 => array(
				'Array', 'Float', 'Integer', 'String', 'at_exit',
				'autoload', 'binding', 'caller', 'catch', 'chop', 'chop!',
				'chomp', 'chomp!', 'eval', 'exec', 'exit', 'exit!', 'fail',
				'fork', 'format', 'gets', 'global_variables', 'gsub', 'gsub!',
				'iterator?', 'lambda', 'load', 'local_variables', 'loop', 'open',
				'p', 'print', 'printf', 'proc', 'putc', 'puts', 'raise',
				'rand', 'readline', 'readlines', 'require', 'select', 'sleep',
				'split', 'sprintf', 'srand', 'sub', 'sub!', 'syscall',
				'system', 'test', 'trace_var', 'trap', 'untrace_var'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '[', ']', '{', '}', '@', '%', '&', '*', '|', '/', '<', '>',
		'+', '-', '=&gt;', '=>'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => false,
		2 => false,
		3 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color:#9966CC; font-weight:bold;',
			2 => 'color:#0000FF; font-weight:bold;',
			3 => 'color:#CC0066; font-weight:bold;'
			),
		'COMMENTS' => array(
			1 => 'color:#008000; font-style:italic;',
            'MULTI' => 'color:#000080; font-style:italic;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color:#000099;'
			),
		'BRACKETS' => array(
			0 => 'color:#006600; font-weight:bold;'
			),
		'STRINGS' => array(
			0 => 'color:#996600;'
			),
		'NUMBERS' => array(
			0 => 'color:#006666;'
			),
		'METHODS' => array(
			1 => 'color:#9900CC;'
			),
		'SYMBOLS' => array(
			0 => 'color:#006600; font-weight:bold;'
			),
		'REGEXPS' => array(
			),
		'SCRIPT' => array(
			0 => '',
			1 => '',
			2 => '',
			)
		),
	'URLS' => array(
		1 => '',
		2 => '',
		3 => ''
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => '.'
		),
	'REGEXPS' => array(
		),
	'STRICT_MODE_APPLIES' => GESHI_MAYBE,
	'SCRIPT_DELIMITERS' => array(
		0 => array(
			'<%' => '%>'
			)
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		0 => true,
		1 => true,
		2 => true,
		)
);

?>
