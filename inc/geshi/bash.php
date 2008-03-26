<?php
/*************************************************************************************
 * bash.php
 * --------
 * Author: Andreas Gohr (andi@splitbrain.org)
 * Copyright: (c) 2004 Andreas Gohr, Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.21
 * Date Started: 2004/08/20
 *
 * BASH language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2007/09/05 (1.0.7.21)
 *  -  PARSER_CONTROL patch using SF #1788408 (BenBE)
 * 2007/06/11 (1.0.7.20)
 *  -  Added a lot of keywords (BenBE / Jan G)
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
	'COMMENT_SINGLE' => array('#'),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
			'case', 'do', 'done', 'elif', 'else', 'esac', 'fi', 'for', 'function',
			'if', 'in', 'select', 'set', 'then', 'until', 'while', 'time'
			),
		2 => array(
			'aclocal', 'aconnect', 'aplay', 'apm', 'apmsleep', 'apropos',
			'ar', 'arch', 'arecord', 'as', 'as86', 'autoconf', 'autoheader',
			'automake', 'awk',
			'basename', 'bc', 'bison', 'bunzip2', 'bzip2', 'bzcat',
			'bzcmp', 'bzdiff', 'bzegrep', 'bzegrep', 'bzfgrep', 'bzgrep',
			'bzip2', 'bzip2recover', 'bzless', 'bzmore',
			'c++', 'cal', 'cat', 'chattr', 'cc', 'cdda2wav', 'cdparanoia',
			'cdrdao', 'cd-read', 'cdrecord', 'chfn', 'chgrp', 'chmod',
			'chown', 'chroot', 'chsh', 'chvt', 'clear', 'cmp', 'comm', 'co',
			'col', 'cp', 'cpio', 'cpp', 'cut',
			'date', 'dd', 'dc', 'dcop', 'deallocvt', 'df', 'diff', 'diff3', 'dir',
			'dircolors', 'directomatic', 'dirname', 'dmesg',
			'dnsdomainname', 'domainname', 'du', 'dumpkeys',
			'ed', 'egrep', 'env', 'expr',
			'false', 'fbset', 'fgconsole','fgrep', 'find', 'file', 'flex', 'flex++',
			'fmt', 'free', 'ftp', 'funzip', 'fuser',
			'g++', 'gawk', 'gc','gcc', 'gdb', 'getent', 'getkeycodes',
			'getopt', 'gettext', 'gettextize', 'gimp', 'gimp-remote',
			'gimptool', 'gmake', 'gocr', 'grep', 'groups', 'gs', 'gunzip',
			'gzexe', 'gzip',
			'head', 'hexdump', 'hostname',
			'id', 'igawk', 'install',
			'join',
			'kbd_mode','kbdrate', 'kdialog', 'kfile', 'kill', 'killall',
			'last', 'lastb', 'ld', 'ld86', 'ldd', 'less', 'lex', 'link', 'ln', 'loadkeys',
			'loadunimap', 'locate', 'lockfile', 'login', 'logname',
			'lp', 'lpr', 'ls', 'lsattr', 'lsmod', 'lsmod.old', 'lynx',
			'm4', 'make', 'man', 'mapscrn', 'mesg', 'mkdir', 'mkfifo',
			'mknod', 'mktemp', 'more', 'mount', 'msgfmt', 'mv',
			'namei', 'nano', 'nasm', 'nawk', 'netstat', 'nice',
			'nisdomainname', 'nl', 'nm', 'nm86', 'nmap', 'nohup', 'nop',
			'od', 'openvt',
			'passwd', 'patch', 'pcregrep', 'pcretest', 'perl', 'perror',
			'pgawk', 'pidof', 'ping', 'pr', 'procmail', 'prune', 'ps', 'pstree',
			'ps2ascii', 'ps2epsi', 'ps2frag', 'ps2pdf', 'ps2ps', 'psbook',
			'psmerge', 'psnup', 'psresize', 'psselect', 'pstops',
			'rbash', 'rcs', 'read', 'readlink', 'red', 'resizecons', 'rev', 'rm',
			'rmdir', 'run-parts',
			'sash', 'sed', 'setfont', 'setkeycodes', 'setleds',
			'setmetamode', 'setserial', 'scp', 'seq', 'setterm', 'sh',
			'showkey', 'shred', 'size', 'size86', 'skill', 'sleep', 'slogin',
			'snice', 'sort', 'sox', 'split', 'ssed', 'ssh', 'ssh-add',
			'ssh-agent', 'ssh-keygen', 'ssh-keyscan', 'stat', 'strings',
			'strip', 'stty', 'su', 'sudo', 'suidperl', 'sum', 'sync',
			'tac', 'tail', 'tar', 'tee', 'tempfile', 'touch', 'tr', 'true',
			'umount', 'uname', 'unicode_start', 'unicode_stop', 'uniq',
			'unlink', 'unzip', 'updatedb', 'updmap', 'uptime', 'users',
			'utmpdump', 'uuidgen',
			'vdir', 'vmstat',
			'w', 'wall', 'wc', 'wget', 'whatis', 'whereis', 'which', 'who',
			'whoami', 'write',
			'xargs', 'xhost', 'xmodmap', 'xset',
			'yacc', 'yes', 'ypdomainname',
			'zcat', 'zcmp', 'zdiff', 'zegrep', 'zfgrep', 'zforce', 'zgrep',
			'zip', 'zless', 'zmore', 'znew', 'zsh', ' zsoelim'
			),
		3 => array(
			'alias', 'bg', 'bind', 'break', 'builtin', 'cd', 'command',
			'compgen', 'complete', 'continue', 'declare', 'dirs', 'disown',
			'echo', 'enable', 'eval', 'exec', 'exit', 'export', 'fc',
			'fg', 'getopts', 'hash', 'help', 'history', 'jobs', 'kill', 'let',
			'local', 'logout', 'popd', 'printf', 'pushd', 'pwd', 'readonly',
			'return', 'shift', 'shopt', 'source', 'suspend', 'test', 'times',
			'trap', 'type', 'typeset', 'ulimit', 'umask', 'unalias', 'unset',
			'wait'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '[', ']', '!', '@', '%', '&', '*', '|', '/', '<', '>', ';;'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => true,
		2 => true,
		3 => true
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #000000; font-weight: bold;',
			2 => 'color: #c20cb9; font-weight: bold;',
			3 => 'color: #7a0874; font-weight: bold;'
			),
		'COMMENTS' => array(
			0 => 'color: #808080; font-style: italic;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #7a0874; font-weight: bold;'
			),
		'STRINGS' => array(
			0 => 'color: #ff0000;'
			),
		'NUMBERS' => array(
			0 => 'color: #000000;'
			),
		'METHODS' => array(
			),
		'SYMBOLS' => array(
			0 => 'color: #000000; font-weight: bold;'
			),
		'REGEXPS' => array(
			0 => 'color: #007800;',
			1 => 'color: #007800;',
			2 => 'color: #007800;',
//			3 => 'color: #808080; font-style: italic;',
			4 => 'color: #007800;'
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		1 => '',
		2 => '',
		3 => ''
	),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
	'REGEXPS' => array(
		0 => "\\$\\{[a-zA-Z_][a-zA-Z0-9_]*?\\}",
		1 => "\\$[a-zA-Z_][a-zA-Z0-9_]*",
		2 => "([a-zA-Z_][a-zA-Z0-9_]*)=",
//		3 => "(?<!\\$)#[^\n]*",
		4 => "\\$[*#\$\\-\\?!]"
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		),
	'PARSER_CONTROL' => array(
	    'COMMENTS' => array(
	       'DISALLOWED_BEFORE' => '$'
        )
    )
);

?>
