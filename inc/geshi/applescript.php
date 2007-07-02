<?php
/*************************************************************************************
 * applescript.php
 * --------
 * Author: Stephan Klimek (http://www.initware.org)
 * Copyright: Stephan Klimek (http://www.initware.org)
 * Release Version: 1.0.7.20
 * Date Started: 2005/07/20
 *
 * AppleScript language file for GeSHi.
 *
 * CHANGES
 * -------
 *
 * TODO 
 * -------------------------
 * URL settings to references
 *
 **************************************************************************************
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
	'LANG_NAME' => 'AppleScript',
	'COMMENT_SINGLE' => array(1 => '--'),
	'COMMENT_MULTI' => array( '(*' => '*)'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array('"',"'"),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
            'script','property','prop','end','copy','to','set','global','local','on','to','of',
            'in','given','with','without','return','continue','tell','if','then','else','repeat',
            'times','while','until','from','exit','try','error','considering','ignoring','timeout',
            'transaction','my','get','put','into','is'
			),
		2 => array(
            'each','some','every','whose','where','id','index','first','second','third','fourth',
            'fifth','sixth','seventh','eighth','ninth','tenth','last','front','back','st','nd',
            'rd','th','middle','named','through','thru','before','after','beginning','the'
			),
		3 => array(
            'close','copy','count','delete','duplicate','exists','launch','make','move','open',
            'print','quit','reopen','run','save','saving',
            'it','me','version','pi','result','space','tab','anything','case','diacriticals','expansion',
            'hyphens','punctuation','bold','condensed','expanded','hidden','italic','outline','plain',
            'shadow','strikethrough','subscript','superscript','underline','ask','no','yes','false',
            'true','weekday','monday','mon','tuesday','tue','wednesday','wed','thursday','thu','friday',
            'fri','saturday','sat','sunday','sun','month','january','jan','february','feb','march',
            'mar','april','apr','may','june','jun','july','jul','august','aug','september',
            'sep','october','oct','november','nov','december','dec','minutes','hours',
            'days','weeks','div','mod','and','not','or','as','contains','equal','equals','isnt'
			)
		),
	'SYMBOLS' => array(
        ')','+','-','^','*','/','&','<','>=','<','<=','=','�'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		3 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #b1b100;',
			2 => 'color: #000000; font-weight: bold;',
			3 => 'color: #000066;'
			),
		'COMMENTS' => array(
			1 => 'color: #808080; font-style: italic;',
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
		'REGEXPS' => array(
			0 => 'color: #0000ff;',
			4 => 'color: #009999;',
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		3 => ''
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => ',+-=&lt;&gt;/?^&amp;*'
		),
	'REGEXPS' => array(
		0 => '[\\$%@]+[a-zA-Z_][a-zA-Z0-9_]*',
		4 => '&lt;[a-zA-Z_][a-zA-Z0-9_]*&gt;',
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);

?>
