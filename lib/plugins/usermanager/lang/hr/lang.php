<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Davor Turkalj <turki.bsc@gmail.com>
 */
$lang['menu']                  = 'Upravitelj korisnicima';
$lang['noauth']                = '(korisnička prijava nije dostupna)';
$lang['nosupport']             = '(upravljanje korisnikom nije podržano)';
$lang['badauth']               = 'pogrešan mehanizam prijave';
$lang['user_id']               = 'Korisnik';
$lang['user_pass']             = 'Lozinka';
$lang['user_name']             = 'Stvarno ime';
$lang['user_mail']             = 'E-pošta';
$lang['user_groups']           = 'Grupe';
$lang['field']                 = 'Polje';
$lang['value']                 = 'Vrijednost';
$lang['add']                   = 'Dodaj';
$lang['delete']                = 'Obriši';
$lang['delete_selected']       = 'Obriši odabrano';
$lang['edit']                  = 'Uredi';
$lang['edit_prompt']           = 'Uredi ovog korisnika';
$lang['modify']                = 'Pohrani promjene';
$lang['search']                = 'Potraži';
$lang['search_prompt']         = 'Izvedi potragu';
$lang['clear']                 = 'Obriši filtar potrage';
$lang['filter']                = 'Filtar';
$lang['export_all']            = 'Izvezi sve korisnike (CSV)';
$lang['export_filtered']       = 'Izvezi filtriranu listu korisnika (CSV)';
$lang['import']                = 'Unos novih korisnika';
$lang['line']                  = 'Linija br.';
$lang['error']                 = 'Poruka o grešci';
$lang['summary']               = 'Prikaz korisnika %1$d-%2$d od %3$d nađenih. Ukupno %4$d korisnika.';
$lang['nonefound']             = 'Nema korisnika koji odgovaraju filtru.Ukupno %d korisnika.';
$lang['delete_ok']             = '%d korisnika obrisano';
$lang['delete_fail']           = '%d neuspjelih brisanja.';
$lang['update_ok']             = 'Korisnik uspješno izmijenjen';
$lang['update_fail']           = 'Neuspjela izmjena korisnika';
$lang['update_exists']         = 'Promjena korisničkog imena neuspješna, traženo ime (%s) već postoji (ostale izmjene biti će primijenjene).';
$lang['start']                 = 'početni';
$lang['prev']                  = 'prethodni';
$lang['next']                  = 'slijedeći';
$lang['last']                  = 'zadnji';
$lang['edit_usermissing']      = 'Odabrani korisnik nije nađen, traženo korisničko ime vjerojatno je obrisano i promijenjeno negdje drugdje.';
$lang['user_notify']           = 'Obavijesti korisnika';
$lang['note_notify']           = 'Obavijest korisniku biti će poslana samo ako je upisana nova lozinka.';
$lang['note_group']            = 'Novi korisnik biti će dodijeljen u podrazumijevanu grupu (%s) ako grupa nije specificirana.';
$lang['note_pass']             = 'Lozinka će biti generirana ako se polje ostavi prazno i obavješćivanje korisnika je omogućeno.';
$lang['add_ok']                = 'Korisnik uspješno dodan';
$lang['add_fail']              = 'Neuspješno dodavanje korisnika';
$lang['notify_ok']             = 'Poslana obavijest korisniku';
$lang['notify_fail']           = 'Obavijest korisniku ne može biti poslana';
$lang['import_userlistcsv']    = 'Datoteka s popisom korisnika (CSV):';
$lang['import_header']         = 'Zadnje greške pri uvozu';
$lang['import_success_count']  = 'Uvoz korisnika: %d korisnika nađeno, %d uspješno uvezeno';
$lang['import_failure_count']  = 'Uvoz korisnika: %d neuspješno. Greške su navedene niže.';
$lang['import_error_fields']   = 'Nedovoljan broj polja, nađeno %d, potrebno 4.';
$lang['import_error_baduserid'] = 'Nedostaje korisničko ime';
$lang['import_error_badname']  = 'Krivo ime';
$lang['import_error_badmail']  = 'Kriva adresa e-pošte';
$lang['import_error_upload']   = 'Uvoz neuspješan. CSV datoteka ne može biti učitana ili je prazna.';
$lang['import_error_readfail'] = 'Uvoz neuspješan. Ne mogu pročitati učitanu datoteku.';
$lang['import_error_create']   = 'Ne mogu kreirati korisnika';
$lang['import_notify_fail']    = 'Obavijest uvezenom korisniku %s nije moguće poslati na adresu e-pošte %s.';
$lang['import_downloadfailures'] = 'Preuzmi  greške kao CSV za ispravak';
$lang['addUser_error_missing_pass'] = 'Molim ili postavite lozinku ili aktivirajte obavijest korisniku za omogućavanje generiranje lozinke.';
$lang['addUser_error_pass_not_identical'] = 'Unesene lozinke nisu identične.';
$lang['addUser_error_modPass_disabled'] = 'Izmjena lozinke je trenutno onemogućena.';
$lang['addUser_error_name_missing'] = 'Molim unesite ime novog korisnika.';
$lang['addUser_error_modName_disabled'] = 'Izmjena imena je trenutno onemogućena.';
$lang['addUser_error_mail_missing'] = 'Molim unesite adresu epošte za novog korisnika.';
$lang['addUser_error_modMail_disabled'] = 'Izmjena adrese epošte je trenutno onemogućena.';
$lang['addUser_error_create_event_failed'] = 'Dodatak je spriječio dodavanje novog korisnika. Pogledajte eventualne ostale poruke za više informacija.';
