<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Aleksandr Selivanov <alexgearbox@yandex.ru>
 * @author Yuriy Skalko <yuriy.skalko@gmail.com>
 * @author Denis Simakov <akinoame1@gmail.com>
 * @author Andrew Pleshakov <beotiger@mail.ru>
 * @author Змей Этерийский <evil_snake@eternion.ru>
 * @author Hikaru Nakajima <jisatsu@mail.ru>
 * @author Alexei Tereschenko <alexeitlex@yahoo.com>
 * @author Alexander Sorkin <kibizoid@gmail.com>
 * @author Kirill Krasnov <krasnovforum@gmail.com>
 * @author Vlad Tsybenko <vlad.development@gmail.com>
 * @author Aleksey Osadchiy <rfc@nm.ru>
 * @author Ladyko Andrey <fylh@succexy.spb.ru>
 * @author Eugene <windy.wanderer@gmail.com>
 * @author Johnny Utah <pcpa@cyberpunk.su>
 * @author Pavel <ivanovtsk@mail.ru>
 * @author Igor Degraf <igordegraf@gmail.com>
 * @author Vitaly Filatenko <kot@hacktest.net>
 * @author dimsharav <dimsharav@gmail.com>
 * @author Radimir <radimir.shevchenko@gmail.com>
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
$lang['export_filtered']       = 'Экспорт отфильтрованного списка пользователей (CSV)';
$lang['import']                = 'импортировать новых пользователей';
$lang['line']                  = 'Строка №';
$lang['error']                 = 'Ошибка';
$lang['summary']               = 'Показаны пользователи %1$d–%2$d из %3$d найденных. Всего пользователей: %4$d.';
$lang['nonefound']             = 'Не найдено ни одного пользователя. Всего пользователей: %d.';
$lang['delete_ok']             = 'Удалено пользователей: %d';
$lang['delete_fail']           = 'Не удалось удалить %d.';
$lang['update_ok']             = 'Пользователь успешно обновлён';
$lang['update_fail']           = 'Не удалось обновить пользователя';
$lang['update_exists']         = 'Не удалось изменить имя пользователя, потому что такой пользователь (%s) уже существует (все остальные изменения будут применены).';
$lang['start']                 = 'в начало';
$lang['prev']                  = 'назад';
$lang['next']                  = 'вперёд';
$lang['last']                  = 'в конец';
$lang['edit_usermissing']      = 'Выбранный пользователь не найден. Возможно, указанный логин был удалён или изменён извне.';
$lang['user_notify']           = 'Оповестить пользователя';
$lang['note_notify']           = 'Письма с уведомлением высылаются только в случае получения нового пароля.';
$lang['note_group']            = 'Если группа не указана, новые пользователи будут добавлены в группу по умолчанию (%s).';
$lang['note_pass']             = 'Пароль будет сгенерирован автоматически, если поле оставлено пустым и включено уведомление пользователя.';
$lang['add_ok']                = 'Пользователь успешно добавлен';
$lang['add_fail']              = 'Не удалось добавить пользователя';
$lang['notify_ok']             = 'Письмо с уведомлением отправлено';
$lang['notify_fail']           = 'Не удалось отправить письмо с уведомлением';
$lang['import_userlistcsv']    = 'Файл со списком пользователей (CSV):';
$lang['import_header']         = 'Последний импорт — список ошибок';
$lang['import_success_count']  = 'Импорт пользователей. Найдено пользователей: %d, импортировано успешно: %d.';
$lang['import_failure_count']  = 'Импорт пользователей: %d не удалось. Ошибки перечислены ниже.';
$lang['import_error_fields']   = 'Не все поля заполнены. Найдено %d, а требуется 4.';
$lang['import_error_baduserid'] = 'Отсутствует идентификатор пользователя';
$lang['import_error_badname']  = 'Неверное имя';
$lang['import_error_badmail']  = 'Неверный адрес эл. почты';
$lang['import_error_upload']   = 'Импорт не удался. CSV-файл не загружен или пуст.';
$lang['import_error_readfail'] = 'Импорт не удался. Невозможно прочесть загруженный файл.';
$lang['import_error_create']   = 'Невозможно создать пользователя';
$lang['import_notify_fail']    = 'Оповещение не может быть отправлено импортированному пользователю %s по электронной почте %s.';
$lang['import_downloadfailures'] = 'Скачать ошибочные данные в формате CSV для исправления';
$lang['addUser_error_missing_pass'] = 'Для возможности генерации пароля, пожалуйста, установите пароль или активируйте оповещения.';
$lang['addUser_error_pass_not_identical'] = 'Введённые пароли не совпадают.';
$lang['addUser_error_modPass_disabled'] = 'Изменение пароля в настоящее время невозможно.';
$lang['addUser_error_name_missing'] = 'Укажите имя нового пользователя.';
$lang['addUser_error_modName_disabled'] = 'Изменение имени в настоящее время невозможно.';
$lang['addUser_error_mail_missing'] = 'Укажите адрес эл. почты нового пользователя.';
$lang['addUser_error_modMail_disabled'] = 'Изменение адреса эл. почты отключено.';
$lang['addUser_error_create_event_failed'] = 'Плагин заблокировал добавление нового пользователя. Смотрите также другие сообщения.';
