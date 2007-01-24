<?php
/*************************************************************************************
 * bash.php
 * --------
 * Author: Andreas Gohr (andi@splitbrain.org)
 * Copyright: (c) 2004 Andreas Gohr, Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.17
 * CVS Revision Version: $Revision: 866 $
 * Date Started: 2004/08/20
 * Last Modified: $Date: 2006-11-26 21:40:26 +1300 (Sun, 26 Nov 2006) $
 *
 * BASH language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.2)
 *  -  Added support for multiple object splitters
 * 2004/10/27 (1.0.1)
 *   -  Added support for URLs
 * 2004/08/20 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
 * * Get symbols working
 * * Highlight builtin vars
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
    'LANG_NAME' => 'Bash',
    // Bash DOES have single line comments with # markers. But bash also has
    // the  $# variable, so comments need special handling (see sf.net
    // 1564839)
	'COMMENT_SINGLE' => array(),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
			'case', 'do', 'done', 'elif', 'else', 'esac', 'fi', 'for', 'function',
			'if', 'in', 'select', 'then', 'until', 'while', 'time'
			),
		3 => array(
			'source', 'alias', 'bg', 'bind', 'break', 'builtin', 'cd', 'command',
			'compgen', 'complete', 'continue', 'declare', 'typeset', 'dirs',
			'disown', 'echo', 'enable', 'eval', 'exec', 'exit', 'export', 'fc',
			'fg', 'getopts', 'hash', 'help', 'history', 'jobs', 'kill', 'let',
			'local', 'logout', 'popd', 'printf', 'pushd', 'pwd', 'read', 'readonly',
			'return', 'set', 'shift', 'shopt', 'suspend', 'test', 'times', 'trap',
			'type', 'ulimit', 'umask', 'unalias', 'unset', 'wait'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '[', ']', '!', '@', '%', '&', '*', '|', '/', '<', '>'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => true,
		3 => true,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #b1b100;',
			3 => 'color: #000066;'
			),
		'COMMENTS' => array(
			1 => 'color: #808080; font-style: italic;',
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
			),
		'SYMBOLS' => array(
			0 => 'color: #66cc66;'
			),
		'REGEXPS' => array(
			0 => 'color: #0000ff;',
			1 => 'color: #0000ff;',
            2 => 'color: #0000ff;',
            3 => 'color: #808080; font-style: italic;',
            4 => 'color: #0000ff;'
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		1 => '',
		3 => ''
		),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
	'REGEXPS' => array(
		0 => "\\$\\{[a-zA-Z_][a-zA-Z0-9_]*?\\}",
		1 => "\\$[a-zA-Z_][a-zA-Z0-9_]*",
        2 => "([a-zA-Z_][a-zA-Z0-9_]*)=",
        3 => "(?<!\\$)#.*\n",
        4 => "\\$#"
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);

?>
