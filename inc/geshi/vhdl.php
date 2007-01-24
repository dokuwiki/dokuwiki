<?php
/*************************************************************************************
 * vhdl.php
 * --------
 * Author: Alexander 'E-Razor' Krause (admin@erazor-zone.de)
 * Copyright: (c) 2005 Alexander Krause
 * Release Version: 1.0.7.17
 * CVS Revision Version: $Revision: 866 $
 * Date Started: 2005/06/15
 * Last Modified: $Date: 2006-11-26 21:40:26 +1300 (Sun, 26 Nov 2006) $
 * 
 * VHDL (VHSICADL, very high speed integrated circuit HDL) language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2006/06/15 (1.0.0)
 *  -  First Release
 *
 * TODO
 * ----
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
    'LANG_NAME' => 'VHDL',
    'COMMENT_SINGLE' => array(1 => '--'),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        /*keywords*/
        1 => array(
            'access','after','alias','all','assert','architecture','begin',
            'block','body','buffer','bus','case','component','configuration','constant',
            'disconnect','downto','else','elsif','end','entity','exit','file','for',
            'function','generate','generic','group','guarded','if','impure','in',
            'inertial','inout','is','label','library','linkage','literal','loop',
            'map','new','next','null','of','on','open','others','out','package',
            'port','postponed','procedure','process','pure','range','record','register',
            'reject','report','return','select','severity','signal','shared','subtype',
            'then','to','transport','type','unaffected','units','until','use','variable',
            'wait','when','while','with','note','warning','error','failure','and',
            'or','xor','not','nor'
        ),
        /*types*/
        2 => array(
            'bit','bit_vector','character','boolean','integer','real','time','string',
            'severity_level','positive','natural','signed','unsigned','line','text',
            'std_logic','std_logic_vector','std_ulogic','std_ulogic_vector','qsim_state',
            'qsim_state_vector','qsim_12state','qsim_12state_vector','qsim_strength',
            'mux_bit','mux_vector','reg_bit','reg_vector','wor_bit','wor_vector'
        ),
        /*operators*/
        3 => array(
                '=','<=',':=','=>','=='
        )
    ),
    'SYMBOLS' => array(
        '[', ']', '(', ')',';','<','>',':'
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => true,
        1 => false,
        2 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000000; font-weight: bold;',
            2 => 'color: #aa0000;'
            ),
        'COMMENTS' => array(
            1 => 'color: #adadad; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #66cc66;'
            ),
        'STRINGS' => array(
            0 => 'color: #7f007f;'
            ),
        'NUMBERS' => array(
            0 => 'color: #ff0000;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
            ),
        'REGEXPS' => array(
            0 => 'color: #ff0000;',
            1 => 'color: #ff0000;',
            2 => 'color: #ff0000;',
            3 => 'color: #ff0000;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => ''
    ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        0 => '(\b(0x)[0-9a-fA-F]{2,}[hH]?|\b(0x)?[0-9a-fA-F]{2,}[hH])|'.
        '(\b[0-9]{1,}((\.){1}[0-9]{1,}){0,1}(E)[\-]{0,1}[0-9]{1,})|'.
         '(\b(ns))|'.
         "('[0-9a-zA-Z]+)",
         1 => "\b(''[0-9]'')"
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);
 
?>
