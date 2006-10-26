<?php
/*************************************************************************************
 * autoit.php
 * --------
 * Author: mastrboy
 * Copyright: (c) 2006 and to GESHi ;)
 * Release Version: 1.0.7.15
 * Date Started: 26.01.2006
 *
 * Current bugs & todo:
 * ----------
 * - can't get #cs and #ce to work as multiple comments while still #comments-start/end working
 * - dosn't highlight symbols (Please note that in 1.0.X these are not used. Hopefully they will be used in 1.2.X.)
 * - not sure how to get sendkeys to work " {!}, {SPACE} etc... "
 * - jut copyied the regexp for variable from php so this HAVE to be checked and fixed to a better one ;)
 *
 * Reference: http://www.autoitscript.com/autoit3/docs/
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
	'LANG_NAME' => 'AutoIT',
	'COMMENT_SINGLE' => array(';'),
	'COMMENT_MULTI' => array('#comments-start' => '#comments-end'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		1 => array(
            'continueloop', 'and', 'byref', 'case', 'const', 'dim', 'do', 'else',
            'elseif', 'endfunc', 'endif', 'endselect', 'exit', 'exitloop', 'for',
            'func', 'global', 'if', 'local', 'next', 'not', 'or', 'redim', 'return',
            'select', 'step', 'then', 'to', 'until', 'wend', 'while'
			),
		2 => array(
            '@appdatacommondir','@appdatadir','@autoitexe','@autoitversion','@commonfilesdir',
            '@compiled','@computername','@comspec','@cr','@crlf','@desktopcommondir','@desktopdepth','@desktopdir',
            '@desktopheight','@desktoprefresh','@desktopwidth','@documentscommondir','@error','@extended',
            '@favoritescommondir','@favoritesdir','@gui_ctrlhandle','@gui_ctrlid','@gui_winhandle','@homedrive',
            '@homepath','@homeshare','@hour','@inetgetactive','@inetgetbytesread','@ipaddress1','@ipaddress2',
            '@ipaddress3','@ipaddress4','@lf','@logondnsdomain','@logondomain','@logonserver','@mday','@min',
            '@mon','@mydocumentsdir','@numparams','@osbuild','@oslang','@osservicepack','@ostype','@osversion',
            '@programfilesdir','@programscommondir','@programsdir','@scriptdir','@scriptfullpath','@scriptname',
            '@sec','@startmenucommondir','@startmenudir','@startupcommondir','@startupdir','@sw_disable',
            '@sw_enable','@sw_hide','@sw_maximize','@sw_minimize','@sw_restore','@sw_show','@sw_showdefault',
            '@sw_showmaximized','@sw_showminimized','@sw_showminnoactive','@sw_showna','@sw_shownoactivate',
            '@sw_shownormal','@systemdir','@tab','@tempdir','@username','@userprofiledir','@wday','@windowsdir',
            '@workingdir','@yday','@year'
			),
		3 => array(
            'abs','acos','adlibdisable','adlibenable','asc','asin','assign','atan','autoitsetoption',
            'autoitwingettitle','autoitwinsettitle','bitand','bitnot','bitor','bitshift','bitxor','blockinput',
            'break','call','cdtray','chr','clipget','clipput','consolewrite','controlclick','controlcommand','controldisable',
            'controlenable','controlfocus','controlgetfocus','controlgethandle','controlgetpos','controlgettext',
            'controlhide','controllistview','controlmove','controlsend','controlsettext','controlshow','cos',
            'dec','dircopy','dircreate','dirgetsize','dirmove','dirremove','dllcall','dllclose','dllopen','drivegetdrive',
            'drivegetfilesystem','drivegetlabel','drivegetserial','drivegettype','drivemapadd','drivemapdel',
            'drivemapget','drivesetlabel','drivespacefree','drivespacetotal','drivestatus','envget','envset',
            'envupdate','eval','exp','filechangedir','fileclose','filecopy','filecreateshortcut','filedelete',
            'fileexists','filefindfirstfile','filefindnextfile','filegetattrib','filegetlongname','filegetshortcut',
            'filegetshortname','filegetsize','filegettime','filegetversion','fileinstall','filemove','fileopen',
            'fileopendialog','fileread','filereadline','filerecycle','filerecycleempty','filesavedialog',
            'fileselectfolder','filesetattrib','filesettime','filewrite','filewriteline','ftpsetproxy','guicreate',
            'guictrlcreateavi','guictrlcreatebutton','guictrlcreatecheckbox','guictrlcreatecombo','guictrlcreatecontextmenu',
            'guictrlcreatedate','guictrlcreatedummy','guictrlcreateedit','guictrlcreategroup','guictrlcreateicon',
            'guictrlcreateinput','guictrlcreatelabel','guictrlcreatelist','guictrlcreatelistview','guictrlcreatelistviewitem',
            'guictrlcreatemenu','guictrlcreatemenuitem','guictrlcreatepic','guictrlcreateprogress','guictrlcreateradio',
            'guictrlcreateslider','guictrlcreatetab','guictrlcreatetabitem','guictrlcreatetreeview','guictrlcreatetreeviewitem',
            'guictrlcreateupdown','guictrldelete','guictrlgetstate','guictrlread','guictrlrecvmsg','guictrlsendmsg',
            'guictrlsendtodummy','guictrlsetbkcolor','guictrlsetcolor','guictrlsetcursor','guictrlsetdata',
            'guictrlsetfont','guictrlsetimage','guictrlsetlimit','guictrlsetonevent','guictrlsetpos','guictrlsetresizing',
            'guictrlsetstate','guictrlsetstyle','guictrlsettip','guidelete','guigetcursorinfo','guigetmsg',
            'guisetbkcolor','guisetcoord','guisetcursor','guisetfont','guisethelp','guiseticon','guisetonevent',
            'guisetstate','guistartgroup','guiswitch','hex','hotkeyset','httpsetproxy','inetget','inetgetsize',
            'inidelete','iniread','inireadsection','inireadsectionnames','iniwrite','inputbox','int','isadmin',
            'isarray','isdeclared','isfloat','isint','isnumber','isstring','log','memgetstats','mod','mouseclick',
            'mouseclickdrag','mousedown','mousegetcursor','mousegetpos','mousemove','mouseup','mousewheel',
            'msgbox','number','opt','ping','pixelchecksum','pixelgetcolor','pixelsearch','processclose','processexists',
            'processlist','processsetpriority','processwait','processwaitclose','progressoff','progresson',
            'progressset','random','regdelete','regenumkey','regenumval','regread','regwrite','round','run','runasset',
            'runwait','send','seterror','setextended','shutdown','sin','sleep','soundplay','soundsetwavevolume',
            'splashimageon','splashoff','splashtexton','sqrt','statusbargettext','string','stringaddcr','stringformat',
            'stringinstr','stringisalnum','stringisalpha','stringisascii','stringisdigit','stringisfloat',
            'stringisint','stringislower','stringisspace','stringisupper','stringisxdigit','stringleft','stringlen',
            'stringlower','stringmid','stringregexp','stringregexpreplace','stringreplace','stringright',
            'stringsplit','stringstripcr','stringstripws','stringtrimleft','stringtrimright','stringupper',
            'tan','timerdiff','timerinit','timerstart','timerstop','tooltip','traytip','ubound','winactivate','winactive',
            'winclose','winexists','wingetcaretpos','wingetclasslist','wingetclientsize','wingethandle','wingetpos',
            'wingetprocess','wingetstate','wingettext','wingettitle','winkill','winlist','winmenuselectitem',
            'winminimizeall','winminimizeallundo','winmove','winsetontop','winsetstate','winsettitle','winsettrans',
            'winshow','winwait','winwaitactive','winwaitclose','winwaitnotactive'
			)
        ),
	'SYMBOLS' => array(
		'(', ')', '[', ']', '&', '*', '/', '<', '>', '+', '-', '^', '='
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => false,
		2 => false,
		3 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #0000FF; font-weight: bold;',
			2 => 'color: #FF33FF; font-weight: bold;',
			3 => 'color: #000090; font-style: italic; font-weight: bold;',
			),
		'COMMENTS' => array(
			0 => 'font-style: italic; color: #669900;', 'MULTI' => 'font-style: italic; color: #669900;'
			),
		'ESCAPE_CHAR' => array(
			0 => ''
			),
		'BRACKETS' => array(
			0 => 'color: #FF0000; font-weight: bold;'
			),
		'STRINGS' => array(
			0 => 'font-weight: bold; color: #9999CC;'
			),
		'NUMBERS' => array(
			0 => 'font-style: italic; font-weight: bold; color: #AC00A9;'
			),
		'METHODS' => array(
			1 => 'color: #006600;',
			2 => 'color: #006600;'
			),
		'SYMBOLS' => array(
			0 => 'color: #FF0000; font-weight: bold;'
			),
		'REGEXPS' => array(
			0 => 'font-weight: bold; color: #AA0000;'
			),
		'SCRIPT' => array(
			0 => '',
			1 => '',
			2 => '',
			3 => ''
			)
		),
	'URLS' => array(
		1 => 'http://www.autoitscript.com/autoit3/docs/keywords.htm',
		2 => 'http://www.autoitscript.com/autoit3/docs/macros.htm',
		3 => 'http://www.autoitscript.com/autoit3/docs/functions/{FNAME}.htm',
		4 => ''
		),

	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
	'REGEXPS' => array(
		0 => "[\\$]{1,2}[a-zA-Z_][a-zA-Z0-9_]*",
		),
	'STRICT_MODE_APPLIES' => GESHI_MAYBE,
/*	'SCRIPT_DELIMITERS' => array(
		0 => array(
			'<?php' => '?>'
			),
		1 => array(
			'<?' => '?>'
			),
		2 => array(
			'<%' => '%>'
			),
		3 => array(
			'<script language="php">' => '</script>'
			)
		),*/

	'HIGHLIGHT_STRICT_BLOCK' => array(
		0 => true,
		1 => true,
		2 => true,
		3 => true
		)
);

?>

