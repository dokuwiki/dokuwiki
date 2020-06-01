<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Tomas Valenta <t.valenta@sh.cvut.cz>
 * @author Zbynek Krivka <zbynek.krivka@seznam.cz>
 * @author Bohumir Zamecnik <bohumir@zamecnik.org>
 * @author tomas <tomas@valenta.cz>
 * @author Marek Sacha <sachamar@fel.cvut.cz>
 * @author Lefty <lefty@multihost.cz>
 * @author Vojta Beran <xmamut@email.cz>
 * @author Jakub A. Těšínský (j@kub.cz)
 * @author mkucera66 <mkucera66@seznam.cz>
 * @author Zbyněk Křivka <krivka@fit.vutbr.cz>
 * @author Jaroslav Lichtblau <jlichtblau@seznam.cz>
 * @author Daniel Slováček <danslo@danslo.cz>
 * @author Martin Růžička <martinr@post.cz>
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
$lang['nonefound']             = 'Žádný uživatel nebyl nalezen. Celkem %d uživatelů.';
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
$lang['note_notify']           = 'E-maily s upozorněním se budou posílat pouze, když uživatel dostává nové heslo.';
$lang['note_group']            = 'Noví uživatelé budou přidáváni do této výchozí skupiny (%s), pokud pro ně není uvedena žádná skupina.';
$lang['note_pass']             = 'Heslo bude automaticky vygenerováno, pokud je pole ponecháno prázdné a je zapnuto upozornění uživatele.';
$lang['add_ok']                = 'Uživatel úspěšně vytvořen';
$lang['add_fail']              = 'Vytvoření uživatele selhalo';
$lang['notify_ok']             = 'Odeslán e-mail s upozorněním';
$lang['notify_fail']           = 'E-mail s upozorněním nebylo možno odeslat';
$lang['import_userlistcsv']    = 'Seznam uživatelů (CSV):';
$lang['import_header']         = 'Poslední selhání importu';
$lang['import_success_count']  = 'Import uživatelů: nalezeno %d uživatelů, %d úspěšně importováno.';
$lang['import_failure_count']  = 'Import uživatelů: %d selhalo. Seznam chybných je níže.';
$lang['import_error_fields']   = 'Nedostatek položek, nalezena/y %d, požadovány 4.';
$lang['import_error_baduserid'] = 'Chybí User-id';
$lang['import_error_badname']  = 'Špatné jméno';
$lang['import_error_badmail']  = 'Špatná e-mailová adresa';
$lang['import_error_upload']   = 'Import selhal. CSV soubor nemohl být nahrán nebo je prázdný.';
$lang['import_error_readfail'] = 'Import selhal. Nelze číst nahraný soubor.';
$lang['import_error_create']   = 'Nelze vytvořit uživatele';
$lang['import_notify_fail']    = 'Importovanému uživateli %s s e-mailem %s nemohlo být zasláno upozornění.';
$lang['import_downloadfailures'] = 'Stáhnout chyby pro nápravu jako CVS';
$lang['addUser_error_missing_pass'] = 'Buď prosím nastavte heslo nebo aktivujte upozorňování uživatel aby fungovalo vytváření hesel.';
$lang['addUser_error_pass_not_identical'] = 'Zadaná hesla nebyla shodná.';
$lang['addUser_error_modPass_disabled'] = 'Změna hesel je momentálně zakázána.';
$lang['addUser_error_name_missing'] = 'Zadejte prosím jméno nového uživatele.';
$lang['addUser_error_modName_disabled'] = 'Změna jmen je momentálně zakázána.';
$lang['addUser_error_mail_missing'] = 'Zadejte prosím emailovou adresu nového uživatele.';
$lang['addUser_error_modMail_disabled'] = 'Změna e-mailové adresy je momentálně zakázána.';
$lang['addUser_error_create_event_failed'] = 'Zásuvný modul zabránil přidání nového uživatele. Pro více informací si prohlédněte další možné zprávy.';
