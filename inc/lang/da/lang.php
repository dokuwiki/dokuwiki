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
$lang['doublequoteopening']  = '„';//&bdquo;
$lang['doublequoteclosing']  = '“';//&ldquo;
$lang['singlequoteopening']  = '‚';//&sbquo;
$lang['singlequoteclosing']  = '‘';//&lsquo;

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
$lang['btn_draft']    = 'Rediger kladde';
$lang['btn_recover']  = 'Gendan kladde';
$lang['btn_draftdel'] = 'Slet kladde';

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
$lang['draftdate']  = 'Kladde automatisk gemt d.';

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

$lang['pwdforget'] = 'Glemt dit password? Få et nyt';
$lang['resendna']  = 'Denne wiki understøtter ikke udsendelse af nyt password.';
$lang['resendpwd'] = 'Send nyt password for';
$lang['resendpwdmissing'] = 'Du skal udfylde alle felter.';
$lang['resendpwdnouser']  = 'Vi kan ikke finde denne bruger i vores database.';
$lang['resendpwdbadauth'] = 'Beklager, denne autoriseringskode er ikke gyldig. Kontroller venligst at du benyttede det fulde link til bekræftelse.';
$lang['resendpwdconfirm'] = 'Et link med bekræftelse er blevet sendt med email.';
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
$lang['uploadbadcontent'] = 'Upload indhold matchede ikke %s fil-endelsen.';
$lang['uploadspam']  = 'Upload blev blokeret af spam sortlisten.';
$lang['uploadxss']   = 'Upload blev blokeret p� grund af mulig skadeligt indhold.';
$lang['deletesucc']  = 'Filen "%s" er blevet slettet.';
$lang['deletefail']  = '"%s" kunne ikke slettes - check rettighederne.';
$lang['mediainuse']  = 'Filen "%s" er ikke slettet - den er stadig i brug.';
$lang['namespaces']  = 'Navnerum';
$lang['mediafiles']  = 'Tilgængelige filer i';

$lang['js']['keepopen']    = 'Hold vindue åbent ved valg';
$lang['js']['hidedetails'] = 'Skjul detaljer';
$lang['mediausage']  = 'Brug den følgende syntaks til at henvise til denne fil:';
$lang['mediaview']   = 'Vis oprindelig fil';
$lang['mediaroot']   = 'rod';
$lang['mediaupload'] = 'Upload en fil til det nuværende navnerum her. For at oprette under-navnerum, tilføj dem til "Upload som" filnavnet, adskilt af kolontegn.';
$lang['mediaextchange'] = 'Filudvidelse ændret fra .%s til .%s!';

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
$lang['external_edit'] = 'ekstern redigering';
$lang['summary']    = 'Redigerings resume';

$lang['mail_newpage'] = 'dokument tilføjet:';
$lang['mail_changed'] = 'dokument ændret:';
$lang['mail_new_user'] = 'Ny bruger';
$lang['mail_upload'] = 'fil uploadet:';
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

/* auth.class language support */
$lang['authmodfailed']   = 'Fejl i brugervalideringens konfiguration. Kontakt venligst wikiens administrator.';
$lang['authtempfail']    = 'Brugervalidering er midlertidigt ude af drift. Hvis dette er vedvarende, kontakt venligst wikiens administrator.';

/* installer strings */
$lang['i_chooselang'] = 'Vælg dit sprog';
$lang['i_installer']  = 'DokuWiki Installer';
$lang['i_wikiname']   = 'Wiki Navn';
$lang['i_enableacl']  = 'Brug ACL (foreslået)';
$lang['i_superuser']  = 'Superbruger';
$lang['i_problems']   = 'Installeren fandt nogle problemer, vist nedenunder. Du kan ikke fortsætte før du har rettet dem.';
$lang['i_modified']   = 'Af sikkerheds hensyn vil dette script kun virke på en ny og umodificeret Dokuwiki installation.
                         Du burde enten gen-udpakke filerne fra den downloaded pakke eller tjekke den fuldstændige
                         <a href="http://wiki.splitbrain.org/wiki:install">DokuWiki installations instruktioner</a>';
$lang['i_funcna']     = 'PHP funtionen <code>%s</code> er ikke tilgængelig. Måske har din udbyder slået det fra af en eller anden grund?';
$lang['i_phpver']     = 'Din PHP version <code>%s</code> er mindre en den nødvendige <code>%s</code>. Du er nÃ¸d til at opgradere din PHP installation.';
$lang['i_permfail']   = 'DokuWiki kan ikke skrive til <code>%s</code>. Du er nød til at rette tilladelses indstillingerne for denne mappe!';
$lang['i_confexists'] = '<code>%s</code> eksisterer allerede';
$lang['i_writeerr']   = 'Kunne ikke oprette <code>%s</code>. Du bliver nød til at tjekke mappe/fil- tilladelserne og oprette filen manuelt.';
$lang['i_badhash']    = 'uigenkendelig eller modificeret dokuwiki.php (hash=<code>%s</code>)';
$lang['i_badval']     = '<code>%s</code> - ulovlig eller tom vÃ¦rdi';
$lang['i_success']    = 'Konfigurationen fulførtedes med success.  Du kan nu slette install.php filen. Forsæt til
                        <a href="doku.php">din nye DokuWiki</a>.';
$lang['i_failure']    = 'Nogle fejl forekom mens konfigurations filerne skulle skrives. Du er mulighvis nød til at fixe dem manuelt før
                         du kan bruge <a href="doku.php">din nye DokuWiki</a>.';
$lang['i_policy']     = 'Begyndende ACL politik';
$lang['i_pol0']       = 'Åben Wiki (alle kan læse, skrive og uploade)';
$lang['i_pol1']       = 'Offentlig Wiki (alle kan læse, kun registrerede brugere kan skrive og uploade)';
$lang['i_pol2']       = 'Lukket Wiki (kun for registerede brugere kan læse, skrive og uploade)';
$lang['i_retry']      = 'Forsøg igen';

//Setup VIM: ex: et ts=2 enc=utf-8 :
