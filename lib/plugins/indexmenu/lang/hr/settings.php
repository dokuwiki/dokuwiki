<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Davor Turkalj <turki.bsc@gmail.com>
 */
$lang['checkupdate']           = 'Periodička provjera za nadogradnje.';
$lang['only_admins']           = 'Dopusti indexmenu sintaksu samo administratorima.<br>Zapamtite da stranice uređivane od strane ostalih korisnika će izgubiti sva indexmenu stabla.';
$lang['aclcache']              = 'Optimiraj indexmenu priručnu pohranu za ACL ( radi samo s korijenskim imenskim prostorima).<br>Odabir utječe samo na prikaz čvorova, ne i na autorizacije.<ul><li>None: standardno. Ovo je brža metoda i ne kreira nove priručne datoteke za pohranu, ali čvorovi s zabranom pristupa će biti vidljivi neovlaštenim korisnicima. Preporučeno ako ne koristite ACL ili Vam nije bitno kako će stablo biti prikazano.<li>User: po korisniku. Sporija metoda i kreira dosta priručnih datoteka, ali uvijek ispravno ne prikazuje nedozvoljene stranice. Preporuča se kada u ACL listi se koriste korisnici.<li>Groups: članstvo po grupama. Dobar kompromis prethodnih dviju metoda, ali može dovesti do krivog prikaza ako korisnik je izravno blokiran, a ima dopuštenje preko grupe. Preporuča se kada se za autoriziranje u ACL koriste samo grupe.</ul>';
$lang['headpage']              = 'Glavna strana: stranica iz koje se dohvaća naziv i veza prema imenskom prostoru.<br>Može biti jedna od slijedećih vrijednosti:<ul><li>Globalno definirana početna stranica.<li>Stranica s istim imenom imenskog prostora i nalazi se unutar njega.<li>Stranica s istim imenom imenskog prostora i nalazi se pored njega u istom novou.<li>Posebno definiran naziv stranice.<li>Zarezom odvojena lista naziva stranica.</ul>';
$lang['hide_headpage']         = 'Sakrij glavne stranice.';
$lang['page_index']            = 'Stranica koja zamjenjuje glavno kazalo DokuWiki-a. Napravite ju i ubacite indexmenu kod u nju. Korisite <code>id#random</code> ako već imate indexmenu traku sa strane s navbar opcijom. Moj prijedlog je <code>{{indexmenu>..|js navbar nocookie id#random}}</code>.';
$lang['empty_msg']             = 'Poruka koja se prikazuje kada je stablo prazno. Koristi Dokuwiki sintaksu, a ne HTML kod. <code>{{ns}}</code> varijabla je kratica za tekući imenski prostor.';
$lang['skip_index']            = 'Oznake imenskih prostora koje ne treba prikazati. Korisite format regularnih izraza. Primjer: <code>/(sidebars|private:myns)/</code>';
$lang['skip_file']             = 'Oznake stranica koje ne treba prikazati. Korisite format regularnih izraza. Primjer: <code>/(:start$|^public:newstart$)/</code>';
$lang['show_sort']             = 'Prikaži administratorima indexmenu sort broj kao poruku na vrhu stranice';
$lang['themes_url']            = 'Dohvati js teme s ove http url adrese.';
$lang['be_repo']               = 'Neka drugi mogu preuzeti teme s vašeg site-a.';
$lang['defaultoptions']        = 'Lista Indeks-meni opcija odvojena razmacima. Ove opcije biti će primijenjene na svaki indeksni izbornik i mogu biti uklonjene sa reverse komandom u sintaksi dodatka';
