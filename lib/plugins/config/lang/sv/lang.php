<?php
/**
 * swedish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Per Foreby <per@foreby.se>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Hantera inställningar';

$lang['error']      = 'Inställningarna uppdaterades inte på grund av ett felaktigt värde. Titta igenom dina ändringar och försök sedan spara igen.
                       <br />Felaktiga värden är omgivna av en röd ram.';
$lang['updated']    = 'Inställningarna uppdaterade.';
$lang['nochoice']   = '(inga andra val tillgängliga)';
$lang['locked']     = 'Filen med inställningar kan inte uppdateras. Om det inte är meningen att det ska vara så, <br />
                       kontrollera att filen med lokala inställningar har rätt namn och filskydd.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Hantera inställningar'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'Inställningar för DokuWiki';
$lang['_header_plugin'] = 'Inställningar för insticksmoduler';
$lang['_header_template'] = 'Inställningar för mallar';
$lang['_header_undefined'] = 'Odefinierade inställningar';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Grundläggande inställningar';
$lang['_display'] = 'Inställningar för presentation';
$lang['_authentication'] = 'Inställningar för autentisering';
$lang['_anti_spam'] = 'Inställningar för anti-spam';
$lang['_editing'] = 'Inställningar för redigering';
$lang['_links'] = 'Inställningar för länkar';
$lang['_media'] = 'Inställningar för medier';
$lang['_advanced'] = 'Avancerade inställningar';
$lang['_network'] = 'Nätverksinställningar';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = '(inställningar för insticksmodul)';
$lang['_template_sufix'] = '(inställningar för mall)';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Ingen inställningsmetadata.';
$lang['_msg_setting_no_class'] = 'Ingen inställningsklass.';
$lang['_msg_setting_no_default'] = 'Inget standardvärde.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Filskydd för nya filer';
$lang['dmode']       = 'Filskydd för nya kataloger';
$lang['lang']        = 'Språk';
$lang['basedir']     = 'Grundkatalog';
$lang['baseurl']     = 'Grund-webbadress';
$lang['savedir']     = 'Katalog för att spara data';
$lang['start']       = 'Startsidans namn';
$lang['title']       = 'Wikins namn';
$lang['template']    = 'Mall';
$lang['fullpath']    = 'Visa fullständig sökväg i sidfoten';
$lang['recent']      = 'Antal poster under "Nyligen ändrat"';
$lang['breadcrumbs'] = 'Antal spår';
$lang['youarehere']  = 'Hierarkiska spår';
$lang['typography']  = 'Aktivera typografiska ersättningar';
$lang['htmlok']      = 'Tillåt inbäddad HTML';
$lang['phpok']       = 'Tillåt inbäddad PHP';
$lang['dformat']     = 'Datumformat (se PHP:s <a href="http://www.php.net/date">date</a>-funktion)';
$lang['signature']   = 'Signatur';
$lang['toptoclevel'] = 'Toppnivå för innehållsförteckning';
$lang['maxtoclevel'] = 'Maximal nivå för innehållsförteckning';
$lang['maxseclevel'] = 'Maximal nivå för redigering av rubriker';
$lang['camelcase']   = 'Använd CamelCase för länkar';
$lang['deaccent']    = 'Rena sidnamn';
$lang['useheading']  = 'Använda första rubriken som sidnamn';
$lang['refcheck']    = 'Kontrollera referenser till mediafiler';
$lang['refshow']     = 'Antal mediareferenser som ska visas';
$lang['allowdebug']  = 'Tillåt felsökning <b>stäng av om det inte behövs!</b>';

$lang['usewordblock']= 'Blockera spam baserat på ordlista';
$lang['indexdelay']  = 'Tidsfördröjning före indexering (sek)';
$lang['relnofollow'] = 'Använd rel="nofollow" för externa länkar';
$lang['mailguard']   = 'Koda e-postadresser';
$lang['iexssprotect']= 'Kontrollera om uppladdade filer innehåller eventuellt skadlig JavaScript eller HTML-kod';

/* Authentication Options */
$lang['useacl']      = 'Använd behörighetslista (ACL)';
$lang['autopasswd']  = 'Autogenerera lösenord';
$lang['authtype']    = 'System för autentisering';
$lang['passcrypt']   = 'Metod för kryptering av lösenord';
$lang['defaultgroup']= 'Förvald grupp';
$lang['superuser']   = 'Huvudadministratör - en grupp eller en användare med full tillgång till alla sidor och funktioner, oavsett behörighetsinställningars';
$lang['manager']     = 'Administratör - en grupp eller användare med tillgång till vissa administrativa funktioner.';
$lang['profileconfirm'] = 'Bekräfta ändringarna i profilen med lösenordet';
$lang['disableactions'] = 'Stäng av funktioner i DokuWiki';
$lang['disableactions_check'] = 'Kontroll';
$lang['disableactions_subscription'] = 'Prenumerera/Säg upp prenumeration';
$lang['disableactions_wikicode'] = 'Visa källkod/Exportera råtext';
$lang['disableactions_other'] = 'Andra funktioner (kommaseparerade)';
$lang['sneaky_index'] = 'Som standard visar DokuWiki alla namnrymder på indexsidan. Genom att aktivera det här valet döljer man namnrymder som användaren inte har behörighet att läsa. Det kan leda till att man döljer åtkomliga undernamnrymder, och gör indexet oanvändbart med vissa ACL-inställningar.';

