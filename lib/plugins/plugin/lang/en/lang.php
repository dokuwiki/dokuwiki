<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

$lang['menu'] = 'Manage Plugins';

// custom language strings for the plugin
$lang['download'] = "Download and install a new plugin";
$lang['manage'] = "Installed Plugins";

$lang['btn_info'] = 'info';
$lang['btn_update'] = 'update';
$lang['btn_delete'] = 'delete';
$lang['btn_settings'] = 'settings';
$lang['btn_download'] = 'Download';
$lang['btn_enable'] = 'Save';

$lang['url']              = 'URL';

$lang['installed']        = 'Installed:';
$lang['lastupdate']       = 'Last updated:';
$lang['source']           = 'Source:';
$lang['unknown']          = 'unknown';

// ..ing = header message
// ..ed = success message

$lang['updating']         = 'Updating ...';
$lang['updated']          = 'Plugin %s updated successfully';
$lang['updates']          = 'The following plugins have been updated successfully';
$lang['update_none']      = 'No updates found.';

$lang['deleting']         = 'Deleting ...';
$lang['deleted']          = 'Plugin %s deleted.';

$lang['downloading']      = 'Downloading ...';
$lang['downloaded']       = 'Plugin %s installed successfully';
$lang['downloads']        = 'The following plugins have been installed successfully:';
$lang['download_none']    = 'No plugins found, or there has been an unknown problem during downloading and installing.';

// info titles
$lang['plugin']           = 'Plugin:';
$lang['components']       = 'Components';
$lang['noinfo']           = 'This plugin returned no information, it may be invalid.';
$lang['name']             = 'Name:';
$lang['date']             = 'Date:';
$lang['type']             = 'Type:';
$lang['desc']             = 'Description:';
$lang['author']           = 'Author:';
$lang['www']              = 'Web:';

// error messages
$lang['error']            = 'An unknown error occurred.';
$lang['error_download']   = 'Unable to download the plugin file: %s';
$lang['error_badurl']     = 'Suspect bad url - unable to determine file name from the url';
$lang['error_dircreate']  = 'Unable to create temporary folder to receive download';
$lang['error_decompress'] = 'The plugin manager was unable to decompress the downloaded file. '.
                            'This maybe as a result of a bad download, in which case you should try again; '.
                            'or the compression format may be unknown, in which case you will need to '.
                            'download and install the plugin manually.';
$lang['error_copy']       = 'There was a file copy error while attempting to install files for plugin '.
                            '<em>%s</em>: the disk could be full or file access permissions may be incorrect. '.
                            'This may have resulted in a partially installed plugin and leave your wiki '.
                            'installation unstable.';
$lang['error_delete']     = 'There was an error while attempting to delete plugin <em>%s</em>.  '.
                            'The most probably cause is insufficient file or directory access permissions';

$lang['enabled']          = 'Plugin %s enabled.';
$lang['notenabled']       = 'Plugin %s could not be enabled, check file permissions.';
$lang['disabled']         = 'Plugin %s disabled.';
$lang['notdisabled']      = 'Plugin %s could not be disabled, check file permissions.';
$lang['packageinstalled'] = 'Plugin package (%d plugin(s): %s) successfully installed.';

//Setup VIM: ex: et ts=4 :
