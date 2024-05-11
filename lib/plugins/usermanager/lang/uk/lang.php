<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author uaKalwin <world9online@gmail.com>
 * @author Oleksiy Voronin <ovoronin@gmail.com>
 * @author serg_stetsuk <serg_stetsuk@ukr.net>
 * @author Oleksandr Kunytsia <okunia@gmail.com>
 */
$lang['menu']                  = 'Керування користувачами';
$lang['noauth']                = '(автентифікація користувачів не	доступна)';
$lang['nosupport']             = '(керування користувачами не підтримується)';
$lang['badauth']               = 'невірний механізм автентифікації';
$lang['user_id']               = 'Ім’я користувача';
$lang['user_pass']             = 'Пароль';
$lang['user_name']             = 'Повне ім’я';
$lang['user_mail']             = 'E-mail';
$lang['user_groups']           = 'Групи';
$lang['field']                 = 'Поле';
$lang['value']                 = 'Значення';
$lang['add']                   = 'Додати';
$lang['delete']                = 'Видалити';
$lang['delete_selected']       = 'Видалити вибраних';
$lang['edit']                  = 'Змінити';
$lang['edit_prompt']           = 'Змінити цього користувача';
$lang['modify']                = 'Зберегти зміни';
$lang['search']                = 'Пошук';
$lang['search_prompt']         = 'Шукати';
$lang['clear']                 = 'Очистити фільтр пошуку';
$lang['filter']                = 'Фільтр';
$lang['summary']               = 'Показано користувачі %1$d-%2$d з %3$d знайдених. Всього користувачів: %4$d.';
$lang['nonefound']             = 'Не знайдено жодного користувача. Всього користувачів: %d.';
$lang['delete_ok']             = 'Видалено користувачів: %d';
$lang['delete_fail']           = 'Не вдалося видалити %d.';
$lang['update_ok']             = 'Дані користувача оновлено';
$lang['update_fail']           = 'Не вдалося оновити дані користувача';
$lang['update_exists']         = 'Не вдалося змінити ім’я користувача, такий користувач (%s) вже існує (всі інші зміни будуть застосовані).';
$lang['start']                 = 'на початок';
$lang['prev']                  = 'назад';
$lang['next']                  = 'вперед';
$lang['last']                  = 'в кінець';
$lang['edit_usermissing']      = 'Обраного користувача не знайдено, можливо його було вилучено або змінено ще десь.';
$lang['user_notify']           = 'Повідомити користувача';
$lang['note_notify']           = 'Листи з повідомленнями відсилаються лише у випадку видачі нового пароля користувачу.';
$lang['note_group']            = 'Якщо не визначено групи, то нові користувачі будуть автоматично додані до групи за замовчуванням (%s).';
$lang['note_pass']             = 'Пароль буде згенеровано автоматично, якщо поле пароля не заповнено і увімкнено прапорець "повідомити користувача".';
$lang['add_ok']                = 'Користувача додано';
$lang['add_fail']              = 'Неможливо додати користувача';
$lang['notify_ok']             = 'Листа з повідомленням надіслано';
$lang['notify_fail']           = 'Неможливо вислати листа з повідомленням';
$lang['import_error_badname']  = 'Погана назва';
$lang['import_error_badmail']  = 'Погана адреса електронної пошти';
$lang['import_error_upload']   = 'Імпорт не вдався. Файл CSV не може бути завантажений або він порожній.';
$lang['import_error_readfail'] = 'Імпорт не вдався. Неможливо прочитати завантажений файлю.';
$lang['import_error_create']   = 'Не вдалося створити користувача';
$lang['import_notify_fail']    = 'Повідомлення не вдалося відправити для імпортованого користувача, %s з електронною адресою %s.';
$lang['import_downloadfailures'] = 'Завантажені помилки у форматі CSV для виправлення';
$lang['addUser_error_missing_pass'] = 'Будь ласка, встановіть пароль або активуйте сповіщення користувача для генерації пароля.';
$lang['addUser_error_pass_not_identical'] = 'Введені паролі не співпадають.';
$lang['addUser_error_modPass_disabled'] = 'Зміна паролей наразі відключена';
$lang['addUser_error_name_missing'] = 'Будь ласка, введіть ім\'я для нового користувача.';
$lang['addUser_error_modName_disabled'] = 'Зміна імен наразі відключена.';
$lang['addUser_error_mail_missing'] = 'Будь ласка, введіть адресу електронної пошти для нового користувача.';
$lang['addUser_error_modMail_disabled'] = 'Зміна електронних адрес наразі відключена.';
$lang['addUser_error_create_event_failed'] = 'Плагін перешкодив додаванню нового користувача. Перегляньте інші можливі повідомлення для отримання додаткової інформації.';
