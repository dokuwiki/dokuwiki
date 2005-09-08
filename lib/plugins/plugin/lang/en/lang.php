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
$lang['updates'] = 'The following plugins have been updated successfully';
$lang['update_none'] = 'No updates found.';

$lang['deleting'] = 'Deleting ...';
$lang['deleted'] = 'Plugin %s deleted.';

$lang['downloading'] = 'Downloading ...';
$lang['downloaded'] = 'Plugin %s installed successfully';
$lang['downloads'] = 'The following plugins have been installed successfully:';
$lang['download_none'] = 'No plugins found, or there has been an unknown problem during downloading and installing.';

// info titles
$lang['plugin'] = 'Plugin:';
$lang['components'] = 'Components';
$lang['noinfo'] = 'This plugin returned no information, it may be invalid.';
$lang['name'] = 'Name:';
$lang['date'] = 'Date:';
$lang['type'] = 'Type:';
$lang['desc'] = 'Description:';
$lang['author'] = 'Author:';
$lang['www'] = 'Web:';
    
// error messages
$lang['error'] = 'An unknown error occurred.';
$lang['error_download'] = 'Unable to download the plugin file: %s';
$lang['error_write'] = 'Unable to create aggregate file %s';
$lang['error_badurl'] = 'Suspect bad url - unable to determine file name from the url';
$lang['error_dircreate'] = 'Unable to create temporary folder to receive download';
$lang['error_decompress'] = 'The plugin manager was unable to decompress the downloaded file. '.
            'This maybe as a result of a bad download, in which case you should try again; '.
            'or the compression format may be unknown, in which case you will need to download and install the plugin manually.';
$lang['error_copy'] = 'There was a file copy error while attempting to install files for plugin %s: '.
            'the disk could be full or file access permissions may be incorrect. '.
            'This may have resulted in a partially installed plugin and leave your wiki installation unstable.';
//$lang['error_'] = '';    

//Setup VIM: ex: et ts=2 enc=utf-8 :
