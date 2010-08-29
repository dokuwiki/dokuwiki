<?php
/*************************************************************************************
 * j.php
 * --------
 * Author: Ric Sherlock (tikkanz@gmail.com)
 * Copyright: (c) 2009 Ric Sherlock
 * Release Version: 1.0.8.8
 * Date Started: 2009/11/10
 *
 * J language file for GeSHi.
 *
 * CHANGES
 * -------
 *  2010/03/01 (1.0.8.8)
 *   - Add support for label_xyz. and goto_xyz.
 *   - Fix highlighting of for_i.
 *   - Use alternative method for highlighting for_xyz. construct
 *  2010/02/14 (1.0.8.7)
 *   - Add support for primitives
 *  2010/01/12 (1.0.2)
 *   - Use HARDQUOTE for strings
 *   - Highlight open quotes/incomplete strings
 *   - Highlight multi-line comments that use Note
 *   - Refinements for NUMBERS and Argument keywords
 *   - Highlight infinity and neg. infinity using REGEXPS
 *   - Highlight "for_myvar." style Control keyword using REGEXPS
 *  2009/12/14 (1.0.1)
 *   -  Regex for NUMBERS, SYMBOLS for () and turn off BRACKETS
 *  2009/11/12 (1.0.0)
 *   -  First Release
 *
 *
 * TODO (updated 2010/01/27)
 * -------------------------
 *  * combine keyword categories by using conditional regex statement in PARSER CONTROL?
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
    'LANG_NAME' => 'J',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(
        1 => '/(?<!\w)NB\..*?$/m',                //singleline comments NB.
        2 => '/(?<=\bNote\b).*?$\s+\)(?:(?!\n)\s)*$/sm',   //multiline comments in Note
        3 => "/'[^']*?$/m"                        //incomplete strings/open quotes
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array(),
    'ESCAPE_CHAR' => '',
    'HARDQUOTE' => array("'", "'"),
    'HARDESCAPE' => array("'"),
    'HARDCHAR' => "'",
    'NUMBERS' => array(
        //Some instances of infinity are not correctly handled by GeSHi NUMBERS currently
        //There are two solutions labelled "infinity Method A" and "infinity Method B"
        //infinity Method B - requires following adjustment to line 3349 of geshi.php
        //   preg_match('#\d#'  becomes  preg_match('#[\d_]#'
        0 => '\b(?:_?\d+(?:\.\d+)?(?:x|[bejprx]_?[\da-z]+(?:\.[\da-z]+)?)?)(?![\w\.\:])',       //infinity Method A
        //0 => '\b(?:_?\d+(?:\.\d+)?(?:x|[bejprx]_?[\da-z]+(?:\.[\da-z]+)?)?|__?)(?![\w\.\:])', //infinity Method B
        ),
    'KEYWORDS' => array(
        //Control words
        1 => array(
            'assert.', 'break.', 'case.', 'catch.', 'catcht.', 'continue.', 'do.',
            'else.', 'elseif.', 'end.', 'fcase.', 'for.', 'goto.', 'if.', 'label.',
            'return.', 'select.', 'throw.', 'trap.', 'try.', 'while.', 'whilst.'
            ),
        //Arguments
        2 => array(
            'm', 'n', 'u', 'v', 'x', 'y'
            ),
/*
Commented out for now due to conflicts with Lang Check
        //Primitives beginning with a symbol (except . or :)
        6 => array(
            '=', '&lt;', '&lt;.', '&lt;:',                  //verbs
            '_:','&gt;', '&gt;.', '&gt;:',
            '+', '+.', '+:', '*', '*.', '*:', '-', '-.', '-:', '%', '%.', '%:',
            '^', '^.', '$', '$.', '$:', '~.', '~:', '\|', '|.', '|:',
            ',', ',.', ',:', ';', ';:', '#', '#.', '#:', '!', '/:', '\:',
            '[', '[:', ']', '{', '{.', '{:', '{::', '}.', '}:',
            '&quot;.', '&quot;:', '?', '?.',
            '~', '\/;', '\\', '/.', '\\.', '}',             //adverbs
            '^:', ';.', '!.', '!:',                         //conj
            '&quot;', '`', '`:', '@', '@.', '@:',
            '&amp;', '&amp;.', '&amp;:', '&amp;.:',
            '_.',                                           //nouns
            '=.', '=:',                                     //other
            ),
        //Primitives beginning with a letter or number
        7 => array(
            'A.', 'c.', 'C.', 'e.', 'E.',                   //verbs
            'i.', 'i:', 'I.', 'j.', 'L.', 'o.',
            'p.', 'p..', 'p:', 'q:', 'r.', 's:', 'u:', 'x:',
            '_9:', '_8:', '_7:', '_6:', '_5:', '_4:', '_3:', '_2:', '_1:',
            '0:', '1:', '2:', '3:', '4:', '5:', '6:', '7:', '8:', '9:',
            'b.', 'f.', 'M.', 't.', 't:',                   //adverbs
            'd.', 'D.', 'D:', 'H.', 'L:', 'S:', 'T.',       //conj
            'a.', 'a:',                                     //nouns
            ),
        //Primitives beginning with symbol . or :
        8 => array(
            '..', '.:', '.', ':.', '::', ':',               //conj
            ),
*/
        ),
    'SYMBOLS' => array(
        //Punctuation
        0 => array(
            '(', ')'
            )
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
//        6 => true,
//        7 => true,
//        8 => true,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0000ff; font-weight: bold;',
            2 => 'color: #0000cc; font-weight: bold;',
//            6 => 'color: #000000; font-weight: bold;',
//            7 => 'color: #000000; font-weight: bold;',
//            8 => 'color: #000000; font-weight: bold;',
            ),
        'COMMENTS' => array(
            1 => 'color: #666666; font-style: italic;',
            2 => 'color: #666666; font-style: italic; font-weight: bold;',
            3 => 'color: #ff00ff; ',                      //open quote
            'MULTI' => 'color: #666666; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            'HARD' => 'font-weight: bold;',
            0 => '',
            ),
        'BRACKETS' => array(
            0 => 'color: #009900;'
            ),
        'STRINGS' => array(
            'HARD' => 'color: #ff0000;',
            0 => 'color: #ff0000;',
            ),
        'NUMBERS' => array(
            0 => 'color: #009999; font-weight: bold;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #009900; font-weight: bold;'
            ),
        'REGEXPS' => array(
            0 => 'color: #0000ff; font-weight: bold;',   //for_xyz. - same as kw1
            1 => 'color: #009999; font-weight: bold;'    //infinity - same as nu0
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '', //'http://www.jsoftware.com/help/dictionary/ctrl.htm',
        2 => '',
//        6 => '', //'http://www.jsoftware.com/jwiki/Vocabulary',
//        7 => '', //'http://www.jsoftware.com/jwiki/Vocabulary',
//        8 => '', //'http://www.jsoftware.com/jwiki/Vocabulary',
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        0 => '\b(for|goto|label)_[a-zA-Z]\w*\.',   //for_xyz. - should be kw1
        1 => '\b__?(?![\w\.\:])'                   //infinity - should be nu0
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'PARSER_CONTROL' => array(
        'ENABLE_FLAGS' => array(
            'BRACKETS' => GESHI_NEVER,
            ),
        'KEYWORDS' => array(
            //Control words
            2 => array(
                'DISALLOWED_BEFORE' => '(?<!\w)',
                'DISALLOWED_AFTER' => '(?![\w\.\:])',
                ),
            //Primtives starting with a symbol (except . or :)
            6 => array(
                'DISALLOWED_BEFORE' => '(?!K)',    // effect should be to allow anything
                'DISALLOWED_AFTER' => '(?=.*)',
                ),
            //Primtives starting with a letter
            7 => array(
                'DISALLOWED_BEFORE' => '(?<!\w)',
                'DISALLOWED_AFTER' => '(?=.*)',
                ),
            //Primtives starting with symbol . or :
            8 => array(
                'DISALLOWED_BEFORE' => '(?<=\s)',
                'DISALLOWED_AFTER' => '(?=.*)',
                ),
            )
        )
);

?>