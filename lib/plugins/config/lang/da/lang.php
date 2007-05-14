<?php
/**
 * Danish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Lars Næsbye Christensen <larsnaesbye@stud.ku.dk>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Konfigurationsindstillinger'; 

$lang['error']      = 'Indstillingerne blev ikke opdateret på grund af en ugyldig værdi, gennemse venligst dine ændringer og gem dem igen.
                       <br />De(n) ugyldig(e) værdie(r) vil blive rammet ind med rødt.';
$lang['updated']    = 'Indstillingerne blev opdateret korrekt.';
$lang['nochoice']   = '(ingen andre valgmuligheder)';
$lang['locked']     = 'Indstillingsfilen kunne ikke opdateres, hvis dette er en fejl, <br />
                       sørg da for at den lokale indstillingsfils navn og rettigheder er korrekte.';

/* --- Config Setting Headers --- */
$lang['_configuration_manager'] = 'Konfigurationsstyring'; //same as heading in intro.txt
$lang['_header_dokuwiki'] = 'DokuWiki indstillinger';
$lang['_header_plugin'] = 'Pluginindstillinger';
$lang['_header_template'] = 'Skabelonindstillinger';
$lang['_header_undefined'] = 'Udefinerede indstillinger';

/* --- Config Setting Groups --- */
$lang['_basic'] = 'Grundindstillinger';
$lang['_display'] = 'Synlighedsindstillinger';
$lang['_authentication'] = 'Bekræftelsesindstillinger';
$lang['_anti_spam'] = 'Anti-spam indstillinger';
$lang['_editing'] = 'Redigeringsindstillinger';
$lang['_links'] = 'Linkindstillinger';
$lang['_media'] = 'Medieindstillinger';
$lang['_advanced'] = 'Avancerede indstillinger';
$lang['_network'] = 'Netværksindstillinger';
// The settings group name for plugins and templates can be set with
// plugin_settings_name and template_settings_name respectively. If one
// of these lang properties is not set, the group name will be generated
// from the plugin or template name and the localized suffix.
$lang['_plugin_sufix'] = 'Pluginindstillinger';
$lang['_template_sufix'] = 'Skabelonindstillinger';

/* --- Undefined Setting Messages --- */
$lang['_msg_setting_undefined'] = 'Ingen indstillingsmetadata.';
$lang['_msg_setting_no_class'] = 'Ingen indstillingsklasse.';
$lang['_msg_setting_no_default'] = 'Ingen standardværdi.';

/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'Filoprettelsestilstand';
$lang['dmode']       = 'Katalogoprettelsestilstand';
$lang['lang']        = 'Sprog';
$lang['basedir']     = 'Grundkatalog';
$lang['baseurl']     = 'Grund URL';
$lang['savedir']     = 'Katalog til opbevaring af data';
$lang['start']       = 'Startsidens navn';
$lang['title']       = 'Wiki titel';
$lang['template']    = 'Skabelon';
$lang['fullpath']    = 'Vis den fulde sti til siderne i bundlinjen';
$lang['recent']      = 'Nylige ændringer';
$lang['breadcrumbs'] = 'Stilængde';
$lang['youarehere']  = 'Hierarkisk sti';
$lang['typography']  = 'Typografiske erstatninger';
$lang['htmlok']      = 'Tillad indlejret HTML';
$lang['phpok']       = 'Tillad indlejret PHP';
$lang['dformat']     = 'Datoformat (se PHP\'s <a href="http://www.php.net/date">date</a> funktion)';
$lang['signature']   = 'Signatur';
$lang['toptoclevel'] = 'Højeste niveau for indholdsfortegnelse';
$lang['maxtoclevel'] = 'Maksimalt niveau for indholdsfortegnelse';
$lang['maxseclevel'] = 'Maksimalt niveau for redigering af sektioner';
$lang['camelcase']   = 'Brug CamelCase til links';
$lang['deaccent']    = 'Pæne sidenavne';
$lang['useheading']  = 'Brug første overskrift til sidenavne';
$lang['refcheck']    = 'Mediereference kontrol';
$lang['refshow']     = 'Antal viste mediereferencer';
$lang['allowdebug']  = 'Tillad debugging <b>slå fra hvis unødvendig!</b>';

$lang['usewordblock']= 'Bloker spam baseret på ordliste';
$lang['indexdelay']  = 'Tidsforsinkelse af indeksering';
$lang['relnofollow'] = 'Brug rel="nofollow"';
$lang['mailguard']   = 'Slør email adresser';
$lang['iexssprotect']= 'Tjek uploadede filer for mulig skadelig JavaScript eller HTML kode.';

/* Authentication Options */
$lang['useacl']      = 'Benyt adgangskontrollister';
$lang['autopasswd']  = 'Generer passwords automatisk';
$lang['authtype']    = 'Bekræftelsesbackend';
$lang['passcrypt']   = 'Passwordkrypteringsmetode';
$lang['defaultgroup']= 'Standardgruppe';
$lang['superuser']   = 'Superbruger';
$lang['manager']     = 'Bestyrer - en gruppe eller bruger med adgang til bestemte styrende funktioner';
$lang['profileconfirm'] = 'Bekræft profilændringer med password';
$lang['disableactions'] = 'Slå DokuWiki muligheder fra';
$lang['disableactions_check'] = 'Check';
$lang['disableactions_subscription'] = 'Abonner/Fjern abonnement';
$lang['disableactions_wikicode'] = 'Vis kilde/eksporter råtekst';
$lang['disableactions_other'] = 'Andre muligheder (kommasepareret)';

