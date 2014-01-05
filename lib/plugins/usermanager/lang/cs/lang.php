<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Tomas Valenta <t.valenta@sh.cvut.cz>
 * @author Zbynek Krivka <zbynek.krivka@seznam.cz>
 * @author Bohumir Zamecnik <bohumir@zamecnik.org>
 * @author tomas@valenta.cz
 * @author Marek Sacha <sachamar@fel.cvut.cz>
 * @author Lefty <lefty@multihost.cz>
 * @author Vojta Beran <xmamut@email.cz>
 * @author zbynek.krivka@seznam.cz
 * @author Bohumir Zamecnik <bohumir.zamecnik@gmail.com>
 * @author Jakub A. Těšínský (j@kub.cz)
 * @author mkucera66@seznam.cz
 * @author Zbyněk Křivka <krivka@fit.vutbr.cz>
 */
$lang['menu']                  = 'Správa uživatelů';
$lang['noauth']                = '(autentizace uživatelů není k dispozici)';
$lang['nosupport']             = '(správa uživatelů není podporována)';
$lang['badauth']               = 'chybná metoda autentizace';
$lang['user_id']               = 'Uživatel';
$lang['user_pass']             = 'Heslo';
$lang['user_name']             = 'Celé jméno';
$lang['user_mail']             = 'E-mail';
$lang['user_groups']           = 'Skupiny';
$lang['field']                 = 'Položka';
$lang['value']                 = 'Hodnota';
$lang['add']                   = 'Přidat';
$lang['delete']                = 'Smazat';
$lang['delete_selected']       = 'Smazat vybrané';
$lang['edit']                  = 'Upravit';
$lang['edit_prompt']           = 'Upravit uživatele';
$lang['modify']                = 'Uložit změny';
$lang['search']                = 'Hledání';
$lang['search_prompt']         = 'Prohledat';
$lang['clear']                 = 'Zrušit vyhledávací filtr';
$lang['filter']                = 'Filtr';
$lang['export_all']            = 'Exportovat všechny uživatele (CSV)';
$lang['export_filtered']       = 'Exportovat filtrovaný seznam uživatelů (CSV)';
$lang['import']                = 'Importovat nové uživatele';
$lang['line']                  = 'Řádek č.';
$lang['error']                 = 'Chybová zpráva';
$lang['summary']               = 'Zobrazuji uživatele %1$d-%2$d z %3$d nalezených. Celkem %4$d uživatelů.';
$lang['nonefound']             = 'Žadný uživatel nenalezen. Celkem %d uživatelů.';
$lang['delete_ok']             = '%d uživatelů smazáno';
$lang['delete_fail']           = '%d uživatelů nelze smazat.';
$lang['update_ok']             = 'Uživatel upraven';
$lang['update_fail']           = 'Úprava uživatele selhala';
$lang['update_exists']         = 'Jméno nelze změnit, jelikož zadané uživatelské jméno (%s) již existuje (ostatní změny ale budou provedeny).';
$lang['start']                 = 'první';
$lang['prev']                  = 'předchozí';
$lang['next']                  = 'další';
$lang['last']                  = 'poslední';
$lang['edit_usermissing']      = 'Vybraný uživatel nebyl nalezen, zadané uživatelského mohlo být smazáno nebo změněno.';
$lang['user_notify']           = 'Upozornit uživatele';
$lang['note_notify']           = 'Maily s upozorněním se budou posílat pouze, když uživatel dostává nové heslo.';
$lang['note_group']            = 'Noví uživatelé budou přidáváni do této výchozí skupiny (%s), pokud pro ně není uvedena žádná skupina.';
$lang['note_pass']             = 'Heslo bude automaticky vygenerováno, pokud je pole ponecháno prázdné a je zapnuto upozornění uživatele.';
$lang['add_ok']                = 'Uživatel úspěšně vytvořen';
$lang['add_fail']              = 'Vytvoření uživatele selhalo';
$lang['notify_ok']             = 'Odeslán mail s upozorněním';
$lang['notify_fail']           = 'Mail s upozorněním nebylo možno odeslat';
$lang['import_success_count']  = 'Import uživatelů: nalezeno %d uživatelů, %d úspěšně importováno.';
$lang['import_failure_count']  = 'Import uživatelů: %d selhalo. Seznam chybných je níže.';
$lang['import_error_fields']   = 'Nedostatek položek, nalezena/y %d, požadovány 4.';
$lang['import_error_baduserid'] = 'Chybí User-id';
$lang['import_error_badname']  = 'Špatné jméno';
$lang['import_error_badmail']  = 'Špatná emailová adresa';
$lang['import_error_upload']   = 'Import selhal. CSV soubor nemohl být nahrán nebo je prázdný.';
$lang['import_error_readfail'] = 'Import selhal. Nelze číst nahraný soubor.';
$lang['import_error_create']   = 'Nelze vytvořit uživatele';
$lang['import_notify_fail']    = 'Importovanému uživateli %s s emailem %s nemohlo být zasláno upozornění.';
