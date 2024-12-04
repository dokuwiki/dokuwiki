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
$lang['msg_delete_success']           = 'Extension %s uninstalled';
$lang['msg_delete_failed']            = 'Uninstalling Extension %s failed';
$lang['msg_install_success']          = 'Extension %s installed successfully';
$lang['msg_update_success']           = 'Extension %s updated successfully';
$lang['msg_upload_failed']            = 'Uploading the file failed: %s';
$lang['msg_nooverwrite']              = 'Extension %s already exists so it is not being overwritten; to overwrite, tick the overwrite option';

$lang['missing_dependency']           = 'Missing or disabled dependency: %s';
$lang['found_conflict']               = 'This extension is marked as conflictig with the following installed extensions: %s';
$lang['security_issue']               = 'Security Issue: %s';
$lang['security_warning']             = 'Security Warning: %s';
$lang['update_message']               = 'Update Message: %s';
$lang['wrong_folder']                 = 'Extension installed incorrectly: Rename directory from "%s" to "%s".';
$lang['url_change']                   = "URL changed: Download URL has changed since last download. Check if the new URL is valid before updating the extension.\nNew: %s\nOld: %s";

$lang['error_badurl']                 = 'URLs should start with http or https';
$lang['error_dircreate']              = 'Unable to create temporary folder to receive download';
$lang['error_download']               = 'Unable to download the file: %s %s %s';
$lang['error_decompress']             = 'Unable to decompress the downloaded file. This maybe as a result of a bad download, in which case you should try again; or the compression format may be unknown, in which case you will need to download and install manually.';
$lang['error_findfolder']             = 'Unable to identify extension directory, you need to download and install manually';
$lang['error_copy']                   = 'There was a file copy error while attempting to install files for directory \'%s\': the disk could be full or file access permissions may be incorrect. This may have resulted in a partially installed plugin and leave your wiki installation unstable';
$lang['error_copy_read']              = 'Could not read directory %s';
$lang['error_copy_mkdir']             = 'Could not create directory %s';
$lang['error_copy_copy']              = 'Could not copy %s to %s';
$lang['error_archive_read']           = 'Could not open archive %s for reading';
$lang['error_archive_extract']        = 'Could not extract archive %s: %s';
$lang['error_uninstall_protected']    = 'Extension %s is protected and cannot be uninstalled';
$lang['error_uninstall_dependants']   = 'Extension %s is still required by %s and thus cannot be uninstalled';
$lang['error_disable_protected']      = 'Extension %s is protected and cannot be disabled';
$lang['error_disable_dependants']     = 'Extension %s is still required by %s and thus cannot be disabled';
$lang['error_nourl']                  = 'No download URL could be found for extension %s';
$lang['error_notinstalled']           = 'Extension %s is not installed';
$lang['error_alreadyenabled']         = 'Extension %s has already been enabled';
$lang['error_alreadydisabled']        = 'Extension %s has already been disabled';
$lang['error_minphp']                 = 'Extension %s requires at least PHP %s but this wiki is running PHP %s';
$lang['error_maxphp']                 = 'Extension %s only supports PHP up to %s but this wiki is running PHP %s';

$lang['noperms']                      = 'Extension directory is not writable';
$lang['notplperms']                   = 'Template directory is not writable';
$lang['nopluginperms']                = 'Plugin directory is not writable';
$lang['git']                          = 'This extension was installed via git, you may not want to update it here.';
$lang['auth']                         = 'This auth plugin is not enabled in configuration, consider disabling it.';

$lang['install_url']                  = 'Install from URL:';
$lang['install_upload']               = 'Upload Extension:';

$lang['repo_badresponse']             = 'The plugin repository returned an invalid response.';
$lang['repo_error']                   = 'The plugin repository could not be contacted. Make sure your server is allowed to contact www.dokuwiki.org and check your proxy settings.';
$lang['nossl']                        = 'Your PHP seems to miss SSL support. Downloading will not work for many DokuWiki extensions.';

$lang['popularity_high'] = 'This is one of the most popular extensions';
$lang['popularity_medium'] = 'This extension is quite popular';
$lang['popularity_low'] = 'This extension has garnered some interest';

$lang['details'] = 'Details';

$lang['js']['display_viewoptions']    = 'View Options:';
$lang['js']['display_enabled']        = 'enabled';
$lang['js']['display_disabled']       = 'disabled';
$lang['js']['display_updatable']      = 'updatable';

$lang['js']['close']                  = 'Click to close';
$lang['js']['filter']                 = 'Show updatable extensions only';
