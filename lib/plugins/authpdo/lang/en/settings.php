<?php
/**
 * english language file for authpdo plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$lang['debug']              = 'Print out detailed error messages. Should be disabled after setup.';
$lang['dsn']                = 'The DSN to connect to the database.';
$lang['user']               = 'The user for the above database connection (empty for sqlite)';
$lang['pass']               = 'The password for the above database connection (empty for sqlite)';
$lang['select-user']        = 'SQL Statement to select the data of a single user';
$lang['select-user-groups'] = 'SQL Statement to select all groups of a single user';
$lang['select-groups']      = 'SQL Statement to select all available groups';
$lang['insert-user']        = 'SQL Statement to insert a new user into the database';
$lang['delete-user']        = 'SQL Statement to remove a single user from the database';
$lang['list-users']         = 'SQL Statement to list users matching a filter';
$lang['count-users']        = 'SQL Statement to count users matching a filter';
$lang['update-user-info']   = 'SQL Statement to update the full name and email address of a single user';
$lang['update-user-login']  = 'SQL Statement to update the login name of a single user';
$lang['update-user-pass']   = 'SQL Statement to update the password of a single user';
$lang['insert-group']       = 'SQL Statement to insert a new group into the database';
$lang['join-group']         = 'SQL Statement to add a user to an exisitng group';
$lang['leave-group']        = 'SQL Statement to remove a user from a group';
