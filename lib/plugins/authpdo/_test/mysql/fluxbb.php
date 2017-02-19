<?php
/**
 * Confiuration for fluxbb. They have a very simplistic model. There is no separate display name and a user can
 * only be in a single group.
 */
/** @noinspection SqlResolve */
$data = array(
    'passcrypt' => 'sha1',
    'conf' => array(
        'select-user' => '
            SELECT id AS uid,
                   username AS user,
                   username AS name,
                   password AS hash,
                   email AS mail
              FROM fluy_users
             WHERE username = :user
        ',
        'select-user-groups' => '
            SELECT g_title AS `group`
              FROM fluy_groups G, fluy_users U
             WHERE U.id = :uid
               AND U.group_id = G.g_id
        ',
        'select-groups' => '
            SELECT g_id AS gid, g_title AS `group`
              FROM fluy_groups
        ',
        'insert-user' => '
            INSERT INTO fluy_users
                   (group_id, username, password, email)
            VALUES (0, :user, :hash, :mail)
        ',
        'delete-user' => '
            DELETE FROM fluy_users
             WHERE id = :uid
        ',
        'list-users' => '
            SELECT DISTINCT username AS user
              FROM fluy_users U, fluy_groups G
             WHERE U.id = G.g_id
               AND G.g_title LIKE :group
               AND U.username LIKE :user
               AND U.username LIKE :name
               AND U.email LIKE :mail
          ORDER BY username
             LIMIT :limit
            OFFSET :start
        ',
        'count-users' => '
            SELECT COUNT(DISTINCT username) AS `count`
              FROM fluy_users U, fluy_groups G
             WHERE U.id = G.g_id
               AND G.g_title LIKE :group
               AND U.username LIKE :user
               AND U.username LIKE :name
               AND U.email LIKE :mail
        ',
        'update-user-info' => '', // we can't do this because username = displayname
        'update-user-login' => '
            UPDATE fluy_users
               SET username = :newlogin
             WHERE id = :uid
        ',
        'update-user-pass' => '
            UPDATE fluy_users
               SET password = :hash
             WHERE id = :uid
        ',
        'insert-group' => '
            INSERT INTO fluy_groups (g_title) VALUES (:group)
        ',
        'join-group' => '
            UPDATE fluy_users
               SET group_id = :gid
             WHERE id = :uid
        ',
        'leave-group' => '
            SELECT 1
        ', // we do a no-op for this
    ),
    'users' => array(
        array(
            'user' => 'admin',
            'pass' => 'pass',
            'name' => 'admin',
            'mail' => 'admin@example.com',
            'grps' =>
                array(
                    0 => 'Administrators',
                ),
        ),
        array(
            'user' => 'test1',
            'pass' => 'password',
            'name' => 'test1',
            'mail' => 'test1@example.com',
            'grps' =>
                array(
                    0 => 'test',
                ),
        ),
    ),
);
