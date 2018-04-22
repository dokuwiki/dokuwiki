<?php
/*************************************************************************************
 * phix.php
 * ---------------------------------
 * Author: Pete Lomax
 * Copyright: (c) 2010 Nicholas Koceja
 * Release Version: 1.0.9.0
 * Date Started: 16/08/2015
 *
 * Phix language file for GeSHi.
 *
 * Author's note:  The colors are based on those of Edita.
 *
 * CHANGES
 * -------
 * <date-of-release> (1.0.8.9)
 *  -  First Release
 *
 * TODO (updated <date-of-release>)
 * -------------------------
 * seperate the funtions from the procedures, and have a slight color change for each.
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
    'LANG_NAME' => 'Phix',
    'COMMENT_SINGLE' => array(1 => '--'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(2 => '/\/\*(?:(?R)|.)+?\*\//s'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"', '"""', '`'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array( // keywords
            'and', 'as',
            'break', 'by',
            'case', 'constant',
            'default', 'do',
            'else', 'elsif', 'end', 'enum', 'exit',
            'for', 'forward', 'function',
            'global',
            'if', 'include',
            'not',
            'or',
            'procedure',
            'return',
            'switch',
            'then', 'to', 'type',
            'while', 'with', 'without',
            'xor'
        ),
        2 => array( // directives
            'console',
            'debug',
            'fallthru',
            'fallthrough', 'format',
            'gui',
            'ilASM',
            'jump_table',
            'profile', 'profile_time',
            'trace', 'type_check',
            'warning'
        ),
        3 => array( // built-ins
            'abort',
            'abs',
            'adjust_timedate',
            'allocate',
            'allocate_string',
            'allocate_struct',
            'allow_break',
            'and_bits',
            'append',
            'arccos',
            'arcsin',
            'arctan',
            'atom',
            'atom_to_float32',
            'atom_to_float64',
            'atom_to_float80',

            'bits_to_int',
            'bk_color',
            'bytes_to_int',

            'call',
            'call_back',
            'call_func',
            'call_proc',
            'canonical_path',
            'ceil',
            'change_timezone',
            'chdir',
            'check_break',
            'clear_screen',
            'close',
            'columnize',
            'compare',
            'command_line',
            'cos',
            'crash_file',
            'crash_message',
            'crash_routine',
            'create_thread',
            'current_dir',
            'cursor',
            'custom_sort',
            'c_func',
            'c_proc',

            'date',
            'day_of_week',
            'day_of_year',
            'db_close',
            'db_compress',
            'db_create',
            'db_create_table',
            'db_delete_record',
            'db_delete_table',
            'db_dump',
            'db_fatal_id',
            'db_find_key',
            'db_insert',
            'db_open',
            'db_record_data',
            'db_record_key',
            'db_replace_data',
            'db_select',
            'db_select_table',
            'db_table_list',
            'db_table_size',
            'define_c_func',
            'define_c_proc',
            'define_c_var',
            'define_cfunc',
            'define_cproc',
            'define_struct',
            'delete',
            'delete_cs',
            'delete_routine',
            'dir',
            'display_text_image',

            'enter_cs',
            'equal',
            'exit_thread',

            'factorial',
            'factors',
            'find',
            'flatten',
            'float32_to_atom',
            'float64_to_atom',
            'float80_to_atom',
            'floor',
            'flush',
            'format_timedate',
            'free',
            'free_console',

            'gcd',
            'get',
            'getc',
            'getenv',
            'gets',
            'get_bytes',
            'get_field_details',
            'get_key',
            'get_position',
            'get_proper_path',
            'get_screen_char',
            'get_struct_field',
            'get_struct_size',
            'get_text',
            'get_thread_exitcode',

            'iif',
            'iff',
            'include_paths',
            'init_cs',
            'instance',
            'integer',
            'int_to_bits',
            'int_to_bytes',
            'is_leap_year',

            'join',

            'leave_cs',
            'length',
            'lock_file',
            'log',
            'lower',

            'machine_bits',
            'machine_func',
            'machine_proc',
            'match',
            'max',
            'mem_copy',
            'mem_set',
            'message_box',
            'min',
            'mod',

            'not_bits',

            'object',
            'open',
            'open_dll',
            'or_bits',

            'parse_date_string',
            'peek',
            'peek1s',
            'peek1u',
            'peek2s',
            'peek2u',
            'peek4s',
            'peek4u',
            'peek8s',
            'peek8u',
            'peekNS',
            'peek_string',
            'permute',
            'platform',
            'poke',
            'poke1',
            'poke2',
            'poke4',
            'poke8',
            'pokeN',
            'position',
            'power',
            'prepend',
            'prime_factors',
            'print',
            'printf',
            'prompt_number',
            'prompt_string',
            'puts',
            'put_screen_char',

            'rand',
            'read_bitmap',
            'remainder',
            'repeat',
            'resume_thread',
            'reverse',
            'rfind',
            'round',
            'routine_id',

            'save_bitmap',
            'save_text_image',
            'scanf',
            'scroll',
            'seek',
            'sequence',
            'set_rand',
            'set_struct_field',
            'set_system_doevents',
            'set_timedate_formats',
            'set_timezone',
            'set_unicode',
            'sign',
            'sin',
            'sleep',
            'sort',
            'sprint',
            'sprintf',
            'sqrt',
            'sq_abs',
            'sq_add',
            'sq_and',
            'sq_and_bits',
            'sq_arccos',
            'sq_arcsin',
            'sq_arctan',
            'sq_atom',
            'sq_ceil',
            'sq_cos',
            'sq_div',
            'sq_eq',
            'sq_floor',
            'sq_floor_div',
            'sq_ge',
            'sq_gt',
            'sq_int',
            'sq_le',
            'sq_log',
            'sq_lower',
            'sq_lt',
            'sq_mod',
            'sq_mul',
            'sq_ne',
            'sq_not',
            'sq_not_bits',
            'sq_or',
            'sq_or_bits',
            'sq_power',
            'sq_rand',
            'sq_round',
            'sq_rmdr',
            'sq_seq',
            'sq_sign',
            'sq_sin',
            'sq_sqrt',
            'sq_str',
            'sq_sub',
            'sq_tan',
            'sq_trunc',
            'sq_uminus',
            'sq_upper',
            'sq_xor',
            'sq_xor_bits',
            'string',
            'substitute',
            'sum',
            'suspend_thread',
            'system',
            'system_exec',
            'system_open',
            'system_wait',

            'tagset',
            'tan',
            'task_clock_stop',
            'task_clock_start',
            'task_create',
            'task_list',
            'task_schedule',
            'task_self',
            'task_status',
            'task_suspend',
            'task_yield',
            'text_color',
            'text_rows',
            'time',
            'timedate',
            'timedelta',
            'trunc',
            'try_cs',

            'unlock_file',
            'upper',

            'value',
            'video_config',

            'wait_key',
            'wait_thread',
            'walk_dir',
            'where',
            'wildcard_file',
            'wildcard_match',
            'wrap',

            'xor_bits'
        ),
    ),
    'SYMBOLS' => array(
        0 => array(
            '(', ')', '{', '}', '[', ']'
        ),
        1 => array(
            '+', '-', '*', '/', '=', '&', '^', '?', ',', ':'
        )
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #008080;', // keywords
            2 => 'color: #008080;', // directives
            3 => 'color: #004080;'  // builtins
        ),
        'COMMENTS' => array(
            1 => 'color: #000080; font-style: italic;',
            2 => 'color: #000080; font-style: italic;',
            'MULTI' => 'color: #000080; font-style: italic;'
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #009900; font-weight: bold;'
        ),
        'BRACKETS' => array(
            0 => 'color: #0000FF;'
        ),
        'STRINGS' => array(
            0 => 'color: #008000;'
        ),
        'NUMBERS' => array(
            0 => 'color: #000000;'
        ),
        'METHODS' => array( // Do not exist in Phix)
            0 => ''
        ),
        'SYMBOLS' => array(
            0 => 'color: #0000FF;', // brackets
            1 => 'color: #0000FF;'  // operators
        ),
        'REGEXPS' => array(),
        'SCRIPT' => array( // Never included in scripts.
        )
    ),
    'REGEXPS' => array(),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => ''
    ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
