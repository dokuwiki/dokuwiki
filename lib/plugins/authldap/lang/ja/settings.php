<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author HokkaidoPerson <dosankomali@yahoo.co.jp>
 * @author lempel <riverlempel@hotmail.com>
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 * @author Hideaki SAWADA <sawadakun@live.jp>
 * @author PzF_X <jp_minecraft@yahoo.co.jp>
 * @author Ikuo Obataya <i.obataya@gmail.com>
 */
$lang['server']                = 'LDAPサーバー<br>ホスト名（<code>localhost</code>）又は完全修飾URL（<code>ldap://server.tld:389</code>）';
$lang['port']                  = '上記が完全修飾URLでない場合のLDAPサーバーポート';
$lang['usertree']              = 'ユーザーアカウントを探す場所（例：<code>ou=People, dc=server, dc=tld</code>）';
$lang['grouptree']             = 'ユーザーグループを探す場所（例：<code>ou=Group, dc=server, dc=tld</code>）';
$lang['userfilter']            = 'ユーザーアカウントを探すためのLDAP抽出条件（例：<code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>）';
$lang['groupfilter']           = 'グループを探すLDAP抽出条件（例：<code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>）';
$lang['version']               = '使用するプロトコルのバージョン（場合によっては<code>3</code>を設定する必要があります。）';
$lang['starttls']              = 'TLS接続を使用する';
$lang['referrals']             = '紹介に従う';
$lang['deref']                 = 'どのように間接参照のエイリアスにするか';
$lang['binddn']                = '匿名バインドでは不十分な場合の、オプションバインドユーザーのDN（例：<code>cn=admin, dc=my, dc=home</code>）';
$lang['bindpw']                = '上記ユーザーのパスワード';
$lang['attributes']            = 'LDAP検索で取得する属性。';
$lang['userscope']             = 'ユーザー検索の範囲を限定させる';
$lang['groupscope']            = 'グループ検索の範囲を限定させる';
$lang['userkey']               = 'ユーザー名を示す属性（userfilter と一致している必要があります。）';
$lang['groupkey']              = 'ユーザー属性をグループのメンバーシップから設定する（標準のADグループの代わり。例：部署や電話番号など）';
$lang['modPass']               = 'DokuWiki から LDAP パスワードの変更が可能かどうか';
$lang['debug']                 = 'エラーに関して追加のデバッグ情報を表示する';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = 'デフォルトを使用する';
$lang['referrals_o_0']         = 'referral に従わない';
$lang['referrals_o_1']         = 'referral に従う';