/* Advanced Options */
$lang['updatecheck'] = 'Kontrollera uppdateringar och säkerhetsvarningar? DokuWiki behöver kontakta splitbrain.org för den här funktionen.';
$lang['userewrite']  = 'Använd rena webbadresser';
$lang['useslash']    = 'Använd snedstreck för att separera namnrymder i webbadresser';
$lang['usedraft']    = 'Spara utkast automatiskt under redigering';
$lang['sepchar']     = 'Ersätt blanktecken i webbadresser med';
$lang['canonical']   = 'Använd fullständiga webbadresser';
$lang['autoplural']  = 'Leta efter pluralformer av länkar';
$lang['compression'] = 'Metod för komprimering av gamla versioner';
$lang['cachetime']   = 'Max livslängd för cache (sek)';
$lang['locktime']    = 'Max livslängd för fillåsning (sek)';
$lang['fetchsize']   = 'Max storlek (bytes) som fetch.php får ladda ned  externt';
$lang['notify']      = 'Skicka meddelande om ändrade sidor till den här e-postadressen';
$lang['registernotify'] = 'Skicka meddelande om nyregistrerade användare till en här e-postadressen';
$lang['mailfrom']    = 'Avsändaradress i automatiska e-postmeddelanden';
$lang['gzip_output'] = 'Använd gzip Content-Encoding för xhtml';
$lang['gdlib']       = 'Version av GD-biblioteket';
$lang['im_convert']  = 'Sökväg till ImageMagicks konverteringsverktyg';
$lang['jpg_quality'] = 'Kvalitet för JPG-komprimering (0-100)';
$lang['spellchecker']= 'Aktivera stavningskontroll';
$lang['subscribers'] = 'Aktivera stöd för prenumeration på ändringar';
$lang['compress']    = 'Komprimera CSS och javascript';
$lang['hidepages']   = 'Dölj matchande sidor (reguljära uttryck)';
$lang['send404']     = 'Skicka "HTTP 404/Page Not Found" för sidor som inte finns';
$lang['sitemap']     = 'Skapa Google sitemap (dagar)';
$lang['broken_iua']  = 'Är funktionen ignore_user_abort trasig på ditt system? Det kan i så fall leda till att indexering av sökningar inte fungerar. Detta är ett känt problem med IIS+PHP/CGI. Se <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> för mer info.';

$lang['rss_type']    = 'Typ av XML-flöde';
$lang['rss_linkto']  = 'XML-flöde pekar på';
$lang['rss_update']  = 'Uppdateringsintervall för XML-flöde (sek)';
$lang['recent_days'] = 'Hur många ändringar som ska sparas (dagar)';
$lang['rss_show_summary'] = 'XML-flöde visar sammanfattning i rubriken';

/* Target options */
$lang['target____wiki']      = 'Målfönster för interna länkar';
$lang['target____interwiki'] = 'Målfönster för interwiki-länkar';
$lang['target____extern']    = 'Målfönster för externa länkar';
$lang['target____media']     = 'Målfönster för medialänkar';
$lang['target____windows']   = 'Målfönster för windowslänkar';

/* Proxy Options */
$lang['proxy____host'] = 'Proxyserver';
$lang['proxy____port'] = 'Proxyport';
$lang['proxy____user'] = 'Användarnamn för proxy';
$lang['proxy____pass'] = 'Lösenord för proxy';
$lang['proxy____ssl']  = 'Använd ssl för anslutning till proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'Aktivera safemode hack';
$lang['ftp____host'] = 'FTP-server för safemode hack';
$lang['ftp____port'] = 'FTP-port för safemode hack';
$lang['ftp____user'] = 'FTP-användarnamn för safemode hack';
$lang['ftp____pass'] = 'FTP-lösenord för safemode hack';
$lang['ftp____root'] = 'FTP-rotkatalog för safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'av';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'DokuWiki internt';

/* deaccent options */
$lang['deaccent_o_0'] = 'av';
$lang['deaccent_o_1'] = 'ta bort accenter';
$lang['deaccent_o_2'] = 'romanisera';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD-bibliotek inte tillgängligt';
$lang['gdlib_o_1'] = 'Version 1.x';
$lang['gdlib_o_2'] = 'Automatisk detektering';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'lista på skillnader';
$lang['rss_linkto_o_page']    = 'den reviderade sidan';
$lang['rss_linkto_o_rev']     = 'lista över ändringar';
$lang['rss_linkto_o_current'] = 'den aktuella sidan';

/* compression options */
$lang['compression_o_0']   = 'none';
$lang['compression_o_gz']  = 'gzip';
$lang['compression_o_bz2'] = 'bz2';

