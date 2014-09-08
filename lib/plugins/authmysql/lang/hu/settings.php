<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Marton Sebok <sebokmarton@gmail.com>
 * @author Marina Vladi <deldadam@gmail.com>
 */
$lang['server']                = 'MySQL-kiszolgáló';
$lang['user']                  = 'MySQL-felhasználónév';
$lang['password']              = 'Fenti felhasználó jelszava';
$lang['database']              = 'Adatbázis';
$lang['charset']               = 'Az adatbázisban használt karakterkészlet';
$lang['debug']                 = 'Hibakeresési üzenetek megjelenítése';
$lang['forwardClearPass']      = 'A jelszó nyílt szövegként történő átadása az alábbi SQL-utasításoknak a passcrypt opció használata helyett';
$lang['TablesToLock']          = 'Az íráskor zárolni kívánt táblák vesszővel elválasztott listája';
$lang['checkPass']             = 'SQL-utasítás a jelszavak ellenőrzéséhez';
$lang['getUserInfo']           = 'SQL-utasítás a felhasználói információk lekérdezéséhez';
$lang['getGroups']             = 'SQL-utasítás egy felhasználó csoporttagságainak lekérdezéséhez';
$lang['getUsers']              = 'SQL-utasítás a felhasználók listázásához';
$lang['FilterLogin']           = 'SQL-kifejezés a felhasználók azonosító alapú szűréséhez';
$lang['FilterName']            = 'SQL-kifejezés a felhasználók név alapú szűréséhez';
$lang['FilterEmail']           = 'SQL-kifejezés a felhasználók e-mail cím alapú szűréséhez';
$lang['FilterGroup']           = 'SQL-kifejezés a felhasználók csoporttagság alapú szűréséhez';
$lang['SortOrder']             = 'SQL-kifejezés a felhasználók rendezéséhez';
$lang['addUser']               = 'SQL-utasítás új felhasználó hozzáadásához';
$lang['addGroup']              = 'SQL-utasítás új csoport hozzáadásához';
$lang['addUserGroup']          = 'SQL-utasítás egy felhasználó egy meglévő csoporthoz való hozzáadásához';
$lang['delGroup']              = 'SQL-utasítás egy csoport törléséhez';
$lang['getUserID']             = 'SQL-utasítás egy felhasználó elsődleges kulcsának lekérdezéséhez';
$lang['delUser']               = 'SQL-utasítás egy felhasználó törléséhez';
$lang['delUserRefs']           = 'SQL-utasítás egy felhasználó eltávolításához az összes csoportból';
$lang['updateUser']            = 'SQL-utasítás egy felhasználó profiljának frissítéséhez';
$lang['UpdateLogin']           = 'UPDATE-klauzula a felhasználó azonosítójának frissítéséhez';
$lang['UpdatePass']            = 'UPDATE-klauzula a felhasználó jelszavának frissítéséhez';
$lang['UpdateEmail']           = 'UPDATE-klauzula a felhasználó e-mail címének frissítéséhez';
$lang['UpdateName']            = 'UPDATE-klauzula a felhasználó teljes nevének frissítéséhez';
$lang['UpdateTarget']          = 'LIMIT-klauzula a felhasználó kiválasztásához az adatok frissítésekor';
$lang['delUserGroup']          = 'SQL-utasítás felhasználó adott csoportból történő törléséhez ';
$lang['getGroupID']            = 'SQL-utasítás adott csoport elsődleges kulcsának lekérdezéséhez';
$lang['debug_o_0']             = 'nem';
$lang['debug_o_1']             = 'csak hiba esetén';
$lang['debug_o_2']             = 'minden SQL-lekérdezésnél';
