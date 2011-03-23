<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Anika Henke <anika@selfthinker.org>
 * @author     Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */

$lang['admin_acl']  = 'Access Control List Management';
$lang['acl_group']  = 'Group';
$lang['acl_user']   = 'User';
$lang['acl_perms']  = 'Permissions for';
$lang['page']       = 'Page';
$lang['namespace']  = 'Namespace';

$lang['btn_select']  = 'Select';

$lang['p_user_id']    = 'User <b class="acluser">%s</b> currently has the following permissions on page <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_user_ns']    = 'User <b class="acluser">%s</b> currently has the following permissions in namespace <b class="aclns">%s</b>: <i>%s</i>.';
$lang['p_group_id']   = 'Members of group <b class="aclgroup">%s</b> currently have the following permissions on page <b class="aclpage">%s</b>: <i>%s</i>.';
$lang['p_group_ns']   = 'Members of group <b class="aclgroup">%s</b> currently have the following permissions in namespace <b class="aclns">%s</b>: <i>%s</i>.';

$lang['p_choose_id']  = 'Please <b>enter a user or group</b> in the form above to view or edit the permissions set for the page <b class="aclpage">%s</b>.';
$lang['p_choose_ns']  = 'Please <b>enter a user or group</b> in the form above to view or edit the permissions set for the namespace <b class="aclns">%s</b>.';


$lang['p_inherited']  = 'Note: Those permissions were not set explicitly but were inherited from other groups or higher namespaces.';
$lang['p_isadmin']    = 'Note: The selected group or user has always full permissions because it is configured as superuser.';
$lang['p_include']    = 'Higher permissions include lower ones. Create, Upload and Delete permissions only apply to namespaces, not pages.';

$lang['current'] = 'Current ACL Rules';
$lang['where'] = 'Page/Namespace';
$lang['who']   = 'User/Group';
$lang['perm']  = 'Permissions';

$lang['acl_perm0']  = 'None';
$lang['acl_perm1']  = 'Read';
$lang['acl_perm2']  = 'Edit';
$lang['acl_perm4']  = 'Create';
$lang['acl_perm8']  = 'Upload';
$lang['acl_perm16'] = 'Delete';
$lang['acl_new']    = 'Add new Entry';
$lang['acl_mod']    = 'Modify Entry';
//Setup VIM: ex: et ts=2 :
