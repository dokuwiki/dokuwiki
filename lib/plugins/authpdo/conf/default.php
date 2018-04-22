<?php
/**
 * Default settings for the authpdo plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$conf['debug'] = 0;
$conf['dsn'] = '';
$conf['user'] = '';
$conf['pass'] = '';

/**
 * statement to select a single user identified by its login name
 *
 * input: :user
 * return: user, name, mail, (clear|hash), [uid], [*]
 */
$conf['select-user'] = '';

/**
 * statement to check the password in SQL, optional when above returned clear or hash
 *
 * input: :user, :clear, :hash, [uid], [*]
 * return: *
 */
$conf['check-pass'] = '';

/**
 * statement to select a single user identified by its login name
 *
 * input: :user, [uid]
 * return: group
 */
$conf['select-user-groups'] = '';

/**
 * Select all the existing group names
 *
 * return: group, [gid], [*]
 */
$conf['select-groups'] = '';

/**
 * Create a new user
 *
 * input: :user, :name, :mail, (:clear|:hash)
 */
$conf['insert-user'] = '';

/**
 * Remove a user
 *
 * input: :user, [:uid], [*]
 */
$conf['delete-user'] = '';

/**
 * list user names matching the given criteria
 *
 * Make sure the list is distinct and sorted by user name. Apply the given limit and offset
 *
 * input: :user, :name, :mail, :group, :start, :end, :limit
 * out: user
 */
$conf['list-users'] = '';

/**
 * count user names matching the given criteria
 *
 * Make sure the counted list is distinct
 *
 * input: :user, :name, :mail, :group
 * out: count
 */
$conf['count-users'] = '';

/**
 * Update user data (except password and user name)
 *
 * input: :user, :name, :mail, [:uid], [*]
 */
$conf['update-user-info'] = '';

/**
 * Update user name aka login
 *
 * input: :user, :newlogin, [:uid], [*]
 */
$conf['update-user-login'] = '';

/**
 * Update user password
 *
 * input: :user, :clear, :hash, [:uid], [*]
 */
$conf['update-user-pass'] = '';

/**
 * Create a new group
 *
 * input: :group
 */
$conf['insert-group'] = '';

/**
 * Make user join group
 *
 * input: :user, [:uid], group, [:gid], [*]
 */
$conf['join-group'] = '';

/**
 * Make user leave group
 *
 * input: :user, [:uid], group, [:gid], [*]
 */
$conf['leave-group'] = '';
