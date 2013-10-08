<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 * @author Hideaki SAWADA <sawadakun@live.jp>
 * @author Hideaki SAWADA <chuno@live.jp>
 */
$lang['server']                = 'LDAPサーバー。ホスト名（<code>localhost</code）又は完全修飾URL（<code>ldap://server.tld:389</code>）';
$lang['port']                  = '上記が完全修飾URLでない場合、LDAPサーバーポート';
$lang['usertree']              = 'ユーザーアカウントを探す場所。例：<code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'ユーザーグループを探す場所。例：<code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'ユーザーアカウントを探すためのLDAP抽出条件。例：<code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'グループを探すLDAP抽出条件。例：<code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = '使用するプロトコルのバージョン。<code>3</code>を設定する必要がある場合があります。';
$lang['starttls']              = 'TLS接続を使用しますか？';
$lang['binddn']                = '匿名バインドでは不十分な場合、オプションバインドユーザーのDN。例：<code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = '上記ユーザーのパスワード';
$lang['debug']                 = 'エラーに関して追加のデバッグ情報を表示する。';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
