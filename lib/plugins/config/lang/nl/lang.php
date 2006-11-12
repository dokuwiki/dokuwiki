<?php
/**
 * dutch language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Pieter van der Meulen <pieter@vdmeulen.net>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Configuratie instellingen';

$lang['error']      = 'De instellingen zijn niet aangebracht wegens een niet correcte waarde, kijk svp je wijzigingen na en sla dan opnieuw op.<br />Je kunt de incorrecte waarde herkennen aan de rode rand.';
$lang['updated']    = 'Instellingen met succes opgeslagen.';
$lang['nochoice']   = '(geen andere keuzemogelijkheden)';
$lang['locked']     = 'Het bestand met instellinegn kan niet worden gewijzigd. Als dit niet de bedeoeling is, <br />zorg dan dat naam en permissies voor het lokale installingen bestand kloppen.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Configuratiie manager'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'DokuWiki instellingen';
$lang['_header_plugin'] = 'Plugin instellingen';
$lang['_header_template'] = 'Sjabloon instellingen';
$lang['_header_undefined'] = 'Ongedefinierde instellingen';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Basis instellingen';
$lang['_display'] = 'Beeld instellingen';
$lang['_authentication'] = 'Toegangsverificatie instellingen';
$lang['_anti_spam'] = 'Anti-Spam instellingen';
$lang['_editing'] = 'Pagina-wijzigings instellingen';
$lang['_links'] = 'Link instellingen';
$lang['_media'] = 'Media instellingen';
$lang['_advanced'] = 'Geavanceerde instellingen';
$lang['_network'] = 'Netwerk instellingen';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'Plugin instellingen';
$lang['_template_sufix'] = 'Sjabloon instellingen';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Geen metedata voor deze instelling.';
$lang['_msg_setting_no_class'] = 'Geen class voor deze instelling.';
$lang['_msg_setting_no_default'] = 'Geen standaard waarde.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Bestand aanmaak modus (file creation mode)';
$lang['dmode']       = 'Directory aanmaak modus (directory creation mode)';
$lang['lang']        = 'Taal';
$lang['basedir']     = 'Basis directory';
$lang['baseurl']     = 'Basis URL';
$lang['savedir']     = 'Directory om data op te slaan';
$lang['start']       = 'Start pagina naam';
$lang['title']       = 'Wiki titel';
$lang['template']    = 'Sjabloon';
$lang['fullpath']    = 'Volledig pad van pagina\'s in de footer weergeven';
$lang['recent']      = 'Recente wijzigingen';
$lang['breadcrumbs'] = 'Aantal broodkruimels';
$lang['youarehere']  = 'Hierarchische broodkruimels';
$lang['typography']  = 'Breng typografische wijzigingen aan';
$lang['htmlok']      = 'Embedded HTML toestaan';
$lang['phpok']       = 'Embedded PHP toestaan';
$lang['dformat']     = 'Datum formaat (zie de PHP <a href="http://www.php.net/date">date</a> functie)';
$lang['signature']   = 'Ondertekening';
$lang['toptoclevel'] = 'Bovenste niveau voor inhoudsopgave';
$lang['maxtoclevel'] = 'Laagste niveau voor inhoudsopgave';
$lang['maxseclevel'] = 'Laagste sectiewijzigingsniveau';
$lang['camelcase']   = 'CamelCase gebruiken voor links';
$lang['deaccent']    = 'Paginanamen ontdoen van niet-standaard tekens';
$lang['useheading']  = 'Eerste kopje voor paginanaam gebruiken';
$lang['refcheck']    = 'Controleer verwijzingen naar media';
$lang['refshow']     = 'Aantal te tonen media verwijzigen';
$lang['allowdebug']  = 'Debug toestaan <b>uitzetten indien niet noodzakelijk!</b>';

$lang['usewordblock']= 'Blokkeer spam op basis van woordenlijst';
$lang['indexdelay']  = 'Uitstel alvorens te indexeren (sec)';
$lang['relnofollow'] = 'Gebruik rel="nofollow" voor externe links';
$lang['mailguard']   = 'Eemail adressen onherkenbaar maken';

/* Authentication Options */
$lang['useacl']      = 'Gebruik access control lists';
$lang['autopasswd']  = 'Zelf wachtwoorden genereren';
$lang['authtype']    = 'Authenticatie mechanisme';
$lang['passcrypt']   = 'Wachtwoord encryptie methode';
$lang['defaultgroup']= 'Standaard groep';
$lang['superuser']   = 'Superuser';
$lang['profileconfirm'] = 'Bevestig profielwijzigingen met wachtwoord';
$lang['disableactions'] = 'Aangevinkte Dokuwiki akties uitschakelen';
$lang['disableactions_check'] = 'Controleer';
$lang['disableactions_subscription'] = 'Inschrijven/opzeggen';
$lang['disableactions_wikicode'] = 'Bron bekijken/exporteer rauw';
$lang['disableactions_other'] = 'Andere akties (gescheiden door komma)';

