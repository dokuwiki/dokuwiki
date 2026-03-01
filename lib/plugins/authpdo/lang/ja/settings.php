<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author HokkaidoPerson <dosankomali@yahoo.co.jp>
 */
$lang['debug']                 = '詳細なエラーメッセージを出力する（セットアップ後、このオプションはオフにすべきです）';
$lang['dsn']                   = 'データベースにアクセスするDSN';
$lang['user']                  = '上記データベースに接続するユーザー名（sqliteの場合は空欄にしておいて下さい）';
$lang['pass']                  = '上記データベースに接続するパスワード（sqliteの場合は空欄にしておいて下さい）';
$lang['select-user']           = '個々のユーザーのデータを選ぶSQL命令文';
$lang['select-user-groups']    = '個々のユーザーが属する全てのグループを選ぶSQL命令文';
$lang['select-groups']         = '利用可能な全グループを選ぶSQL命令文';
$lang['insert-user']           = 'データベースに新規ユーザーを追加するSQL命令文';
$lang['delete-user']           = '個々のユーザーをデータベースから取り除くSQL命令文';
$lang['list-users']            = 'フィルターに一致するユーザーを一覧にするSQL命令文';
$lang['count-users']           = 'フィルターに一致するユーザーを数えるSQL命令文';
$lang['update-user-info']      = '個々のユーザーのフルネームとメールアドレスを更新するSQL命令文';
$lang['update-user-login']     = '個々のユーザーのログイン名を更新するSQL命令文';
$lang['update-user-pass']      = '個々のユーザーのパスワードを更新するSQL命令文';
$lang['insert-group']          = 'データベースに新規グループを追加するSQL命令文';
$lang['join-group']            = '既にあるグループにユーザーを追加するSQL命令文';
$lang['leave-group']           = 'グループからユーザーを取り除くSQL命令文';
$lang['check-pass']            = 'ユーザーのパスワードをチェックするSQL命令文（select-userでパスワード情報を呼び出す場合は空欄にしておけます）';
