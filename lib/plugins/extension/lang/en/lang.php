<?php
/**
 * English language file for extension plugin
 *
 * @author Michael Hamann <michael@content-space.de>
 * @author Christopher Smith <chris@jalakai.co.uk>
 */

$lang['menu']                         = 'Extension Manager';

$lang['tab_plugins']                  = 'Installed Plugins';
$lang['tab_templates']                = 'Installed Templates';
$lang['tab_search']                   = 'Search and Install';
$lang['tab_install']                  = 'Manual Install';

$lang['notimplemented']               = 'This feature hasn\'t been implemented yet';
$lang['notinstalled']                 = 'This extension is not installed';
$lang['alreadyenabled']               = 'This extension has already been enabled';
$lang['alreadydisabled']              = 'This extension has already been disabled';
$lang['pluginlistsaveerror']          = 'There was an error saving the plugin list';
$lang['unknownauthor']                = 'Unknown author';
$lang['unknownversion']               = 'Unknown version';

$lang['btn_info']                     = 'Show more info';
$lang['btn_update']                   = 'Update';
$lang['btn_uninstall']                = 'Uninstall';
$lang['btn_enable']                   = 'Enable';
$lang['btn_disable']                  = 'Disable';
$lang['btn_install']                  = 'Install';
$lang['btn_reinstall']                = 'Re-install';

$lang['js']['reallydel']              = 'Really uninstall this extension?';

$lang['search_for']                   = 'Search Extension:';
$lang['search']                       = 'Search';

$lang['extensionby']                  = '<strong>%s</strong> by %s';
$lang['screenshot']                   = 'Screenshot of %s';
$lang['popularity']                   = 'Popularity: %s%%';
$lang['homepage_link']                = 'Docs';
$lang['bugs_features']                = 'Bugs';
$lang['tags']                         = 'Tags:';
$lang['author_hint']                  = 'Search extensions by this author';
$lang['installed']                    = 'Installed:';
$lang['downloadurl']                  = 'Download URL:';
$lang['repository']                   = 'Repository:';
$lang['unknown']                      = '<em>unknown</em>';
$lang['installed_version']            = 'Installed version:';
$lang['install_date']                 = 'Your last update:';
$lang['available_version']            = 'Available version:';
$lang['compatible']                   = 'Compatible with:';
$lang['depends']                      = 'Depends on:';
$lang['similar']                      = 'Similar to:';
$lang['conflicts']                    = 'Conflicts with:';
$lang['donate']                       = 'Like this?';
$lang['donate_action']                = 'Buy the author a coffee!';
$lang['repo_retry']                   = 'Retry';
$lang['provides']                     = 'Provides:';
$lang['status']                       = 'Status:';
$lang['status_installed']             = 'installed';
$lang['status_not_installed']         = 'not installed';
$lang['status_protected']             = 'protected';
$lang['status_enabled']               = 'enabled';
$lang['status_disabled']              = 'disabled';
$lang['status_unmodifiable']          = 'unmodifiable';
$lang['status_plugin']                = 'plugin';
$lang['status_template']              = 'template';
$lang['status_bundled']               = 'bundled';

$lang['msg_enabled']                  = 'Plugin %s enabled';
$lang['msg_disabled']                 = 'Plugin %s disabled';
$lang['msg_delete_success']           = 'Extension uninstalled';
$lang['msg_template_install_success'] = 'Template %s installed successfully';
$lang['msg_template_update_success']  = 'Template %s updated successfully';
$lang['msg_plugin_install_success']   = 'Plugin %s installed successfully';
$lang['msg_plugin_update_success']    = 'Plugin %s updated successfully';
$lang['msg_upload_failed']            = 'Uploading the file failed';

$lang['missing_dependency']           = '<strong>Missing or disabled dependency:</strong> %s';
$lang['security_issue']               = '<strong>Security Issue:</strong> %s';
$lang['security_warning']             = '<strong>Security Warning:</strong> %s';
$lang['update_available']             = '<strong>Update:</strong> New version %s is available.';
$lang['wrong_folder']                 = '<strong>Plugin installed incorrectly:</strong> Rename plugin directory "%s" to "%s".';
$lang['url_change']                   = '<strong>URL changed:</strong> Download URL has changed since last download. Check if the new URL is valid before updating the extension.<br />New: %s<br />Old: %s';

$lang['error_badurl']                 = 'URLs should start with http or https';
$lang['error_dircreate']              = 'Unable to create temporary folder to receive download';
$lang['error_download']               = 'Unable to download the file: %s';
$lang['error_decompress']             = 'Unable to decompress the downloaded file. This maybe as a result of a bad download, in which case you should try again; or the compression format may be unknown, in which case you will need to download and install manually.';
$lang['error_findfolder']             = 'Unable to identify extension directory, you need to download and install manually';
$lang['error_copy']                   = 'There was a file copy error while attempting to install files for directory <em>%s</em>: the disk could be full or file access permissions may be incorrect. This may have resulted in a partially installed plugin and leave your wiki installation unstable';

$lang['noperms']                      = 'Extension directory is not writable';
$lang['notplperms']                   = 'Template directory is not writable';
$lang['nopluginperms']                = 'Plugin directory is not writable';
$lang['git']                          = 'This extension was installed via git, you may not want to update it here.';

$lang['install_url']                  = 'Install from URL:';
$lang['install_upload']               = 'Upload Extension:';

$lang['repo_error']                   = 'The plugin repository could not be contacted. Make sure your server is allowed to contact www.dokuwiki.org and check your proxy settings.';