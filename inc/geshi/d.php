<?php
/*************************************************************************************
 * d.php
 * -----
 * Author: Thomas Kuehne (thomas@kuehne.cn)
 * Copyright: (c) 2005 Thomas Kuehne (http://thomas.kuehne.cn/)
 * Release Version: 1\.0\.8
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
    'NUMBERS' => GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_INT_CSTYLE | GESHI_NUMBER_BIN_PREFIX_0B |
                 GESHI_NUMBER_OCT_PREFIX | GESHI_NUMBER_HEX_PREFIX | GESHI_NUMBER_FLT_NONSCI |
                 GESHI_NUMBER_FLT_NONSCI_F | GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,
    'KEYWORDS' => array(
        1 => array(
                'break', 'case', 'continue', 'do', 'else',
                'for', 'foreach', 'goto', 'if', 'return',
                'switch', 'while'
            ),
        2 => array(
                'alias', 'asm', 'assert', 'body', 'cast',
                'catch', 'default', 'delegate', 'delete',
                'extern', 'false', 'finally', 'function',
                'import', 'in', 'inout', 'interface',
                'invariant', 'is', 'mixin', 'module', 'new',
                'null', 'out', 'pragma', 'super', 'this',
                'throw', 'true', 'try', 'typedef', 'typeid',
                'typeof', 'union', 'with'
            ),
        3 => array(
                'ArrayBoundsError', 'AssertError',
                'ClassInfo', 'Error', 'Exception',
                'Interface', 'ModuleInfo', 'Object',
                'OutOfMemoryException', 'SwitchError',
                'TypeInfo', '_d_arrayappend',
                '_d_arrayappendb', '_d_arrayappendc',
                '_d_arrayappendcb', '_d_arraycast',
                '_d_arraycast_frombit', '_d_arraycat',
                '_d_arraycatb', '_d_arraycatn',
                '_d_arraycopy', '_d_arraycopybit',
                '_d_arraysetbit', '_d_arraysetbit2',
                '_d_arraysetlength', '_d_arraysetlengthb',
                '_d_callfinalizer',
                '_d_create_exception_object',
                '_d_criticalenter', '_d_criticalexit',
                '_d_delarray', '_d_delclass',
                '_d_delinterface', '_d_delmemory',
                '_d_dynamic_cast', '_d_exception',
                '_d_exception_filter', '_d_framehandler',
                '_d_interface_cast', '_d_interface_vtbl',
                '_d_invariant', '_d_isbaseof',
                '_d_isbaseof2', '_d_local_unwind',
                '_d_monitorenter', '_d_monitorexit',
                '_d_monitorrelease', '_d_monitor_epilog',
                '_d_monitor_handler', '_d_monitor_prolog',
                '_d_new', '_d_newarrayi', '_d_newbitarray',
                '_d_newclass', '_d_obj_cmp', '_d_obj_eq',
                '_d_OutOfMemory', '_d_switch_dstring',
                '_d_switch_string', '_d_switch_ustring',
                '_d_throw',
            ),
        4 => array(
                'abstract', 'align', 'auto', 'bit', 'bool',
                'byte', 'cdouble', 'cent', 'cfloat', 'char',
                'class', 'const', 'creal', 'dchar', 'debug',
                'deprecated', 'double', 'enum', 'export',
                'final', 'float', 'idouble', 'ifloat', 'int',
                'ireal', 'long', 'override', 'package',
                'private', 'protected', 'ptrdiff_t',
                'public', 'real', 'short', 'size_t',
                'static', 'struct', 'synchronized',
                'template', 'ubyte', 'ucent', 'uint',
                'ulong', 'unittest', 'ushort', 'version',
                'void', 'volatile', 'wchar'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '[', ']', '{', '}', '?', '!', ';', ':', ',', '...', '..',
        '+', '-', '*', '/', '%', '&', '|', '^', '<', '>', '=', '~',
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
            1 => 'color: #b1b100;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #aaaadd; font-weight: bold;',
            4 => 'color: #993333;'
            ),
        'COMMENTS' => array(
            1=> 'color: #808080; font-style: italic;',
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
            0 => 'color: #0000dd;',
            GESHI_NUMBER_BIN_PREFIX_0B => 'color: #208080;',
            GESHI_NUMBER_OCT_PREFIX => 'color: #208080;',
            GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
            GESHI_NUMBER_FLT_SCI_SHORT => 'color:#800080;',
            GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI_F => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI => 'color:#800080;'
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
