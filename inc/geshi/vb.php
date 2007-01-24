<?php
/*************************************************************************************
 * vb.php
 * ------
 * Author: Roberto Rossi (rsoftware@altervista.org)
 * Copyright: (c) 2004 Roberto Rossi (http://rsoftware.altervista.org), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.17
 * CVS Revision Version: $Revision: 870 $
 * Date Started: 2004/08/30
 * Last Modified: $Date: 2006-12-10 22:48:21 +1300 (Sun, 10 Dec 2006) $
 *
 * Visual Basic language file for GeSHi.
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
	'LANG_NAME' => 'Visual Basic',
	'COMMENT_SINGLE' => array(1 => "'"),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array('"'),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		1 => array(
			'as', 'err', 'boolean', 'and', 'or', 'recordset', 'unload', 'to',
			'integer','long','single','new','database','nothing','set','close',
			'open','print','split','line','field','querydef','instrrev',
			'abs','array','asc','ascb','ascw','atn','avg','me',
			'cbool','cbyte','ccur','cdate','cdbl','cdec','choose','chr','chrb','chrw','cint','clng',
			'command','cos','count','createobject','csng','cstr','curdir','cvar','cvdate','cverr',
			'date','dateadd','datediff','datepart','dateserial','datevalue','day','ddb','dir','doevents',
			'environ','eof','error','exp',
			'fileattr','filedatetime','filelen','fix','format','freefile','fv',
			'getallstrings','getattr','getautoserversettings','getobject','getsetting',
			'hex','hour','iif','imestatus','input','inputb','inputbox','instr','instb','int','ipmt',
			'isarray','isdate','isempty','iserror','ismissing','isnull','isnumeric','isobject',
			'lbound','lcase','left','leftb','len','lenb','loadpicture','loc','lof','log','ltrim',
			'max','mid','midb','min','minute','mirr','month','msgbox',
			'now','nper','npv','oct','partition','pmt','ppmt','pv','qbcolor',
			'rate','rgb','right','rightb','rnd','rtrim',
			'second','seek','sgn','shell','sin','sln','space','spc','sqr','stdev','stdevp','str',
			'strcomp','strconv','string','switch','sum','syd',
			'tab','tan','time','timer','timeserial','timevalue','trim','typename',
			'ubound','ucase','val','var','varp','vartype','weekday','year',
			'appactivate','base','beep','call','case','chdir','chdrive','const',
			'declare','defbool','defbyte','defcur','defdate','defdbl','defdec','defint',
			'deflng','defobj','defsng','defstr','deftype','defvar','deletesetting','dim','do',
			'else','elseif','end','enum','erase','event','exit','explicit',
			'false','filecopy','for','foreach','friend','function','get','gosub','goto',
			'if','implements','kill','let','lineinput','lock','loop','lset','mkdir','name','next','not',
			'onerror','on','option','private','property','public','put','raiseevent','randomize',
			'redim','rem','reset','resume','return','rmdir','rset',
			'savepicture','savesetting','sendkeys','setattr','static','sub',
			'then','true','type','unlock','wend','while','width','with','write',
			'vbabort','vbabortretryignore','vbapplicationmodal','vbarray',
			'vbbinarycompare','vbblack','vbblue','vbboolean','vbbyte','vbcancel',
			'vbcr','vbcritical','vbcrlf','vbcurrency','vbcyan','vbdataobject',
			'vbdate','vbdecimal','vbdefaultbutton1','vbdefaultbutton2',
			'vbdefaultbutton3','vbdefaultbutton4','vbdouble','vbempty',
			'vberror','vbexclamation','vbfirstfourdays','vbfirstfullweek',
			'vbfirstjan1','vbformfeed','vbfriday','vbgeneraldate','vbgreen',
			'vbignore','vbinformation','vbinteger','vblf','vblong','vblongdate',
			'vblongtime','vbmagenta','vbmonday','vbnewline','vbno','vbnull',
			'vbnullchar','vbnullstring','vbobject','vbobjecterror','vbok','vbokcancel',
			'vbokonly','vbquestion','vbred','vbretry','vbretrycancel','vbsaturday',
			'vbshortdate','vbshorttime','vbsingle','vbstring','vbsunday',
			'vbsystemmodal','vbtab','vbtextcompare','vbthursday','vbtuesday',
			'vbusesystem','vbusesystemdayofweek','vbvariant','vbverticaltab',
			'vbwednesday','vbwhite','vbyellow','vbyes','vbyesno','vbyesnocancel',
			'vbnormal','vbdirectory'
			)
		),
	'SYMBOLS' => array(
		'(', ')'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => false
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #b1b100;'
			),
		'COMMENTS' => array(
			1 => 'color: #808080;'
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
			1 => 'color: #66cc66;'
			),
		'SYMBOLS' => array(
			0 => 'color: #66cc66;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099;'
			),
		'SCRIPT' => array(
			),
		'REGEXPS' => array(
			)
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

?>
