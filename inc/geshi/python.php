<?php
/*************************************************************************************
 * python.php
 * ----------
 * Author: Roberto Rossi (rsoftware@altervista.org)
 * Copyright: (c) 2004 Roberto Rossi (http://rsoftware.altervista.org), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.6
 * CVS Revision Version: $Revision: 1.1 $
 * Date Started: 2004/08/30
 * Last Modified: $Date: 2005/01/29 01:48:39 $
 *
 * Python language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.1)
 *  -  Added support for multiple object splitters
 * 2004/08/30 (1.0.0)
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
	'LANG_NAME' => 'Python',
	'COMMENT_SINGLE' => array(1 => '#'),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array('"'),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		1 => array(
			'and','assert','break','class','continue','def','del','elif','else','except','exec','finally','for','from',
			'global','if','import','in','is','lambda','map','not','None','or','pass','print','raise','range','return',
			'try','while','abs','apply','callable','chr','cmp','coerce','compile','complex','delattr','dir','divmod',
			'eval','execfile','filter','float','getattr','globals','group','hasattr','hash','hex','',
			'id','input','int','intern','isinstance','issubclass','joinfields','len','list','local','long',
			'max','min','match','oct','open','ord','pow','raw_input','reduce','reload','repr','round',
			'search','setattr','setdefault','slice','str','splitfields','unichr','unicode','tuple','type',
			'vars','xrange','zip','__abs__','__add__','__and__','__call__','__cmp__','__coerce__',
			'__del__','__delattr__','__delitem__','__delslice__','__div__','__divmod__',
			'__float__','__getattr__','__getitem__','__getslice__','__hash__','__hex__',
			'__iadd__','__isub__','__imod__','__idiv__','__ipow__','__iand__','__ior__','__ixor__',
			'__ilshift__','__irshift__','__invert__','__int__','__init__','__len__','__long__','__lshift__',
			'__mod__','__mul__','__neg__','__nonzero__','__oct__','__or__','__pos__','__pow__',
			'__radd__','__rdiv__','__rdivmod__','__rmod__','__rpow__','__rlshift__','__rrshift__',
			'__rshift__','__rsub__','__rmul__','__repr__','__rand__','__rxor__','__ror__',
			'__setattr__','__setitem__','__setslice__','__str__','__sub__','__xor__',
			'__bases__','__class__','__dict__','__methods__','__members__','__name__',
			'__version__','ArithmeticError','AssertionError','AttributeError','EOFError','Exception',
			'FloatingPointError','IOError','ImportError','IndentationError','IndexError',
			'KeyError','KeyboardInterrupt','LookupError','MemoryError','NameError','OverflowError',
			'RuntimeError','StandardError','SyntaxError','SystemError','SystemExit','TabError','TypeError',
			'ValueError','ZeroDivisionError','AST','','atexit','BaseHTTPServer','Bastion',
			'cmd','codecs','commands','compileall','copy','CGIHTTPServer','Complex','dbhash',
			'dircmp','dis','dospath','dumbdbm','emacs','find','fmt','fnmatch','ftplib',
			'getopt','glob','gopherlib','grep','htmllib','httplib','ihooks','imghdr','imputil',
			'linecache','lockfile','macpath','macurl2path','mailbox','mailcap',
			'mimetools','mimify','mutex','math','Mimewriter','newdir','ni','nntplib','ntpath','nturl2path',
			'os','ospath','pdb','pickle','pipes','poly','popen2','posixfile','posixpath','profile','pstats','pyclbr',
			'pyexpat','Para','quopri','Queue','rand','random','regex','regsub','rfc822',
			'sched','sgmllib','shelve','site','sndhdr','string','sys','snmp',
			'SimpleHTTPServer','StringIO','SocketServer',
			'tb','tempfile','toaiff','token','tokenize','traceback','tty','types','tzparse',
			'Tkinter','unicodedata','urllib','urlparse','util','uu','UserDict','UserList',
			'wave','webbrowser','whatsound','whichdb','whrandom','xdrlib','xml','xmlpackage',
			'zmod','array','struct','self',
			)
		),
	'SYMBOLS' => array(
			'(', ')', '[', ']', '{', '}', '*', '&', '%', '!', ';', '<', '>', '?'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => true
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #b1b100;'
			),
		'COMMENTS' => array(
			1 => 'color: #808080; font-style: italic;',
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