/* Advanced Options */
$lang['updatecheck'] = 'Controlele op nieuwe versies en beveiligingswaarschuwingen? DokuWiki moet hiervoor contact opnemen met splitbrain.org.';
$lang['userewrite']  = 'Gebruik nette URL\'s';
$lang['useslash']    = 'Gebruik slash (/) als scheifing tussen namepaces in URL\'s';
$lang['usedraft']    = 'Sla automatisch een concept op tijdens het wijzigen';
$lang['sepchar']     = 'Pagina naam woordscheiding';
$lang['canonical']   = 'Herleid URL\'s tot hun basisvorm';
$lang['autoplural']  = 'Controleer op meervoudsvormen in links';
$lang['compression'] = 'Compressie methode voor attic bestanden';
$lang['cachetime']   = 'Maximum leeftijd voor cache (sec)';
$lang['locktime']    = 'Maximum leeftijd voor lock bestanden (sec)';
$lang['fetchsize']   = 'Maximum grootte (bytes) die fetch.php mag downloaden van buiten';
$lang['notify']      = 'Stuur email wijzingsbereichten naar dit adres';
$lang['registernotify'] = 'Stuur informatie over nieuw aangemelde gebruikeers naar dit email adres';
$lang['mailfrom']    = 'Email adres voor automatische email';
$lang['gzip_output'] = 'Gebruik gzip Content-Encoding voor xhtml';
$lang['gdlib']       = 'GD Lib versie';
$lang['im_convert']  = 'Path naar ImageMagick\'s convert tool';
$lang['jpg_quality'] = 'JPG compressie kwaliteit (0-100)';
$lang['spellchecker']= 'Spellingscontrole aanzetten';
$lang['subscribers'] = 'Page subscription ondersteuning aanzetten';
$lang['compress']    = 'Compacte CSS en javascript output';
$lang['hidepages']   = 'Verberg deze pagina\'s (regular expressions)';
$lang['send404']     = 'Stuur "HTTP 404/Page Not Found" voor niet bestaande pagina\'s';
$lang['sitemap']     = 'Genereer Google sitemap (dagen)';

$lang['rss_type']    = 'XML feed type';
$lang['rss_linkto']  = 'XML feed linkt naar';
$lang['rss_update']  = 'XML feed verversingsinterval (sec)';
$lang['recent_days'] = 'Hoeveel recente wijzigingen bewaren (days)';

/* Target options */
$lang['target____wiki']      = 'Doelvenster voor interne links';
$lang['target____interwiki'] = 'Doelvenster voor interwiki links';
$lang['target____extern']    = 'Doelvenster voor externe  links';
$lang['target____media']     = 'Doelvenster voor media links';
$lang['target____windows']   = 'Doelvenster voor windows links';

/* Proxy Options */
$lang['proxy____host'] = 'Proxy server';
$lang['proxy____port'] = 'Proxy port';
$lang['proxy____user'] = 'Proxy gebruikersnaam';
$lang['proxy____pass'] = 'Proxy wachtwoord';
$lang['proxy____ssl']  = 'Gebruik SSL om een connectie te maken met de proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'Safemode hack aanzetten';
$lang['ftp____host'] = 'FTP server voor safemode hack';
$lang['ftp____port'] = 'FTP port voor safemode hack';
$lang['ftp____user'] = 'FTP gebruikersnaam voor safemode hack';
$lang['ftp____pass'] = 'FTP wachtwoord voor safemode hack';
$lang['ftp____root'] = 'FTP root directory voor safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'geen';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'DokuWiki intern';

/* deaccent options */
$lang['deaccent_o_0'] = 'uit';
$lang['deaccent_o_1'] = 'accenten verwijderen';
$lang['deaccent_o_2'] = 'romaniseer';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib niet beschikbaar';
$lang['gdlib_o_1'] = 'Version 1.x';
$lang['gdlib_o_2'] = 'Autodetectie';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'verschillen';
$lang['rss_linkto_o_page']    = 'de gewijzigde pagina';
$lang['rss_linkto_o_rev']     = 'lijst van wijzigingen';
$lang['rss_linkto_o_current'] = 'de huidige pagina';

/* compression options */
$lang['compression_o_0']   = 'geen';
$lang['compression_o_gz']  = 'gzip';
$lang['compression_o_bz2'] = 'bz2';

