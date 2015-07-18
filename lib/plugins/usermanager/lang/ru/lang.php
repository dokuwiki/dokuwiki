<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Denis Simakov <akinoame1@gmail.com>
 * @author Andrew Pleshakov <beotiger@mail.ru>
 * @author Змей Этерийский evil_snake@eternion.ru
 * @author Hikaru Nakajima <jisatsu@mail.ru>
 * @author Alexei Tereschenko <alexeitlex@yahoo.com>
 * @author Irina Ponomareva irinaponomareva@webperfectionist.com
 * @author Alexander Sorkin <kibizoid@gmail.com>
 * @author Kirill Krasnov <krasnovforum@gmail.com>
 * @author Vlad Tsybenko <vlad.development@gmail.com>
 * @author Aleksey Osadchiy <rfc@nm.ru>
 * @author Aleksandr Selivanov <alexgearbox@gmail.com>
 * @author Ladyko Andrey <fylh@succexy.spb.ru>
 * @author Eugene <windy.wanderer@gmail.com>
 * @author Johnny Utah <pcpa@cyberpunk.su>
 * @author Ivan I. Udovichenko (sendtome@mymailbox.pp.ua)
 * @author Pavel <ivanovtsk@mail.ru>
 * @author Aleksandr Selivanov <alexgearbox@yandex.ru>
 * @author Igor Degraf <igordegraf@gmail.com>
 * @author Vitaly Filatenko <kot@hacktest.net>
 */
$lang['menu']                  = 'Управление пользователями';
$lang['noauth']                = '(авторизация пользователей недоступна)';
$lang['nosupport']             = '(управление пользователями не поддерживается)';
$lang['badauth']               = 'некорректный механизм аутентификации';
$lang['user_id']               = 'Логин';
$lang['user_pass']             = 'Пароль';
$lang['user_name']             = 'Полное имя';
$lang['user_mail']             = 'Эл. адрес';
$lang['user_groups']           = 'Группы';
$lang['field']                 = 'Поле';
$lang['value']                 = 'Значение';
$lang['add']                   = 'Добавить';
$lang['delete']                = 'Удалить';
$lang['delete_selected']       = 'Удалить выбранные';
$lang['edit']                  = 'Редактировать';
$lang['edit_prompt']           = 'Редактировать этого пользователя';
$lang['modify']                = 'Сохранить изменения';
$lang['search']                = 'Поиск';
$lang['search_prompt']         = 'Искать';
$lang['clear']                 = 'Сброс фильтра поиска';
$lang['filter']                = 'Фильтр';
$lang['export_all']            = 'Экспорт всех пользователей (CSV)';
$lang['export_filtered']       = 'Экспорт пользователей с фильтрацией списка (CSV)';
$lang['import']                = 'Импорт новых пользователей';
$lang['line']                  = 'Строка №';
$lang['error']                 = 'Ошибка';
$lang['summary']               = 'Показаны пользователи %1$d–%2$d из %3$d найденных. Всего пользователей: %4$d.';
$lang['nonefound']             = 'Не найдено ни одного пользователя. Всего пользователей: %d.';
$lang['delete_ok']             = 'Удалено пользователей: %d';
$lang['delete_fail']           = 'Не удалось удалить %d.';
$lang['update_ok']             = 'Пользователь успешно обновлён';
$lang['update_fail']           = 'Не удалось обновить пользователя';
$lang['update_exists']         = 'Не удалось изменить имя пользователя, такой пользователь (%s) уже существует (все остальные изменения будут применены).';
$lang['start']                 = 'в начало';
$lang['prev']                  = 'назад';
$lang['next']                  = 'вперёд';
$lang['last']                  = 'в конец';
$lang['edit_usermissing']      = 'Выбранный пользователь не найден. Возможно, указанный логин был удалён или изменён извне.';
$lang['user_notify']           = 'Сообщить пользователю';
$lang['note_notify']           = 'Письма с уведомлением высылаются только в случае получения нового пароля.';
$lang['note_group']            = 'Если группа не указана, новые пользователи будут добавлены в группу по умолчанию (%s).';
$lang['note_pass']             = 'Пароль будет сгенерирован автоматически, если поле оставлено пустым и включено уведомление пользователя.';
$lang['add_ok']                = 'Пользователь успешно добавлен';
$lang['add_fail']              = 'Не удалось добавить пользователя';
$lang['notify_ok']             = 'Письмо с уведомлением отправлено';
$lang['notify_fail']           = 'Не удалось отправить письмо с уведомлением';
$lang['import_userlistcsv']    = 'Файл со списком пользователей (CSV):';
$lang['import_header']         = 'Последний импорт — список ошибок';
$lang['import_success_count']  = 'Импорт пользователей: %d пользователей найдено, %d импортировано успешно.';
$lang['import_failure_count']  = 'Импорт пользователей: %d не удалось. Список ошибок прочтите ниже.';
$lang['import_error_fields']   = 'Не все поля заполнены. Найдено %d, а нужно: 4.';
$lang['import_error_baduserid'] = 'Отсутствует идентификатор пользователя';
$lang['import_error_badname']  = 'Имя не годится';
$lang['import_error_badmail']  = 'Адрес электронной почты не годится';
$lang['import_error_upload']   = 'Импорт не удался. CSV-файл не загружен или пуст.';
$lang['import_error_readfail'] = 'Импорт не удался. Невозможно прочесть загруженный файл.';
$lang['import_error_create']   = 'Невозможно создать пользователя';
$lang['import_notify_fail']    = 'Оповещение не может быть отправлено импортированному пользователю %s по электронной почте %s.';
$lang['import_downloadfailures'] = 'Скачать ошибки в формате CSV для исправления';
