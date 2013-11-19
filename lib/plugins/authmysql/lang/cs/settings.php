<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author mkucera66@seznam.cz
 */
$lang['server']                = 'Váš server MySQL';
$lang['user']                  = 'Uživatelské jméno pro MySQL';
$lang['password']              = 'Heslo tohoto uživatele';
$lang['database']              = 'Použtá databáze';
$lang['charset']               = 'znaková sada použitá v databázi';
$lang['debug']                 = 'Zobrazit dodatečné debugovací informace';
$lang['forwardClearPass']      = 'Posílat uživatelské heslo jako čistý text do příkazů SQL namísto využití volby passcrypt.';
$lang['TablesToLock']          = 'Čárkou oddělený seznam tabulek, které mohou být zamčené během operací zápisu';
$lang['checkPass']             = 'Příkaz SQL pro kontrolu hesel';
$lang['getUserInfo']           = 'Příkaz SQL pro získání informací o uživateli';
$lang['getGroups']             = 'Příkaz SQL pro získání uživatelovy skupiny';
$lang['getUsers']              = 'Příkaz SQL pro seznam všech uživatelů';
$lang['FilterLogin']           = 'Příkaz SQL pro filtrování uživatelů podle přihlašovacího jména';
$lang['FilterName']            = 'Příkaz SQL pro filtrování uživatelů podle celého jména';
$lang['FilterEmail']           = 'Příkaz SQL pro filtrování uživatelů podle adres emailů';
$lang['FilterGroup']           = 'Příkaz SQL pro filtrování uživatelů podle členství ve skupinách';
$lang['SortOrder']             = 'Příkaz SQL pro řazení uživatelů';
$lang['addUser']               = 'Příkaz SQL pro přidání nového uživatele';
$lang['addGroup']              = 'Příkaz SQL pro přidání nové skupiny';
$lang['addUserGroup']          = 'Příkaz SQL pro přidání uživatele do existující skupiny';
$lang['delGroup']              = 'Příkaz SQL pro vymazání skupiny';
$lang['getUserID']             = 'Příkaz SQL pro získání primárního klíče uživatele';
$lang['delUser']               = 'Příkaz SQL pro vymazání uživatele';
$lang['delUserRefs']           = 'Příkaz SQL pro odstranění členství uživatele se všech skupin';
$lang['updateUser']            = 'Příkaz SQL pro aktualizaci uživatelského profilu';
$lang['UpdateLogin']           = 'Klauzule pro aktualizaci přihlačovacího jména uživatele';
$lang['UpdatePass']            = 'Klauzule pro aktualizaci hesla uživatele';
$lang['UpdateEmail']           = 'Klauzule pro aktualizaci emailové adresy uživatele';
$lang['UpdateName']            = 'Klauzule pro aktualizaci celého jména uživatele';
$lang['UpdateTarget']          = 'Omezující klauzule pro identifikaci uživatele při aktualizaci';
$lang['delUserGroup']          = 'Příkaz SQL pro zrušení členství uživatele v dané skupině';
$lang['getGroupID']            = 'Příkaz SQL pro získání primárního klíče skupiny';
$lang['debug_o_0']             = 'nic';
$lang['debug_o_1']             = 'pouze při chybách';
$lang['debug_o_2']             = 'všechny dotazy SQL';
