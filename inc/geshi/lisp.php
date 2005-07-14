<?php
/*************************************************************************************
 * lisp.php
 * --------
 * Author: Roberto Rossi (rsoftware@altervista.org)
 * Copyright: (c) 2004 Roberto Rossi (http://rsoftware.altervista.org), Nigel McNie (http://qbnz.com/highlighter
 * Release Version: 1.0.6
 * CVS Revision Version: $Revision: 1.1 $
 * Date Started: 2004/08/30
 * Last Modified: $Date: 2005/06/02 04:57:18 $
 *
 * Generic Lisp language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.1)
 *  -  Added support for multiple object splitters
 * 2004/08/30 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2004/11/27)
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
	'LANG_NAME' => 'LISP',
	'COMMENT_SINGLE' => array(1 => ';'),
	'COMMENT_MULTI' => array(';|' => '|;'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array('"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
		  'not','defun','princ',
		  'eval','apply','funcall','quote','identity','function',
		  'complement','backquote','lambda','set','setq','setf',
		  'defun','defmacro','gensym','make','symbol','intern',
		  'symbol','name','symbol','value','symbol','plist','get',
		  'getf','putprop','remprop','hash','make','array','aref',
		  'car','cdr','caar','cadr','cdar','cddr','caaar','caadr','cadar',
		  'caddr','cdaar','cdadr','cddar','cdddr','caaaar','caaadr',
		  'caadar','caaddr','cadaar','cadadr','caddar','cadddr',
		  'cdaaar','cdaadr','cdadar','cdaddr','cddaar','cddadr',
		  'cdddar','cddddr','cons','list','append','reverse','last','nth',
		  'nthcdr','member','assoc','subst','sublis','nsubst',
		  'nsublis','remove','length','list','length',
		  'mapc','mapcar','mapl','maplist','mapcan','mapcon','rplaca',
		  'rplacd','nconc','delete','atom','symbolp','numberp',
		  'boundp','null','listp','consp','minusp','zerop','plusp',
		  'evenp','oddp','eq','eql','equal','cond','case','and','or',
		  'let','l','if','prog','prog1','prog2','progn','go','return',
		  'do','dolist','dotimes','catch','throw','error','cerror','break',
		  'continue','errset','baktrace','evalhook','truncate','float',
		  'rem','min','max','abs','sin','cos','tan','expt','exp','sqrt',
		  'random','logand','logior','logxor','lognot','bignums','logeqv',
		  'lognand','lognor','logorc2','logtest','logbitp','logcount',
		  'integer','length','nil'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '{', '}', '[', ']', '!', '%', '^', '&', '/','+','-','*','=','<','>',';','|'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #b1b100;'
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
			0 => 'color: #202020;'
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
		)
);

?>