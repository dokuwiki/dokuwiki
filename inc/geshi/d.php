<?php
/*************************************************************************************
 * d.php
 * -----
 * Author: Thomas Kuehne (thomas@kuehne.cn)
 * Copyright: (c) 2005 Thomas Kuehne (http://thomas.kuehne.cn/)
 * Release Version: 1.0.7.20
 * Date Started: 2005/04/22
 *
 * D language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2005/04/22 (0.0.2)
 *  -  added _d_* and sizeof/ptrdiff_t
 * 2005/04/20 (0.0.1)
 *  -  First release
 *
 * TODO (updated 2005/04/22)
 * -------------------------
 * * nested comments
 * * correct handling of r"" and ``
 * * correct handling of ... and ..
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
    'LANG_NAME' => 'D',
    'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"', "'", '`'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array(
                'while',
                'switch',
                'if',
                'foreach',
                'for',
                'goto',
                'return',
                'else',
                'do',
                'case',
                'continue',
                'break'
            ),
        2 => array(
                'with',
                'union',
                'typeof',
                'typeid',
                'typedef',
                'try',
                'true',
                'throw',
                'this',
                'super',
                'pragma',
                'out',
                'null',
                'new',
                'module',
                'mixin',
                'is',
                'invariant',
                'interface',
                'inout',
                'in',
                'import',
                'function',
                'finally',
                'false',
                'extern',
                'delete',
                'delegate',
                'default',
                'catch',
                'cast',
                'body',
                'assert',
                'asm',
                'alias'
            ),
        3 => array(
                'TypeInfo',
                'SwitchError',
                'OutOfMemoryException',
                'Object',
                'ModuleInfo',
                'Interface',
                'Exception',
                'Error',
                'ClassInfo',
                'ArrayBoundsError',
                'AssertError',
                '_d_throw',
                '_d_switch_ustring',
                '_d_switch_string',
                '_d_switch_dstring',
                '_d_OutOfMemory',
                '_d_obj_eq',
                '_d_obj_cmp',
                '_d_newclass',
                '_d_newbitarray',
                '_d_newarrayi',
                '_d_new',
                '_d_monitorrelease',
                '_d_monitor_prolog',
                '_d_monitor_handler',
                '_d_monitorexit',
                '_d_monitor_epilog',
                '_d_monitorenter',
                '_d_local_unwind',
                '_d_isbaseof2',
                '_d_isbaseof',
                '_d_invariant',
                '_d_interface_vtbl',
                '_d_interface_cast',
                '_d_framehandler',
                '_d_exception_filter',
                '_d_exception',
                '_d_dynamic_cast',
                '_d_delmemory',
                '_d_delinterface',
                '_d_delclass',
                '_d_delarray',
                '_d_criticalexit',
                '_d_criticalenter',
                '_d_create_exception_object',
                '_d_callfinalizer',
                '_d_arraysetlengthb',
                '_d_arraysetlength',
                '_d_arraysetbit2',
                '_d_arraysetbit',
                '_d_arraycopybit',
                '_d_arraycopy',
                '_d_arraycatn',
                '_d_arraycatb',
                '_d_arraycat',
                '_d_arraycast_frombit',
                '_d_arraycast',
                '_d_arrayappendcb',
                '_d_arrayappendc',
                '_d_arrayappendb',
                '_d_arrayappend',
            ),
        4 => array(
                'wchar',
                'volatile',
                'void',
                'version',
                'ushort',
                'unittest',
                'ulong',
                'uint',
                'ucent',
                'ubyte',
                'template',
                'struct',
                'static',
                'synchronized',
                'size_t',
                'short',
                'real',
                'public',
                'protected',
                'private',
                'ptrdiff_t',
                'package',
                'override',
                'long',
                'int',
                'ireal',
                'ifloat',
                'idouble',
                'float',
                'final',
                'export',
                'enum',
                'double',
                'deprecated',
                'debug',
                'dchar',
                'creal',
                'const',
                'class',
                'char',
                'cfloat',
                'cent',
                'cdouble',
                'byte',
                'bool',
                'bit',
                'auto',
                'align',
                'abstract'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '[', ']', '{', '}', '?', '!', ';', ':', ',', '...', '..',
        '+', '-', '*', '/', '%', '&', '|', '^', '<', '>', '=', '~',
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => true,
        1 => true,
        2 => true,
        3 => true,
        4 => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #b1b100;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #aaaadd; font-weight: bold;',
            4 => 'color: #993333;'
            ),
        'COMMENTS' => array(
            1=> 'color: #808080; font-style: italic;',
            2=> 'color: #a1a100;',
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
            1 => 'color: #006600;',
            2 => 'color: #006600;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
            ),
        'SCRIPT' => array(
            ),
        'REGEXPS' => array(
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
