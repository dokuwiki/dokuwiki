<?php
/**
 * Russian language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Denis Simakov <akinoame1@gmail.com>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Настройки Вики'; 

$lang['error']      = 'Настройки не были сохранены из-за ошибки в одном из значений. Пожалуйста, проверьте ваши изменения и попробуйте еще раз.
                       <br />Неправильные значения будут обведены красной рамкой.';
$lang['updated']    = 'Настройки успешно сохранены.';
$lang['nochoice']   = '(нет других вариантов)';
$lang['locked']     = 'Файл настройки недоступен для изменения. Если это не специально, <br />
                       убедитесь, что файл локальной настройки имеет правильное имя и права доступа.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Настройки Вики'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'Параметры DokuWiki';
$lang['_header_plugin'] = 'Параметры плагинов';
$lang['_header_template'] = 'Параметры шаблонов';
$lang['_header_undefined'] = 'Прочие параметры';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Основные параметры';
$lang['_display'] = 'Параметры отображения';
$lang['_authentication'] = 'Параметры аутентификации';
$lang['_anti_spam'] = 'Параметры блокировки спама';
$lang['_editing'] = 'Параметры правки';
$lang['_links'] = 'Параметры ссылок';
$lang['_media'] = 'Параметры медиа-файлов';
$lang['_advanced'] = 'Тонкая настройка';
$lang['_network'] = 'Параметры сети';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'Параметры плагина';
$lang['_template_sufix'] = 'Параметры шаблона';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Не найдены метаданные настроек.';
$lang['_msg_setting_no_class'] = 'Не найден класс настроек.';
$lang['_msg_setting_no_default'] = 'Не задано значение по умолчанию.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Права для создаваемых файлов';         //directory mask accordingly
$lang['dmode']       = 'Права для создаваемых директорий';    //directory mask accordingly
$lang['lang']        = 'Язык';           //your language
$lang['basedir']     = 'Корневая директория';     //absolute dir from serveroot - blank for autodetection
$lang['baseurl']     = 'Корневой адрес (URL)';           //URL to server including protocol - blank for autodetect
$lang['savedir']     = 'Директория для данных';     //where to store all the files
$lang['start']       = 'Имя стартовой страницы';    //name of start page
$lang['title']       = 'Название Вики';         //what to show in the title
$lang['template']    = 'Шаблон';           //see tpl directory
$lang['fullpath']    = 'Полный путь к документу';      //show full path of the document or relative to datadir only? 0|1
$lang['recent']      = 'Недавние изменения (кол-во)';     //how many entries to show in recent
$lang['breadcrumbs'] = 'Вы посетили (кол-во)';        //how many recent visited pages to show
$lang['youarehere'] = 'Показывать "Вы находитесь здесь"';     //show "You are here" navigation? 0|1
$lang['typography']  = 'Типографские символы';         //convert quotes, dashes and stuff to typographic equivalents? 0|1
$lang['htmlok']      = 'Разрешить HTML';//may raw HTML be embedded? This may break layout and XHTML validity 0|1
$lang['phpok']       = 'Разрешить PHP'; //may PHP code be embedded? Never do this on the internet! 0|1
$lang['dformat']     = 'Формат даты и времени';        //dateformat accepted by PHPs date() function
$lang['signature']   = 'Шаблон подписи';          //signature see wiki:langig for details
$lang['toptoclevel'] = 'Мин. уровень в содержании';      //Level starting with and below to include in AutoTOC (max. 5)
$lang['maxtoclevel'] = 'Макс. уровень в содержании';      //Up to which level include into AutoTOC (max. 5)
$lang['maxseclevel'] = 'Макс. уровень для правки';   //Up to which level create editable sections (max. 5)
$lang['camelcase']   = 'Использовать ВикиРегистр для ссылок';  //Use CamelCase for linking? (I don't like it) 0|1
$lang['deaccent']    = 'Транслитерация в именах страниц';    //convert accented chars to unaccented ones in pagenames?
$lang['useheading']  = 'Первый заголовок вместо имени';        //use the first heading in a page as its name
$lang['refcheck']    = 'Проверять ссылки на медиа-файлы';    //check for references before deleting media files
$lang['refshow']     = 'Показывать ссылок на медиа-файлы'; //how many references should be shown, 5 is a good value
$lang['allowdebug']  = 'Включить отладку (отключите!)';   //make debug possible, disable after install! 0|1

$lang['usewordblock']= 'Блокировать спам по ключевым словам';  //block spam based on words? 0|1
$lang['indexdelay']  = 'Задержка перед индексированием'; //allow indexing after this time (seconds) default is 5 days
$lang['relnofollow'] = 'rel="nofollow" для внешних ссылок';         //use rel="nofollow" for external links?
$lang['mailguard']   = 'Кодировать адреса е-мэйл';  //obfuscate email addresses against spam harvesters?
$lang['iexssprotect']= 'Проверять закачанные файлы на наличие потенциально опасного кода JavaScript или HTML';

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */
$lang['useacl']      = 'Использовать списки прав доступа';                //Use Access Control Lists to restrict access?
$lang['openregister']= 'Открытая регистрация';          //Should users to be allowed to register?
$lang['autopasswd']  = 'Автогенерация паролей'; //autogenerate passwords and email them to user
$lang['resendpasswd']= 'Разрешить напоминание пароля';  //allow resend password function?
$lang['authtype']    = 'Механизм аутентификации'; //which authentication backend should be used
$lang['passcrypt']   = 'Метод шифрования пароля';    //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$lang['defaultgroup']= 'Группа по умолчанию';          //Default groups new Users are added to
$lang['superuser']   = 'Администратор';              //The admin can be user or @group
$lang['manager']     = 'Менеджер - группа или пользователь с доступом к определенным функциям администрирования';
$lang['profileconfirm'] = 'Пароль для изменения профиля';     //Require current password to langirm changes to user profile
$lang['disableactions'] = 'Заблокировать операции DokuWiki';
$lang['disableactions_check'] = 'Проверка';
$lang['disableactions_subscription'] = 'Подписка/Отмена подписки';
$lang['disableactions_wikicode'] = 'Показ/экспорт исходного текста';
$lang['disableactions_other'] = 'Другие операции (через запятую)';
$lang['sneaky_index'] = 'По умолчанию, DokuWiki показывает в индексе страниц все пространства имен. Включение этой опции скроет пространства имен, для которых пользователь не имеет прав чтения. Это может привести к скрытию доступных вложенных пространств имен и потере функциональности индекса страниц при некоторых конфигурациях прав доступа.';
$lang['auth_security_timeout'] = 'Интервал для безопасности авторизации (сек.)';

