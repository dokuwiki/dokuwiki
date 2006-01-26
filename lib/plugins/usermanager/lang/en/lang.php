<?php
/**
 * english language file
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'User Manager'; 
 
// custom language strings for the plugin
$lang['noauth']      = '(user authentication not available)';
$lang['nosupport']   = '(user management not supported)';

$lang['badauth']     = 'invalid auth mechanism';     // should never be displayed!

$lang['user_id']     = 'User';
$lang['user_pass']   = 'Password';
$lang['user_name']   = 'Real Name';
$lang['user_mail']   = 'Email';
$lang['user_groups'] = 'Groups';

$lang['field']       = 'Field';
$lang['value']       = 'Value';
$lang['add']         = 'Add';
$lang['delete']      = 'Delete';
$lang['delete_selected'] = 'Delete Selected';
$lang['edit']        = 'Edit';
$lang['edit_prompt'] = 'Edit this user';
$lang['modify']      = 'Save Changes';
$lang['search']      = 'Search';
$lang['search_prompt'] = 'Perform search';
$lang['clear']       = 'Reset Search Filter';
$lang['filter']      = 'Filter';

$lang['summary']     = 'Displaying users %1$d-%2$d of %3$d found. %4$d users total.';
$lang['nonefound']   = 'No users found. %d users total.';
$lang['delete_ok']   = '%d users deleted';
$lang['delete_fail'] = '%d failed deleting.';
$lang['update_ok']   = 'user updated sucessfully';
$lang['update_fail'] = 'user update failed';
$lang['update_exists'] = 'user name change failed, the specified user name (%s) already exists (any other changes will be applied).';

$lang['start']  = 'start';
$lang['prev']   = 'previous';
$lang['next']   = 'next';
$lang['last']   = 'last';

?>
