<?php
/**
 * danish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     koeppe <koeppe@kazur.dk>
 * @author     Jon Bendtsen <bendtsen@diku.dk>
 * @author     Lars Næsbye Christensen <larsnaesbye@stud.ku.dk>
 */
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

$lang['btn_edit']   = 'Rediger dette dokument';
$lang['btn_source'] = 'Vis kildekode';
$lang['btn_show']   = 'Vis dokument';
$lang['btn_create'] = 'Opret dette dokument';
$lang['btn_search'] = 'Søg';
$lang['btn_save']   = 'Gem';
$lang['btn_preview']= 'Forhåndsvisning';
$lang['btn_top']    = 'Tilbage til toppen';
$lang['btn_newer']  = '<< forrige side';
$lang['btn_older']  = 'næste side >>';
$lang['btn_revs']   = 'Gamle udgaver';
$lang['btn_recent'] = 'Nye ændringer';
$lang['btn_upload'] = 'Upload';
$lang['btn_cancel'] = 'Fortryd';
$lang['btn_index']  = 'Indeks';
$lang['btn_secedit']= 'Rediger';
$lang['btn_login']  = 'Log ind';
$lang['btn_logout'] = 'Log ud';
$lang['btn_admin']  = 'Admin';
$lang['btn_update'] = 'Opdater';
$lang['btn_delete'] = 'Slet';
$lang['btn_back']   = 'Tilbage';
$lang['btn_backlink']    = "Henvisninger bagud";
$lang['btn_backtomedia'] = 'Tilbage til valg af mediefil';
$lang['btn_subscribe']   = 'Abonner på ændringer';
$lang['btn_unsubscribe'] = 'Fjern abonnement på ændringer';
$lang['btn_profile']    = 'Opdater profil';
$lang['btn_reset']     = 'Nulstil';
$lang['btn_resendpwd'] = 'Send nyt password';

$lang['loggedinas'] = 'Logget på som';
$lang['user']       = 'Brugernavn';
$lang['pass']       = 'Password';
$lang['newpass']    = 'Nyt password';
$lang['oldpass']    = 'Bekræft gammelt password';
$lang['passchk']    = 'Gentag nyt password';
$lang['remember']   = 'Log automatisk på';
$lang['fullname']   = 'Navn';
$lang['email']      = 'E-mail';
$lang['register']   = 'Tilmeld';
$lang['profile']    = 'Brugerprofil';
$lang['badlogin']   = 'Forkert brugernavn eller password.';
$lang['minoredit']  = 'Mindre ændringer';

$lang['regmissing'] = 'Du skal udfylde alle felter.';
$lang['reguexists'] = 'Dette brugernavn er allerede i brug.';
$lang['regsuccess'] = 'Du er nu oprettet som bruger. Dit password bliver sendt til dig i en e-mail.';
$lang['regsuccess2']= 'Du er nu oprettet som bruger.';
$lang['regmailfail']= 'Dit password blev ikke sendt. Kontakt venligst administratoren.';
$lang['regbadmail'] = 'E-mail-adressen er ugyldig. Kontakt venligst administratoren, hvis du mener dette er en fejl.';
$lang['regbadpass'] = 'De to passwords er ikke ens, vær venlig at prøve igen.';
$lang['regpwmail']  = 'Dit DokuWiki password';
$lang['reghere']    = 'Opret en DokuWiki-konto her';

$lang['profna']       = 'Denne wiki understøtter ikke ændring af profiler';
$lang['profnochange'] = 'Ingen ændringer, intet modificeret.';
$lang['profnoempty']  = 'Tomt navn eller e-mail adresse er ikke tilladt.';
$lang['profchanged']  = 'Brugerprofil opdateret korrekt.';

$lang['pwdforget'] = 'Glemt dit password? F√• et nyt';
$lang['resendna']  = 'Denne wiki underst√∏tter ikke udsendelse af nyt password.';
$lang['resendpwd'] = 'Send nyt password for';
$lang['resendpwdmissing'] = 'Du skal udfylde alle felter.';
$lang['resendpwdnouser']  = 'Vi kan ikke finde denne bruger i vores database.';
$lang['resendpwdsuccess'] = 'Dit nye password er blevet sendt med e-mail.';

$lang['txt_upload']   = 'Vælg den fil der skal uploades';
$lang['txt_filename'] = 'Indtast wikinavn (valgfrit)';
$lang['txt_overwrt']  = 'Overskriv eksisterende fil';
$lang['lockedby']     = 'Midlertidig låst af';
$lang['lockexpire']   = 'Lås udløber kl.';
$lang['willexpire']   = 'Din lås på dette dokument udløber om et minut.\nTryk på '.$lang['btn_preview'].'-knappen for at undgå konflikter.';

$lang['notsavedyet'] = 'Der er lavet ændringer i dokumentet, hvis du fortsætter vil ændringerne gå tabt.\nØnsker du at fortsætte?';
$lang['rssfailed']   = 'Der opstod en fejl ved indhentning af: ';
$lang['nothingfound']= 'Søgningen gav intet resultat.';

