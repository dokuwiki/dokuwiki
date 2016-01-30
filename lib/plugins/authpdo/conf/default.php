<?php
/**
 * Default settings for the authpdo plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

//$conf['fixme']    = 'FIXME';

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
 * Select all the existing group names
 *
 * return: group, [gid], [*]
 */
$conf['select-group'] = '';

/**
 * Create a new user
 *
 * input: :user, :name, :mail, (:clear,:hash)
 */
$conf['insert-user'] = '';

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
