<?php
/*************************************************************************************
 * cpp.php
 * -------
 * Author: Dennis Bayer (Dennis.Bayer@mnifh-giessen.de)
 * Contributors:
 *  - M. Uli Kusterer (witness.of.teachtext@gmx.net)
 *  - Jack Lloyd (lloyd@randombit.net)
 * Copyright: (c) 2004 Dennis Bayer, Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.20
 * Date Started: 2004/09/27
 *
 * C++ language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/XX/XX (1.0.2)
 *  -  Added several new keywords (Jack Lloyd)
 * 2004/11/27 (1.0.1)
 *  -  Added StdCLib function and constant names, changed color scheme to
 *     a cleaner one. (M. Uli Kusterer)
 *  -  Added support for multiple object splitters
 * 2004/10/27 (1.0.0)
 *  -  First Release
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
	'LANG_NAME' => 'C++',
	'COMMENT_SINGLE' => array(1 => '//', 2 => '#'),
	'COMMENT_MULTI' => array('/*' => '*/'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
			'case', 'continue', 'default', 'do', 'else', 'for', 'goto', 'if', 'return',
			'switch', 'while'
			),
		2 => array(
			'NULL', 'false', 'break', 'true', 'enum', 'errno', 'EDOM',
			'ERANGE', 'FLT_RADIX', 'FLT_ROUNDS', 'FLT_DIG', 'DBL_DIG', 'LDBL_DIG',
			'FLT_EPSILON', 'DBL_EPSILON', 'LDBL_EPSILON', 'FLT_MANT_DIG', 'DBL_MANT_DIG',
			'LDBL_MANT_DIG', 'FLT_MAX', 'DBL_MAX', 'LDBL_MAX', 'FLT_MAX_EXP', 'DBL_MAX_EXP',
			'LDBL_MAX_EXP', 'FLT_MIN', 'DBL_MIN', 'LDBL_MIN', 'FLT_MIN_EXP', 'DBL_MIN_EXP',
			'LDBL_MIN_EXP', 'CHAR_BIT', 'CHAR_MAX', 'CHAR_MIN', 'SCHAR_MAX', 'SCHAR_MIN',
			'UCHAR_MAX', 'SHRT_MAX', 'SHRT_MIN', 'USHRT_MAX', 'INT_MAX', 'INT_MIN',
			'UINT_MAX', 'LONG_MAX', 'LONG_MIN', 'ULONG_MAX', 'HUGE_VAL', 'SIGABRT',
			'SIGFPE', 'SIGILL', 'SIGINT', 'SIGSEGV', 'SIGTERM', 'SIG_DFL', 'SIG_ERR',
			'SIG_IGN', 'BUFSIZ', 'EOF', 'FILENAME_MAX', 'FOPEN_MAX', 'L_tmpnam', 'NULL',
			'SEEK_CUR', 'SEEK_END', 'SEEK_SET', 'TMP_MAX', 'stdin', 'stdout', 'stderr',
			'EXIT_FAILURE', 'EXIT_SUCCESS', 'RAND_MAX', 'CLOCKS_PER_SEC',
			'virtual', 'public', 'private', 'protected', 'template', 'using', 'namespace',
			'try', 'catch', 'inline', 'dynamic_cast', 'const_cast', 'reinterpret_cast',
			'static_cast', 'explicit', 'friend', 'wchar_t', 'typename', 'typeid', 'class'
			),
		3 => array(
			'cin', 'cerr', 'clog', 'cout', 'delete', 'new', 'this',
			'printf', 'fprintf', 'snprintf', 'sprintf', 'assert',
			'isalnum', 'isalpha', 'isdigit', 'iscntrl', 'isgraph', 'islower', 'isprint',
			'ispunct', 'isspace', 'ispunct', 'isupper', 'isxdigit', 'tolower', 'toupper',
			'exp', 'log', 'log10', 'pow', 'sqrt', 'ceil', 'floor', 'fabs', 'ldexp',
			'frexp', 'modf', 'fmod', 'sin', 'cos', 'tan', 'asin', 'acos', 'atan', 'atan2',
			'sinh', 'cosh', 'tanh', 'setjmp', 'longjmp', 'asin', 'acos', 'atan', 'atan2',
			'va_start', 'va_arg', 'va_end', 'offsetof', 'sizeof', 'fopen', 'freopen',
			'fflush', 'fclose', 'remove', 'rename', 'tmpfile', 'tmpname', 'setvbuf',
			'setbuf', 'vfprintf', 'vprintf', 'vsprintf', 'fscanf', 'scanf', 'sscanf',
			'fgetc', 'fgets', 'fputc', 'fputs', 'getc', 'getchar', 'gets', 'putc',
			'putchar', 'puts', 'ungetc', 'fread', 'fwrite', 'fseek', 'ftell', 'rewind',
			'fgetpos', 'fsetpos', 'clearerr', 'feof', 'ferror', 'perror', 'abs', 'labs',
			'div', 'ldiv', 'atof', 'atoi', 'atol', 'strtod', 'strtol', 'strtoul', 'calloc',
			'malloc', 'realloc', 'free', 'abort', 'exit', 'atexit', 'system', 'getenv',
			'bsearch', 'qsort', 'rand', 'srand', 'strcpy', 'strncpy', 'strcat', 'strncat',
			'strcmp', 'strncmp', 'strcoll', 'strchr', 'strrchr', 'strspn', 'strcspn',
			'strpbrk', 'strstr', 'strlen', 'strerror', 'strtok', 'strxfrm', 'memcpy',
			'memmove', 'memcmp', 'memchr', 'memset', 'clock', 'time', 'difftime', 'mktime',
			'asctime', 'ctime', 'gmtime', 'localtime', 'strftime'
			),
		4 => array(
			'auto', 'bool', 'char', 'const', 'double', 'float', 'int', 'long', 'longint',
			'register', 'short', 'shortint', 'signed', 'static', 'struct',
			'typedef', 'union', 'unsigned', 'void', 'volatile', 'extern', 'jmp_buf',
			'signal', 'raise', 'va_list', 'ptrdiff_t', 'size_t', 'FILE', 'fpos_t',
			'div_t', 'ldiv_t', 'clock_t', 'time_t', 'tm',
			),
		),
	'SYMBOLS' => array(
		'(', ')', '{', '}', '[', ']', '=', '+', '-', '*', '/', '!', '%', '^', '&', ':'
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
			1 => 'color: #0000ff;',
			2 => 'color: #0000ff;',
			3 => 'color: #0000dd;',
			4 => 'color: #0000ff;'
			),
		'COMMENTS' => array(
			1 => 'color: #ff0000;',
			2 => 'color: #339900;',
			'MULTI' => 'color: #ff0000; font-style: italic;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #666666; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #000000;'
			),
		'STRINGS' => array(
			0 => 'color: #666666;'
			),
		'NUMBERS' => array(
			0 => 'color: #0000dd;'
			),
		'METHODS' => array(
			1 => 'color: #00eeff;',
			2 => 'color: #00eeff;'
			),
		'SYMBOLS' => array(
			0 => 'color: #000000;'
			),
		'REGEXPS' => array(
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
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
        ),
    'TAB_WIDTH' => 4
);

?>
