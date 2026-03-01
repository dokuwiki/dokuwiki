<?php
/*************************************************************************************
 * tclegg.php
 * ---------------------------------
 * Author: Reid van Melle (rvanmelle@gmail.com)
 * Copyright: (c) 2004 Reid van Melle (sorry@nowhere)
 * Release Version: 1.0.9.1
 * Date Started: 2006/05/05
 *
 * TCL/iTCL language file for GeSHi.
 *
 * This was thrown together in about an hour so I don't expect
 * really great things.  However, it is a good start.  I never
 * got a change to try out the iTCL or object-based support but
 * this is not widely used anyway.
 *
 * CHANGES
 * -------
 * 2008/05/23 (1.0.7.22)
 *  -  Added description of extra language features (SF#1970248)
 * 2006/05/05 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2006/05/05)
 * -------------------------
 * - Get TCL built-in special variables highlighted with a new color..
 *   currently, these are listed in //special variables in the keywords
 *   section, but they get covered by the general REGEXP for symbols
 * - General cleanup, testing, and verification
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

$language_data = array(
    'LANG_NAME' => 'TCLEGG',
    'COMMENT_SINGLE' => array(1 => '#'),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(
        1 => '/(?<!\\\\)#(?:\\\\\\\\|\\\\\\n|.)*$/m',
        //2 => '/{[^}\n]+}/'
    ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"', "'"),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        /*
         * Set 1: reserved words
         * http://python.org/doc/current/ref/keywords.html
         */
        1 => array(
            'break',
            'case',
            'catch',
            'continue',
            'default',
            'else',
            'elseif',
            'error',
            'eval',
            'exit',
            'expr',
            'for',
            'for_array_keys',
            'for_file',
            'for_recursive_glob',
            'foreach',
            'global',
            'if',
            'in',
            'itcl_class',
            'loop',
            'method',
            'namespace',
            'proc',
            'protected',
            'public',
            'rename',
            'return',
            'set',
            'switch',
            'then',
            'unwind_protect',
            'uplevel',
            'upvar',
            'variable',
            'while',
        ),

        /*
         * Set 2: builtins
         * http://asps.activatestate.com/ASPN/docs/ActiveTcl/8.4/tcl/tcl_2_contents.htm
         */
        2 => array(
            // string handling
            'append', 'binary', 'format', 're_syntax', 'regexp', 'regsub',
            'scan', 'string', 'subst',
            // list handling
            'concat', 'join', 'lappend', 'lindex', 'list', 'llength', 'lrange',
            'lreplace', 'lsearch', 'lset', 'lsort', 'split',
            // procedures and output
            'incr', 'close', 'eof', 'fblocked', 'fconfigure', 'fcopy', 'file',
            'fileevent', 'flush', 'gets', 'open', 'puts', 'read', 'seek',
            'socket', 'tell',
            // packages and source files
            'load', 'loadTk', 'package', 'pgk::create', 'pgk_mkIndex', 'source',
            // interpreter routines
            'bgerror', 'history', 'info', 'interp', 'memory', 'unknown',
            // library routines
            'enconding', 'http', 'msgcat',
            // system related
            'cd', 'clock', 'exec', 'glob', 'pid', 'pwd', 'time',
            // platform specified
            'dde', 'registry', 'resource',
            // special variables
            '$argc', '$argv', '$errorCode', '$errorInfo', '$argv0',
            '$auto_index', '$auto_oldpath', '$auto_path', '$env',
            '$tcl_interactive', '$tcl_libpath', '$tcl_library',
            '$tcl_pkgPath', '$tcl_platform', '$tcl_precision', '$tcl_traceExec',
        ),

        /*
         * Set 3: standard library
         * Replaced by binds
         */
        3 => array(
            //'comment', 'filename', 'library', 'packagens', 'tcltest', 'tclvars',
            'act',
            'away',
            'bcst',
            'bot',
            'chat',
            'chjn',
            'chof',
            'chon',
            'chpt',
            'cron',
            'ctcp',
            'ctcr',
            'dcc',
            'disc',
            'evnt',
            'fil',
            'filt',
            'flud',
            'kick',
            'link',
            'log',
            'lost',
            'mode',
            'msg',
            'msgm',
            'need',
            'nick',
            'nkch',
            'notc',
            'note',
            'out',
            'part',
            'pub',
            'pubm',
            'raw',
            'rcvd',
            'rejn',
            'sent',
            'sign',
            'splt',
            'topc',
            'tout',
            'unld',
            'wall',
        ),

        /*
         * Set 4: tcl-commands (eggdrop dedicated)
         */
        4 => array(
            'addbot',
            'addchanrec',
            'adduser',
            'assoc',

            'backup',
            'banlist',
            'bind',
            'binds',
            'boot',
            'botattr',
            'botishalfop',
            'botisop',
            'botisvoice',
            'botlist',
            'botonchan',
            'bots',

            'callevent',
            'chanbans',
            'chanexempts',
            'chaninvites',
            'chanlist',
            'channel',
            'channels',
            'chansettype',
            'chattr',
            'chhandle',
            'clearqueue',
            'compressfile',
            'connect',
            'console',
            'control',
            'countusers',
            'cp',
            'ctime',

            'dccbroadcast',
            'dccdumpfile',
            'dcclist',
            'dccputchan',
            'dccsend',
            'dccsimul',
            'dccused',
            'decrypt',
            'delchanrec',
            'delhost',
            'deludef',
            'deluser',
            'die',
            'dnslookup',
            'dumpfile',
            'duration',

            'echo',
            'encpass',
            'encrypt',
            'erasenotes',
            'exemptlist',

            'filesend',
            'finduser',
            'flushmode',

            'getchan',
            'getchanhost',
            'getchanidle',
            'getchaninfo',
            'getchanjoin',
            'getchanmode',
            'getdccaway',
            'getdccidle',
            'getdesc',
            'getdirs',
            'getfileq',
            'getfiles',
            'getfilesendtime',
            'getflags',
            'getlink',
            'getowner',
            'getpwd',
            'getting',
            'getudefs',
            'getuser',

            'hand',
            'handonchan',

            'idx',
            'ignorelist',
            'invitelist',
            'isban',
            'isbansticky',
            'isbotnick',
            'ischanban',
            'ischanexempt',
            'ischaninvite',
            'ischanjuped',
            'iscompressed',
            'isdynamic',
            'isexempt',
            'isexemptsticky',
            'ishalfop',
            'isignore',
            'isinvite',
            'isinvitesticky',
            'islinked',
            'isop',
            'ispermban',
            'ispermexempt',
            'isperminvite',
            'isvoice',

            'jump',

            'killassoc',
            'killban',
            'killchanban',
            'killchanexempt',
            'killchaninvite',
            'killdcc',
            'killexempt',
            'killignore',
            'killinvite',
            'killtimer',
            'killutimer',

            'listen',
            'listnotes',
            'loadchannels',
            'loadhelp',
            'loadmodule',
            'logfile',

            'maskhost',
            'matchaddr',
            'matchattr',
            'matchban',
            'matchcidr',
            'matchexempt',
            'matchinvite',
            'matchstr',
            'md',
            'mkdir',
            'modules',
            'mv',
            'myip',

            'newban',
            'newchanban',
            'newchanexempt',
            'newchaninvite',
            'newexempt',
            'newignore',
            'newinvite',
            'notes',

            'onchan',
            'onchansplit',

            'passwdok',
            'pushmode',
            'putallbots',
            'putbot',
            'putcmdlog',
            'putdcc',
            'puthelp',
            'putkick',
            'putlog',
            'putloglev',
            'putnow',
            'putquick',
            'putserv',
            'putxferlog',

            'queuesize',

            'rand',
            'rehash',
            'reload',
            'reloadhelp',
            'renudef',
            'resetbans',
            'resetchan',
            'resetchanidle',
            'resetchanjoin',
            'resetexempts',
            'resetinvites',
            'restart',
            'rmdir',

            'save',
            'savechannels',
            'sendnote',
            'setchan',
            'setchaninfo',
            'setdccaway',
            'setdesc',
            'setflags',
            'setlink',
            'setowner',
            'setpwd',
            'setudef',
            'setuser',
            'stickban',
            'stickexempt',
            'stickinvite',
            'storenote',
            'strftime',
            'strip',
            'stripcodes',

            'timer',
            'timers',
            'topic',
            'traffic',

            'unames',
            'unbind',
            'uncompressfile',
            'unixtime',
            'unlink',
            'unloadhelp',
            'unloadmodule',
            'unstickban',
            'unstickexempt',
            'unstickinvite',
            'userlist',
            'utimer',
            'utimers',

            'validchan',
            'valididx',
            'validuser',

            'washalfop',
            'wasop',
            'whom',
        )
    ),
    'SYMBOLS' => array(
        '(', ')', '[', ']', '{', '}', '$', '*', '&', '%', '!', ';', '<', '>', '?'
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #ff7700;font-weight:bold;',    // Reserved
            2 => 'color: #008000;',                        // Built-ins + self
            3 => 'color: #dc143c;',                        // Standard lib
            4 => 'color: #0000cd;'                        // Special methods
        ),
        'COMMENTS' => array(
            1 => 'color: #808080; font-style: italic;',
//            2 => 'color: #483d8b;',
            'MULTI' => 'color: #808080; font-style: italic;'
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
        ),
        'BRACKETS' => array(
            0 => 'color: black;'
        ),
        'STRINGS' => array(
            0 => 'color: #483d8b;'
        ),
        'NUMBERS' => array(
            0 => 'color: #ff4500;'
        ),
        'METHODS' => array(
            1 => 'color: black;'
        ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
        ),
        'REGEXPS' => array(
            0 => 'color: #ff3333;'
        ),
        'SCRIPT' => array()
    ),
    'URLS' => array(
        1 => 'http://wiki.tcl.tk/{FNAMEL}',
        2 => 'http://wiki.tcl.tk/{FNAMEUF}',
        3 => 'http://wiki.eggdrop.fr/Binds#{FNAMEU}',
        4 => 'http://wiki.eggdrop.fr/{FNAMEUF}'
    ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '::'
    ),
    'REGEXPS' => array(
        //Special variables
        0 => '[\\$]+[a-zA-Z_][a-zA-Z0-9_]*',
    ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array(),
    'PARSER_CONTROL' => array(
        'COMMENTS' => array(
            'DISALLOWED_BEFORE' => '\\'
        )
    )
);
