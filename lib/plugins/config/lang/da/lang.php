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


/* -------------------- Config Options --------------------------- */

//$lang['fmode']       = 'File creation mode';
//$lang['dmode']       = 'Directory creation mode';
$lang['lang']        = 'Sprog';
//$lang['basedir']     = 'Base directory';
//$lang['baseurl']     = 'Base URL';
//$lang['savedir']     = 'Directory for saving data';
$lang['start']       = 'Startsidens navn';
$lang['title']       = 'Wiki titel';
$lang['template']    = 'Skabelon';
//$lang['fullpath']    = 'Reveal full path of pages in the footer';
$lang['recent']      = 'Nylige ændringer';
$lang['breadcrumbs'] = 'Stilængde';
$lang['youarehere']  = 'Hierarkisk sti';
$lang['typography']  = 'Typografiske erstatninger';
$lang['htmlok']      = 'Tillad indlejret HTML';
$lang['phpok']       = 'Tillad indlejret PHP';
$lang['dformat']     = 'Datoformat (se PHP\'s <a href="http://www.php.net/date">date</a> funktion)';
$lang['signature']   = 'Signatur';
//$lang['toptoclevel'] = 'Top level for table of contents';
//$lang['maxtoclevel'] = 'Maximum level for table of contents';
//$lang['maxseclevel'] = 'Maximum section edit level';
$lang['camelcase']   = 'Brug CamelCase til links';
//$lang['deaccent']    = 'Clean pagenames';
//$lang['useheading']  = 'Use first heading for pagenames';
//$lang['refcheck']    = 'Media reference check';
//$lang['refshow']     = 'Number of media references to show';
$lang['allowdebug']  = 'Tillad debugging <b>slå fra hvis unødvendig!</b>';

$lang['usewordblock']= 'Bloker spam baseret på ordliste';
//$lang['indexdelay']  = 'Time delay before indexing';
$lang['relnofollow'] = 'Brug rel="nofollow"';
//$lang['mailguard']   = 'Obfuscate email addresses';

/* Authentication Options */
//$lang['useacl']      = 'Use access control lists';
//$lang['openregister']= 'Allow everyon to register';
//$lang['autopasswd']  = 'Autogenerate passwords';
//$lang['resendpasswd']= 'Allow resend password';
//$lang['authtype']    = 'Authentication backend';
//$lang['passcrypt']   = 'Password encryption method';
//$lang['defaultgroup']= 'Default group';
$lang['superuser']   = 'Superbruger';
//$lang['profileconfirm'] = 'Confirm profile changes with password';

/* Advanced Options */
//$lang['userewrite']  = 'Use nice URLs';
//$lang['useslash']    = 'Use slash as namespace separator in URLs';
//$lang['sepchar']     = 'Page name word separator';
//$lang['canonical']   = 'Use fully canonical URLs';
//$lang['autoplural']  = 'Check for plural forms in links';
//$lang['usegzip']     = 'Use gzip (for attic)';
//$lang['cachetime']   = 'Maximum age for cache (sec)';
$lang['purgeonadd']  = 'Ryd cache når nye sider tilføjes';
//$lang['locktime']    = 'Maximum age for lock files (sec)';
//$lang['notify']      = 'Send change notifications to this email address';
$lang['mailfrom']    = 'Email adresse til brug for automatiske mails';
$lang['gdlib']       = 'GD Lib version';
//$lang['im_convert']  = 'Path to ImageMagick\'s convert tool';
$lang['spellchecker']= 'Slå stavekontrol til';
//$lang['subscribers'] = 'Enable page subscription support';
$lang['compress']    = 'Komprimer CSS og Javascript filer';
//$lang['hidepages']   = 'Hide matching pages (regular expressions)';
$lang['send404']     = 'Send "HTTP 404/Page Not Found" for ikke-eksisterende sider';
$lang['sitemap']     = 'Generer Google sitemap (dage)';

$lang['rss_type']    = 'XML feed type';
$lang['rss_linkto']  = 'XML feed linker til';

/* Target options */
$lang['target____wiki']      = 'Destinationsvindue til interne links';/$lang['target____interwiki'] = 'Destinationsvindue til interwiki links';
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
//$lang['safemodehack'] = 'enable safemode hack';
//$lang['ftp____host'] = 'FTP server for safemode hack';
//$lang['ftp____port'] = 'FTP port for safemode hack';
//$lang['ftp____user'] = 'FTP user name for safemode hack';
//$lang['ftp____pass'] = 'FTP password for safemode hack';
//$lang['ftp____root'] = 'FTP root directory for safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'ingen';
$lang['userewrite_o_1'] = 'htaccess';
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

