<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author HokkaidoPerson <dosankomali@yahoo.co.jp>
 * @author Satoshi Sahara <sahara.satoshi@gmail.com>
 * @author Hideaki SAWADA <chuno@live.jp>
 * @author PzF_X <jp_minecraft@yahoo.co.jp>
 */
$lang['account_suffix']        = 'アカウントの接尾語（例：<code>@my.domain.org</code>）';
$lang['base_dn']               = 'ベースDN（例：<code>DC=my,DC=domain,DC=org</code>）';
$lang['domain_controllers']    = 'ドメインコントローラのカンマ区切り一覧（例：<code>srv1.domain.org,srv2.domain.org</code>）';
$lang['admin_username']        = '全ユーザーデータへのアクセス権のある特権Active Directoryユーザー（任意ですが、メール通知の登録等の特定の動作に必要となります。）';
$lang['admin_password']        = '上記ユーザーのパスワード';
$lang['sso']                   = 'Kerberos か NTLM を使ったシングルサインオン（SSO）をしますか？';
$lang['sso_charset']           = 'サーバーは空のUTF-8かLatin-1でKerberosかNTLMユーザネームを送信します。iconv拡張モジュールが必要です。';
$lang['real_primarygroup']     = '"Domain Users" を仮定する代わりに本当のプライマリグループを解決する（低速）';
$lang['use_ssl']               = 'SSL接続を使用する（使用する場合、下のTLSを有効にしないでください。）';
$lang['use_tls']               = 'TLS接続を使用する（使用する場合、上のSSLを有効にしないでください。）';
$lang['debug']                 = 'エラー時に追加のデバッグ出力を表示する';
$lang['expirywarn']            = '何日前からパスワードの有効期限をユーザーに警告するか（0 の場合は無効）';
$lang['additional']            = 'ユーザデータから取得する追加AD属性のカンマ区切り一覧（一部プラグインが使用します。）';
$lang['update_name']           = 'ユーザー自身にAD表示名の変更を許可する';
$lang['update_mail']           = 'ユーザー自身にメールアドレスの変更を許可する';
$lang['recursive_groups']      = 'それぞれのメンバーについて入れ子のグループを解決する（動作が遅くなります）';
