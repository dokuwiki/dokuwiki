<?php
/*************************************************************************************
 * standardml.php
 * ----------
 * Author: eldesh (nephits@gmail.com)
 * Copyright: (c) 2014 eldesh (http://d.hatena.ne.jp/eldesh/)
 * Release Version: 1.0.9.0
 * Date Started: 2014/02/04
 *
 * SML (StandardML'97) language file for GeSHi.
 * This file also support some implementation dependent keywords by SML/NJ and SML#.
 *
 * CHANGES
 * -------
 * 2014/02/05 (1.0.8.11)
 *   -  First Release
 *
 * TODO (updated 2014/02/04)
 * -------------------------
 * - support character literal
 * - support Vector expressions and patterns (http://www.smlnj.org/doc/features.html)
 * - support more Basis functions...?
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
    'LANG_NAME' => 'StandardML',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array('(*' => '*)'),
    'COMMENT_REGEXP' => array(1 => '/\(\*(?:(?R)|.)+?\*\)/s'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '\\',
    'NUMBERS' =>
        array(
            /* integer dec */
            0 => GESHI_NUMBER_INT_BASIC,
            /* integer hex */
            1 => GESHI_NUMBER_HEX_PREFIX,
            /* real */
            2 => GESHI_NUMBER_FLT_SCI_ZERO,
            /* word dec */
            3 => '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])0w[0-9]+?(?![0-9a-z]|\.(?:[eE][+\-]?)?\d)',
            /* word hex */
            4 => '(?<![0-9a-z_\.])(?<![\d\.]e[+\-])0wx[0-9a-fA-F]+?(?![0-9a-z]|\.(?:[eE][+\-]?)?\d)'
        ),
    'KEYWORDS' => array(
        /* main SML keywords */
        1 => array(
            /* deprecated: SML90 */
            'abstype',

            'and', 'andalso', 'as', 'case', 'datatype', 'else',
            'end', 'exception', 'fn', 'fun', 'functor',
            'if', 'in', 'infix', 'infixr', 'let', 'local', 'nonfix',
            'of', 'op', 'open', 'orelse',
            'rec', 'raise', 'sharing', 'sig', 'signature', 'struct', 'structure', 'then',
            'type', 'val', 'while', 'with', 'withtype'
            ),
        /* Top-level type and constructors */
        2 => array(
            'unit', 'int', 'word', 'real', 'char', 'string', 'substring', 'exn',
            'array', 'vector', 'bool', 'option',
            'list'
            ),
        /* standard structures/signatures/functors provided by Basis library */
        3 => array(
            'ARRAY', 'Array', 'Array2', 'ARRAY2', 'ArraySlice', 'ARRAY_SLICE',
            'BinIO', 'BIT_FLAGS', 'Bool', 'BOOL', 'Byte', 'CHAR', 'Char',
            'CommandLine', 'Date', 'General', 'GenericSock', 'IEEEReal', 'IMPERATIVE_IO',
            'ImperativeIO', 'INetSock', 'INTEGER', 'Int', 'IntInf', 'IO', 'List', 'ListPair',
            'MATH', 'MONO_ARRAY', 'MONO_ARRAY2', 'MONO_ARRAY_SLICE', 'MONO_VECTOR',
            'MONO_VECTOR_SLICE', 'NetHostDB', 'NetProtDB', 'NetServDB', 'Option',
            'OS', 'OS.FileSys', 'OS.IO', 'OS.Path', 'OS.Process', 'PACK_REAL', 'PACK_WORD',
            'Posix', 'Posix.Error', 'Posix.FileSys', 'Posix.IO', 'Posix.ProcEnv', 'Posix.Process',
            'Posix.Signal', 'Posix.SysDB', 'Posix.TTY', 'PRIM_IO', 'PrimIO', 'REAL', 'Real', 'Socket',
            'STREAM_IO', 'StreamIO', 'STRING', 'String', 'StringCvt', 'SUBSTRING', 'Substring', 'TEXT', 'TEXT_IO',
            'TEXT_STREAM_IO', 'Time', 'Timer', 'Unix', 'UnixSock', 'VECTOR', 'Vector', 'VECTOR_SLICE',
            'Windows', 'WORD', 'Word'
            ),
        /* Top-level value identifiers / constructors */
        4 => array(
            'app', 'before', 'ceil', 'chr', 'concat', 'exnMessage', 'exnName', 'explode',
            'floor', 'foldl', 'foldr', 'getOpt', 'hd', 'ignore', 'implode', 'isSome', 'length', 'map', 'not',
            'null', 'o', 'ord', 'print', 'rev', 'round', 'size', 'str', 'tl', 'trunc',
            'use', 'valOf',
            /* constructors */
            'ref', 'true', 'false', 'NONE', 'SOME', 'LESS', 'EQUAL', 'GREATER', 'nil',
            /* overloaded identifiers */
            'div', 'mod', 'abs'
            ),
        /* standard exceptions */
        5 => array (
            'Bind', 'Chr', 'Div', 'Domain', 'Empty', 'Fail', 'Match', 'Overflow', 'Size', 'Span', 'Subscript'
            ),
        /* implementation dependent keyword (not be sorted) */
        6 => array (
            /** SML/NJ */
            /* functor signature > http://www.smlnj.org/doc/features.html */
            'funsig',
            /* lazy evaluation */
            'lazy',
            /** SML# */
            /* binding to C function */
            '_import',
            /* read other source */
            '_require',
            /* export aggregated interface files */
            'include',
            /* integrated sql */
            '_sqlserver', '_sql', 'from', 'where', '_sqleval', '_sqlexec',
            'select', 'insert', 'update', 'begin', 'commit', 'rollback',
            'values', 'delete'
            )
        ),
    /* highlighting symbols */
    'SYMBOLS' => array(
        0 => array('=', ':', ':>', '=>', '(', ')', '|', '_', '==', ';', '.'),
        1 => array('!', ':=', '@', '^'),
        2 => array('[', ']', '::', '{', '}'),
        /* overloaded identifiers */
        3 => array('+', '-', '*', '/', '~', '<', '>', '<=', '>=')
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true, /* keywords */
        2 => true, /* top level types */
        3 => true, /* structures */
        4 => true, /* top level identifiers */
        5 => true, /* top level exceptions */
        6 => true  /* implementation dependent keyword */
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #557cde; font-weight: bold;',
            2 => 'color: #8dda4a; font-weight: bold;',
            3 => 'color: #0066cc; font-weight: bold;',
            4 => 'color: #5c8cbb;',
            5 => 'color: #f33e64; font-weight: bold;',
            6 => 'color: #f33e64;'
            ),
        'COMMENTS' => array(
            'MULTI' => 'color: #5d478b; font-style: italic;', /* light purple */
            1 => 'color: #5d478b; font-style: italic;' /* light purple */
            ),
        'ESCAPE_CHAR' => array(
            ),
        'BRACKETS' => array(
            0 => 'color: #79c200;'
            ),
        'STRINGS' => array(
            0 => 'color: #488614;'
            ),
        'NUMBERS' => array(
            0 => 'color: #fb7600;',
            1 => 'color: #fb7600;',
            2 => 'color: #fb7600;',
            3 => 'color: #fb7600;',
            4 => 'color: #fb7600;'
            ),
        'METHODS' => array(
            1 => 'color: #0066cc;'
            ),
        'REGEXPS' => array(
            1 => 'font-style:italic; color:#9f7eff;',
            2 => 'font-weight:bold; color:#8dda4a;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #ff4bcf;',
            1 => 'color: #ff4bcf; font-weight: bold;', // pink
            2 => 'color: #90f963;', // orange
            3 => 'color: #fa5bf8;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => 'http://www.standardml.org/Basis/top-level-chapter.html',
        3 => '',
        4 => '',
        5 => 'http://www.standardml.org/Basis/top-level-chapter.html#section:2',
        6 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
        ),
    'REGEXPS' => array(
        1 => '(?<!\w)#\w+',  /* record field access */
        2 => '(?:(?<![0-9a-zA-Z]))\'[a-z]+' /* type variable */
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);
