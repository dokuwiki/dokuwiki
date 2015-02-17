<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Ivan I. Udovichenko (sendtome@mymailbox.pp.ua)
 * @author Aleksandr Selivanov <alexgearbox@gmail.com>
 * @author Erli Moen <evseev.jr@gmail.com>
 * @author Aleksandr Selivanov <alexgearbox@yandex.ru>
 * @author Владимир <id37736@yandex.ru>
 * @author Vitaly Filatenko <kot@hacktest.net>
 */
$lang['server']                = 'Ваш LDAP-сервер. Либо имя хоста (<code>localhost</code>), либо полный URL (<code>ldap://server.tld:389</code>)';
$lang['port']                  = 'Порт LDAP-сервера, если выше не был указан полный URL';
$lang['usertree']              = 'Где искать аккаунты пользователей? Например: <code>ou=People, dc=server, dc=tld</code>';
$lang['grouptree']             = 'Где искать группы пользователей? Например: <code>ou=Group, dc=server, dc=tld</code>';
$lang['userfilter']            = 'LDAP-фильтр для поиска аккаунтов пользователей. Например: <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['groupfilter']           = 'LDAP-фильтр для поиска групп. Например: <code>(&amp;(objectClass=posixGroup)(|(gidNumber=%{gid})(memberUID=%{user})))</code>';
$lang['version']               = 'Версия протокола. Возможно, вам нужно указать <code>3</code>';
$lang['starttls']              = 'Использовать TLS-подключения?';
$lang['deref']                 = 'Как расшифровывать псевдонимы?';
$lang['bindpw']                = 'Пароль для указанного пользователя';
$lang['userscope']             = 'Ограничить область поиска при поиске пользователей';
$lang['groupscope']            = 'Ограничить область поиска при поиске групп';
$lang['debug']                 = 'Показывать дополнительную отладочную информацию при ошибках';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['deref_o_3']             = 'LDAP_DEREF_ALWAYS';
