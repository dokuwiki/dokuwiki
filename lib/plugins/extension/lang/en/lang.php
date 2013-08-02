<?php
/**
 * English language file for extension plugin
 *
 * @author Michael Hamann <michael@content-space.de>
 */

// menu entry for admin plugins
$lang['menu'] = 'Extension manager';

// custom language strings for the plugin
$lang['notimplemented'] = 'This feature hasn\'t been implemented yet';
$lang['alreadyenabled'] = 'This extension has already been enabled';
$lang['alreadydisabled'] = 'This extension has already been disabled';
$lang['pluginlistsaveerror'] = 'There was an error saving the plugin list';
$lang['unknownauthor'] = 'Unknown author';
$lang['unknownversion'] = 'Unknown version';


$lang['error_badurl']           = 'URL ends with slash - unable to determine file name from the url';
$lang['error_dircreate']        = 'Unable to create temporary folder to receive download';
$lang['error_download']         = 'Unable to download the file: %s';
$lang['error_decompress']       = 'Unable to decompress the downloaded file. This maybe as a result of a bad download, in which case you should try again; or the compression format may be unknown, in which case you will need to download and install manually';
$lang['error_findfolder']       = 'Unable to identify extension directory, you need to download and install manually';
$lang['error_copy']             = 'There was a file copy error while attempting to install files for directory <em>%s</em>: the disk could be full or file access permissions may be incorrect. This may have resulted in a partially installed plugin and leave your wiki installation unstable';

//Setup VIM: ex: et ts=4 :
