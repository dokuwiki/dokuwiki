<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Manage Plugins...'; 

// custom language strings for the plugin
$lang['refresh'] = "Refresh list of installed plugins";
$lang['refresh_x'] = "Use this option if you have altered any of your plugins manually"; 
$lang['download'] = "Download and install a new plugin";
$lang['manage'] = "Installed Plugins";
$lang['btn_info'] = 'info';
$lang['btn_update'] = 'update';
$lang['btn_delete'] = 'delete';
$lang['btn_settings'] = 'settings';
$lang['btn_refresh'] = 'Refresh';
$lang['btn_download'] = 'Download';
$lang['url'] = 'URL';
//$lang[''] = '';

$lang['installed'] = 'Installed:';
$lang['lastupdate'] = 'Last updated:';
$lang['source'] = 'Source:';
$lang['unknown'] = 'unknown';

// ..ing = header message
// ..ed = success message

$lang['refreshing'] = 'Refreshing ...';
$lang['refreshed'] = 'Plugin refresh completed.';

$lang['updating'] = 'Updating ...';
$lang['updated'] = 'Plugin %s updated successfully';

$lang['downloading'] = 'Downloading ...';
$lang['downloaded'] = 'Plugin %s installed successfully';
$lang['downloads'] = 'The following plugins have been installed successfully:';
$lang['download_none'] = 'No plugins found, or there has been an unknown problem during downloading and installing.';
	
// error messages
$lang['error_download'] = 'Unable to download the plugin file: %s';
$lang['error_write'] = 'Unable to write create aggregate file %s';
$lang['error_badurl'] = 'Suspect bad url - unable to determine file name from the url';
$lang['error_dircreate'] = 'Unable to create temporary folder to receive download';
//$lang['error_'] = '';	
	

//Setup VIM: ex: et ts=2 enc=utf-8 :