$lang['mediaselect'] = 'Vælg mediefil';
$lang['fileupload']  = 'Upload mediefil';
$lang['uploadsucc']  = 'Upload var en succes';
$lang['uploadfail']  = 'Upload fejlede. Der er muligvis problemer med rettighederne';
$lang['uploadwrong'] = 'Upload afvist. Filtypen er ikke tilladt';
$lang['uploadexist'] = 'Filen eksisterer allerede.';
$lang['deletesucc']  = 'Filen "%s" er blevet slettet.';
$lang['deletefail']  = '"%s" kunne ikke slettes - check rettighederne.';
$lang['mediainuse']  = 'Filen "%s" er ikke slettet - den er stadig i brug.';
$lang['namespaces']  = 'Navnerum';
$lang['mediafiles']  = 'Tilgængelige filer i';

$lang['reference']   = 'Henvisning til';
$lang['ref_inuse']   = 'Filen kan ikke slettes, da den stadig er i brug på følgende sider:';
$lang['ref_hidden']  = 'Nogle henvisninger er i dokumenter du ikke har læserettigheder til';

$lang['hits']       = 'Hits';
$lang['quickhits']  = 'Tilsvarende dokumentnavne';
$lang['toc']        = 'Indholdsfortegnelse';
$lang['current']    = 'nuværende';
$lang['yours']      = 'Din version';
$lang['diff']       = 'vis forskelle i forhold til den nuværende udgave';
$lang['line']       = 'Linje';
$lang['breadcrumb'] = 'Sti';
$lang['youarehere'] = 'Du er her';
$lang['lastmod']    = 'Sidst ændret';
$lang['by']         = 'af';
$lang['deleted']    = 'slettet';
$lang['created']    = 'oprettet';
$lang['restored']   = 'gammel udgave reetableret';
$lang['summary']    = 'Redigerings resume';

$lang['mail_newpage'] = 'dokument tilføjet:';
$lang['mail_changed'] = 'dokument ændret:';

$lang['nosmblinks'] = 'Henvisninger til Windows shares virker kun i Microsoft Internet Explorer.\nDu kan stadig kopiere og indsætte linket.';

$lang['qb_alert']   = 'Skriv den tekst du ønsker at formatere.\nDen vil blive tilføjet i slutningen af dokumentet.';
$lang['qb_bold']    = 'Fed';
$lang['qb_italic']  = 'Kursiv';
$lang['qb_underl']  = 'Understregning';
$lang['qb_code']    = 'Skrivemaskine tekst';
$lang['qb_strike']  = 'Gennemstregning';
$lang['qb_h1']      = 'Niveau 1 overskrift';
$lang['qb_h2']      = 'Niveau 2 overskrift';
$lang['qb_h3']      = 'Niveau 3 overskrift';
$lang['qb_h4']      = 'Niveau 4 overskrift';
$lang['qb_h5']      = 'Niveau 5 overskrift';
$lang['qb_link']    = 'Intern henvisning';
$lang['qb_extlink'] = 'Ekstern henvisning';
$lang['qb_hr']      = 'Vandret linje';
$lang['qb_ol']      = 'Nummereret liste';
$lang['qb_ul']      = 'Unummereret liste';
$lang['qb_media']   = 'Tilføj billeder og andre filer';
$lang['qb_sig']     = 'Indsæt signatur';
$lang['qb_smileys'] = 'Smileys';
$lang['qb_chars']   = 'Specialtegn';

$lang['del_confirm']= 'Slet valgte post(er)?';
$lang['admin_register']= 'Tilføj ny bruger';

$lang['spell_start']= 'Stavekontrol';
$lang['spell_stop'] = 'Fortsæt redigering';
$lang['spell_wait'] = 'Vent et øjeblik...';
$lang['spell_noerr']= 'Der blev ikke fundet nogle fejl';
$lang['spell_nosug']= 'Ingen forslag fundet';
$lang['spell_change']= 'Ændr';

$lang['metaedit']    = 'Rediger metadata';
$lang['metasaveerr'] = 'Skrivning af metadata fejlede';
$lang['metasaveok']  = 'Metadata gemt';
$lang['img_backto']  = 'Tilbage til';
$lang['img_title']   = 'Titel';
$lang['img_caption'] = 'Billedtekst';
$lang['img_date']    = 'Dato';
$lang['img_fname']   = 'Filnavn';
$lang['img_fsize']   = 'Størrelse';
$lang['img_artist']  = 'Fotograf';
$lang['img_copyr']   = 'Copyright';
$lang['img_format']  = 'Format';
$lang['img_camera']  = 'Kamera';
$lang['img_keywords']= 'Emneord';

$lang['subscribe_success']  = 'Tilføjet %s til abonnentliste for %s';
$lang['subscribe_error']    = 'Fejl ved tilføjelse af %s til abonnentliste for %s';
$lang['subscribe_noaddress']= 'Ingen adresse knyttet til dit login, du kan ikke tilføjes til abonnentlisten';
$lang['unsubscribe_success']= 'Fjernet %s fra abonnentliste for %s';
$lang['unsubscribe_error']  = 'Fejl ved fjernelse af %s fra abonnentliste for %s';

+/* auth.class language support */
+$lang['authmodfailed']   = 'Fejl i brugervalideringens konfiguration. Kontakt venligst wikiens administrator.';
+$lang['authtempfail']    = 'Brugervalidering er midlertidigt ude af drift. Hvis dette er vedvarende, kontakt venligst wikiens administrator.';

//Setup VIM: ex: et ts=2 enc=utf-8 :
