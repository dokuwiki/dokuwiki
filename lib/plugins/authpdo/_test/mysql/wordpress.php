<?php
/**
 * Basic Wordpress config
 *
 * Wordpress has no proper groups. This configures the default access permissions as groups. Better group
 * support is available through a Wrdpress plugin
 */
/** @noinspection SqlResolve */
$data = array(
    'passcrypt' => 'pmd5',
    'conf' => array(
        'select-user' => '
            SELECT ID AS uid,
                   user_login AS user,
                   display_name AS name,
                   user_pass AS hash,
                   user_email AS mail
              FROM wpvk_users
             WHERE user_login = :user
        ',
        'select-user-groups' => '
            SELECT CONCAT("group",meta_value) AS `group`
              FROM wpvk_usermeta
             WHERE user_id = :uid
               AND meta_key = "wpvk_user_level"
        ',
        'select-groups' => '',
        'insert-user' => '',
        'delete-user' => '',
        'list-users' => '
            SELECT DISTINCT user_login AS user
              FROM wpvk_users U, wpvk_usermeta M
             WHERE U.ID = M.user_id
               AND M.meta_key = "wpvk_user_level"
               AND CONCAT("group", M.meta_value) LIKE :group
               AND U.user_login LIKE :user
               AND U.display_name LIKE :name
               AND U.user_email LIKE :mail
          ORDER BY user_login
             LIMIT :limit
            OFFSET :start
        ',
        'count-users' => '
            SELECT COUNT(DISTINCT user_login) as `count`
              FROM wpvk_users U, wpvk_usermeta M
             WHERE U.ID = M.user_id
               AND M.meta_key = "wpvk_user_level"
               AND CONCAT("group", M.meta_value) LIKE :group
               AND U.user_login LIKE :user
               AND U.display_name LIKE :name
               AND U.user_email LIKE :mail
        ',
        'update-user-info' => '
            UPDATE wpvk_users
               SET display_name = :name,
                   user_email = :mail
             WHERE ID = :uid
        ',
        'update-user-login' => '
            UPDATE wpvk_users
               SET user_login  = :newlogin
             WHERE ID = :uid
        ',
        'update-user-pass' => '
            UPDATE wpvk_users
               SET user_pass = :hash
             WHERE ID = :uid
        ',
        'insert-group' => '',
        'join-group' => '',
        'leave-group' => '',
    ),
    'users' => array(
        array(
            'user' => 'admin',
            'pass' => 'pass',
            'name' => 'admin',
            'mail' => 'admin@example.com',
            'grps' =>
                array(
                    0 => 'group10',
                ),
        ),
        array(
            'user' => 'test1',
            'pass' => 'pass',
            'name' => 'Test1 Subscriber',
            'mail' => 'test1@example.com',
            'grps' =>
                array(
                    0 => 'group0',
                ),
        ),
        array(
            'user' => 'test2',
            'pass' => 'pass',
            'name' => 'Test2 Contributor',
            'mail' => 'test2@example.com',
            'grps' =>
                array(
                    0 => 'group1',
                ),
        ),
        array(
            'user' => 'test3',
            'pass' => 'pass',
            'name' => 'Test3 Author',
            'mail' => 'test3@example.com',
            'grps' =>
                array(
                    0 => 'group2',
                ),
        ),
    ),
);
