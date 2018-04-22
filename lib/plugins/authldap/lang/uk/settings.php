<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Oleksii <alexey.furashev@gmail.com>
 * @author Nina Zolotova <nina-z@i.ua>
 */
$lang['userfilter']            = 'Фільтр LDAP для відображення облікових записів. Щось на зразок <code>(&amp;(uid=%{user})(objectClass=posixAccount))</code>';
$lang['version']               = 'Використовувати версію протоколу. Можливо Вам доведеться вказати <code>3</code>.';
$lang['starttls']              = 'Використовуєте TLS з\'єднання?';
$lang['referrals']             = 'Слід підтримувати перепосилання?';
$lang['deref']                 = 'Як скинути псевдоніми?';
$lang['bindpw']                = 'Пароль вказаного користувача';
$lang['userscope']             = 'Обмежити область пошуку користувачів';
$lang['groupscope']            = 'Обмежити коло пошуку для групового запиту';
$lang['userkey']               = 'Атрибут, який визначає ім\'я користувача, має бути узгодженим із правилами користувацьких фільтрів.';
$lang['modPass']               = 'Можете змінити пароль в LDAP через DokuWiki?';
$lang['debug']                 = 'Показати додаткову інформацію про помилки';
$lang['deref_o_0']             = 'LDAP_DEREF_NEVER';
$lang['deref_o_1']             = 'LDAP_DEREF_SEARCHING';
$lang['deref_o_2']             = 'LDAP_DEREF_FINDING';
$lang['referrals_o_-1']        = 'Використовувати за замовчуванням';
