<?php
/*************************************************************************************
 * nsis.php
 * --------
 * Author: Tux (tux@inmail.cz)
 * Copyright: (c) 2004 Tux (http://tux.a4.cz/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.6
 * CVS Revision Version: $Revision: 1.1 $
 * Date Started: 2004/29/07
 * Last Modified: $Date: 2005/06/02 04:57:18 $
 *
 * NullSoft Installer System language file for GeSHi.
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
	'LANG_NAME' => 'nsis',
	'COMMENT_SINGLE' => array(1 => ';'),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'",'"'),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		1 => array(
			'What','Abort','AddSize','AllowRootDirInstall','AutoCloseWindow',
			'BGGradient','BrandingText','BringToFront','CRCCheck','Call','CallInstDLL','Caption','ClearErrors',
			'CompletedText','ComponentText','CopyFiles','CreateDirectory','CreateShortCut','Delete',
			'DeleteINISec','DeleteINIStr','DeleteRegKey','DeleteRegValue','DetailPrint','DetailsButtonText',
			'DirShow','DirText','DisabledBitmap','EnabledBitmap','EnumRegKey','EnumRegValue','Exch','Exec',
			'ExecShell','ExecWait','ExpandEnvStrings','File','FileClose','FileErrorText','FileOpen','FileRead',
			'FileReadByte','FileSeek','FileWrite','FileWriteByte','FindClose','FindFirst','FindNext','FindWindow',
			'Function','FunctionEnd','GetCurrentAddress','GetDLLVersionLocal','GetDllVersion','GetFileTime',
			'GetFileTimeLocal','GetFullPathName','GetFunctionAddress','GetLabelAddress','GetTempFileName',
			'Goto','HideWindow','Icon','IfErrors','IfFileExists','IfRebootFlag','InstProgressFlags','InstType',
			'InstallButtonText','InstallColors','InstallDir','InstallDirRegKey','IntCmp','IntCmpU','IntFmt','IntOp',
			'IsWindow','LicenseData','LicenseText','MessageBox','MiscButtonText','Name','OutFile','Pop','Push',
			'Quit','RMDir','ReadEnvStr','ReadINIStr','ReadRegDword','ReadRegStr','Reboot','RegDLL','Rename',
			'Return','SearchPath','Section','SectionDivider','SectionEnd','SectionIn','SendMessage','SetAutoClose',
			'SetCompress','SetCompressor','SetDatablockOptimize','SetDateSave','SetDetailsPrint','SetDetailsView','SetErrors',
			'SetFileAttributes','SetOutPath','SetOverwrite','SetRebootFlag','ShowInstDetails','ShowUninstDetails',
			'SilentInstall','SilentUnInstall','Sleep','SpaceTexts','StrCmp','StrCpy','StrLen','SubCaption','UnRegDLL',
			'UninstallButtonText','UninstallCaption','UninstallEXEName','UninstallIcon','UninstallSubCaption',
			'UninstallText','WindowIcon','WriteINIStr','WriteRegBin','WriteRegDword','WriteRegDWORD','WriteRegExpandStr',
			'WriteRegStr','WriteUninstaller','SectionGetFlags','SectionSetFlags','SectionSetText','SectionGetText',
			'LogText','LogSet','CreateFont','SetShellVarContext','SetStaticBkColor','SetBrandingImage','PluginDir',
			'SubSectionEnd','SubSection','CheckBitmap','ChangeUI','SetFont','AddBrandingImage','XPStyle','Var',
			'LangString','!define','!undef','!ifdef','!ifndef','!endif','!else','!macro','!echo','!warning','!error','!verbose',
			'!macroend','!insertmacro','!system','!include','!cd','!packhdr','!addplugindir'
		  ),
		2 => array(
			'$0','$1','$2','$3','$4','$5','$6','$7','$8','$9',
			'$R0','$R1','$R2','$R3','$R4','$R5','$R6','$R7','$R8','$R9','$CMDLINE','$DESKTOP',
			'$EXEDIR','$HWNDPARENT','$INSTDIR','$OUTDIR','$PROGRAMFILES','${NSISDIR}',
			'$QUICKLAUNCH','$SMPROGRAMS','$SMSTARTUP','$STARTMENU','$SYSDIR','$TEMP','$WINDIR'
		    ),
		3 => array(
			'ARCHIVE','FILE_ATTRIBUTE_ARCHIVE','FILE_ATTRIBUTE_HIDDEN',
			'FILE_ATTRIBUTE_NORMAL','FILE_ATTRIBUTE_OFFLINE','FILE_ATTRIBUTE_READONLY',
			'FILE_ATTRIBUTE_SYSTEM','FILE_ATTRIBUTE_TEMPORARY','HIDDEN','HKCC','HKCR','HKCU',
			'HKDD','HKEY_CLASSES_ROOT','HKEY_CURRENT_CONFIG','HKEY_CURRENT_USER','HKEY_DYN_DATA',
			'HKEY_LOCAL_MACHINE','HKEY_PERFORMANCE_DATA','HKEY_USERS','HKLM','HKPD','HKU','IDABORT',
			'IDCANCEL','IDIGNORE','IDNO','IDOK','IDRETRY','IDYES','MB_ABORTRETRYIGNORE','MB_DEFBUTTON1',
			'MB_DEFBUTTON2','MB_DEFBUTTON3','MB_DEFBUTTON4','MB_ICONEXCLAMATION',
			'MB_ICONINFORMATION','MB_ICONQUESTION','MB_ICONSTOP','MB_OK','MB_OKCANCEL',
			'MB_RETRYCANCEL','MB_RIGHT','MB_SETFOREGROUND','MB_TOPMOST','MB_YESNO','MB_YESNOCANCEL',
			'NORMAL','OFFLINE','READONLY','SW_SHOWMAXIMIZED','SW_SHOWMINIMIZED','SW_SHOWNORMAL',
			'SYSTEM','TEMPORARY','auto','colored','false','force','hide','ifnewer','nevershow','normal',
			'off','on','show','silent','silentlog','smooth','true','try'
		   ),
		4 => array(
			'MyFunction','MySomethingElse'
		)
	),
	'SYMBOLS' => array(
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		3 => false,
		4 => false,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #00007f;',
			2 => 'color: #0000ff;',
			3 => 'color: #46aa03; font-weight:bold;',
			4 => 'color: #0000ff;',
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
		2 => '',
		3 => '',
		4 => ''
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
