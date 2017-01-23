<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author lainme <lainme993@gmail.com>
 * @author oott123 <ip.192.168.1.1@qq.com>
 * @author JellyChen <451453325@qq.com>
 * @author 高博 <bobnemo1983@gmail.com>
 */
$lang['account_suffix']        = '您的账户后缀。例如 <code>@my.domain.org</code>';
$lang['base_dn']               = '您的基本分辨名。例如 <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = '逗号分隔的域名控制器列表。例如 <code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = '一个活动目录的特权用户，可以查看其他所有用户的数据。可选，但对某些活动例如发送订阅邮件是必须的。';
$lang['admin_password']        = '上述用户的密码。';
$lang['sso']                   = '是否使用经由 Kerberos 和 NTLM 的 Single-Sign-On？';
$lang['sso_charset']           = '服务器传入 Kerberos 或者 NTLM 用户名的编码。留空为 UTF-8 或 latin-1 。此功能需要服务器支持iconv扩展。';
$lang['real_primarygroup']     = ' 是否解析真实的主要组，而不是假设为“域用户” (较慢)';
$lang['use_ssl']               = '使用 SSL 连接？如果是，不要激活下面的 TLS。';
$lang['use_tls']               = '使用 TLS 连接？如果是 ，不要激活上面的 SSL。';
$lang['debug']                 = '有错误时显示额外的调试信息？';
$lang['expirywarn']            = '提前多少天警告用户密码即将到期。0 则禁用。';
$lang['additional']            = '需要从用户数据中获取的额外 AD 属性的列表，以逗号分隔。用于某些插件。';
$lang['update_name']           = '允许用户更新其AD显示名称？';
$lang['update_mail']           = '是否允许用户更新他们的电子邮件地址？';
