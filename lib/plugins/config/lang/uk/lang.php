<?php
/**
 * ukrainian language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Oleksiy Voronin <ovoronin@gmail.com>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Налагодження конфігурації';

$lang['error']      = 'Параметри не збережені через помилкові значення. Будь ласка, перегляньте ваші зміни та спробуйте ще раз
		      <br />Помилкові значення будуть виділені червоною рамкою.'; 
$lang['updated']    = 'Параметри успішно збережені.';
$lang['nochoice']   = '(інших варіантів не існує)';
$lang['locked']     = 'Неможливо записати файл параметрів налагодження, якщо це не спеціально <br />
                      переконайтеся, що ім\'я та права доступа для локального файла налагодження вірні';


/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Управління конфігурацією'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'Настройки ДокуВікі';
$lang['_header_plugin'] = 'Настройки Доданків';
$lang['_header_template'] = 'Настройки шаблонів';
$lang['_header_undefined'] = 'Невизначені настройки';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Базові настройки';
$lang['_display'] = 'Настройки дісплея';
$lang['_authentication'] = 'Настройки автентифікації';
$lang['_anti_spam'] = 'Настройки Антиспама';
$lang['_editing'] = 'Настройки редагування';
$lang['_links'] = 'Настройки посилань';
$lang['_media'] = 'Натройки медіа';
$lang['_advanced'] = 'Продвинуті настройки';
$lang['_network'] = 'Настройки мережі';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'настройки (доданок)';
$lang['_template_sufix'] = 'настройки (шаблон)';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Немає метаданних параметру.';
$lang['_msg_setting_no_class'] = 'Немає класу параметру.';
$lang['_msg_setting_no_default'] = 'Намає значення завмовчки.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Права для створених файлів';
$lang['dmode']       = 'Права для створених тек';
$lang['lang']        = 'Мова';
$lang['basedir']     = 'Коренева тека';
$lang['baseurl']     = 'Кореневий URL';
$lang['savedir']     = 'Тека для збереження даних';
$lang['start']       = 'Найменування стартової сторінки';
$lang['title']       = 'Назва Вікі';
$lang['template']    = 'Шаблон';
$lang['fullpath']    = 'Повний шлях до документу';
$lang['recent']      = 'Останні зміни';
$lang['breadcrumbs'] = 'Ви відвідали (кількість сторінок, що показується)';
$lang['youarehere']  = 'Показувати "Ви тут"';
$lang['typography']  = 'Заміняти типографськи символи';
$lang['htmlok']      = 'Дозволити HTML';
$lang['phpok']       = 'Дозволити PHP';
$lang['dformat']     = 'Формат дати (дивіться функцію <a href="http://www.php.net/strftime">strftime</a> PHP)';
$lang['signature']   = 'Підпис';
$lang['toptoclevel'] = 'Мінімальний рівень для змісту';
$lang['maxtoclevel'] = 'Максимальний рівень для змісту';
$lang['maxseclevel'] = 'Максимальний рівень секції для редагування';
$lang['camelcase']   = 'Використовувати CamelCase';
$lang['deaccent']    = 'Транслітерація в іменах сторінок';
$lang['useheading']  = 'Першій заголовок замість імені';
$lang['refcheck']    = 'Перевіряти посилання на медіа-файлі';
$lang['refshow']     = 'Показувати кількість медіа-посилань';
$lang['allowdebug']  = 'Дозволити налагодження <b>вимкніть, якщо не потрібно!</b>';

$lang['usewordblock']= 'Блокувати спам по списку слів';
$lang['indexdelay']  = 'Затримка перед індексацією';
$lang['relnofollow'] = 'Використовувати rel="nofollow"';
$lang['mailguard']   = 'Кодувати адреси email';

/* Authentication Options */
$lang['useacl']      = 'Використовувати ACL';
$lang['openregister']= 'Дозволити реєстрацію всім';
$lang['autopasswd']  = 'Автоматичне створення паролей';
$lang['resendpasswd']= 'Повторна відсилка паролей';
$lang['authtype']    = 'Аутентифікація';
$lang['passcrypt']   = 'Метод шифрування паролей';
$lang['defaultgroup']= 'Група завмовчки';
$lang['superuser']   = 'Суперкористувач';
$lang['profileconfirm'] = 'Підтвержувати зміни профайла паролем';
$lang['disableactions'] = 'Заборонити дії ДокуВікі';
$lang['disableactions_check'] = 'Перевірити';
$lang['disableactions_subscription'] = 'Підписатись/Відписатись';
$lang['disableactions_wikicode'] = 'Переглянути код/Експорт';
$lang['disableactions_other'] = 'Інші дії (розділені комами)';

