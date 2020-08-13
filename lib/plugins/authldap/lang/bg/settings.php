<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Kiril <neohidra@gmail.com>
 */
$lang['server']                = 'Вашият LDAP сървър. Име на хоста (<code>localhost</code>) или целият URL адрес (<code>ldap://сървър.tld:389</code>)';
$lang['port']                  = 'Порт на LDAP  сървъра, ако не сте въвели целия URL адрес по-горе';
$lang['usertree']              = 'Къде да се търси за потребителски акаунти. Например <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Къде да се търси за потребителски групи. Например <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP филтър за търсене на потребителски акаунти. Например <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP филтър за търсене на потребителски групи. Например <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Коя версия на протокола да се ползва? Вероятно ще се наложи да зададете <code>3</code>';
$lang['starttls']              = 'Ползване на TLS свързаност?';
$lang['referrals']             = 'Да бъдат ли следвани препратките (препращанията)?';
$lang['bindpw']                = 'Парола за горния потребител';
$lang['userscope']             = 'Ограничаване на обхвата за търсене на потребители';
$lang['groupscope']            = 'Ограничаване на обхвата за търсене на потребителски групи';
$lang['debug']                 = 'Показване на допълнителна debug информация при грешка';
