<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Marek Adamski <fevbew@wp.pl>
 * @author pavulondit <pavloo@vp.pl>
 * @author Bartek S <sadupl@gmail.com>
 * @author Przemek <p_kudriawcew@o2.pl>
 */
$lang['debug']                 = 'Wyświetlanie szczegółowej wiadomości o błędzie. Powinno być wyłączone po  ';
$lang['dsn']                   = 'Nazwa źródła danych do łączenia się z bazą danych';
$lang['user']                  = 'Użytkownik do powyższego połączenia z bazą danych (puste dla sqlite)';
$lang['pass']                  = 'Hasło do powyższego połączenia z bazą danych (puste dla sqlite)';
$lang['select-user']           = 'Zapytanie SQL, aby wybrać dane jednego użytkownika';
$lang['select-user-groups']    = 'Zapytanie SQL aby wybrać wszystkie grupy jednego użytkownika';
$lang['select-groups']         = 'Instrukcja SQL do wybrania wszystkich dostępnych grup';
$lang['insert-user']           = 'Instrukcja SQL do wstawienia nowego użytkownika do bazy danych';
$lang['delete-user']           = 'Instrukcja SQL do usunięcia pojedynczego użytkownika z bazy danych';
$lang['list-users']            = 'Instrukcja SQL do wyświetlenia listy użytkowników pasujących do filtra';
$lang['count-users']           = 'Instrukcja SQL do zliczenia użytkowników pasujących do filtra';
$lang['update-user-info']      = 'Wyrażenie SQL aby zaktualizować imię oraz adres email dla pojedynczego użytkownika';
$lang['update-user-login']     = 'Wyrażenie SQL aby zaktualizować login dla pojedynczego użytkownika';
$lang['update-user-pass']      = 'Zapytanie SQL do zaktualizowania hasła dla pojedynczego użytkownika';
$lang['insert-group']          = 'Zapytanie SQL aby dodać nową grupę do bazy danych';
$lang['join-group']            = 'Zapytanie SQL aby dodać użytkownika do istniejącej grupy';
$lang['leave-group']           = 'Zapytanie SQL aby usunąć użytkownika z grupy';
$lang['check-pass']            = 'Zapytanie SQL aby sprawdzić hasło użytkownika. Można pozostawić puste, jeśli informacje o haśle są pobierane w select-user.';
