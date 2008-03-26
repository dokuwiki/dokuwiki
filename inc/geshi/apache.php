<?php
/*************************************************************************************
 * apache.php
 * ----------
 * Author: Tux (tux@inmail.cz)
 * Copyright: (c) 2004 Tux (http://tux.a4.cz/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.21
 * Date Started: 2004/29/07
 *
 * Apache language file for GeSHi.
 * Words are from SciTe configuration file
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.2)
 *  -  Added support for multiple object splitters
 * 2004/10/27 (1.0.1)
 *   -  Added support for URLs
 * 2004/08/05 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2004/07/29)
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
	'LANG_NAME' => 'Apache Log',
	'COMMENT_SINGLE' => array(1 => '#'),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		/*keywords*/
	        1 => array(
			'accessconfig','accessfilename','action','addalt',
			'addaltbyencoding','addaltbytype','addcharset',
			'adddefaultcharset','adddescription',
			'addencoding','addhandler','addicon','addiconbyencoding',
			'addiconbytype','addlanguage','addmodule','addmoduleinfo',
			'addtype','agentlog','alias','aliasmatch',
			'allow','allowconnect','allowoverride','anonymous',
			'anonymous_authoritative','anonymous_logemail','anonymous_mustgiveemail',
			'anonymous_nouserid','anonymous_verifyemail','authauthoritative',
			'authdbauthoritative','authdbgroupfile','authdbmauthoritative',
			'authdbmgroupfile','authdbmgroupfile','authdbuserfile','authdbmuserfile',
			'authdigestfile','authgroupfile','authname','authtype',
			'authuserfile','bindaddress','browsermatch','browsermatchnocase',
			'bs2000account','cachedefaultexpire','cachedirlength','cachedirlevels',
			'cacheforcecompletion','cachegcinterval','cachelastmodifiedfactor','cachemaxexpire',
			'cachenegotiateddocs','cacheroot','cachesize','checkspelling',
			'clearmodulelist','contentdigest','cookieexpires','cookielog',
			'cookielog','cookietracking','coredumpdirectory','customlog',
			'defaulticon','defaultlanguage','defaulttype','define',
			'deny','directory','directorymatch','directoryindex',
			'documentroot','errordocument','errorlog','example',
			'expiresactive','expiresbytype','expiresdefault','extendedstatus',
			'fancyindexing','files','filesmatch','forcetype',
			'group','header','headername','hostnamelookups',
			'identitycheck','ifdefine','ifmodule','imapbase',
			'imapdefault','imapmenu','include','indexignore',
			'indexoptions','keepalive','keepalivetimeout','languagepriority',
			'limit','limitexcept','limitrequestbody','limitrequestfields',
			'limitrequestfieldsize','limitrequestline','listen','listenbacklog',
			'loadfile','loadmodule','location','locationmatch',
			'lockfile','logformat','loglevel','maxclients',
			'maxkeepaliverequests','maxrequestsperchild','maxspareservers','metadir',
			'metafiles','metasuffix','mimemagicfile','minspareservers',
			'mmapfile','namevirtualhost','nocache','options','order',
			'passenv','pidfile','port','proxyblock','proxydomain',
			'proxypass','proxypassreverse','proxyreceivebuffersize','proxyremote',
			'proxyrequests','proxyvia','qsc','readmename',
			'redirect','redirectmatch','redirectpermanent','redirecttemp',
			'refererignore','refererlog','removehandler','require',
			'resourceconfig','rewritebase','rewritecond','rewriteengine',
			'rewritelock','rewritelog','rewriteloglevel','rewritemap',
			'rewriteoptions','rewriterule','rlimitcpu','rlimitmem',
			'rlimitnproc','satisfy','scoreboardfile','script',
			'scriptalias','scriptaliasmatch','scriptinterpretersource','scriptlog',
			'scriptlogbuffer','scriptloglength','sendbuffersize',
			'serveradmin','serveralias','servername','serverpath',
			'serverroot','serversignature','servertokens','servertype',
			'setenv','setenvif','setenvifnocase','sethandler',
			'singlelisten','startservers','threadsperchild','timeout',
			'transferlog','typesconfig','unsetenv','usecanonicalname',
			'user','userdir','virtualhost','virtualdocumentroot',
			'virtualdocumentrootip','virtualscriptalias','virtualscriptaliasip',
			'xbithack','from','all'
		  ),
		/*keyords 2*/
		2 => array(
			'on','off','standalone','inetd',
			'force-response-1.0','downgrade-1.0','nokeepalive',
			'ndexes','includes','followsymlinks','none',
			'x-compress','x-gzip'
		)
	),
	'SYMBOLS' => array(
		'(', ')'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #00007f;',
			2 => 'color: #0000ff;',
			),
		'COMMENTS' => array(
			1 => 'color: #adadad; font-style: italic;',
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
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);

?>
