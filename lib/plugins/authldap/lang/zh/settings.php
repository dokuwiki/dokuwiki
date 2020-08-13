<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author FENG.JIE <ahx@qq.com>
 * @author lainme <lainme993@gmail.com>
 * @author oott123 <ip.192.168.1.1@qq.com>
 * @author Errol <errol@hotmail.com>
 * @author phy25 <git@phy25.com>
 */
$lang['server']                = '您的 LDAP 服务器。填写主机名 (<code>localhost</code>) 或者完整的 URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'LDAP 服务器端口 (如果上面没有给出完整的 URL)';
$lang['usertree']              = '在何处查找用户账户。例如 <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = '在何处查找用户组。例如 <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = '用于搜索用户账户的 LDAP 筛选器。例如 <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = '用于搜索组的 LDAP 筛选器。例如 <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = '使用的协议版本。您或许需要设置为 <code>3</code>';
$lang['starttls']              = '使用 TLS 连接？';
$lang['referrals']             = '是否允许引用 (referrals)？';
$lang['deref']                 = '如何间接引用别名？';
$lang['binddn']                = '一个可选的绑定用户的 DN (如果匿名绑定不满足要求)。例如 <code>cn=admin, dc=my, dc=home</code>';
$lang['bindpw']                = '上述用户的密码';
$lang['attributes']            = '使用LDAP搜索属性。';
$lang['userscope']             = '限制用户搜索的范围';
$lang['groupscope']            = '限制组搜索的范围';
$lang['userkey']               = '表示用户名的属性；必须和用户过滤器保持一致。';
$lang['groupkey']              = '根据任何用户属性得来的组成员(而不是标准的 AD 组)，例如根据部门或者电话号码得到的组。';
$lang['modPass']               = ' LDAP密码可以通过 DokuWiki 修改吗？';
$lang['debug']                 = '有错误时显示额外的调试信息';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
$lang['referrals_o_-1']        = '默认';
$lang['referrals_o_0']         = '不要跟随参照(referral)';
$lang['referrals_o_1']         = '跟随参照(referral)';
