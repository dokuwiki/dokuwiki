<?php
/*************************************************************************************
 * cfm.php
 * -------
 * Author: Diego
 * Copyright: (c) 2006 Diego
 * Release Version: 1.0.7.21
 * Date Started: 2006/02/25
 *
 * ColdFusion language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2006/02/25 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2006/02/25)
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
	'LANG_NAME' => 'ColdFusion',
	'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('<!--' => '-->','&lt;!---' => '---&gt;', '/*' => '*/'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		/* CFM Tags */
		1 => array(
			'&lt;cfabort', '&lt;cfapplet', '&lt;cfapplication', '&lt;cfargument', '&lt;cfassociate', '&lt;&lt;cfbreak&gt;',
			'&lt;cfcache', '&lt;cfcase', '&lt;cfcatch', '&lt;/cfcatch&gt;', '&lt;cfchart', '&lt;/cfchart&gt;', '&lt;cfchartdata',
			'&lt;cfchartseries', '&lt;/cfchartseries&gt;', '&lt;cfcol', '&lt;cfcollection', '&lt;cfcomponent', '&lt;/cfcomponent&gt;',
			'&lt;cfcontent', '&lt;cfcookie', '&lt;/cfdefaultcase&gt;', '&lt;cfdirectory', '&lt;cfdocument', '&lt;/cfdocument&gt;',
			'&lt;cfdocumentitem', '&lt;/cfdocumentitem&gt;', '&lt;cfdocumentsection', '&lt;/cfdocumentsection&gt;', '&lt;cfdump',
			'&lt;cfelse', '&lt;cfelseif', '&lt;cferror', '&lt;cfexecute', '&lt;/cfexecute&gt;', '&lt;cfexit', '&lt;cffile',
			'&lt;cfflush', '&lt;cfform', '&lt;/cfform&gt;', '&lt;cfformgroup', '&lt;/cfformgroup', '&lt;cfformitem',
			'&lt;/cfformitem&gt;', '&lt;cfftp', '&lt;cffunction', '&lt;/cffunction&gt;', '&lt;cfgrid', '&lt;/cfgrid&gt;',
			'&lt;cfgridcolumn', '&lt;cfgridrow', '&lt;cfgridupdate', '&lt;cfheader', '&lt;cfhtmlhead', '&lt;cfhttp',
			'&lt;/cfhttp&gt;', '&lt;cfhttpparam', '&lt;cfif', '&lt;/cfif&gt;', '&lt;cfimport', '&lt;cfinclude', '&lt;cfindex',
			'&lt;cfinput', '&lt;cfinsert', '&lt;cfinvoke', '&lt;cfinvokeargument', '&lt;cfldap', '&lt;cflocation', '&lt;cflock',
			'&lt;/cflock&gt;', '&lt;cflog', '&lt;cflogin', '&lt;/cflogin&gt;', '&lt;cfloginuser', '&lt;cflogout', '&lt;cfloop',
			'&lt;/cfloop&gt;', '&lt;cfmail', '&lt;/cfmail&gt;', '&lt;cfmailparam', '&lt;cfmailpart', '&lt;/cfmailpart&gt;',
			'&lt;cfmodule', '&lt;cfNTauthenticate', '&lt;cfobject', '&lt;cfobjectcache', '&lt;cfoutput&gt;', '&lt;cfoutput', '&lt;/cfoutput&gt;',
			'&lt;cfparam', '&lt;cfpop', '&lt;cfprocessingdirective', '&lt;/cfprocessingdirective&gt;', '&lt;cfprocparam',
			'&lt;cfprocresult', '&lt;cfproperty', '&lt;cfquery', '&lt;/cfquery&gt;', '&lt;cfqueryparam', '&lt;cfregistry',
			'&lt;/cfregistry&gt;', '&lt;cfreport', '&lt;/cfreport&gt;', '&lt;cfreportparam', '&lt;/cfreportparam&gt;',
			'&lt;cfrethrow', '&lt;cfreturn', '&lt;cfsavecontent', '&lt;/cfsavecontent&gt;', '&lt;cfschedule', '&lt;cfscript', '&lt;cfscript&gt;',
			'&lt;/cfscript&gt;', '&lt;cfsearch', '&lt;cfselect', '&lt;/cfselect&gt;', '&lt;cfset', '&lt;cfsetting', '&lt;cfsilent',
			'&lt;/cfsilent&gt;', '&lt;cfstoredproc', '&lt;/cfstoredproc&gt;', '&lt;cfswitch', '&lt;/cfswitch&gt;', '&lt;cftable',
			'&lt;/cftable&gt;', '&lt;cftextarea', '&lt;/cftextarea&gt;', '&lt;cfthrow', '&lt;cftimer', '&lt;/cftimer&gt;',
			'&lt;cftrace', '&lt;/cftrace&gt;', '&lt;cftransaction', '&lt;/cftransaction&gt;', '&lt;cftree', '&lt;/cftree&gt;',
			'&lt;cftreeitem', '&lt;cftry', '&lt;/cftry&gt;', '&lt;cfupdate', '&lt;cfwddx','&lt;','&gt;'
			),
		/* HTML Tags */
		2 => array(
			'&lt;a&gt;', '&lt;abbr&gt;', '&lt;acronym&gt;', '&lt;address&gt;', '&lt;applet&gt;',
			'&lt;a', '&lt;abbr', '&lt;acronym', '&lt;address', '&lt;applet',
			'&lt;/a&gt;', '&lt;/abbr&gt;', '&lt;/acronym&gt;', '&lt;/address&gt;', '&lt;/applet&gt;',
			'&lt;/a', '&lt;/abbr', '&lt;/acronym', '&lt;/address', '&lt;/applet',

			'&lt;base&gt;', '&lt;basefont&gt;', '&lt;bdo&gt;', '&lt;big&gt;', '&lt;blockquote&gt;', '&lt;body&gt;', '&lt;br&gt;', '&lt;button&gt;', '&lt;b&gt;',
			'&lt;base', '&lt;basefont', '&lt;bdo', '&lt;big', '&lt;blockquote', '&lt;body', '&lt;br', '&lt;button', '&lt;b',
			'&lt;/base&gt;', '&lt;/basefont&gt;', '&lt;/bdo&gt;', '&lt;/big&gt;', '&lt;/blockquote&gt;', '&lt;/body&gt;', '&lt;/br&gt;', '&lt;/button&gt;', '&lt;/b&gt;',
			'&lt;/base', '&lt;/basefont', '&lt;/bdo', '&lt;/big', '&lt;/blockquote', '&lt;/body', '&lt;/br','&lt;br /&gt;', '&lt;/button', '&lt;/b',

			'&lt;caption&gt;', '&lt;center&gt;', '&lt;cite&gt;', '&lt;code&gt;', '&lt;colgroup&gt;', '&lt;col&gt;',
			'&lt;caption', '&lt;center', '&lt;cite', '&lt;code', '&lt;colgroup', '&lt;col',
			'&lt;/caption&gt;', '&lt;/center&gt;', '&lt;/cite&gt;', '&lt;/code&gt;', '&lt;/colgroup&gt;', '&lt;/col&gt;',
			'&lt;/caption', '&lt;/center', '&lt;/cite', '&lt;/code', '&lt;/colgroup', '&lt;/col',

			'&lt;dd&gt;', '&lt;del&gt;', '&lt;dfn&gt;', '&lt;dir&gt;', '&lt;div&gt;', '&lt;dl&gt;', '&lt;dt&gt;',
			'&lt;dd', '&lt;del', '&lt;dfn', '&lt;dir', '&lt;div', '&lt;dl', '&lt;dt',
			'&lt;/dd&gt;', '&lt;/del&gt;', '&lt;/dfn&gt;', '&lt;/dir&gt;', '&lt;/div&gt;', '&lt;/dl&gt;', '&lt;/dt&gt;',
			'&lt;/dd', '&lt;/del', '&lt;/dfn', '&lt;/dir', '&lt;/div', '&lt;/dl', '&lt;/dt',

			'&lt;em&gt;',
			'&lt;em',
			'&lt;/em&gt;',
			'&lt;/em',

			'&lt;fieldset&gt;', '&lt;font&gt;', '&lt;form&gt;', '&lt;frame&gt;', '&lt;frameset&gt;',
			'&lt;fieldset', '&lt;font', '&lt;form', '&lt;frame', '&lt;frameset',
			'&lt;/fieldset&gt;', '&lt;/font&gt;', '&lt;/form&gt;', '&lt;/frame&gt;', '&lt;/frameset&gt;',
			'&lt;/fieldset', '&lt;/font', '&lt;/form', '&lt;/frame', '&lt;/frameset',

			'&lt;h1&gt;', '&lt;h2&gt;', '&lt;h3&gt;', '&lt;h4&gt;', '&lt;h5&gt;', '&lt;h6&gt;', '&lt;head&gt;', '&lt;hr&gt;', '&lt;html&gt;',
			'&lt;h1', '&lt;h2', '&lt;h3', '&lt;h4', '&lt;h5', '&lt;h6', '&lt;head', '&lt;hr', '&lt;html',
			'&lt;/h1&gt;', '&lt;/h2&gt;', '&lt;/h3&gt;', '&lt;/h4&gt;', '&lt;/h5&gt;', '&lt;/h6&gt;', '&lt;/head&gt;', '&lt;/hr&gt;', '&lt;/html&gt;',
			'&lt;/h1', '&lt;/h2', '&lt;/h3', '&lt;/h4', '&lt;/h5', '&lt;/h6', '&lt;/head', '&lt;/hr', '&lt;/html',

			'&lt;iframe&gt;', '&lt;ilayer&gt;', '&lt;img&gt;', '&lt;input&gt;', '&lt;ins&gt;', '&lt;isindex&gt;', '&lt;i&gt;',
			'&lt;iframe', '&lt;ilayer', '&lt;img', '&lt;input', '&lt;ins', '&lt;isindex', '&lt;i',
			'&lt;/iframe&gt;', '&lt;/ilayer&gt;', '&lt;/img&gt;', '&lt;/input&gt;', '&lt;/ins&gt;', '&lt;/isindex&gt;', '&lt;/i&gt;',
			'&lt;/iframe', '&lt;/ilayer', '&lt;/img', '&lt;/input', '&lt;/ins', '&lt;/isindex', '&lt;/i',

			'&lt;kbd&gt;',
			'&lt;kbd',
			'&t;/kbd&gt;',
			'&lt;/kbd',

			'&lt;label&gt;', '&lt;legend&gt;', '&lt;link&gt;', '&lt;li&gt;',
			'&lt;label', '&lt;legend', '&lt;link', '&lt;li',
			'&lt;/label&gt;', '&lt;/legend&gt;', '&lt;/link&gt;', '&lt;/li&gt;',
			'&lt;/label', '&lt;/legend', '&lt;/link', '&lt;/li',

			'&lt;map&gt;', '&lt;meta&gt;',
			'&lt;map', '&lt;meta',
			'&lt;/map&gt;', '&lt;/meta&gt;',
			'&lt;/map', '&lt;/meta',

			'&lt;noframes&gt;', '&lt;noscript&gt;',
			'&lt;noframes', '&lt;noscript',
			'&lt;/noframes&gt;', '&lt;/noscript&gt;',
			'&lt;/noframes', '&lt;/noscript',

			'&lt;object&gt;', '&lt;ol&gt;', '&lt;optgroup&gt;', '&lt;option&gt;',
			'&lt;object', '&lt;ol', '&lt;optgroup', '&lt;option',
			'&lt;/object&gt;', '&lt;/ol&gt;', '&lt;/optgroup&gt;', '&lt;/option&gt;',
			'&lt;/object', '&lt;/ol', '&lt;/optgroup', '&lt;/option',

			'&lt;param&gt;', '&lt;pre&gt;', '&lt;p&gt;',
			'&lt;param', '&lt;pre', '&lt;p',
			'&lt;/param&gt;', '&lt;/pre&gt;', '&lt;/p&gt;',
			'&lt;/param', '&lt;/pre', '&lt;/p',

			'&lt;q&gt;',
			'&lt;q',
			'&lt;/q&gt;',
			'&lt;/q',

			'&lt;samp&gt;', '&lt;script&gt;', '&lt;select&gt;', '&lt;small&gt;', '&lt;span&gt;', '&lt;strike&gt;', '&lt;strong&gt;', '&lt;style&gt;', '&lt;sub&gt;', '&lt;sup&gt;', '&lt;s&gt;',
			'&lt;samp', '&lt;script', '&lt;select', '&lt;small', '&lt;span', '&lt;strike', '&lt;strong', '&lt;style', '&lt;sub', '&lt;sup', '&lt;s',
			'&lt;/samp&gt;', '&lt;/script&gt;', '&lt;/select&gt;', '&lt;/small&gt;', '&lt;/span&gt;', '&lt;/strike&gt;', '&lt;/strong&gt;', '&lt;/style&gt;', '&lt;/sub&gt;', '&lt;/sup&gt;', '&lt;/s&gt;',
			'&lt;/samp', '&lt;/script', '&lt;/select', '&lt;/small', '&lt;/span', '&lt;/strike', '&lt;/strong', '&lt;/style', '&lt;/sub', '&lt;/sup', '&lt;/s',

			'&lt;table&gt;', '&lt;tbody&gt;', '&lt;td&gt;', '&lt;textarea&gt;', '&lt;text&gt;', '&lt;tfoot&gt;', '&lt;thead&gt;', '&lt;th&gt;', '&lt;title&gt;', '&lt;tr&gt;', '&lt;tt&gt;',
			'&lt;table', '&lt;tbody', '&lt;td', '&lt;textarea', '&lt;text', '&lt;tfoot', '&lt;tfoot', '&lt;thead', '&lt;th', '&lt;title', '&lt;tr', '&lt;tt',
			'&lt;/table&gt;', '&lt;/tbody&gt;', '&lt;/td&gt;', '&lt;/textarea&gt;', '&lt;/text&gt;', '&lt;/tfoot&gt;', '&lt;/thead', '&lt;/tfoot', '&lt;/th&gt;', '&lt;/title&gt;', '&lt;/tr&gt;', '&lt;/tt&gt;',
			'&lt;/table', '&lt;/tbody', '&lt;/td', '&lt;/textarea', '&lt;/text', '&lt;/tfoot', '&lt;/tfoot', '&lt;/thead', '&lt;/th', '&lt;/title', '&lt;/tr', '&lt;/tt',

			'&lt;ul&gt;', '&lt;u&gt;',
			'&lt;ul', '&lt;u',
			'&lt;/ul&gt;', '&lt;/ul&gt;',
			'&lt;/ul', '&lt;/u',

			'&lt;var&gt;',
			'&lt;var',
			'&lt;/var&gt;',
			'&lt;/var',

			'&gt;', '&lt;'
			),
		/* HTML attributes */
		3 => array(
			'abbr', 'accept-charset', 'accept', 'accesskey', 'action', 'align', 'alink', 'alt', 'archive', 'axis',
			'background', 'bgcolor', 'border',
			'cellpadding', 'cellspacing', 'char', 'char', 'charoff', 'charset', 'checked', 'cite', 'class', 'classid', 'clear', 'code', 'codebase', 'codetype', 'color', 'cols', 'colspan', 'compact', 'content', 'coords',
			'data', 'datetime', 'declare', 'defer', 'dir', 'disabled',
			'enctype',
			'face', 'for', 'frame', 'frameborder',
			'headers', 'height', 'href', 'hreflang', 'hspace', 'http-equiv',
			'id', 'ismap',
			'label', 'lang', 'language', 'link', 'longdesc',
			'marginheight', 'marginwidth', 'maxlength', 'media', 'method', 'multiple',
			'name', 'nohref', 'noresize', 'noshade', 'nowrap',
			'object', 'onblur', 'onchange', 'onclick', 'ondblclick', 'onfocus', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onreset', 'onselect', 'onsubmit', 'onunload',
			'profile', 'prompt',
			'readonly', 'rel', 'rev', 'rowspan', 'rows', 'rules',
			'scheme', 'scope', 'scrolling', 'selected', 'shape', 'size', 'span', 'src', 'standby', 'start', 'style', 'summary',
			'tabindex', 'target', 'text', 'title', 'type',
			'usemap',
			'valign', 'value', 'valuetype', 'version', 'vlink', 'vspace',
			'width'
			),
			/* CFM Script delimeters */
		4 => array(
			'var', 'function', 'while', 'if','else'
			),
			/* CFM Functions */
		5 => array(
			'Abs', 'GetFunctionList', 'LSTimeFormat','ACos','GetGatewayHelper','LTrim','AddSOAPRequestHeader','GetHttpRequestData',
			'Max','AddSOAPResponseHeader','GetHttpTimeString','Mid','ArrayAppend','GetLocale','Min','ArrayAvg','GetLocaleDisplayName',
			'Minute','ArrayClear','GetMetaData','Month','ArrayDeleteAt','GetMetricData','MonthAsString','ArrayInsertAt','GetPageContext',
			'Now','ArrayIsEmpty','GetProfileSections','NumberFormat','ArrayLen','GetProfileString','ParagraphFormat','ArrayMax',
			'GetLocalHostIP','ParseDateTime','ArrayMin','GetSOAPRequest','Pi','ArrayNew','GetSOAPRequestHeader','PreserveSingleQuotes',
			'ArrayPrepend','GetSOAPResponse','Quarter','ArrayResize','GetSOAPResponseHeader','QueryAddColumn','ArraySet',
			'GetTempDirectory','QueryAddRow','ArraySort','GetTempDirectory','QueryNew','ArraySum','GetTempFile','QuerySetCell',
			'ArraySwap','GetTickCount','QuotedValueList','ArrayToList','GetTimeZoneInfo','Rand','Asc','GetToken','Randomize',
			'ASin','Hash','RandRange','Atn','Hour','REFind','BinaryDecode','HTMLCodeFormat','REFindNoCase','BinaryEncode',
			'HTMLEditFormat','ReleaseComObject','BitAnd','IIf','RemoveChars','BitMaskClear','IncrementValue','RepeatString',
			'BitMaskRead','InputBaseN','Replace','BitMaskSet','Insert','ReplaceList','BitNot','Int','ReplaceNoCase','BitOr',
			'IsArray','REReplace','BitSHLN','IsBinary','REReplaceNoCase','BitSHRN','IsBoolean','Reverse','BitXor','IsCustomFunction',
			'Right','Ceiling','IsDate','RJustify','CharsetDecode','IsDebugMode','Round','CharsetEncode','IsDefined','RTrim',
			'Chr','IsLeapYear','Second','CJustify','IsLocalHost','SendGatewayMessage','Compare','IsNumeric','SetEncoding',
			'CompareNoCase','IsNumericDate','SetLocale','Cos','IsObject','SetProfileString','CreateDate','IsQuery','SetVariable',
			'CreateDateTime','IsSimpleValue','Sgn','CreateObject','IsSOAPRequest','Sin','CreateODBCDate','IsStruct','SpanExcluding',
			'CreateODBCDateTime','IsUserInRole','SpanIncluding','CreateODBCTime','IsValid','Sqr','CreateTime','IsWDDX','StripCR',
			'CreateTimeSpan','IsXML','StructAppend','CreateUUID','IsXmlAttribute','StructClear','DateAdd','IsXmlDoc','StructCopy',
			'DateCompare','IsXmlElem','StructCount','DateConvert','IsXmlNode','StructDelete','DateDiff','IsXmlRoot','StructFind',
			'DateFormat','JavaCast','StructFindKey','DatePart','JSStringFormat','StructFindValue','Day','LCase','StructGet',
			'DayOfWeek','Left','StructInsert','DayOfWeekAsString','Len','StructIsEmpty','DayOfYear','ListAppend','StructKeyArray',
			'DaysInMonth','ListChangeDelims','StructKeyExists','DaysInYear','ListContains','StructKeyList','DE','ListContainsNoCase',
			'StructNew','DecimalFormat','ListDeleteAt','StructSort','DecrementValue','ListFind','StructUpdate','Decrypt','ListFindNoCase',
			'Tan','DecryptBinary','ListFirst','TimeFormat','DeleteClientVariable','ListGetAt','ToBase64','DirectoryExists',
			'ListInsertAt','ToBinary','DollarFormat','ListLast','ToScript','Duplicate','ListLen','ToString','Encrypt','ListPrepend',
			'Trim','EncryptBinary','ListQualify','UCase','Evaluate','ListRest','URLDecode','Exp','ListSetAt','URLEncodedFormat',
			'ExpandPath','ListSort','URLSessionFormat','FileExists','ListToArray','Val','Find','ListValueCount','ValueList',
			'FindNoCase','ListValueCountNoCase','Week','FindOneOf','LJustify','Wrap','FirstDayOfMonth','Log','WriteOutput',
			'Fix','Log10','XmlChildPos','FormatBaseN','LSCurrencyFormat','XmlElemNew','GetAuthUser','LSDateFormat','XmlFormat',
			'GetBaseTagData','LSEuroCurrencyFormat','XmlGetNodeType','GetBaseTagList','LSIsCurrency','XmlNew','GetBaseTemplatePath',
			'LSIsDate','XmlParse','GetClientVariablesList','LSIsNumeric','XmlSearch','GetCurrentTemplatePath','LSNumberFormat',
			'XmlTransform','GetDirectoryFromPath','LSParseCurrency','XmlValidate','GetEncoding','LSParseDateTime','Year',
			'GetException','LSParseEuroCurrency','YesNoFormat','GetFileFromPath','LSParseNumber'
			),
		/* CFM Attributes */
		6 => array(
			'=','&amp;','name','dbtype','connectstring','datasource','username','password','query','delimeter','description','required','hint','default','access','from','to','list','index'
			)
		),
	'SYMBOLS' => array(
		'/', '=', 'EQ', 'GT', 'LT', 'GTE', 'LTE', 'IS', 'LIKE', '&', '{', '}', '(', ')', '[', ']','gt','lt'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => false,
		2 => false,
		3 => false,
		4 => false,
        5 => false,
        6 => false
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #990000;',
			2 => 'color: #000000; font-weight: bold;',
			3 => 'color: #0000FF;',
			4 => 'color: #000000; font-weight: bold;',
			5 => 'color: #0000FF;',
			6 => 'color: #0000FF'
			),
		'COMMENTS' => array(
			1 => 'color: #808080; font-style: italic;',
			'MULTI' => 'color: #808080; font-style: italic; background-color:#FFFF99;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #0000FF;'
			),
		'STRINGS' => array(
			0 => 'color: #009900;'
			),
		'NUMBERS' => array(
			0 => 'color: #FF0000;'
			),
		'METHODS' => array(
			),
		'SYMBOLS' => array(
			0 => 'color: #0000FF;'
			),
		'SCRIPT' => array(
			0 => 'color: #00bbdd;',
			1 => 'color: #0000FF;',
			2 => 'color: #000099;',
			3 => 'color: #333333;'
			),
		'REGEXPS' => array(
			)
		),
	'URLS' => array(
		1 => '',
		2 => '',
		3 => '',
		4 => ''
		),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
	'REGEXPS' => array(
		),
	'STRICT_MODE_APPLIES' => GESHI_ALWAYS,
	'SCRIPT_DELIMITERS' => array(
		0 => array(
			'<!DOCTYPE' => '>'
			),
		1 => array(
			 '#' => '#'
			),
		2 => array(
			'<cfscript>' => '</cfscript>;'
			),
		3 => array(
			'<' => '>'
			)
	),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		0 => false,
		1 => true,
		2 => true,
		3 => true
		)
);

?>