/* Advanced Options */
$lang['updatecheck'] = 'Проверять наличие обновлений и предупреждений о безопасности? Для этого DokuWiki потребуется связываться со splitbrain.org.';
$lang['userewrite']  = 'Удобочитаемые адреса (URL)';             //this makes nice URLs: 0: off 1: .htaccess 2: internal
$lang['useslash']    = 'Использовать слэш';                 //use slash instead of colon? only when rewrite is on
$lang['usedraft']    = 'Автоматически сохранять черновик в время правки';
$lang['sepchar']     = 'Разделитель слов в имени страницы';  //word separator character in page names; may be a
$lang['canonical']   = 'Полные канонические адреса (URL)';  //Should all URLs use full canonical http://... style?
$lang['autoplural']  = 'Автоматическое мн. число';               //try (non)plural form of nonexisting files?
$lang['compression'] = 'Метод сжатия для архивных файлов';
$lang['cachetime']   = 'Время жизни кэш-файла (сек.)';  //maximum age for cachefile in seconds (defaults to a day)
$lang['locktime']    = 'Время блокировки страницы (сек.)';  //maximum age for lockfiles (defaults to 15 minutes)
$lang['fetchsize']   = 'Максимальный размер файла (в байтах) который fetch.php может скачивать с внешнего источника';
$lang['notify']      = 'Е-мэйл для извещений';      //send change info to this email (leave blank for nobody)
$lang['registernotify'] = 'Посылать информацию о новых зарегистрированных пользователях на этот адрес е-мэйл';
$lang['mailfrom']    = 'Е-мэйл Вики (От:)';            //use this email when sending mails
$lang['gzip_output'] = 'Использовать gzip Content-Encoding для xhtml';
$lang['gdlib']       = 'Версия GD Lib';              //the GDlib version (0, 1 or 2) 2 tries to autodetect
$lang['im_convert']  = 'Путь к imagemagick';            //path to ImageMagicks convert (will be used instead of GD)
$lang['jpg_quality'] = 'Качество сжатия JPG (0-100)';
$lang['spellchecker']= 'Включить проверку орфографии';         //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$lang['subscribers'] = 'Разрешить подписку на изменения'; //enable change notice subscription support
$lang['compress']    = 'Сжимать файлы CSS и javascript';  //Strip whitespaces and comments from Styles and JavaScript? 1|0
$lang['hidepages']   = 'Скрыть страницы (рег. выражение)';      //Regexp for pages to be skipped from RSS, Search and Recent Changes
$lang['send404']     = 'Посылать "HTTP404/Page Not Found"';    //Send a HTTP 404 status for non existing pages?
$lang['sitemap']     = 'Карта сайта для Google (дни)';   //Create a google sitemap? How often? In days.
$lang['broken_iua']  = 'Возможно, функция ignore_user_abort не работает в вашей системе? Это может привести к потере функциональности индексирования поиска. Эта проблема присутствует, например, в IIS+PHP/CGI. Для дополнительной информации смотрите <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">баг 852</a>.';

