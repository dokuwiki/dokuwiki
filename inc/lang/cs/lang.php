<?php
/**
 * Czech language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Bohumir Zamecnik <bohumir@zamecnik.org>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
$lang['doublequoteopening']  = '„';//&bdquo;
$lang['doublequoteclosing']  = '“';//&ldquo;
$lang['singlequoteopening']  = '‚';//&sbquo;
$lang['singlequoteclosing']  = '‘';//&lsquo;

$lang['btn_edit']   = 'Upravit stránku';
$lang['btn_source'] = 'Zdrojový kód stránky';
$lang['btn_show']   = 'Zobrazit stránku';
$lang['btn_create'] = 'Vytvořit stránku';
$lang['btn_search'] = 'Hledat';
$lang['btn_save']   = 'Uložit';
$lang['btn_preview']= 'Náhled';
$lang['btn_top']    = 'Nahoru';
$lang['btn_newer']  = '<< novější';
$lang['btn_older']  = 'starší >>';
$lang['btn_revs']   = 'Starší verze';
$lang['btn_recent'] = 'Poslední úpravy';
$lang['btn_upload'] = 'Přiložit';
$lang['btn_cancel'] = 'Storno';
$lang['btn_index']  = 'Index';
$lang['btn_secedit']= 'Upravit';
$lang['btn_login']  = 'Přihlásit se';
$lang['btn_logout'] = 'Odhlásit se';
$lang['btn_admin']  = 'Správa';
$lang['btn_update'] = 'Aktualizovat';
$lang['btn_delete'] = 'Vymazat';
$lang['btn_back']   = 'Zpět';
$lang['btn_backlink']   = 'Zpětné odkazy';
$lang['btn_backtomedia'] = 'Zpět do Výběru dokumentu';
$lang['btn_subscribe'] = 'Odebírat změny mailem';
$lang['btn_unsubscribe'] = 'Neodebírat změny mailem';
$lang['btn_profile']    = 'Upravit profil';
$lang['btn_reset'] = 'Reset';
$lang['btn_resendpwd'] = 'Zaslat nové heslo';
$lang['btn_draft']    = 'Upravit koncept';
$lang['btn_recover']  = 'Obnovit koncept';
$lang['btn_draftdel'] = 'Vymazat koncept';

$lang['loggedinas'] = 'Přihlášen(a) jako';
$lang['user']       = 'Uživatelské jméno';
$lang['pass']       = 'Heslo';
$lang['newpass']    = 'Nové heslo';
$lang['oldpass']    = 'Současné heslo';
$lang['passchk']    = 'ještě jednou';
$lang['remember']   = 'Přihlásit se nastálo';
$lang['fullname']   = 'Celé jméno';
$lang['email']      = 'E-Mail';
$lang['register']   = 'Registrovat';
$lang['profile']    = 'Uživatelský profil';
$lang['badlogin']   = 'Zadané uživatelské jméno a heslo není správně.';
$lang['minoredit']  = 'Drobné změny';
$lang['draftdate']  = 'Koncept automaticky uložen v'; // full dformat date will be added


$lang['regmissing'] = 'Musíte vyplnit všechny údaje.';
$lang['reguexists'] = 'Uživatel se stejným jménem už je zaregistrován.';
$lang['regsuccess'] = 'Uživatelský účet byl vytvořen a heslo zasláno mailem.';
$lang['regsuccess2']= 'Uživatelský účet byl vytvořen.';
$lang['regmailfail']= 'Zdá se, že nastala chyba při posílání mailu s heslem. Zkuste kontaktovat správce.';
$lang['regbadmail'] = 'Zadaná mailová adresa není platná. Pokud si myslíte, že to je špatně, zkuste kontaktovat správce.';
$lang['regbadpass'] = 'Heslo nebylo zadáno dvakrát stejně, zkuste to prosím znovu.';
$lang['regpwmail']  = 'Vaše heslo do systému DokuWiki';
$lang['reghere']    = 'Nemáte uživatelský účet? Zřiďte si ho';

$lang['profna']       = 'Toto wiki neumožnuje změnu profilu';
$lang['profnochange'] = 'Žádné změny nebyly provedeny.';
$lang['profnoempty']  = 'Nelze zada prázdné jméno nebo email.';
$lang['profchanged']  = 'Uživatelský profil změněn.';

$lang['pwdforget'] = 'Zapoměli jste heslo? Nechte si zaslat nové';
$lang['resendna']  = 'Toto wiki neumožnuje zasílání nových hesel.';
$lang['resendpwd'] = 'Odesla nové heslo pro uživatele';
$lang['resendpwdmissing'] = 'Musíte vyplnit všechny položky.';
$lang['resendpwdnouser']  = 'Bohužel, takový uživatel v systému není.';
$lang['resendpwdsuccess'] = 'Vaše nové heslo bylo odesláno emailem.';

$lang['txt_upload']   = 'Vyberte soubor jako přílohu';
$lang['txt_filename'] = 'Wiki jméno (volitelné)';
$lang['txt_overwrt']  = 'Přepsat existující soubor';
$lang['lockedby']     = 'Právě zamknuto:';
$lang['lockexpire']   = 'Zámek vyprší:';
$lang['willexpire']   = 'Váš zámek pro editaci za chvíli vyprší.\nAbyste předešli konfliktům, stiskněte tlačítko Náhled a zámek se prodlouží.';

$lang['notsavedyet'] = 'Jsou tu neuložené změny, které budou ztraceny.\nChcete opravdu pokračovat?';
$lang['rssfailed']   = 'Nastala chyba při vytváření tohoto RSS: ';
$lang['nothingfound']= 'Nic nenalezeno.';

$lang['mediaselect'] = 'Výběr dokumentu';
$lang['fileupload']  = 'Nahrávání dokumentu';
$lang['uploadsucc']  = 'Přenos proběhl v pořádku';
$lang['uploadfail']  = 'Chyba při nahrávání. Možná kvůli špatně nastaveným právům?';
$lang['uploadwrong'] = 'Přiložení souboru s takovouto příponou není dovoleno.';
$lang['uploadexist'] = 'Soubor už existuje, necháme ho být.';
$lang['deletesucc']  = 'Soubor "%s" byl vymazán.';
$lang['deletefail']  = 'Soubor "%s" nelze vymazat - zkontrolujte oprávnění.';
$lang['mediainuse']  = 'Soubor "%s" nebyl vymazán - používá se.';
$lang['namespaces']  = 'Jmenné prostory';
$lang['mediafiles']  = 'Dostupné soubory';

$lang['js']['keepopen']    = 'Po vybrání souboru nechat okno otevřené';
$lang['js']['hidedetails'] = 'Skrýt detaily';
$lang['mediausage']  = 'K odkázání se na tento soubor použijte následující syntax:';
$lang['mediaview']   = 'Zobrazit původní soubor';
$lang['mediaroot']   = 'root';
$lang['mediaupload'] = 'Přiložit soubor do aktuálního jmenného prostoru. K vytvoření nových jmenných prostorů, přidejte jejich názvy na začátek wiki jména (oddělte dvojtečkou).';
$lang['mediaextchange'] = 'Přípona souboru byla změněna z .%s na .%s!';


$lang['reference']   = 'Odkazy na';
$lang['ref_inuse']   = 'Soubor nelze vymazat, jelikož ho využívají následující stránky:';
$lang['ref_hidden']  = 'Některé odkazy jsou na stránkách, kam nemáte právo přístupu';

$lang['hits']       = '- počet výskytů';
$lang['quickhits']  = 'Odpovídající stránky';
$lang['toc']        = 'Obsah';
$lang['current']    = 'aktuální';
$lang['yours']      = 'Vaše verze';
$lang['diff']       = 'zobrazit rozdíly vůči aktuální verzi';
$lang['line']       = 'Řádek';
$lang['breadcrumb'] = 'Historie';
$lang['youarehere'] = 'Umístění';
$lang['lastmod']    = 'Poslední úprava';
$lang['by']         = 'autor:';
$lang['deleted']    = 'odstraněno';
$lang['created']    = 'vytvořeno';
$lang['restored']   = 'stará verze byla obnovena';
$lang['summary']    = 'Komentář k úpravám';

$lang['mail_newpage'] = 'nová stránka:';
$lang['mail_changed'] = 'změna stránky:';

$lang['nosmblinks'] = 'Odkazování na sdílené prostředky Windows funguje jen v Internet Exploreru.\nPřesto tento odkaz můžete zkopírovat a vložit jinde.';

$lang['qb_alert']   = 'Vložte prosím text, který chcete formátovat.\nTen bude přidán na konec dokumentu.';
$lang['qb_bold']    = 'Tučně';
$lang['qb_italic']  = 'Kurzíva';
$lang['qb_underl']  = 'Podtržení';
$lang['qb_code']    = 'Neformátovat (zdrojový kód)';
$lang['qb_strike']  = 'Přeškrtnutý text';
$lang['qb_h1']      = 'Nadpis 1. úrovně';
$lang['qb_h2']      = 'Nadpis 2. úrovně';
$lang['qb_h3']      = 'Nadpis 3. úrovně';
$lang['qb_h4']      = 'Nadpis 4. úrovně';
$lang['qb_h5']      = 'Nadpis 5. úrovně';
$lang['qb_link']    = 'Interní odkaz';
$lang['qb_extlink'] = 'Externí odkaz';
$lang['qb_hr']      = 'Horizontální linka';
$lang['qb_ol']      = 'Číslovaný seznam';
$lang['qb_ul']      = 'Nečíslovaný seznam';
$lang['qb_media']   = 'Vložit obrázky nebo jiné soubory';
$lang['qb_sig']     = 'Vložit podpis';
$lang['qb_smileys'] = 'Emotikony';
$lang['qb_chars']   = 'Speciální znaky';

$lang['del_confirm']= 'Vymazat tuto položku?';
$lang['admin_register']= 'Přidat nového uživatele';

$lang['spell_start'] = 'Zkontrolovat pravopis';
$lang['spell_stop']  = 'Pokračovat v úpravách';
$lang['spell_wait']  = 'Prosím počkejte...';
$lang['spell_noerr'] = 'Bez chyb';
$lang['spell_nosug'] = 'Žádné návrhy';
$lang['spell_change']= 'Změnit';

$lang['metaedit']    = 'Upravit Metadata';
$lang['metasaveerr'] = 'Chyba při zápisu metadat';
$lang['metasaveok']  = 'Metadata uložena';
$lang['img_backto']  = 'Zpět na';
$lang['img_title']   = 'Titulek';
$lang['img_caption'] = 'Popis';
$lang['img_date']    = 'Datum';
$lang['img_fname']   = 'Jméno souboru';
$lang['img_fsize']   = 'Velikost';
$lang['img_artist']  = 'Autor fotografie';
$lang['img_copyr']   = 'Copyright';
$lang['img_format']  = 'Formát';
$lang['img_camera']  = 'Typ fotoaparátu';
$lang['img_keywords']= 'Klíčová slova';

$lang['subscribe_success']  = 'Uživatel %s je nyní přihlášen k odběru změn ve stránce %s';
$lang['subscribe_error']    = 'Chyba při zařazování uživatele %s do seznamu pro odběr změn ve stránce %s';
$lang['subscribe_noaddress']= 'K vašemu uživatelskému profilu chybí e-mailová adresa, takže vás nelze do seznamu pro odběr změn';
$lang['unsubscribe_success']= 'Uživatel %s byl odebrán ze seznamu pro odběr změn ve stránce %s';
$lang['unsubscribe_error']  = 'Chyba při odstraňování uživatele %s ze seznamu pro odběru změn ve stránce %s';

$lang['authmodfailed']   = 'Autentizace uživatelů je špatně nastavena. Informujte správce této wiki.';
$lang['authtempfail']    = 'Autentizace uživatelů je dočasně nedostupná. Pokud tento problém přetrvává, informujte správce této wiki.';

//Setup VIM: ex: et ts=2 enc=utf-8 :
