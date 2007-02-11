<?php
/**
 * Hungarian language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ziegler Gábor <gziegler@freemail.hu>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
$lang['doublequoteopening']  = '„';//&bdquo;
$lang['doublequoteclosing']  = '“';//&ldquo;
$lang['singlequoteopening']  = '‚';//&sbquo;
$lang['singlequoteclosing']  = '‘';//&lsquo;

$lang['btn_edit']   = 'Oldal szerkesztése';
$lang['btn_source'] = 'Oldalforrás megtekintése';
$lang['btn_show']   = 'Oldal megtekintése';
$lang['btn_create'] = 'Oldal létrehozása';
$lang['btn_search'] = 'Megtekintés';
$lang['btn_save']   = 'Mentés';
$lang['btn_preview']= 'Előnézet';
$lang['btn_top']    = 'Vissza a tetejére';
$lang['btn_revs']   = 'Előző változatok';
$lang['btn_recent'] = 'Legfrissebb változások';
$lang['btn_upload'] = 'Feltöltés';
$lang['btn_cancel'] = 'Mégsem';
$lang['btn_index']  = 'Áttekintés';
$lang['btn_secedit']= 'Szerkesztés';
$lang['btn_login']  = 'Bejelentkezés';
$lang['btn_logout'] = 'Kijelentkezés';

$lang['loggedinas'] = 'Belépett felhasználó: ';
$lang['user']       = 'Azonosító';
$lang['pass']       = 'Jelszó';
$lang['remember']   = 'Emlékezz rám';
$lang['fullname']   = 'Teljes név';
$lang['email']      = 'E-Mail';
$lang['register']   = 'Regisztráció';
$lang['badlogin']   = 'Sajnáljuk, az azonsító, vagy a jelszó nem jó.';

$lang['regmissing'] = 'Sajnáljuk, az összes mezőt ki kell töltened.';
$lang['reguexists'] = 'Sajnáljuk, ilyen azonosítójú felhasználónk már van.';
$lang['regsuccess'] = 'A felhasználóiazonosítót létrehoztuk. A jelszót postáztuk..';
$lang['regmailfail']= 'Úgy tűnik hiba történt a jelszó postázásáa során. Kérjük lépj kapcsolatba a rendszergazdával!!';
$lang['regbadmail'] = 'A megadott e-mail cím érvénytelennek tűnik. Ha az gondolod ez hiba, lépjkapcsolatba rendszergazdával';
$lang['regpwmail']  = 'A DokuWiki jelszavad';
$lang['reghere']    = 'Még nincs azonosítód? Hát kérj egyet';

$lang['txt_upload']   = 'Válaszd ki a feltöltendő fájlt';
$lang['txt_filename'] = 'Add meg a wikineved (elhagyható)';
$lang['lockedby']     = 'Jelenleg zárolta';
$lang['lockexpire']   = 'A zárolás lejár';
$lang['willexpire']   = 'Az oldal zárolásod szerkesztéshez körülbelül egy percen belül lejár.\nAz ütközések elkerülése végett használd az előnézet gombot a zárolási időzítés frissítéséhez.';

$lang['notsavedyet'] = 'Elmentetlen változások vannak, amelyek el fognak veszni.\nTényleg ezt akarod?';
$lang['rssfailed']   = 'Hiba történt ennek a betöltésekor: ';
$lang['nothingfound']= 'Semmit nem találtam.';

$lang['mediaselect'] = 'Médiafájl kiválasztása';
$lang['fileupload']  = 'Médiafájl felöltése';
$lang['uploadsucc']  = 'A feltöltés sikerült';
$lang['uploadfail']  = 'A feltöltés nem sikerült. Talán rosszak a jogosultságok?';
$lang['uploadwrong'] = 'A feltölés megtagadva. Ez a fájl kiterjesztés tiltott.';
$lang['namespaces']  = 'Névtér';
$lang['mediafiles']  = 'Elérhető fájlok itt:';

$lang['hits']       = 'Találatok';
$lang['quickhits']  = 'Illeszkedő oldalnevek';
$lang['toc']        = 'Tartalomjegyzék';
$lang['current']    = 'aktuális';
$lang['yours']      = 'A te változatod';
$lang['diff']       = 'a különbségeket mutatja az aktuális változathoz képest';
$lang['line']       = 'sorszám';
$lang['breadcrumb'] = 'Nyomvonal';
$lang['lastmod']    = 'Utolsó módosítás';
$lang['by']         = 'szerkesztette:'; 
  // Note: the Hungarian translation for 'by' is 'által', which is used 
  // usually AFTER the word (contrary to English), furthermore, it is very
  // awkward usage as a preposition in the Hungarian language.
  // IIRC this 'by' is used only in "inc/html.php", where the context is:
  //  if($INFO['editor']){
  //    print ' '.$lang['by'].' ';
  //    print $INFO['editor'];
  //  }
  // For THAT language context the translation is correct
$lang['deleted']    = 'eltávolítva';
$lang['created']    = 'létrehozva';
$lang['restored']   = 'az előző változat helyreállítva';
$lang['summary']    = 'A változások összefoglalása';

$lang['mail_newpage'] = 'oldalak hozzáadva:';
$lang['mail_changed'] = 'oldalak megváltoztatva:';

$lang['nosmblinks'] = 'A Windows megosztott könyvtárak kereszthivatkozása csak  Microsoft Internet Explorerben működik közvetlenül.\nA hivatkozást másolni és beszúrni ettől fügetlenül mndig tudod.';

$lang['qb_alert']   = 'Írd be a formázandó szöveget.\nEz a dokumentum szövegének végéhez fog hozzáadódni.';
$lang['qb_bold']    = 'Félkövér szöveg';
$lang['qb_italic']  = 'Dőlt szöveg';
$lang['qb_underl']  = 'Aláhúzott szöveg';
$lang['qb_code']    = 'Forráskód szöveg';
$lang['qb_h1']      = '1. színtű címsor';
$lang['qb_h2']      = '2. színtű címsor';
$lang['qb_h3']      = '3. színtű címsor';
$lang['qb_h4']      = '4. színtű címsor';
$lang['qb_h5']      = '5. színtű címsor';
$lang['qb_link']    = 'Belső hivatkozás';
$lang['qb_extlink'] = 'Külső hivatkozás';
$lang['qb_hr']      = 'Vízszintes elválasztó vonal';
$lang['qb_ol']      = 'Sorszámozott lista elem';
$lang['qb_ul']      = 'Bajuszos lista elem';
$lang['qb_media']   = 'Képek és más fájlok hozzáadása';
$lang['qb_sig']     = 'Aláírás beszúrása';

//Setup VIM: ex: et ts=2 enc=utf-8 :
