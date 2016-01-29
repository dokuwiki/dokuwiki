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
 * Select all the group names a user is member of
 *
 * input: :user, [:uid], [*]
 * return: group
 */
$conf['select-user-group'] = '';
