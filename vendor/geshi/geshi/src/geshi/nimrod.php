<?php
/*************************************************************************************
 * nimrod.php
 * ----------
 * Author: Dennis Felsing (dennis@felsin9.de)
 * Copyright: (c) 2014 Dennis Felsing
 * Release Version: 1.0.9.0
 * Date Started: 2014/07/15
 *
 * Nimrod language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2014/07/15 (1.0.8.13)
 *  -  First Release
 *
 * TODO (updated 2014/07/15)
 * -------------------------
 * - Int literals like 50'u8
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
    'LANG_NAME' => 'Nimrod',
    'COMMENT_SINGLE' => array(1 => '#'),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    //Longest quotemarks ALWAYS first
    'QUOTEMARKS' => array('"""', '"'),
    'ESCAPE_CHAR' => '\\',
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_BIN_PREFIX_0B |
        GESHI_NUMBER_OCT_PREFIX_0O | GESHI_NUMBER_HEX_PREFIX |
        GESHI_NUMBER_FLT_NONSCI | GESHI_NUMBER_FLT_NONSCI_F |
        GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,
    'KEYWORDS' => array(

        /*
        ** Set 1: reserved words
        ** http://nimrod-lang.org/manual.html#identifiers-keywords
        */
        1 => array(
            'addr', 'and', 'as', 'asm', 'atomic',
            'bind', 'block', 'break',
            'case', 'cast', 'const', 'continue', 'converter',
            'discard', 'distinct', 'div', 'do',
            'elif', 'else', 'end', 'enum', 'except', 'export',
            'finally', 'for', 'from',
            'generic',
            'if', 'import', 'in', 'include', 'interface', 'is', 'isnot', 'iterator',
            'lambda', 'let',
            'macro', 'method', 'mixin', 'mod',
            'nil', 'not', 'notin',
            'object', 'of', 'or', 'out',
            'proc',
            'raise', 'ref', 'return',
            'shl', 'shr', 'static',
            'template', 'try', 'tuple', 'type',
            'using',
            'var',
            'when', 'while', 'with', 'without',
            'xor',
            'yield'
            ),

        2 => array(
            'true', 'false'
            ),

        3 => array(
            /* system module */
            'abs', 'accumulateResult', 'add', 'addAndFetch', 'addQuitProc',
            'alloc', 'alloc0', 'allocCStringArray', 'allocShared',
            'allocShared0', 'assert', 'astToStr', 'atomicDec', 'atomicInc',
            'card', 'chr', 'clamp', 'close', 'cmp', 'compileOption',
            'compiles', 'contains', 'copy', 'copyMem', 'countdown', 'countup',
            'create', 'createShared', 'createSharedU', 'createU',
            'cstringArrayToSeq', 'currentSourcePath', 'dealloc',
            'deallocCStringArray', 'deallocShared', 'debugEcho', 'dec',
            'defined', 'definedInScope', 'del', 'delete', 'doAssert', 'each',
            'echo', 'endOfFile', 'equalMem', 'excl', 'failedAssertImpl',
            'fieldPairs', 'fields', 'fileHandle', 'find', 'finished',
            'flushFile', 'free', 'freeShared', 'GC_addCycleRoot', 'GC_disable',
            'GC_disableMarkAndSweep', 'GC_enable', 'GC_enableMarkAndSweep',
            'GC_fullCollect', 'GC_getStatistics', 'gcInvariant', 'GC_ref',
            'GC_setStrategy', 'GC_unref', 'getCurrentException',
            'getCurrentExceptionMsg', 'getFilePos', 'getFileSize',
            'getFreeMem', 'getOccupiedMem', 'getRefcount', 'getStackTrace',
            'getTotalMem', 'getTypeInfo', 'gorge', 'high', 'inc', 'incl',
            'insert', 'instantiationInfo', 'internalNew', 'isNil', 'isOnStack',
            'isStatic', 'items', 'len', 'likely', 'lines', 'locals', 'low',
            'map', 'max', 'min', 'moveMem', 'new', 'newException', 'newSeq',
            'newString', 'newStringOfCap', 'newWideCString', 'nimDestroyRange',
            'onFailedAssert', 'onRaise', 'open', 'ord', 'pairs', 'pop', 'pred',
            'quit', 'raiseAssert', 'rand', 'rawEnv', 'rawProc', 'readAll',
            'readBuffer', 'readBytes', 'readChar', 'readChars', 'readFile',
            'readLine', 'realloc', 'reallocShared', 'reopen', 'repr', 'reset',
            'resize', 'safeAdd', 'setControlCHook', 'setFilePos', 'setLen',
            'shallow', 'shallowCopy', 'sizeof', 'slurp', 'staticExec',
            'staticRead', 'stdmsg', 'substr', 'succ', 'swap', 'toBiggestFloat',
            'toBiggestInt', 'toFloat', 'toInt', 'toU16', 'toU32', 'toU8',
            'unlikely', 'unsafeNew', 'write', 'writeBuffer', 'writeBytes',
            'writeChars', 'writeFile', 'writeln', 'writeStackTrace', 'ze',
            'ze64', 'zeroMem'
            ),

        4 => array(
            'auto', 'pointer', 'ptr', 'void', 'any', 'expr', 'stmt', 'typedesc',
            'int', 'int8', 'int16', 'int32', 'int64', 'float', 'float32', 'float64',
            'uint', 'uint8', 'uint16', 'uint32', 'uint64',
            'bool', 'char', 'range', 'array', 'seq', 'set', 'string', 'TSlice',
            'cstring', 'cint', 'clong', 'culong', 'cchar', 'cschar', 'cshort',
            'csize', 'clonglong', 'cfloat', 'cdouble', 'clongdouble', 'cuchar',
            'cushort', 'cuint', 'culonglong', 'cstringArray'
            )
        ),
    'SYMBOLS' => array(
        '*', '/', '%', '\\',
        '+', '-', '~', '|',
        '&',
        '..',
        '=', '<', '>', '!',
        '@', '?'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #ff7700;font-weight:bold;',    // Reserved
            2 => 'color: #008000;',                     // Built-ins + self
            3 => 'color: #dc143c;',                     // Standard lib
            4 => 'color: #0000cd;'                      // Special methods
            ),
        'COMMENTS' => array(
            1 => 'color: #808080; font-style: italic;',
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
            ),
        'SCRIPT' => array(
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
