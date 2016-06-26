<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Aleksandr Selivanov <alexgearbox@yandex.ru>
 * @author Igor Degraf <igordegraf@gmail.com>
 * @author Type-kun <workwork-1@yandex.ru>
 * @author Vitaly Filatenko <kot@hacktest.net>
 * @author Alex P <alexander@lanos.co.uk>
 * @author Takumo <9206984@mail.ru>
 */
$lang['menu']                  = 'Управление дополнениями';
$lang['tab_plugins']           = 'Установленные плагины';
$lang['tab_templates']         = 'Установленные шаблоны';
$lang['tab_search']            = 'Поиск и установка';
$lang['tab_install']           = 'Ручная установка';
$lang['notimplemented']        = 'Эта возможность ещё не реализована';
$lang['notinstalled']          = 'Это дополнение не установлено';
$lang['alreadyenabled']        = 'Это дополнение уже включено';
$lang['alreadydisabled']       = 'Это дополнение уже отключено';
$lang['pluginlistsaveerror']   = 'Ошибка при сохранении списка плагинов';
$lang['unknownauthor']         = 'Автор неизвестен';
$lang['unknownversion']        = 'Версия неизвестна';
$lang['btn_info']              = 'Отобразить доп. информацию';
$lang['btn_update']            = 'Обновить';
$lang['btn_uninstall']         = 'Удалить';
$lang['btn_enable']            = 'Включить';
$lang['btn_disable']           = 'Отключить';
$lang['btn_install']           = 'Установить';
$lang['btn_reinstall']         = 'Переустановить';
$lang['js']['reallydel']       = 'Действительно удалить это дополнение?';
$lang['js']['display_viewoptions'] = 'Показать как:';
$lang['js']['display_enabled'] = 'включено';
$lang['js']['display_disabled'] = 'отключено';
$lang['js']['display_updatable'] = 'обновление';
$lang['search_for']            = 'Поиск дополнения:';
$lang['search']                = 'Найти';
$lang['extensionby']           = '<strong>%s</strong> — %s';
$lang['screenshot']            = 'Скриншот: %s';
$lang['popularity']            = 'Популярность: %s%%';
$lang['homepage_link']         = 'Описание';
$lang['bugs_features']         = 'Баг-трекер';
$lang['tags']                  = 'Метки:';
$lang['author_hint']           = 'Найти дополнения этого автора';
$lang['installed']             = 'Установлено:';
$lang['downloadurl']           = 'Скачать:';
$lang['repository']            = 'Репозиторий:';
$lang['unknown']               = '<em>неизвестно</em>';
$lang['installed_version']     = 'Уст. версия:';
$lang['install_date']          = 'Посл. обновление:';
$lang['available_version']     = 'Доступная версия:';
$lang['compatible']            = 'Совместим с:';
$lang['depends']               = 'Зависит от:';
$lang['similar']               = 'Похож на:';
$lang['conflicts']             = 'Конфликтует с:';
$lang['donate']                = 'Нравится?';
$lang['donate_action']         = 'Купить автору кофе!';
$lang['repo_retry']            = 'Повторить';
$lang['provides']              = 'Предоставляет:';
$lang['status']                = 'Состояние:';
$lang['status_installed']      = 'установлено';
$lang['status_not_installed']  = 'не установлено';
$lang['status_protected']      = 'защищено';
$lang['status_enabled']        = 'включено';
$lang['status_disabled']       = 'отключено';
$lang['status_unmodifiable']   = 'неизменяемо';
$lang['status_plugin']         = 'плагин';
$lang['status_template']       = 'шаблон';
$lang['status_bundled']        = 'в комплекте';
$lang['msg_enabled']           = 'Плагин %s включён';
$lang['msg_disabled']          = 'Плагин %s отключён';
$lang['msg_delete_success']    = 'Дополнение %s удалено';
$lang['msg_delete_failed']     = 'Не удалось удалить дополнение %s';
$lang['msg_template_install_success'] = 'Шаблон %s успешно установлен';
$lang['msg_template_update_success'] = 'Шаблон %s успешно обновлён';
$lang['msg_plugin_install_success'] = 'Плагин %s успешно установлен';
$lang['msg_plugin_update_success'] = 'Плагин %s успешно обновлён';
$lang['msg_upload_failed']     = 'Не удалось загрузить файл';
$lang['missing_dependency']    = '<strong>Отсутствует или отключена зависимость:</strong> %s';
$lang['security_issue']        = '<strong>Проблема безопасности:</strong> %s';
$lang['security_warning']      = '<strong>Предупреждение безопасности:</strong> %s';
$lang['update_available']      = '<strong>Обновление:</strong> доступна новая версия %s';
$lang['wrong_folder']          = '<strong>Плагин установлен неправильно:</strong> переименуйте директорию плагина из %s в %s';
$lang['url_change']            = '<strong>Ссылка изменилась:</strong> ссылка для загрузки изменилась с прошлого раза. Проверьте новую ссылку прежде, чем обновлять дополнение.<br />Новая: %s<br />Старая: %s';
$lang['error_badurl']          = 'Ссылка должна начинаться с http или https';
$lang['error_dircreate']       = 'Не удалось создать временную директорию для загрузки';
$lang['error_download']        = 'Не удалось загрузить файл: %s';
$lang['error_decompress']      = 'Не удалось распаковать загруженный файл. Возможно, файл был повреждён при загрузке — тогда нужно попробовать ещё раз. Либо неизвестен формат архива — тогда загрузку и установку надо произвести вручную';
$lang['error_findfolder']      = 'Не удалось определить директорию для дополнения, загрузку и установку надо произвести вручную.';
$lang['error_copy']            = 'Возникла ошибка копирования файлов в директорию <em>%s</em>: возможно, диск переполнен, или неверно выставлены права доступа. Это могло привести к неполной установке плагина и нарушить работу вашей вики.';
$lang['noperms']               = 'Директория для дополнений не доступна для записи';
$lang['notplperms']            = 'Директория для шаблонов не доступна для записи';
$lang['nopluginperms']         = 'Директория для плагинов не доступна для записи';
$lang['git']                   = 'Это дополнение было установлено через git. Вы не можете обновить его тут.';
$lang['auth']                  = 'Этот auth плагин не включен в конфигурации, подумайте о его отключении';
$lang['install_url']           = 'Установить с адреса:';
$lang['install_upload']        = 'Скачать дополнение:';
$lang['repo_error']            = 'Сайт с плагинами недоступен. Убедитесь, что у сайта есть доступ на www.dokuwiki.org и также проверьте настройки соединения прокси.';
$lang['nossl']                 = 'Ваша PHP конфигурация не имеет SSL поддержки. Это поломает скачивание для многих DokuWiki плагинов и дополнений.';
