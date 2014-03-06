<?php
/**
 * swedish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Per Foreby <per@foreby.se>
 * @author Nicklas Henriksson <nicklas[at]nihe.se>
 * @author Håkan Sandell <hakan.sandell[at]mydata.se>
 * @author Dennis Karlsson
 * @author Tormod Otter Johansson <tormod@latast.se>
 * @author emil@sys.nu
 * @author Pontus Bergendahl <pontus.bergendahl@gmail.com>
 * @author Tormod Johansson tormod.otter.johansson@gmail.com
 * @author Emil Lind <emil@sys.nu>
 * @author Bogge Bogge <bogge@bogge.com>
 * @author Peter Åström <eaustreum@gmail.com>
 * @author Håkan Sandell <hakan.sandell@home.se>
 * @author mikael@mallander.net
 * @author Smorkster Andersson smorkster@gmail.com
 */
$lang['menu']                  = 'Hantera inställningar';
$lang['error']                 = 'Inställningarna uppdaterades inte på grund av ett felaktigt värde. Titta igenom dina ändringar och försök sedan spara igen.
                       <br />Felaktiga värden är omgivna av en röd ram.';
$lang['updated']               = 'Inställningarna uppdaterade.';
$lang['nochoice']              = '(inga andra val tillgängliga)';
$lang['locked']                = 'Filen med inställningar kan inte uppdateras. Om det inte är meningen att det ska vara så, <br />
                       kontrollera att filen med lokala inställningar har rätt namn och filskydd.';
