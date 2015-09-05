<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Ivan I. Udovichenko (sendtome@mymailbox.pp.ua)
 * @author Aleksandr Selivanov <alexgearbox@gmail.com>
 * @author Artur <ncuxxx@gmail.com>
 * @author Erli Moen <evseev.jr@gmail.com>
 * @author Владимир <id37736@yandex.ru>
 * @author Aleksandr Selivanov <alexgearbox@yandex.ru>
 * @author Type-kun <workwork-1@yandex.ru>
 * @author Vitaly Filatenko <kot@hacktest.net>
 */
$lang['account_suffix']        = 'Суффикс вашего аккаунта. Например, <code>@my.domain.org</code>';
$lang['base_dn']               = 'Ваш базовый DN. Например: <code>DC=my,DC=domain,DC=org</code>';
$lang['domain_controllers']    = 'Список DNS-серверов, разделённых запятой. Например:<code>srv1.domain.org,srv2.domain.org</code>';
$lang['admin_username']        = 'Привилегированный пользователь Active Directory с доступом ко всем остальным пользовательским данным. Необязательно, однако необходимо для определённых действий вроде отправки почтовой подписки.';
$lang['admin_password']        = 'Пароль для указанного пользователя.';
$lang['sso']                   = 'Использовать SSO (Single-Sign-On) через Kerberos или NTLM?';
$lang['sso_charset']           = 'Кодировка, в которой веб-сервер передаёт имя пользователя Kerberos или NTLM. Для UTF-8 или latin-1 остаётся пустым. Требует расширение iconv.';
$lang['real_primarygroup']     = 'Должна ли использоваться настоящая первичная группа вместо “Domain Users” (медленнее)';
$lang['use_ssl']               = 'Использовать SSL? Если да, то не включайте TLS.';
$lang['use_tls']               = 'Использовать TLS? Если да, то не включайте SSL.';
$lang['debug']                 = 'Выводить дополнительную информацию при ошибках?';
$lang['expirywarn']            = 'За сколько дней нужно предупреждать пользователя о необходимости изменить пароль? Для отключения укажите 0 (ноль).';
$lang['additional']            = 'Дополнительные AD-атрибуты, разделённые запятой, для выборки из данных пользователя. Используется некоторыми плагинами.';