/* Advanced Options */
$lang['updatecheck'] = 'Tjek for opdateringer og sikkerheds advarsler? DokuWiki er nød til at kontakte splitbrain.org for denne funktion.';
$lang['userewrite']  = 'Brug pæne URLer';
$lang['useslash']    = 'Brug skråstreg som navnerumsdeler i URLer';
$lang['usedraft']    = 'Gem automatisk en kladde under redigering';
$lang['sepchar']     = 'Orddelingstegn til sidenavne';
$lang['canonical']   = 'Benyt fuldt kanoniske URLer';
$lang['autoplural']  = 'Check for flertalsendelser i links';
$lang['compression'] = 'Kompressions metode for attic filer';
$lang['usegzip']     = 'Benyt gzip til attic filer';
$lang['cachetime']   = 'Maksimum levetid for cache (sek)';
$lang['locktime']    = 'Maksimum levetid for låsningsfiler (sek)';
$lang['fetchsize']   = 'Maksimum antal (bytes) fetch.php må downloade fra extern';
$lang['notify']      = 'Send ændringsnotifikationer til denne e-mailadresse';
$lang['registernotify'] = 'Send info om nyoprettede brugere til denne email adresse';
$lang['mailfrom']    = 'Email adresse til brug for automatiske mails';
$lang['gzip_output'] = 'Benyt gzip Content-Encoding til XHTML';
$lang['gdlib']       = 'GD Lib version';
$lang['im_convert']  = 'Sti til ImageMagick\'s convert værktøj';
$lang['jpg_quality'] = 'JPG komprimeringskvalitet (0-100)';
$lang['spellchecker']= 'Slå stavekontrol til';
$lang['subscribers'] = 'Slå understøttelse af abonnement på sider til';
$lang['compress']    = 'Komprimer CSS og Javascript filer';
$lang['hidepages']   = 'Skjul matchende sider (regulære udtryk)';
$lang['send404']     = 'Send "HTTP 404/Page Not Found" for ikke-eksisterende sider';
$lang['sitemap']     = 'Generer Google sitemap (dage)';
$lang['broken_iua']  = 'Er ignore_user_abort funktionen i stykker p� dit system? Dette kunne forudsage et ikke virkende s�geindeks. IIS+PHP/CGI er kendt for ikke at virke. Se <a href="http://bugs.splitbrain.org/?do=details&amp;task_id=852">Bug 852</a> for mere information.';

$lang['rss_type']    = 'XML feed type';
$lang['rss_linkto']  = 'XML feed linker til';
$lang['rss_update']  = 'XML feed opdateringsinterval (sek)';
$lang['recent_days'] = 'Hvor mange nye ændringer der skal beholdes (dage)';
$lang['rss_show_summary'] = 'XML feed vis resume i titlen';

/* Target options */
$lang['target____wiki']      = 'Destinationsvindue til interne links';
$lang['target____interwiki'] = 'Destinationsvindue til interwiki links';
$lang['target____extern']    = 'Destinationsvindue til externe links';
$lang['target____media']     = 'Destinationsvindue til medie links';
$lang['target____windows']   = 'Destinationsvindue til Windows links';

/* Proxy Options */
$lang['proxy____host'] = 'Proxy servernavn';
$lang['proxy____port'] = 'Proxy port';
$lang['proxy____user'] = 'Proxy brugernavn';
$lang['proxy____pass'] = 'Proxy password';
$lang['proxy____ssl']  = 'Brug SSL til at forbinde til proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'Slå safemode hack til';
$lang['ftp____host'] = 'FTP server til safemode hack';
$lang['ftp____port'] = 'FTP port til safemode hack';
$lang['ftp____user'] = 'FTP brugernavn til safemode hack';
$lang['ftp____pass'] = 'FTP adgangskode til safemode hack';
$lang['ftp____root'] = 'FTP rodkatalog til safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'ingen';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'Dokuwiki intern';

/* deaccent options */
$lang['deaccent_o_0'] = 'fra';
$lang['deaccent_o_1'] = 'fjern accenter';
$lang['deaccent_o_2'] = 'romaniser';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib ikke tilstede';
$lang['gdlib_o_1'] = 'version 1.x';
$lang['gdlib_o_2'] = 'automatisk detektering';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'liste over forskelle';
$lang['rss_linkto_o_page']    = 'den redigerede side';
$lang['rss_linkto_o_rev']     = 'liste over ændringer';
$lang['rss_linkto_o_current'] = 'den nuværende side';

/* compression options */
$lang['compression_o_0']   = 'ingen';
$lang['compression_o_gz']  = 'gzip';
$lang['compression_o_bz2'] = 'bz2';
