<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author syaoranhinata@gmail.com
 * @author June-Hao Hou <junehao@gmail.com>
 */
$lang['account_suffix']        = '您的帳號後綴。如： <code>@my.domain.org</code>';
$lang['base_dn']               = '您的基本識別名。如： <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = '以逗號分隔的域名控制器列表。如： <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Active Directory 的特權使用者，可以查看所有使用者的數據。(非必要，但對發送訂閱郵件等活動來說，這是必須的。)';
$lang['admin_password']        = '上述使用者的密碼。';
$lang['sso']                   = '是否使用 Kerberos 或 NTLM 的單一登入系統 (Single-Sign-On)？';
$lang['sso_charset']           = '你的網站伺服器傳遞 Kerberos 或 NTML 帳號名稱所用的語系編碼。空白表示 UTF-8 或 latin-1。此設定需要用到 iconv 套件。';
$lang['real_primarygroup']     = '是否視作真正的主要群組，而不是假設為網域使用者 (比較慢)';
$lang['use_ssl']               = '使用 SSL 連接嗎？如果要使用，請不要啟用下方的 TLS。';
$lang['use_tls']               = '使用 TLS 連接嗎？如果要使用，請不要啟用上方的 SSL。';
$lang['debug']                 = '有錯誤時，顯示額外除錯資訊嗎？';
$lang['expirywarn']            = '提前多少天警告使用者密碼即將到期。輸入0表示停用。';
$lang['additional']            = '從使用者數據中取得額外 AD 屬性列表，以供某些附加元件使用。列表以逗號分隔。';
