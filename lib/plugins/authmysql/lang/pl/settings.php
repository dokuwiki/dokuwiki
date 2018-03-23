<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Wojciech Lichota <wojciech@lichota.pl>
 * @author Paweł Jan Czochański <czochanski@gmail.com>
 * @author Mati <mackosa@wp.pl>
 * @author Maciej Helt <geraldziu@gmail.com>
 */
$lang['server']                = 'Twój server MySQL';
$lang['user']                  = 'Nazwa użytkownika MySQL';
$lang['password']              = 'Hasło dla powyższego użytkownika';
$lang['database']              = 'Używana baza danych';
$lang['charset']               = 'Zestaw znaków uzyty w bazie danych';
$lang['debug']                 = 'Wyświetlaj dodatkowe informacje do debugowania.';
$lang['forwardClearPass']      = 'Zamiast używać opcji passcrypt, przekazuj hasła użytkowników jako czysty tekst do poniższej instrukcji SQL';
$lang['TablesToLock']          = 'Rozdzielana przecinkami lista tabel, które powinny być blokowane podczas operacji zapisu';
$lang['checkPass']             = 'Zapytanie SQL wykorzystywane do sprawdzania haseł.';
$lang['getUserInfo']           = 'Zapytanie SQL zwracające informacje o użytkowniku';
$lang['getGroups']             = 'Zapytanie SQL przynależność do grup danego użytkownika';
$lang['getUsers']              = 'Zapytanie SQL zwracające listę wszystkich użytkowników';
$lang['FilterLogin']           = 'Klauzula SQL używana do filtrowania użytkowników na podstawie ich loginu';
$lang['FilterName']            = 'Klauzula SQL używana do filtrowania użytkowników na podstawie ich pełnej nazwy';
$lang['FilterEmail']           = 'Klauzula SQL używana do filtrowania użytkowników na podstawie ich adresu email';
$lang['FilterGroup']           = 'Klauzula SQL używana do filtrowania użytkowników na podstawie ich przynależności do grup';
$lang['SortOrder']             = 'Klauzula SQL używana do sortowania użytkowników';
$lang['addUser']               = 'Zapytanie SQL dodające nowego użytkownika';
$lang['addGroup']              = 'Instrukcja SQL dodająca nową grupę';
$lang['addUserGroup']          = 'Instrukcja SQL dodająca użytkownika do istniejącej grupy';
$lang['delGroup']              = 'Instrukcja SQL usuwająca grupę';
$lang['getUserID']             = 'Instrukcja SQL pobierająca klucz główny użytkownika';
$lang['delUser']               = 'Instrukcja SQL usuwająca użytkownika';
$lang['delUserRefs']           = 'Instrukcja SQL usuwająca użytkownika ze wszystkich grup';
$lang['updateUser']            = 'Instrukcja SQL aktualizująca profil użytkownika';
$lang['UpdateLogin']           = 'Polecenie służące do aktualizacji loginu użytkownika';
$lang['UpdatePass']            = 'Polecenie służące do aktualizacji hasła użytkownika';
$lang['UpdateEmail']           = 'Polecenie służące do aktualizacji e-mailu użytkownika';
$lang['UpdateName']            = 'Polecenie służące do aktualizacji imienia i nazwiska użytkownika';
$lang['UpdateTarget']          = 'Instrukcja limitu do identyfikacji użytkownika podczas aktualizacji';
$lang['delUserGroup']          = 'Instrukcja SQL usuwająca użytkownika ze wskazanej grupy';
$lang['getGroupID']            = 'Instrukcja SQL pobierający klucz główny wskazanej grupy';
$lang['debug_o_0']             = 'brak';
$lang['debug_o_1']             = 'tylko w przypadku błędów';
$lang['debug_o_2']             = 'wszystkie zapytania SQL';
