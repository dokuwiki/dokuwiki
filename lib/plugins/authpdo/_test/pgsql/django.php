<?php
/**
 * Django application config
 *
 */
/** @noinspection SqlResolve */
$data = array(
    'passcrypt' => 'djangopbkdf2_sha256',
    'conf' => array(
        'select-user' => '
            SELECT id AS uid,
                   username AS "user",
                   CONCAT_WS(\' \', first_name, last_name) AS name,
                   password AS hash,
                   email AS mail
              FROM auth_user
             WHERE username = :user
        ',
        'select-user-groups' => '
            SELECT G.name AS "group"
              FROM auth_group G, auth_user_groups UG
             WHERE UG.user_id = :uid
               AND UG.group_id = G.id
        ',
        'select-groups' => '
            SELECT id AS gid, name AS "group"
              FROM auth_group
        ',
        'insert-user' => '
            INSERT INTO auth_user
                   (password, is_superuser, username, first_name, last_name, email, is_staff, is_active, date_joined)
                   VALUES (:hash, false, :user, SPLIT_PART(:name,\' \',1), SPLIT_PART(:name,\' \',2), :mail, false, true, NOW())
        ',
        'delete-user' => '
            DELETE FROM auth_user_user_permissions
             WHERE user_id = :uid
            ;
            DELETE FROM auth_user
             WHERE id = :uid
        ',
        'list-users' => '
            SELECT DISTINCT U.username AS "user"
              FROM auth_user U, auth_user_groups UG, auth_group G
             WHERE U.id = UG.user_id
               AND G.id = UG.group_id
               AND G.name LIKE :group
               AND U.username LIKE :user
               AND CONCAT_WS(\' \', U.first_name, U.last_name) LIKE :name
               AND U.email LIKE :mail
          ORDER BY username
             LIMIT :limit
            OFFSET :start
        ',
        'count-users' => '
            SELECT COUNT(DISTINCT U.username) AS count
              FROM auth_user U, auth_user_groups UG, auth_group G
             WHERE U.id = UG.user_id
               AND G.id = UG.group_id
               AND G.name LIKE :group
               AND U.username LIKE :user
               AND CONCAT_WS(\' \', U.first_name, U.last_name) LIKE :name
               AND U.email LIKE :mail
        ',
        'update-user-info' => '
            UPDATE auth_user
               SET first_name = SPLIT_PART(:name,\' \',1),
                   last_name = SPLIT_PART(:name,\' \',2),
                   email = :mail
             WHERE id = :uid
        ',
        'update-user-login' => '
            UPDATE auth_user
               SET username = :newlogin
             WHERE id = :uid
        ',
        'update-user-pass' => '
            UPDATE auth_user
               SET password = :hash
             WHERE id = :uid
        ',
        'insert-group' => '
            INSERT INTO auth_group (name) VALUES (:group)
        ',
        'join-group' => '
            INSERT INTO auth_user_groups (user_id, group_id) VALUES (:uid, :gid)
        ',
        'leave-group' => '
            DELETE FROM auth_user_groups
             WHERE user_id = :uid
               AND group_id = :gid
        ',
    ),
    'users' => array(
        array(
            'user' => 'test-billing',
            'pass' => 'P4zzW0rd!',
            'name' => 'Joana Gröschel',
            'mail' => 'jg@billing.com',
            'grps' =>
                array(
                    0 => 'Billing',
                ),
        ),
        array(
            'user' => 'test-kunde',
            'pass' => 'P4zzW0rd!',
            'name' => 'Niels Buchberger',
            'mail' => 'ng@kunde.com',
            'grps' =>
                array(
                    0 => 'Kunden',
                ),
        ),
        array(
            'user' => 'test-mitarbeiter',
            'pass' => 'P4zzW0rd!',
            'name' => 'Claus Wernke',
            'mail' => 'cw@mitarbeiter.com',
            'grps' =>
                array(
                    0 => 'Mitarbeiter',
                ),
        ),
        array(
            'user' => 'test-projektleiter',
            'pass' => 'P4zzW0rd!',
            'name' => 'Sascha Weiher',
            'mail' => 'sw@projektleiter.com',
            'grps' =>
                array(
                    0 => 'Projektleiter',
                ),
        ),
    ),
);

// passwords in the dump use the newest format, we need PHP support for that
if(!function_exists('hash_pbkdf2') || !in_array('sha256', hash_algos())){
    $data = 'missing pbkdf2 hash support to check passwords - django test has to be skipped';
}
