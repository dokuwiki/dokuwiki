<?php
/*************************************************************************************
 * julia.php
 * -----------
 * Author: John Lynch (john.lynch@iname.com)
 * Contributors:
 *   - Magnus Lie Hetland
 *   - Curtis Vogt
 * Copyright: (c) 2013 John Lynch
 * Release Version: 1.0.9.0
 * Date Started: 2013/11/06
 *
 * Julia language file for GeSHi.
 *
 * CHANGES
 * -------
 *
 * 2015/11/04
 *   -  First Release submitted
 *
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
    'LANG_NAME' => 'Julia',
    'COMMENT_SINGLE' => array(1 => '#'),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    //Longest quotemarks ALWAYS first
    'QUOTEMARKS' => array('"""', '"', "'"),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(

        /*
        ** reserved words
        */
        1 => array(
            'function', 'global', 'for', 'end', 'while', 'if', 'else', 'elseif', 'break',
            'switch', 'case', 'otherwise', 'try', 'catch', 'const', 'immutable',
            'import', 'importall', 'export', 'type', 'typealias', 'return', 'true',
            'false', 'macro', 'quote', 'in', 'abstract', 'module', 'using', 'continue',
            'ifelse', 'do', 'eval', 'let', 'finally', 'throw'
        ),

        /*
        ** builtins
        */
        2 => array(
            'Array', 'String', 'Bool', 'Number', 'Int', 'Integer', 'Real', 'Complex',
            'FloatingPoint', 'Float64', 'Float32', 'Int8', 'Int16', 'Int32', 'Int64',
            'Rational', 'AbstractArray', 'Unsigned', 'Signed', 'Uint', 'Uint8', 'Uint16',
            'Uint32', 'Uint64', 'Vector', 'AbstractVector', 'Matrix', 'AbstractMatrix',
            'Type', 'IO', 'Any', 'ASCIIString', 'Union', 'Dict', 'Function', 'SubArray',
            'Range', 'Range1', 'Symbol', 'Expr',

            'cell', 'collect', 'filter', 'merge', 'divrem', 'hex', 'dec', 'oct', 'base',
            'int', 'round', 'cmp', 'float', 'linspace', 'fill', 'start', 'done', 'tuple',
            'minimum', 'maximum', 'count', 'index', 'append', 'push', 'pop', 'shift',
            'unshift', 'insert', 'splice', 'reverse', 'sort', 'zip', 'length', 'delete',
            'copy', 'haskey', 'keys', 'values', 'get', 'getkey', 'Set', 'isa', 'issubset',
            'intersect', 'setdiff', 'symdiff', 'complement', 'print', 'printf', 'println',
            'sprintf', 'utf8', 'char', 'search', 'rsearch',
            'replace', 'lowercase', 'uppercase', 'ucfirst', 'lcfirst', 'union',
            'split', 'rsplit', 'chop', 'chomp', 'lpad', 'rpad', 'lstrip', 'rstrip',
            'strip', 'isalnum', 'isalpha', 'isascii', 'isblank', 'iscntrl', 'isdigit',
            'isgraph', 'islower', 'isprint', 'ispunct', 'isspace', 'isupper', 'isxdigit',
            'match', 'captures', 'offset', 'offsets', 'matchall', 'eachmatch', 'hcat',
            'vcat', 'hvcat', 'reshape', 'deepcopy', 'similar', 'reinterpret', 'map',
            'reduce', 'mapreduce', 'DataArray', 'DataFrame', 'removeNA', 'replaceNA',
            'colnames', 'head', 'tail', 'describe', 'join', 'groupby', 'by', 'stack',
            'readtable', 'readcsv', 'readdlm', 'writetable', 'writecsv', 'writedlm',
            'require', 'reload', 'include', 'evalfile', 'cd', 'open', 'write', 'close',
            'position', 'seek', 'seekstart', 'skip', 'isopen', 'eof',
            'isreadonly', 'ltoh', 'htol', 'serialize', 'deserialize', 'download',
            'isequal', 'getindex', 'setindex', 'eachline', 'beginswith', 'endswith',
            'parsefloat', 'parseint', 'seekend', 'findnz', 'DivideError', 'addprocs',
            'scale', 'issubnormal', 'readdir', 'mapslices'
        ),

        /*
        ** system interaction
        */
        3 => array(
            'run', 'spawn', 'success', 'process_running', 'process_exited', 'kill',
            'readsfrom', 'writesto', 'readsandwrite', 'detach', 'setenv', 'ENV', 'getpid',
            'clipboard', 'strftime', 'time', 'gethostname', 'getipaddr', 'pwd',
            'mkdir', 'mkpath', 'rmdir', 'ignorestatus'
        ),

        /*
        ** prompt
        */
        4 => array(
            'julia>'
        )
    ),
    'SYMBOLS' => array(
        '(', ')', '[', ']', '{', '}', '*', '&', '|', '%', '!', ';', '<', '>', '?', '`'
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
            1 => 'color: #4266d5;font-weight:bold;',    // Reserved
            2 => 'color: #0000A8;font-weight:bold;',    // Built-ins
            3 => 'color: #0000A8;font-weight:bold;',    // System Interaction
            4 => 'color: #3b972e;font-weight:bold;'     // Prompt
        ),
        'COMMENTS' => array(
            1 => 'color: #888888;',
            'MULTI' => 'color: #888888;'
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
        ),
        'BRACKETS' => array(
            0 => 'color: #3b972e;'
        ),
        'STRINGS' => array(
            0 => 'color: #c93d39;'
        ),
        'NUMBERS' => array(
            0 => 'color: black;'
        ),
        'METHODS' => array(
            1 => 'color: black;'
        ),
        'SYMBOLS' => array(
            0 => 'color: #6bab5b;'
        ),
        'REGEXPS' => array(),
        'SCRIPT' => array()
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
    'REGEXPS' => array(),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