$lang['danger']                = 'Risk: Denna förändring kan göra wikin och inställningarna otillgängliga.';
$lang['warning']               = 'Varning: Denna förändring kan orsaka icke åsyftade resultat.';
$lang['security']              = 'Säkerhetsvarning: Denna förändring kan innebära en säkerhetsrisk.';
$lang['_configuration_manager'] = 'Hantera inställningar';
$lang['_header_dokuwiki']      = 'Inställningar för DokuWiki';
$lang['_header_plugin']        = 'Inställningar för insticksmoduler';
$lang['_header_template']      = 'Inställningar för mallar';
$lang['_header_undefined']     = 'Odefinierade inställningar';
$lang['_basic']                = 'Grundläggande inställningar';
$lang['_display']              = 'Inställningar för presentation';
$lang['_authentication']       = 'Inställningar för autentisering';
$lang['_anti_spam']            = 'Inställningar för anti-spam';
$lang['_editing']              = 'Inställningar för redigering';
$lang['_links']                = 'Inställningar för länkar';
$lang['_media']                = 'Inställningar för medier';
$lang['_notifications']        = 'Noterings inställningar';
$lang['_syndication']          = 'Syndikats inställningar';
$lang['_advanced']             = 'Avancerade inställningar';
$lang['_network']              = 'Nätverksinställningar';
$lang['_msg_setting_undefined'] = 'Ingen inställningsmetadata.';
$lang['_msg_setting_no_class'] = 'Ingen inställningsklass.';
$lang['_msg_setting_no_default'] = 'Inget standardvärde.';
$lang['title']                 = 'Wikins namn';
$lang['start']                 = 'Startsidans namn';
$lang['lang']                  = 'Språk';
$lang['template']              = 'Mall';
$lang['license']               = 'Under vilken licens skall ditt innehåll publiceras?';
$lang['savedir']               = 'Katalog för att spara data';
$lang['basedir']               = 'Grundkatalog';
$lang['baseurl']               = 'Grund-webbadress';
$lang['cookiedir']             = 'Cookie sökväg. Lämna blankt för att använda basurl.';
$lang['dmode']                 = 'Filskydd för nya kataloger';
$lang['fmode']                 = 'Filskydd för nya filer';
$lang['allowdebug']            = 'Tillåt felsökning <b>stäng av om det inte behövs!</b>';
$lang['recent']                = 'Antal poster under "Nyligen ändrat"';
$lang['recent_days']           = 'Hur många ändringar som ska sparas (dagar)';
$lang['breadcrumbs']           = 'Antal spår';
$lang['youarehere']            = 'Hierarkiska spår';
$lang['fullpath']              = 'Visa fullständig sökväg i sidfoten';
$lang['typography']            = 'Aktivera typografiska ersättningar';
$lang['dformat']               = 'Datumformat (se PHP:s <a href="http://www.php.net/strftime">strftime</a>-funktion)';
$lang['signature']             = 'Signatur';
$lang['showuseras']            = 'Vad som skall visas när man visar den användare som senast redigerade en sida';
$lang['toptoclevel']           = 'Toppnivå för innehållsförteckning';
$lang['tocminheads']           = 'Minimalt antal rubriker för att avgöra om innehållsförteckning byggs';
$lang['maxtoclevel']           = 'Maximal nivå för innehållsförteckning';
$lang['maxseclevel']           = 'Maximal nivå för redigering av rubriker';
$lang['camelcase']             = 'Använd CamelCase för länkar';
$lang['deaccent']              = 'Rena sidnamn';
$lang['useheading']            = 'Använda första rubriken som sidnamn';
$lang['sneaky_index']          = 'Som standard visar DokuWiki alla namnrymder på indexsidan. Genom att aktivera det här valet döljer man namnrymder som användaren inte har behörighet att läsa. Det kan leda till att man döljer åtkomliga undernamnrymder, och gör indexet oanvändbart med vissa ACL-inställningar.';
$lang['hidepages']             = 'Dölj matchande sidor (reguljära uttryck)';
$lang['useacl']                = 'Använd behörighetslista (ACL)';
$lang['autopasswd']            = 'Autogenerera lösenord';
$lang['authtype']              = 'System för autentisering';
$lang['passcrypt']             = 'Metod för kryptering av lösenord';
$lang['defaultgroup']          = 'Förvald grupp';
$lang['superuser']             = 'Huvudadministratör - en grupp eller en användare med full tillgång till alla sidor och funktioner, oavsett behörighetsinställningars';
$lang['manager']               = 'Administratör -- en grupp eller användare med tillgång till vissa administrativa funktioner.';
$lang['profileconfirm']        = 'Bekräfta ändringarna i profilen med lösenordet';
$lang['rememberme']            = 'Tillåt permanenta inloggningscookies (kom ihåg mig)';
$lang['disableactions']        = 'Stäng av funktioner i DokuWiki';
$lang['disableactions_check']  = 'Kontroll';
$lang['disableactions_subscription'] = 'Prenumerera/Säg upp prenumeration';
$lang['disableactions_wikicode'] = 'Visa källkod/Exportera råtext';
$lang['disableactions_other']  = 'Andra funktioner (kommaseparerade)';
$lang['auth_security_timeout'] = 'Autentisieringssäkerhets timeout (sekunder)';
$lang['securecookie']          = 'Skall cookies som sätts via HTTPS endast skickas via HTTPS från webbläsaren? Avaktivera detta alternativ endast om inloggningen till din wiki är säkrad med SSL men läsning av wikin är osäkrad.';
$lang['usewordblock']          = 'Blockera spam baserat på ordlista';
$lang['relnofollow']           = 'Använd rel="nofollow" för externa länkar';
$lang['indexdelay']            = 'Tidsfördröjning före indexering (sek)';
$lang['mailguard']             = 'Koda e-postadresser';
$lang['iexssprotect']          = 'Kontrollera om uppladdade filer innehåller eventuellt skadlig JavaScript eller HTML-kod';
$lang['usedraft']              = 'Spara utkast automatiskt under redigering';
$lang['htmlok']                = 'Tillåt inbäddad HTML';
$lang['phpok']                 = 'Tillåt inbäddad PHP';
$lang['locktime']              = 'Maximal livslängd för fillåsning (sek)';
$lang['cachetime']             = 'Maximal livslängd för cache (sek)';
$lang['target____wiki']        = 'Målfönster för interna länkar';
$lang['target____interwiki']   = 'Målfönster för interwiki-länkar';
$lang['target____extern']      = 'Målfönster för externa länkar';
$lang['target____media']       = 'Målfönster för medialänkar';
$lang['target____windows']     = 'Målfönster för windowslänkar';
$lang['refcheck']              = 'Kontrollera referenser till mediafiler';
$lang['gdlib']                 = 'Version av GD-biblioteket';
$lang['im_convert']            = 'Sökväg till ImageMagicks konverteringsverktyg';
$lang['jpg_quality']           = 'Kvalitet för JPG-komprimering (0-100)';
$lang['fetchsize']             = 'Maximal storlek (bytes) som fetch.php får ladda ned  externt';
$lang['subscribers']           = 'Aktivera stöd för prenumeration på ändringar';
$lang['notify']                = 'Skicka meddelande om ändrade sidor till den här e-postadressen';
$lang['registernotify']        = 'Skicka meddelande om nyregistrerade användare till en här e-postadressen';
$lang['mailfrom']              = 'Avsändaradress i automatiska e-postmeddelanden';
$lang['mailprefix']            = 'Prefix i början på ämnesraden vid automatiska e-postmeddelanden';
$lang['sitemap']               = 'Skapa Google sitemap (dagar)';
$lang['rss_type']              = 'Typ av XML-flöde';
$lang['rss_linkto']            = 'XML-flöde pekar på';
$lang['rss_content']           = 'Vad ska visas för saker i XML-flödet?';
$lang['rss_update']            = 'Uppdateringsintervall för XML-flöde (sek)';
$lang['rss_show_summary']      = 'XML-flöde visar sammanfattning i rubriken';
$lang['rss_media']             = 'Vilka ändringar ska listas i XML flödet?';
$lang['updatecheck']           = 'Kontrollera uppdateringar och säkerhetsvarningar? DokuWiki behöver kontakta update.dokuwiki.org för den här funktionen.';
$lang['userewrite']            = 'Använd rena webbadresser';
$lang['useslash']              = 'Använd snedstreck för att separera namnrymder i webbadresser';
$lang['sepchar']               = 'Ersätt blanktecken i webbadresser med';
$lang['canonical']             = 'Använd fullständiga webbadresser';
$lang['fnencode']              = 'Metod för kodning av icke-ASCII filnamn.';
$lang['autoplural']            = 'Leta efter pluralformer av länkar';
$lang['compression']           = 'Metod för komprimering av gamla versioner';
$lang['gzip_output']           = 'Använd gzip Content-Encoding för xhtml';
$lang['compress']              = 'Komprimera CSS och javascript';
$lang['send404']               = 'Skicka "HTTP 404/Page Not Found" för sidor som inte finns';
$lang['broken_iua']            = 'Är funktionen ignore_user_abort trasig på ditt system? Det kan i så fall leda till att indexering av sökningar inte fungerar. Detta är ett känt problem med IIS+PHP/CGI. Se <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> för mer info.';
$lang['xsendfile']             = 'Använd X-Sendfile huvudet för att låta webservern leverera statiska filer? Din webserver behöver stöd för detta.';
$lang['renderer_xhtml']        = 'Generera för användning i huvudwikipresentation (xhtml)';
$lang['renderer__core']        = '%s (dokuwiki core)';
$lang['renderer__plugin']      = '%s (plugin)';
$lang['proxy____host']         = 'Proxyserver';
$lang['proxy____port']         = 'Proxyport';
$lang['proxy____user']         = 'Användarnamn för proxy';
$lang['proxy____pass']         = 'Lösenord för proxy';
$lang['proxy____ssl']          = 'Använd ssl för anslutning till proxy';
$lang['proxy____except']       = 'Regular expression för matchning av URL som proxy ska hoppa över.';
$lang['safemodehack']          = 'Aktivera safemode hack';
$lang['ftp____host']           = 'FTP-server för safemode hack';
$lang['ftp____port']           = 'FTP-port för safemode hack';
$lang['ftp____user']           = 'FTP-användarnamn för safemode hack';
$lang['ftp____pass']           = 'FTP-lösenord för safemode hack';
$lang['ftp____root']           = 'FTP-rotkatalog för safemode hack';
$lang['license_o_']            = 'Ingen vald';
$lang['typography_o_0']        = 'Inga';
$lang['typography_o_1']        = 'enbart dubbla citattecken';
$lang['typography_o_2']        = 'både dubbla och enkla citattecken (fungerar inte alltid)';
$lang['userewrite_o_0']        = 'av';
$lang['userewrite_o_1']        = '.htaccess';
$lang['userewrite_o_2']        = 'DokuWiki internt';
$lang['deaccent_o_0']          = 'av';
$lang['deaccent_o_1']          = 'ta bort accenter';
$lang['deaccent_o_2']          = 'romanisera';
$lang['gdlib_o_0']             = 'GD-bibliotek inte tillgängligt';
$lang['gdlib_o_1']             = 'Version 1.x';
$lang['gdlib_o_2']             = 'Automatisk detektering';
$lang['rss_type_o_rss']        = 'RSS 0.91';
$lang['rss_type_o_rss1']       = 'RSS 1.0';
$lang['rss_type_o_rss2']       = 'RSS 2.0';
$lang['rss_type_o_atom']       = 'Atom 0.3';
$lang['rss_type_o_atom1']      = 'Atom 1.0';
$lang['rss_content_o_abstract'] = 'Abstrakt';
$lang['rss_content_o_diff']    = 'Unified Diff';
$lang['rss_content_o_htmldiff'] = 'HTML formaterad diff tabell';
$lang['rss_content_o_html']    = 'Sidans innehåll i full HTML';
$lang['rss_linkto_o_diff']     = 'lista på skillnader';
$lang['rss_linkto_o_page']     = 'den reviderade sidan';
$lang['rss_linkto_o_rev']      = 'lista över ändringar';
$lang['rss_linkto_o_current']  = 'den aktuella sidan';
$lang['compression_o_0']       = 'ingen';
$lang['compression_o_gz']      = 'gzip';
$lang['compression_o_bz2']     = 'bz2';
$lang['xsendfile_o_0']         = 'använd ej';
$lang['xsendfile_o_1']         = 'Proprietär lighttpd-header (före version 1.5)';
$lang['xsendfile_o_2']         = 'Standard X-Sendfile-huvud';
$lang['xsendfile_o_3']         = 'Proprietär Nginx X-Accel-Redirect header';
$lang['showuseras_o_loginname'] = 'Användarnamn';
$lang['showuseras_o_username'] = 'Namn';
$lang['showuseras_o_email']    = 'Användarens e-postadress (obfuskerad enligt inställningarna i mailguard)';
$lang['showuseras_o_email_link'] = 'Användarens e-postadress som mailto: länk';
$lang['useheading_o_0']        = 'Aldrig';
$lang['useheading_o_navigation'] = 'Endst navigering';
$lang['useheading_o_content']  = 'Endast innehåll i wiki';
$lang['useheading_o_1']        = 'Alltid';
$lang['readdircache']          = 'Max ålder för readdir cache (sek)';
