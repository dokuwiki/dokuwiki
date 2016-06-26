<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author syaoranhinata@gmail.com
 */
$lang['server']                = '您的 LDAP 伺服器。填寫主機名稱 (<code>localhost</code>) 或完整的 URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP 伺服器端口 (若上方沒填寫完整的 URL)';
$lang['usertree']              = '到哪裏尋找使用者帳號？如： <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = '到哪裏尋找使用者群組？如： <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = '用於搜索使用者賬號的 LDAP 篩選器。如： <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = '用於搜索群組的 LDAP 篩選器。例如 <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = '使用的通訊協定版本。您可能要設置為 <code>3</code>';
$lang['starttls']              = '使用 TLS 連接嗎？';
$lang['referrals']             = '是否允許引用 (referrals)？';
$lang['binddn']                = '非必要綁定使用者 (optional bind user) 的 DN (匿名綁定不能滿足要求時使用)。如： <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = '上述使用者的密碼';
$lang['userscope']             = '限制使用者搜索的範圍';
$lang['groupscope']            = '限制群組搜索的範圍';
$lang['groupkey']              = '以其他使用者屬性 (而非標準 AD 群組) 來把使用者分組，例如以部門或電話號碼分類';
$lang['debug']                 = '有錯誤時，顯示額外除錯資訊';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
