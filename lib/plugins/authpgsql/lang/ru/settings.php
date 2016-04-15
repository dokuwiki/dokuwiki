<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Ivan I. Udovichenko (sendtome@mymailbox.pp.ua)
 * @author Aleksandr Selivanov <alexgearbox@gmail.com>
 * @author Aleksandr Selivanov <alexgearbox@yandex.ru>
 * @author Vitaly Filatenko <kot@hacktest.net>
 * @author Type-kun <workwork-1@yandex.ru>
 * @author Alex P <alexander@lanos.co.uk>
 */
$lang['server']                = 'Ваш PostgreSQL-сервер';
$lang['port']                  = 'Порт вашего PostgreSQL-сервера';
$lang['user']                  = 'Имя пользователя PostgreSQL';
$lang['password']              = 'Пароль для указанного пользователя';
$lang['database']              = 'Имя базы данных';
$lang['debug']                 = 'Отображать дополнительную отладочную информацию';
$lang['forwardClearPass']      = 'Передать чистым текстом ползовательские пароли в SQL запросы ниже, вместо использование опции passcrypt';
$lang['checkPass']             = 'Выражение SQL, осуществляющее проверку пароля';
$lang['getUserInfo']           = 'Выражение SQL, осуществляющее извлечение информации о пользователе';
$lang['getGroups']             = 'Выражение SQL, осуществляющее извлечение информации о членстве пользователе в группах';
$lang['getUsers']              = 'Выражение SQL, осуществляющее извлечение полного списка пользователей';
$lang['FilterLogin']           = 'Выражение SQL, осуществляющее фильтрацию пользователей по логину';
$lang['FilterName']            = 'Выражение SQL, осуществляющее фильтрацию пользователей по полному имени';
$lang['FilterEmail']           = 'Выражение SQL, осуществляющее фильтрацию пользователей по адресу электронной почты';
$lang['FilterGroup']           = 'Выражение SQL, осуществляющее фильтрацию пользователей согласно членству в группе';
$lang['SortOrder']             = 'Выражение SQL, осуществляющее сортировку пользователей';
$lang['addUser']               = 'Выражение SQL, осуществляющее добавление нового пользователя';
$lang['addGroup']              = 'Выражение SQL, осуществляющее добавление новой группы';
$lang['addUserGroup']          = 'Выражение SQL, осуществляющее добавление пользователя в существующую группу';
$lang['delGroup']              = 'Выражение SQL, осуществляющее удаление группы';
$lang['getUserID']             = 'Выражение SQL, обеспечивающее получение первичного ключа пользователя';
$lang['delUser']               = 'Выражение SQL, осуществляющее удаление пользователя';
$lang['delUserRefs']           = 'Выражение SQL, осуществляющее удаление пользователя из всех группы';
$lang['updateUser']            = 'Выражение SQL, осуществляющее обновление профиля пользователя';
$lang['UpdateLogin']           = 'Измените условие для обновления логина';
$lang['UpdatePass']            = 'Измените условие для обновления пароля';
$lang['UpdateEmail']           = 'Измените условие для обновления email';
$lang['UpdateName']            = 'Условие для обновления полного имени пользователя';
$lang['UpdateTarget']          = 'Выражение \'LIMIT\' для идентификации пользователя при обновлении';
$lang['delUserGroup']          = 'Выражение SQL, осуществляющее удаление пользователя из указанной группы';
$lang['getGroupID']            = 'Выражение SQL, обеспечивающее получение первичного ключа указанной группы';