$lang['rss_type']    = 'Тип RSS';             //type of RSS feed to provide, by default:
$lang['rss_linkto']  = 'Ссылки в RSS';              //what page RSS entries link to:
$lang['rss_update']  = 'Интервал обновления XML-ленты (сек.)';
$lang['recent_days'] = 'На сколько дней назад сохранять недавние изменения';
$lang['rss_show_summary'] = 'Показывать краткую выдержку в заголовках XML-ленты';

//Set target to use when creating links - leave empty for same window
$lang['target____wiki']      = 'target для внутренних ссылок';
$lang['target____interwiki'] = 'target для ссылок между Вики';
$lang['target____extern']    = 'target для внешних ссылок';
$lang['target____media']     = 'target для ссылок на медиа-файлы';
$lang['target____windows']   = 'target для ссылок на сетевые каталоги';

//Proxy setup - if your Server needs a proxy to access the web set these
$lang['proxy____host'] = 'proxy - адрес';
$lang['proxy____port'] = 'proxy - порт';
$lang['proxy____user'] = 'proxy - имя пользователя';
$lang['proxy____pass'] = 'proxy - пароль';
$lang['proxy____ssl']  = 'proxy - ssl';

/* Safemode Hack */
$lang['safemodehack'] = 'Включить обход safemode';  //read http://wiki.splitbrain.org/wiki:safemodehack !
$lang['ftp____host'] = 'ftp - адрес';
$lang['ftp____port'] = 'ftp - порт';
$lang['ftp____user'] = 'ftp - имя пользователя';
$lang['ftp____pass'] = 'ftp - пароль';
$lang['ftp____root'] = 'ftp - корневая директория';

/* userewrite options */
$lang['userewrite_o_0'] = '(нет)';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'средствами DokuWiki';

/* deaccent options */
$lang['deaccent_o_0'] = 'отключить';
$lang['deaccent_o_1'] = 'убирать только диакр. знаки';
$lang['deaccent_o_2'] = 'полная транслитерация';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib недоступна';
$lang['gdlib_o_1'] = 'версия 1.x';
$lang['gdlib_o_2'] = 'автоопределение';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'отличия от текущей';
$lang['rss_linkto_o_page']    = 'текст страницы';
$lang['rss_linkto_o_rev']     = 'история правок';
$lang['rss_linkto_o_current'] = 'текущая версия';

/* compression options */
$lang['compression_o_0']   = 'без сжатия';
$lang['compression_o_gz']  = 'gzip';
$lang['compression_o_bz2'] = 'bz2';

