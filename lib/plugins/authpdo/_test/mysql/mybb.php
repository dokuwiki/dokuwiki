<?php
/**
 * Configuration for mybb. Password checking is done in SQL
 *
 * mybb stores additional group ids in a commaseparated list of mybb_users.addtionalgroups This
 * is currently not supported in the setup below. If someone can come up with a clever config for
 * that PRs would be welcome.
 */
/** @noinspection SqlResolve */
$data = array(
    'passcrypt' => 'sha1',
    'conf' => array(
        'select-user' => '
            SELECT uid,
                   username AS user,
                   username AS name,
                   email AS mail
              FROM mybb_users
             WHERE username = :user
        ',
        'check-pass' => '
            SELECT uid
              FROM mybb_users
             WHERE username = :user
               AND password = MD5(CONCAT(MD5(salt), MD5(:clear)))
        ',
        'select-user-groups' => '
            SELECT UG.title AS `group`,
                   UG.gid
              FROM mybb_usergroups UG,
                   mybb_users U
             WHERE U.usergroup = UG.gid
               AND U.uid = :uid
        ',
        'select-groups' => '
            SELECT gid, title AS `group`
              FROM mybb_usergroups
        ',
        'insert-user' => '
            SET @salt = LEFT(UUID(), 10);
            INSERT INTO mybb_users
                   (username, email, salt, password, regdate)
            VALUES (:user, :mail, @salt, MD5(CONCAT(MD5(@salt), MD5(:clear))), UNIX_TIMESTAMP() )                  
        ',
        'delete-user' => '
            DELETE FROM mybb_users
             WHERE uid = :uid 
        ',
        'list-users' => '
            SELECT U.username AS user
             FROM mybb_usergroups UG,
                   mybb_users U
             WHERE U.usergroup = UG.gid
               AND UG.title LIKE :group
               AND U.username LIKE :user
               AND U.username LIKE :name
               AND U.email LIKE :mail
          ORDER BY U.username
             LIMIT :limit
            OFFSET :start
        ',
        'count-users' => '
            SELECT COUNT(U.username) AS `count`
                 FROM mybb_usergroups UG,
                       mybb_users U
                 WHERE U.usergroup = UG.gid
                   AND UG.title LIKE :group
                   AND U.username LIKE :user
                   AND U.username LIKE :name
                   AND U.email LIKE :mail
        ',
        'update-user-info' => '
            UPDATE mybb_users
               SET email = :mail
             WHERE uid = :uid            
        ', // we do not support changing the full name as that is the same as the login
        'update-user-login' => '
            UPDATE mybb_users
               SET username = :newlogin
             WHERE uid = :uid
        ',
        'update-user-pass' => '
            SET @salt = LEFT(UUID(), 10);
            UPDATE mybb_users
               SET salt = @salt,
                   password = MD5(CONCAT(MD5(@salt), MD5(:clear)))
             WHERE uid = :uid
        ',
        'insert-group' => '
            INSERT INTO mybb_usergroups (title)
             VALUES (:group)
        ',
        'join-group' => '
            UPDATE mybb_users
               SET usergroup = :gid
             WHERE uid = :uid
        ',
        'leave-group' => '', // makes probably no sense to implement
    ),
    'users' => array(
        array(
            'user' => 'Test One',
            'pass' => 'fakepass',
            'name' => 'Test One',
            'mail' => 'no_one@nowhere.com',
            'grps' =>
                array(
                    0 => 'Registered',
                ),
        ),
        array(
            'user' => 'Test Two',
            'pass' => 'fakepass',
            'name' => 'Test Two',
            'mail' => 'no_one@nowhere.com',
            'grps' =>
                array(
                    0 => 'Super Moderators',
                ),
        ),
        array(
            'user' => 'Test Three',
            'pass' => 'fakepass',
            'name' => 'Test Three',
            'mail' => 'no_one@nowhere.com',
            'grps' =>
                array(
                    0 => 'Administrators',
                ),
        ),
        array(
            'user' => 'Test Four',
            'pass' => 'fakepass',
            'name' => 'Test Four',
            'mail' => 'no_one@nowhere.com',
            'grps' =>
                array(
                    0 => 'Moderators',
                ),
        ),


    ),
);
