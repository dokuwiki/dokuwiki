<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 */
$lang['server']                = 'PostgreSQL のサーバー名';
$lang['port']                  = 'PostgreSQL サーバーのポート番号';
$lang['user']                  = 'PostgreSQL 接続用ユーザー名';
$lang['password']              = 'PostgreSQL 接続用ユーザーのパスワード';
$lang['database']              = '使用するデータベース名';
$lang['debug']                 = 'デバック情報を表示する';
$lang['forwardClearPass']      = '以下で定義する SQL ステートメントにおいて, パスワード変数 を平文とする(DokiWiki側で暗号化しない)';
$lang['checkPass']             = 'パスワードの照合に用いる SQL ステートメント';
$lang['getUserInfo']           = 'ユーザー情報の取得に用いる SQL ステートメント';
$lang['getGroups']             = 'ユーザーが所属する全てのグループの取得に用いる SQL ステートメント';
$lang['getUsers']              = 'ユーザーリストを取得する SQL ステートメント';
$lang['FilterLogin']           = 'ユーザーリストをログイン名で絞り込む SQL 句';
$lang['FilterName']            = 'ユーザーリストをフルネームで絞り込む SQL 句';
$lang['FilterEmail']           = 'ユーザーリストをメールアドレスで絞り込む SQL 句';
$lang['FilterGroup']           = 'ユーザーリストを所属グループで絞り込む SQL 句';
$lang['SortOrder']             = 'ユーザーリストのソート方法を指定する SQL 句';
$lang['addUser']               = '新規ユーザーを追加する SQL ステートメント';
$lang['addGroup']              = '新規グループを追加する SQL ステートメント';
$lang['addUserGroup']          = 'ユーザーをグループに配属する SQL ステートメント';
$lang['delGroup']              = 'グループを削除する SQL ステートメント';
$lang['getUserID']             = 'ユーザーIDの取得に用いる SQL ステートメン';
$lang['delUser']               = 'ユーザーを削除する SQL ステートメント';
$lang['delUserRefs']           = 'ユーザーのグループ所属を全て取り消す SQL ステートメント';
$lang['updateUser']            = 'ユーザー情報を変更する SQL ステートメント';
$lang['UpdateLogin']           = '変更後のログイン名を指定する SQL 句';
$lang['UpdatePass']            = '変更後のパスワードを指定する SQL 句';
$lang['UpdateEmail']           = '変更後のメールアドレスを指定する SQL 句';
$lang['UpdateName']            = '変更後のフルネームを指定する SQL 句';
$lang['UpdateTarget']          = '変更対象のユーザを特定するための SQL 句';
$lang['delUserGroup']          = 'ユーザーをグループから除名する SQL ステートメント';
$lang['getGroupID']            = 'グループIDの取得に用いる SQL ステートメント';
