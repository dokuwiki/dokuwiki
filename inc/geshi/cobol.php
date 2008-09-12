<?php
/*************************************************************************************
 * cobol.php
 * ----------
 * Author: BenBE (BenBE@omorphia.org)
 * Copyright: (c) 2007-2008 BenBE (http://www.omorphia.de/)
 * Release Version: 1.0.8
 * Date Started: 2007/07/02
 *
 * COBOL language file for GeSHi.
 *
 * CHANGES
 * -------
 *
 * TODO (updated 2007/07/02)
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
    'LANG_NAME' => 'COBOL',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(1 => '/^\*.*?$/m'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"', "'"),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array( //Compiler Directives
            "ANSI", "BLANK", "NOBLANK", "CALL-SHARED", "CANCEL", "NOCANCEL",
            "CHECK", "CODE", "NOCODE", "COLUMNS", "COMPACT", "NOCOMPACT",
            "COMPILE", "CONSULT", "NOCONSULT", "CROSSREF", "NOCROSSREF",
            "DIAGNOSE-74", "NODIAGNOSE-74", "DIAGNOSE-85", "NODIAGNOSE-85",
            "DIAGNOSEALL", "NODIAGNOSEALL", "ENDIF", "ENDUNIT", "ENV",
            "ERRORFILE", "ERRORS", "FIPS", "NOFIPS", "FMAP", "HEADING", "HEAP",
            "HIGHPIN", "HIGHREQUESTERS", "ICODE", "NOICODE", "IF", "IFNOT",
            "INNERLIST", "NOINNERLIST", "INSPECT", "NOINSPECT", "LARGEDATA",
            "LD", "LESS-CODE", "LIBRARY", "LINES", "LIST", "NOLIST", "LMAP",
            "NOLMAP", "MAIN", "MAP", "NOMAP", "NLD", "NONSTOP", "NON-SHARED",
            "OPTIMIZE", "PERFORM-TRACE", "PORT", "NOPORT", "RESETTOG",
            "RUNNABLE", "RUNNAMED", "SAVE", "SAVEABEND", "NOSAVEABEND",
            "SEARCH", "NOSEARCH", "SECTION", "SETTOG", "SHARED", "SHOWCOPY",
            "NOSHOWCOPY", "SHOWFILE", "NOSHOWFILE", "SOURCE", "SQL", "NOSQL",
            "SQLMEM", "SUBSET", "SUBTYPE", "SUPPRESS", "NOSUPPRESS", "SYMBOLS",
            "NOSYMBOLS", "SYNTAX", "TANDEM", "TRAP2", "NOTRAP2", "TRAP2-74",
            "NOTRAP2-74", "UL", "WARN", "NOWARN"
            ),
        2 => array( //Statement Keywords
            "ACCEPT", "ADD", "TO", "GIVING", "CORRESPONDING", "ALTER", "CALL",
            "CANCEL", "CHECKPOINT", "CLOSE", "COMPUTE", "CONTINUE", "COPY",
            "DELETE", "DISPLAY", "DIVIDE", "INTO", "REMAINDER", "ENTER",
            "COBOL", "EVALUATE", "EXIT", "GO", "IF", "INITIALIZE", "INSPECT",
            "TALLYING", "REPLACING", "CONVERTING", "LOCKFILE", "MERGE", "MOVE",
            "CORRESPONDING", "MULTIPLY", "BY", "OPEN", "PERFORM", "TIMES",
            "UNTIL", "VARYING", "READ", "RELEASE", "REPLACE", "RETURN",
            "REWRITE", "SEARCH", "SET", "UP", "DOWN", "SORT", "START",
            "STARTBACKUP", "STOP", "STRING", "SUBTRACT", "FROM", "UNLOCKFILE",
            "UNLOCKRECORD", "UNSTRING", "USE", "DEBUGGING", "AFTER",
            "EXCEPTION", "WRITE"
            ),
        3 => array( //Reserved in some contexts
            "ACCESS", "ADDRESS", "ADVANCING", "AFTER", "ALL",
            "ALPHABET", "ALPHABETIC", "ALPHABETIC-LOWER", "ALPHABETIC-UPPER",
            "ALPHANUMERIC", "ALPHANUMERIC-EDITED", "ALSO", "ALTER", "ALTERNATE",
            "AND", "ANY", "APPROXIMATE", "AREA", "AREAS", "ASCENDING", "ASSIGN",
            "AT", "AUTHOR", "BEFORE", "BINARY", "BLANK", "BLOCK", "BOTTOM", "BY",
            "CALL", "CANCEL", "CD", "CF", "CH", "CHARACTER", "CHARACTERS",
            "CHARACTER-SET", "CHECKPOINT", "CLASS", "CLOCK-UNITS", "CLOSE",
            "COBOL", "CODE", "CODE-SET", "COLLATING", "COLUMN", "COMMA",
            "COMMON", "COMMUNICATION", "COMP", "COMP-3", "COMP-5",
            "COMPUTATIONAL", "COMPUTATIONAL-3", "COMPUTATIONAL-5",
            "CONFIGURATION", "CONTAINS", "CONTENT", "CONTINUE", "CONTROL",
            "CONTROLS", "CONVERTING", "COPY", "CORR", "CORRESPONDING", "COUNT",
            "CURRENCY", "DATA", "DATE", "DATE-COMPILED", "DATE-WRITTEN", "DAY",
            "DAY-OF-WEEK", "DE", "DEBUG-CONTENTS", "DEBUG-ITEM", "DEBUG-LINE",
            "DEBUG-SUB-2", "DEBUG-SUB-3", "DEBUGGING", "DECIMAL-POINT",
            "DECLARATIVES", "DEBUG-NAME", "DEBUG-SUB-1", "DELETE", "DELIMITED",
            "DELIMITER", "DEPENDING", "DESCENDING", "DESTINATION", "DETAIL",
            "DISABLE", "DISPLAY", "DIVIDE", "DIVISION", "DOWN", "DUPLICATES",
            "DYNAMIC", "EGI", "ELSE", "EMI", "ENABLE", "END", "END-ADD",
            "END-COMPUTE", "END-DELETE", "END-DIVIDE", "END-EVALUATE", "END-IF",
            "END-MULTIPLY", "END-OF-PAGE", "END-PERFORM", "END-READ",
            "END-RECEIVE", "END-RETURN", "END-REWRITE", "END-SEARCH",
            "END-START", "END-STRING", "END-SUBTRACT", "END-UNSTRING",
            "END-WRITE", "ENTER", "EOP", "EQUAL", "ERROR", "ESI", "EVALUATE",
            "EVERY", "EXCEPTION", "EXCLUSIVE", "EXIT", "EXTEND",
            "EXTENDED-STORAGE", "EXTERNAL", "FALSE", "FD", "FILE",
            "FILE-CONTROL", "FILLER", "FINAL", "FIRST", "FOOTING", "FOR",
            "FROM", "FUNCTION", "GENERATE", "GENERIC", "GIVING", "GLOBAL",
            "GO", "GREATER", "GROUP", "GUARDIAN-ERR", "HEADING", "HIGH-VALUE",
            "HIGH-VALUES", "I-O", "I-O-CONTROL", "IDENTIFICATION", "IF", "IN",
            "INDEX", "INDEXED", "INDICATE", "INITIAL", "INITIALIZE", "INITIATE",
            "INPUT", "INPUT-OUTPUT", "INSPECT", "INSTALLATION", "INTO",
            "INVALID", "IS", "JUST", "JUSTIFIED", "KEY", "LABEL", "LAST",
            "LEADING", "LEFT", "LENGTH", "LESS", "LIMIT", "LIMITS", "LINAGE",
            "LINAGE-COUNTER", "LINE", "LINE-COUNTER", "LINKAGE", "LOCK",
            "LOCKFILE", "LOW-VALUE", "LOW-VALUES", "MEMORY", "MERGE", "MESSAGE",
            "MODE", "MODULES", "MOVE", "MULTIPLE", "MULTIPLY", "NATIVE",
            "NEGATIVE", "NEXT", "NO", "NOT", "NULL", "NULLS", "NUMBER",
            "NUMERIC", "NUMERIC-EDITED", "OBJECT-COMPUTER", "OCCURS", "OF",
            "OFF", "OMITTED", "ON", "OPEN", "OPTIONAL", "OR", "ORDER",
            "ORGANIZATION", "OTHER", "OUTPUT", "OVERFLOW", "PACKED-DECIMAL",
            "PADDING", "PAGE", "PAGE-COUNTER", "PERFORM", "PF", "PH", "PIC",
            "PICTURE", "PLUS", "POINTER", "POSITION", "POSITIVE", "PRINTING",
            "PROCEDURE", "PROCEDURES", "PROCEED", "PROGRAM", "PROGRAM-ID",
            "PROGRAM-STATUS", "PROGRAM-STATUS-1", "PROGRAM-STATUS-2", "PROMPT",
            "PROTECTED", "PURGE", "QUEUE", "QUOTE", "QUOTES", "RANDOM", "RD",
            "READ", "RECEIVE", "RECEIVE-CONTROL", "RECORD", "RECORDS",
            "REDEFINES", "REEL", "REFERENCE", "REFERENCES", "RELATIVE",
            "RELEASE", "REMAINDER", "REMOVAL", "RENAMES", "REPLACE",
            "REPLACING", "REPLY", "REPORT", "REPORTING", "REPORTS", "RERUN",
            "RESERVE", "RESET", "REVERSED", "REWIND", "REWRITE", "RF",
            "RH", "RIGHT", "ROUNDED", "RUN", "SAME", "SD", "SEARCH", "SECTION",
            "SECURITY", "SEGMENT", "SEGMENT-LIMIT", "SELECT", "SEND",
            "SENTENCE", "SEPARATE", "SEQUENCE", "SEQUENTIAL", "SET", "SHARED",
            "SIGN", "SIZE", "SORT", "SORT-MERGE", "SOURCE", "SOURCE-COMPUTER",
            "SPACE", "SPACES", "SPECIAL-NAMES", "STANDARD", "STANDARD-1",
            "STANDARD-2", "START", "STARTBACKUP", "STATUS", "STOP", "STRING",
            "SUB-QUEUE-1", "SUB-QUEUE-2", "SUB-QUEUE-3", "SUBTRACT", "SUM",
            "SUPPRESS", "SYMBOLIC", "SYNC", "SYNCDEPTH", "SYNCHRONIZED",
            "TABLE", "TAL", "TALLYING", "TAPE", "TERMINAL", "TERMINATE", "TEST",
            "TEXT", "THAN", "THEN", "THROUGH", "THRU", "TIME", "TIMES", "TO",
            "TOP", "TRAILING", "TRUE", "TYPE", "UNIT", "UNLOCK", "UNLOCKFILE",
            "UNLOCKRECORD", "UNSTRING", "UNTIL", "UP", "UPON", "USAGE", "USE",
            "USING", "VALUE", "VALUES", "VARYING", "WHEN", "WITH", "WORDS",
            "WORKING-STORAGE", "WRITE", "ZERO", "ZEROES"
            ),
        4 => array( //Standard functions
            "ACOS", "ANNUITY", "ASIN", "ATAN", "CHAR", "COS", "CURRENT-DATE",
            "DATE-OF-INTEGER", "DAY-OF-INTEGER", "FACTORIAL", "INTEGER",
            "INTEGER-OF-DATE", "INTEGER-OF-DAY", "INTEGER-PART", "LENGTH",
            "LOG", "LOG10", "LOWER-CASE", "MAX", "MEAN", "MEDIAN", "MIDRANGE",
            "MIN", "MOD", "NUMVAL", "NUMVAL-C", "ORD", "ORD-MAX", "ORD-MIN",
            "PRESENT-VALUE", "RANDOM", "RANGE", "REM", "REVERSE", "SIN", "SQRT",
            "STANDARD-DEVIATION", "SUM", "TAN", "UPPER-CASE", "VARIANCE",
            "WHEN-COMPILED"
            ),
        5 => array( //Privileged Built-in Functions
            "#IN", "#OUT", "#TERM", "#TEMP", "#DYNAMIC", "COBOL85^ARMTRAP",
            "COBOL85^COMPLETION", "COBOL_COMPLETION_", "COBOL_CONTROL_",
            "COBOL_GETENV_", "COBOL_PUTENV_", "COBOL85^RETURN^SORT^ERRORS",
            "COBOL_RETURN_SORT_ERRORS_", "COBOL85^REWIND^SEQUENTIAL",
            "COBOL_REWIND_SEQUENTIAL_", "COBOL85^SET^SORT^PARAM^TEXT",
            "COBOL_SET_SORT_PARAM_TEXT_", "COBOL85^SET^SORT^PARAM^VALUE",
            "COBOL_SET_SORT_PARAM_VALUE_", "COBOL_SET_MAX_RECORD_",
            "COBOL_SETMODE_", "COBOL85^SPECIAL^OPEN", "COBOL_SPECIAL_OPEN_",
            "COBOLASSIGN", "COBOL_ASSIGN_", "COBOLFILEINFO", "COBOL_FILE_INFO_",
            "COBOLSPOOLOPEN", "CREATEPROCESS", "ALTERPARAMTEXT",
            "CHECKLOGICALNAME", "CHECKMESSAGE", "DELETEASSIGN", "DELETEPARAM",
            "DELETESTARTUP", "GETASSIGNTEXT", "GETASSIGNVALUE", "GETBACKUPCPU",
            "GETPARAMTEXT", "GETSTARTUPTEXT", "PUTASSIGNTEXT", "PUTASSIGNVALUE",
            "PUTPARAMTEXT", "PUTSTARTUPTEXT"
            )
        ),
    'SYMBOLS' => array(
        //Avoid having - in identifiers marked as symbols
        ' + ', ' - ', ' * ', ' / ', ' ** ',
        '.', ',',
        '=',
        '(', ')', '[', ']'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000080; font-weight: bold;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #008000; font-weight: bold;',
            4 => 'color: #000080;',
            5 => 'color: #008000;',
            ),
        'COMMENTS' => array(
            1 => 'color: #a0a0a0; font-style: italic;',
            'MULTI' => 'color: #a0a0a0; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #339933;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #993399;'
            ),
        'METHODS' => array(
            1 => 'color: #202020;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #000066;'
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
        4 => '',
        5 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
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
