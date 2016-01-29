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
 * statement to select a single user identified by its login name given as :user
 *
 * return; user, name, mail, (clear|hash), [uid]
 * other fields are returned but not used
 */
$conf['select-user'] = '';
