<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Yuriy Skalko <yuriy.skalko@gmail.com>
 * @author Zhassulan <zyesmukanov@gmail.com>
 * @author Denis Simakov <akinoame1@gmail.com>
 * @author Andrew Pleshakov <beotiger@mail.ru>
 * @author Змей Этерийский <evil_snake@eternion.ru>
 * @author Hikaru Nakajima <jisatsu@mail.ru>
 * @author Alexei Tereschenko <alexeitlex@yahoo.com>
 * @author Alexander Sorkin <kibizoid@gmail.com>
 * @author Kirill Krasnov <krasnovforum@gmail.com>
 * @author Vlad Tsybenko <vlad.development@gmail.com>
 * @author Aleksey Osadchiy <rfc@nm.ru>
 * @author Aleksandr Selivanov <alexgearbox@gmail.com>
 * @author Ladyko Andrey <fylh@succexy.spb.ru>
 * @author Eugene <windy.wanderer@gmail.com>
 * @author Johnny Utah <pcpa@cyberpunk.su>
 * @author RainbowSpike <1@2.ru>
 * @author alexey <xeenych@gmail.com>
 */
$lang['menu']                  = 'Настройки вики';
$lang['error']                 = 'Настройки не были сохранены из-за ошибки в одном из значений. Пожалуйста, проверьте свои изменения и попробуйте ещё раз.<br />Неправильные значения будут обведены красной рамкой.';
$lang['updated']               = 'Настройки успешно сохранены.';
$lang['nochoice']              = '(нет других вариантов)';
$lang['locked']                = 'Файл настройки недоступен для изменения. Если это не специально, <br />убедитесь, что файл локальной настройки имеет правильное имя и права доступа.';
$lang['danger']                = 'Внимание: изменение этой опции может сделать вашу вики и меню конфигурации недоступными.';
$lang['warning']               = 'Предостережение: изменение этой опции может вызвать непредсказуемое поведение.';
$lang['security']              = 'Предостережение по безопасности: изменение этой опции может вызвать риск, связанный с безопасностью.';
$lang['_configuration_manager'] = 'Настройки вики';
$lang['_header_dokuwiki']      = 'Параметры «Докувики»';
$lang['_header_plugin']        = 'Параметры плагинов';
$lang['_header_template']      = 'Параметры шаблонов';
$lang['_header_undefined']     = 'Прочие параметры';
$lang['_basic']                = 'Основные параметры';
$lang['_display']              = 'Параметры отображения';
$lang['_authentication']       = 'Параметры аутентификации';
$lang['_anti_spam']            = 'Параметры блокировки спама';
$lang['_editing']              = 'Параметры правки';
$lang['_links']                = 'Параметры ссылок';
$lang['_media']                = 'Параметры медиафайлов';
$lang['_notifications']        = 'Параметры уведомлений';
$lang['_syndication']          = 'Параметры синдикаций (RSS)';
$lang['_advanced']             = 'Тонкая настройка';
$lang['_network']              = 'Параметры сети';
$lang['_msg_setting_undefined'] = 'Не найдены метаданные настроек.';
$lang['_msg_setting_no_class'] = 'Не найден класс настроек.';
$lang['_msg_setting_no_default'] = 'Не задано значение по умолчанию.';
$lang['title']                 = 'Название вики';
$lang['start']                 = 'Имя стартовой страницы';
$lang['lang']                  = 'Язык';
$lang['template']              = 'Шаблон';
$lang['tagline']               = 'Слоган (если поддерживается шаблоном)';
$lang['sidebar']               = 'Боковая панель; пустое поле отключает боковую панель.';
$lang['license']               = 'На условиях какой лицензии будет предоставляться содержимое вики?';
$lang['savedir']               = 'Директория для данных';
$lang['basedir']               = 'Корневая директория (например, <code>/dokuwiki/</code>). Оставьте пустым для автоопределения.';
$lang['baseurl']               = 'Корневой адрес (URL) (например, <code>http://www.yourserver.ru</code>). Оставьте пустым для автоопределения.';
$lang['cookiedir']             = 'Директория для cookie. Оставьте пустым для автоопределения.';
$lang['dmode']                 = 'Права для создаваемых директорий';
$lang['fmode']                 = 'Права для создаваемых файлов';
$lang['allowdebug']            = 'Включить отладку. <b>Отключите, если она вам не нужна!</b>';
$lang['recent']                = 'Недавние изменения (кол-во)';
$lang['recent_days']           = 'На сколько дней назад сохранять недавние изменения';
$lang['breadcrumbs']           = 'Вы посетили (кол-во). Поставьте 0 (ноль) для отключения.';
$lang['youarehere']            = 'Показывать «Вы находитесь здесь»';
$lang['fullpath']              = 'Полный путь к документу';
$lang['typography']            = 'Выполнять замену на типографские символы';
$lang['dformat']               = 'Формат даты и времени (см. функцию PHP <a href="http://php.net/strftime">strftime</a>)';
$lang['signature']             = 'Шаблон подписи';
$lang['showuseras']            = 'Что отображать при показе пользователя, редактировавшего страницу последним';
$lang['toptoclevel']           = 'Мин. уровень в содержании';
$lang['tocminheads']           = 'Мин. количество заголовков, при котором будет составлено содержание';
$lang['maxtoclevel']           = 'Макс. уровень в содержании';
$lang['maxseclevel']           = 'Макс. уровень для правки';
$lang['camelcase']             = 'Использовать ВикиРегистр для ссылок';
$lang['deaccent']              = 'Транслитерация в именах страниц';
$lang['useheading']            = 'Первый заголовок вместо имени страницы';
$lang['sneaky_index']          = 'По умолчанию, «Докувики» показывает в индексе страниц все пространства имён. Включение этой опции скроет пространства имён, для которых пользователь не имеет прав чтения. Это может привести к скрытию доступных вложенных пространств имён и потере функциональности индекса страниц при некоторых конфигурациях прав доступа.';
$lang['hidepages']             = 'Скрыть страницы (регулярное выражение)';
$lang['useacl']                = 'Использовать списки прав доступа';
$lang['autopasswd']            = 'Автогенерация паролей';
$lang['authtype']              = 'Механизм аутентификации';
$lang['passcrypt']             = 'Метод шифрования пароля';
$lang['defaultgroup']          = 'Группа по умолчанию. Все новые пользователю будут добавляться в эту группу.';
$lang['superuser']             = 'Суперпользователь — группа или пользователь с полным доступом ко всем страницам и функциям администрирования, независимо от установок списков прав доступа. Перечень разделяйте запятыми: user1,@group1,user2';
$lang['manager']               = 'Менеджер — группа или пользователь с доступом к определённым функциям управления. Перечень разделяйте запятыми: user1,@group1,user2';
$lang['profileconfirm']        = 'Пароль для изменения профиля';
$lang['rememberme']            = 'Разрешить перманентные куки (cookies) для входа («запомнить меня»)';
$lang['disableactions']        = 'Заблокировать операции «Докувики»';
$lang['disableactions_check']  = 'Проверка';
$lang['disableactions_subscription'] = 'Подписка/Отмена подписки';
$lang['disableactions_wikicode'] = 'Показ/экспорт исходного текста';
$lang['disableactions_profile_delete'] = 'Удалить свой аккаунт';
$lang['disableactions_other']  = 'Другие операции (через запятую)';
$lang['disableactions_rss']    = 'XML-синдикация (RSS)';
$lang['auth_security_timeout'] = 'Интервал для безопасности авторизации (сек.)';
$lang['securecookie']          = 'Должны ли куки (cookies), выставленные через HTTPS, отправляться браузером только через HTTPS. Отключите эту опцию в случае, когда только логин вашей вики передаётся через SSL, а обычный просмотр осуществляется в небезопасном режиме.';
$lang['remote']                = 'Включить систему API для подключений. Это позволит другим приложениям получить доступ к вики через XML-RPC или другие механизмы.';
$lang['remoteuser']            = 'Дать права для удалённого API-доступа пользователям, указанным здесь (разделяйте запятыми). Оставьте поле пустым для предоставления доступа всем.';
$lang['usewordblock']          = 'Блокировать спам по ключевым словам';
$lang['relnofollow']           = 'Использовать rel="nofollow" для внешних ссылок';
$lang['indexdelay']            = 'Задержка перед индексированием (сек.)';
$lang['mailguard']             = 'Кодировать адреса электронной почты';
$lang['iexssprotect']          = 'Проверять закачанные файлы на наличие потенциально опасного кода JavaScript или HTML';
$lang['usedraft']              = 'Автоматически сохранять черновик во время правки';
$lang['htmlok']                = 'Разрешить HTML';
$lang['phpok']                 = 'Разрешить PHP';
$lang['locktime']              = 'Время блокировки страницы (сек.)';
$lang['cachetime']             = 'Время жизни кэш-файла (сек.)';
$lang['target____wiki']        = 'target для внутренних ссылок';
$lang['target____interwiki']   = 'target для ссылок между вики';
$lang['target____extern']      = 'target для внешних ссылок';
$lang['target____media']       = 'target для ссылок на медиафайлы';
$lang['target____windows']     = 'target для ссылок на сетевые каталоги';
$lang['mediarevisions']        = 'Включение версий медиафайлов';
$lang['refcheck']              = 'Проверять ссылки на медиафайлы';
$lang['gdlib']                 = 'Версия LibGD';
$lang['im_convert']            = 'Путь к ImageMagick';
$lang['jpg_quality']           = 'Качество сжатия JPG (0–100). Значение по умолчанию — 70.';
$lang['fetchsize']             = 'Максимальный размер файла (в байтах), который fetch.php может скачивать с внешнего источника';
$lang['subscribers']           = 'Разрешить подписку на изменения';
$lang['subscribe_time']        = 'Интервал рассылки подписок и сводок (сек.). Должен быть меньше, чем значение, указанное в recent_days.';
$lang['notify']                = 'Всегда отправлять сообщения об изменениях на этот электронный адрес';
$lang['registernotify']        = 'Всегода отправлять информацию о новых зарегистрированных пользователях на этот электронный адрес';
$lang['mailfrom']              = 'Электронный адрес вики (От:)';
$lang['mailreturnpath']        = 'Адрес электронной почты для доставки отчета о недоставленных сообщений';
$lang['mailprefix']            = 'Префикс, используемый для автоматического письма, станет темой сообщения. Оставьте поле пустым для использования названия вики.';
$lang['htmlmail']              = 'Отправлять красивые, но более крупные HTML-письма. Отключите для отправки простых текстовых писем.';
$lang['sitemap']               = 'Число дней, через которое нужно создавать (обновлять) карту сайта для поисковиков (Гугл, Яндекс и др.). Укажите 0 (ноль) для отключения.';
$lang['rss_type']              = 'Тип XML-ленты';
$lang['rss_linkto']            = 'Ссылки в XML-ленте указывают на';
$lang['rss_content']           = 'Что показывать в XML-ленте?';
$lang['rss_update']            = 'Интервал обновления XML-ленты (сек.)';
$lang['rss_show_summary']      = 'Показывать краткую выдержку в заголовках XML-ленты';
$lang['rss_media']             = 'Какие изменения должны быть отображены в XML-ленте?';
$lang['rss_media_o_both']      = 'оба';
$lang['rss_media_o_pages']     = 'страницы';
$lang['rss_media_o_media']     = 'медиа';
$lang['updatecheck']           = 'Проверять наличие обновлений и предупреждений о безопасности? Для этого «Докувики» потребуется связываться с update.dokuwiki.org.';
$lang['userewrite']            = 'Удобочитаемые адреса (URL)';
$lang['useslash']              = 'Использовать слэш разделителем пространств имён в URL';
$lang['sepchar']               = 'Разделитель слов в имени страницы';
$lang['canonical']             = 'Полные канонические адреса (URL)';
$lang['fnencode']              = 'Метод кодирования имён файлов, записанных не ASCII-символами.';
$lang['autoplural']            = 'Проверять можественную форму имени страницы в ссылках';
$lang['compression']           = 'Метод сжатия для архивных файлов';
$lang['gzip_output']           = 'Использовать gzip-сжатие для xhtml (Content-Encoding)';
$lang['compress']              = 'Сжимать файлы CSS и javascript';
$lang['cssdatauri']            = 'Размер в байтах, до которого изображения, указанные в CSS-файлах, должны быть встроены прямо в таблицу стилей, для уменьшения избыточных HTTP-запросов. Этот метод не будет работать в IE версии 7 и ниже! Установка от <code>400</code> до <code>600</code> байт является хорошим показателем. Установите <code>0</code>, чтобы отключить.';
$lang['send404']               = 'Посылать «HTTP 404/Страница не найдена» для несуществующих страниц';
$lang['broken_iua']            = 'Возможно, функция ignore_user_abort не работает в вашей системе? Это может привести к потере функциональности индексирования поиска. Эта проблема присутствует, например, в IIS+PHP/CGI. Для дополнительной информации смотрите <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">баг 852</a>.';
$lang['xsendfile']             = 'Используете заголовок X-Sendfile для загрузки файлов на веб-сервер? Ваш веб-сервер должен поддерживать это.';
$lang['renderer_xhtml']        = 'Обработчик основного (xhtml) вывода вики';
$lang['renderer__core']        = '%s (ядро «Докувики»)';
$lang['renderer__plugin']      = '%s (плагин)';
$lang['search_fragment_o_exact'] = 'точно';
$lang['search_fragment_o_starts_with'] = 'начинается на';
$lang['search_fragment_o_ends_with'] = 'заканчивается';
$lang['search_fragment_o_contains'] = 'содержит';
$lang['dnslookups']            = '«Докувики» ищет DNS-имена пользователей, редактирующих страницы. Если у вас нет DNS-сервера или он работает медленно, рекомендуем отключить эту опцию.';
$lang['jquerycdn']             = 'Требуется ли загрузка jQuery-скриптов (включая jQuery UI-скрипты) из CDN сети? Данная функция увеличивает количество HTTP запросов, но загрузка файлов будет проходить быстрее и пользователи смогут кэшировать запросы.';
$lang['jquerycdn_o_0']         = 'Не использовать CDN. Использовать только локальную доставку';
$lang['jquerycdn_o_jquery']    = 'Использовать CDN с code.jquery.com';
$lang['jquerycdn_o_cdnjs']     = 'Использовать CDN с cdnjs.com';
$lang['proxy____host']         = 'proxy-адрес';
$lang['proxy____port']         = 'proxy-порт';
$lang['proxy____user']         = 'proxy-имя пользователя';
$lang['proxy____pass']         = 'proxy-пароль';
$lang['proxy____ssl']          = 'Использовать SSL для соединения с прокси';
$lang['proxy____except']       = 'Регулярное выражение для адресов (URL), для которых прокси должен быть пропущен.';
$lang['safemodehack']          = 'Включить обход safemode (хак)';
$lang['ftp____host']           = 'ftp-адрес';
$lang['ftp____port']           = 'ftp-порт';
$lang['ftp____user']           = 'ftp-имя пользователя';
$lang['ftp____pass']           = 'ftp-пароль';
$lang['ftp____root']           = 'ftp-корневая директория';
$lang['license_o_']            = 'Не выбрано';
$lang['typography_o_0']        = 'нет';
$lang['typography_o_1']        = 'только двойные кавычки';
$lang['typography_o_2']        = 'все кавычки (может не всегда работать)';
$lang['userewrite_o_0']        = '(нет)';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'средствами «Докувики»';
$lang['deaccent_o_0']          = 'отключить';
$lang['deaccent_o_1']          = 'убирать только диакр. знаки';
$lang['deaccent_o_2']          = 'полная транслитерация';
$lang['gdlib_o_0']             = 'GD Lib недоступна';
$lang['gdlib_o_1']             = 'версия 1.x';
$lang['gdlib_o_2']             = 'автоопределение';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'абстрактный';
$lang['rss_content_o_diff']    = 'объединённый diff';
$lang['rss_content_o_htmldiff'] = 'HTML-форматированная таблица diff';
$lang['rss_content_o_html']    = 'полное содержимое HTML-страницы';
$lang['rss_linkto_o_diff']     = 'отличия от текущей';
$lang['rss_linkto_o_page']     = 'текст страницы';
$lang['rss_linkto_o_rev']      = 'история правок';
$lang['rss_linkto_o_current']  = 'текущая версия';
$lang['compression_o_0']       = 'без сжатия';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'не используется';
$lang['xsendfile_o_1']         = 'Проприетарный lighttpd-заголовок (до релиза 1.5)';
$lang['xsendfile_o_2']         = 'Стандартный заголовок X-Sendfile';
$lang['xsendfile_o_3']         = 'Проприетарный заголовок Nginx X-Accel-Redirect';
$lang['showuseras_o_loginname'] = 'логин';
$lang['showuseras_o_username'] = 'полное имя пользователя';
$lang['showuseras_o_username_link'] = 'полное имя пользователя как интервики-ссылка';
$lang['showuseras_o_email']    = 'адрес эл. почты в шифрованном виде (см. mailguard)';
$lang['showuseras_o_email_link'] = 'адрес эл. почты в виде ссылки mailto:';
$lang['useheading_o_0']        = 'никогда';
$lang['useheading_o_navigation'] = 'только в навигации';
$lang['useheading_o_content']  = 'только в содержимом вики';
$lang['useheading_o_1']        = 'всегда';
$lang['readdircache']          = 'Максимальное время жизни кэша readdir (сек.)';
