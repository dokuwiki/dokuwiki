<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Wojciech Lichota <wojciech@lichota.pl>
 * @author Max <maxrb146@gmail.com>
 * @author Grzegorz Żur <grzegorz.zur@gmail.com>
 * @author Mariusz Kujawski <marinespl@gmail.com>
 * @author Maciej Kurczewski <pipijajko@gmail.com>
 * @author Sławomir Boczek <slawkens@gmail.com>
 * @author sleshek <sleshek@wp.pl>
 * @author Leszek Stachowski <shazarre@gmail.com>
 * @author maros <dobrimaros@yahoo.pl>
 * @author Grzegorz Widła <dzesdzes@gmail.com>
 * @author Łukasz Chmaj <teachmeter@gmail.com>
 * @author Begina Felicysym <begina.felicysym@wp.eu>
 * @author Aoi Karasu <aoikarasu@gmail.com>
 */
$lang['menu']                  = 'Menadżer użytkowników';
$lang['noauth']                = '(uwierzytelnienie użytkownika niemożliwe)';
$lang['nosupport']             = '(zarządzanie użytkownikami niemożliwe)';
$lang['badauth']               = 'błędny mechanizm uwierzytelniania';
$lang['user_id']               = 'Nazwa użytkownika';
$lang['user_pass']             = 'Hasło';
$lang['user_name']             = 'Użytkownik';
$lang['user_mail']             = 'E-mail';
$lang['user_groups']           = 'Grupy';
$lang['field']                 = 'Pole';
$lang['value']                 = 'Wartość';
$lang['add']                   = 'Dodaj';
$lang['delete']                = 'Usuń';
$lang['delete_selected']       = 'Usuń zaznaczone';
$lang['edit']                  = 'Edytuj';
$lang['edit_prompt']           = 'Edytuj użytkownika';
$lang['modify']                = 'Zapisz zmiany';
$lang['search']                = 'Szukaj';
$lang['search_prompt']         = 'Rozpocznij przeszukiwanie';
$lang['clear']                 = 'Resetuj filtr przeszukiwania';
$lang['filter']                = 'Filtr';
$lang['export_all']            = 'Eksportuj wszystkich użytkowników (CSV)';
$lang['export_filtered']       = 'Eksportuj wyfiltrowaną listę użytkowników (CSV) ';
$lang['import']                = 'Importuj nowych użytkowników';
$lang['line']                  = 'Numer linii';
$lang['error']                 = 'Błędna wiadomość';
$lang['summary']               = 'Użytkownicy %1$d-%2$d z %3$d znalezionych. Całkowita ilość użytkowników %4$d.';
$lang['nonefound']             = 'Nie znaleziono użytkowników. Całkowita ilość użytkowników %d.';
$lang['delete_ok']             = 'Usunięto %d użytkowników.';
$lang['delete_fail']           = 'Błąd przy usuwaniu %d użytkowników.';
$lang['update_ok']             = 'Dane użytkownika zostały zmienione!';
$lang['update_fail']           = 'Błąd przy zmianie danych użytkownika!';
$lang['update_exists']         = 'Błąd przy zmianie nazwy użytkownika, użytkownik o tej nazwie (%s) już istnieje (inne zmiany zostały wprowadzone).';
$lang['start']                 = 'początek';
$lang['prev']                  = 'poprzedni';
$lang['next']                  = 'następny';
$lang['last']                  = 'ostatni';
$lang['edit_usermissing']      = 'Nie znaleziono wybranego użytkownika, nazwa użytkownika mogła zostać zmieniona lub usunięta.';
$lang['user_notify']           = 'Powiadamianie użytkownika';
$lang['note_notify']           = 'Powiadomienia wysyłane są tylko jeżeli zmieniono hasło użytkownika.';
$lang['note_group']            = 'Nowy użytkownik zostanie dodany do grupy domyślnej (%s) jeśli nie podano innej grupy.';
$lang['note_pass']             = 'Jeśli pole będzie puste i powiadamianie użytkownika jest włączone, hasło zostanie automatyczne wygenerowane.';
$lang['add_ok']                = 'Dodano użytkownika';
$lang['add_fail']              = 'Dodawanie użytkownika nie powiodło się';
$lang['notify_ok']             = 'Powiadomienie zostało wysłane';
$lang['notify_fail']           = 'Wysyłanie powiadomienia nie powiodło się';
$lang['import_userlistcsv']    = 'Plik z listą użytkowników (CSV):';
$lang['import_header']         = 'Najnowszy import - błędy';
$lang['import_success_count']  = 'Import użytkowników: znaleziono %d użytkowników z czego pomyślnie zaimportowano %d.';
$lang['import_failure_count']  = 'Import użytkowników: %d błędów. Błędy wymieniono poniżej.';
$lang['import_error_fields']   = 'Niewystarczająca ilość pól, znalezione %d, wymagane 4.';
$lang['import_error_baduserid'] = 'Brak id użytkownika';
$lang['import_error_badname']  = 'Błędna nazwa';
$lang['import_error_badmail']  = 'Błędny email';
$lang['import_error_upload']   = 'Importowanie nie powiodło się. Nie można załadować pliku CSV lub jest on pusty.';
$lang['import_error_readfail'] = 'Ładownie przerwane. Nie można odczytać pliku. ';
$lang['import_error_create']   = 'Nie można utworzyć użytkownika';
$lang['import_notify_fail']    = 'Powiadomienie nie mogło być wysłane do zaimportowanego użytkownika %s o e-mailu %s.';
$lang['import_downloadfailures'] = 'W celu korekty pobierz niepowodzenia jako plik CSV';
$lang['addUser_error_missing_pass'] = 'Ustaw hasło albo aktywuj powiadomienia użytkowników aby móc włączyć generowanie haseł.';
$lang['addUser_error_pass_not_identical'] = 'Wprowadzone różne hasła ';
$lang['addUser_error_modPass_disabled'] = 'Modyfikacja haseł została wyłączona';
$lang['addUser_error_name_missing'] = 'Wprowadź nazwę dla nowego użytkownika';
$lang['addUser_error_modName_disabled'] = 'Modyfikacja nazw została wyłączona ';
$lang['addUser_error_mail_missing'] = 'Wprowadź adres email dla nowego użytkownika';
$lang['addUser_error_modMail_disabled'] = 'Modyfikacja adresów email została wyłączona ';
$lang['addUser_error_create_event_failed'] = 'Wtyczka uniemożliwiła dodanie nowego użytkownika. Przejrzyj możliwe inne komunikaty, aby uzyskać więcej informacji.';