/* Advanced Options */
$lang['userewrite']  = 'Красиві URL';
$lang['useslash']    = 'Слеш, як розділювач простірів імен в URL';
$lang['usedraft']    = 'Автоматично зберегати чернетку при редагуванні';
$lang['sepchar']     = 'Розділювач слів у імені сторінки';
$lang['canonical']   = 'Каноничні URL';
$lang['autoplural']  = 'Перевіряти множину у посиланнях';
$lang['usegzip']     = 'Використовувати gzip (для горища)';
$lang['cachetime']   = 'Максимальний від для кеша (сек)';
$lang['locktime']    = 'Час блокування (сек)';
$lang['fetchsize']   = 'Максимальний розмір (в байтах), що fetch.php може завантажувати з зовні';
$lang['notify']      = 'Email для сповіщень';
$lang['registernotify'] = 'Надсилати інформацію про нових користувачів на цю адресу';
$lang['mailfrom']    = 'Email для автоматичних повідомлень';
$lang['gdlib']       = 'Версія GD Lib';
$lang['gzip_output'] = 'Використовувати gzip, як Content-Encoding для xhtml';
$lang['im_convert']  = 'Шлях до ImageMagick';
$lang['jpg_quality'] = 'Якість компресії JPG (0-100)';
$lang['subscribers'] = 'Підписка на зміни';
$lang['compress']    = 'Стискати файли CSS та javascript';
$lang['hidepages']   = 'Ховати сторінки (regular expressions)';
$lang['send404']     = 'Надсилати "HTTP 404/Сторінка не знайдена " для неіснуючих сторінок';
$lang['sitemap']     = 'Створювати мапу сайту для Google (дні)';

$lang['rss_type']    = 'тип RSS';
$lang['rss_linkto']  = 'посилання в RSS';
$lang['rss_update']  = 'Інтервал оновлення RSS (сек)';

/* Target options */

$lang['target____wiki']      = 'Target для внутрішніх посилань';
$lang['target____interwiki'] = 'Target для інтервікі-посилань';
$lang['target____extern']    = 'Target для зовнішніх посилань';
$lang['target____media']     = 'Target для медіа-посилань';
$lang['target____windows']   = 'Target для посилань на мережеві теки';

/* Proxy Options */
$lang['proxy____host'] = 'Адреса Proxy';
$lang['proxy____port'] = 'Порт Proxy';
$lang['proxy____user'] = 'Користувач Proxy';
$lang['proxy____pass'] = 'Пароль Proxy';
$lang['proxy____ssl']  = 'використовувати ssl для з\'єднання з Proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'включити хак safemode';
$lang['ftp____host'] = 'FTP-сервер для хака safemode';
$lang['ftp____port'] = 'FTP-порт для хака safemode';
$lang['ftp____user'] = 'Користувач FTP для хака safemode';
$lang['ftp____pass'] = 'Пароль FTP для хака safemode';
$lang['ftp____root'] = 'Коренева тека FTP для хака safemode';

/* userewrite options */
$lang['userewrite_o_0'] = 'немає';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'Засобами ДокуВікі';

/* deaccent options */
$lang['deaccent_o_0'] = 'вимкнено';
$lang['deaccent_o_1'] = 'вилучати діакритичні знаки';
$lang['deaccent_o_2'] = 'транслітерація';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib не доступна';
$lang['gdlib_o_1'] = 'Версія 1.x';
$lang['gdlib_o_2'] = 'Автовизначення';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'перегляд відмінностей';
$lang['rss_linkto_o_page']    = 'текст сторінки';
$lang['rss_linkto_o_rev']     = 'перелік ревізій';
$lang['rss_linkto_o_current'] = 'поточна сторінка';

