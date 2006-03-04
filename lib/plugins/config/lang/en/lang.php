<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Configuration Settings';

$lang['error']      = 'Settings not updated due to an invalid value, please review your changes and resubmit.
                       <br />The incorrect value(s) will be shown surrounded by a red border.';
$lang['updated']    = 'Settings updated successfully.';
$lang['nochoice']   = '(no other choices available)';
$lang['locked']     = 'The settings file can not be updated, if this is unintentional, <br />
                       ensure the local settings file name and permissions are correct.';


/* -------------------- Config Options --------------------------- */

$lang['fmode']       = 'File creation mode';
$lang['dmode']       = 'Directory creation mode';
$lang['lang']        = 'Language';
$lang['basedir']     = 'Base directory';
$lang['baseurl']     = 'Base URL';
$lang['savedir']     = 'Directory for saving data';
$lang['start']       = 'Start page name';
$lang['title']       = 'Wiki title';
$lang['template']    = 'Template';
$lang['fullpath']    = 'Reveal full path of pages in the footer';
$lang['recent']      = 'Recent changes';
$lang['breadcrumbs'] = 'Number of breadcrumbs';
$lang['youarehere']  = 'Hierarchical breadcrumbs';
$lang['typography']  = 'Do typographical replacements';
$lang['htmlok']      = 'Allow embedded HTML';
$lang['phpok']       = 'Allow embedded PHP';
$lang['dformat']     = 'Date format (see PHP\'s <a href="http://www.php.net/date">date</a> function)';
$lang['signature']   = 'Signature';
$lang['toptoclevel'] = 'Top level for table of contents';
$lang['maxtoclevel'] = 'Maximum level for table of contents';
$lang['maxseclevel'] = 'Maximum section edit level';
$lang['camelcase']   = 'Use CamelCase for links';
$lang['deaccent']    = 'Clean pagenames';
$lang['useheading']  = 'Use first heading for pagenames';
$lang['refcheck']    = 'Media reference check';
$lang['refshow']     = 'Number of media references to show';
$lang['allowdebug']  = 'Allow debug <b>disable if not needed!</b>';

$lang['usewordblock']= 'Block spam based on wordlist';
$lang['indexdelay']  = 'Time delay before indexing';
$lang['relnofollow'] = 'Use rel="nofollow"';
$lang['mailguard']   = 'Obfuscate email addresses';

/* Authentication Options */
$lang['useacl']      = 'Use access control lists';
$lang['openregister']= 'Allow everyon to register';
$lang['autopasswd']  = 'Autogenerate passwords';
$lang['resendpasswd']= 'Allow resend password';
$lang['authtype']    = 'Authentication backend';
$lang['passcrypt']   = 'Password encryption method';
$lang['defaultgroup']= 'Default group';
$lang['superuser']   = 'Superuser';
$lang['profileconfirm'] = 'Confirm profile changes with password';

/* Advanced Options */
$lang['userewrite']  = 'Use nice URLs';
$lang['useslash']    = 'Use slash as namespace separator in URLs';
$lang['sepchar']     = 'Page name word separator';
$lang['canonical']   = 'Use fully canonical URLs';
$lang['autoplural']  = 'Check for plural forms in links';
$lang['usegzip']     = 'Use gzip (for attic)';
$lang['cachetime']   = 'Maximum age for cache (sec)';
$lang['purgeonadd']  = 'Purge cache when new pages are added';
$lang['locktime']    = 'Maximum age for lock files (sec)';
$lang['notify']      = 'Send change notifications to this email address';
$lang['mailfrom']    = 'Email address to use for automatic mails';
$lang['gdlib']       = 'GD Lib version';
$lang['im_convert']  = 'Path to ImageMagick\'s convert tool';
$lang['spellchecker']= 'Enable spellchecker';
$lang['subscribers'] = 'Enable page subscription support';
$lang['compress']    = 'Compress CSS and javascript files';
$lang['hidepages']   = 'Hide matching pages (regular expressions)';
$lang['send404']     = 'Send "HTTP 404/Page Not Found" for non existing pages';
$lang['sitemap']     = 'Generate Google sitemap (days)';

$lang['rss_type']    = 'XML feed type';
$lang['rss_linkto']  = 'XML feed links to';

/* Target options */
$lang['target____wiki']      = 'Target window for internal links';
$lang['target____interwiki'] = 'Target window for interwiki links';
$lang['target____extern']    = 'Target window for external links';
$lang['target____media']     = 'Target window for media links';
$lang['target____windows']   = 'Target window for windows links';

/* Proxy Options */
$lang['proxy____host'] = 'Proxy servername';
$lang['proxy____port'] = 'Proxy port';
$lang['proxy____user'] = 'Proxy user name';
$lang['proxy____pass'] = 'Proxy password';
$lang['proxy____ssl']  = 'use ssl to connect to Proxy';

/* Safemode Hack */
$lang['safemodehack'] = 'enable safemode hack';
$lang['ftp____host'] = 'FTP server for safemode hack';
$lang['ftp____port'] = 'FTP port for safemode hack';
$lang['ftp____user'] = 'FTP user name for safemode hack';
$lang['ftp____pass'] = 'FTP password for safemode hack';
$lang['ftp____root'] = 'FTP root directory for safemode hack';

/* userewrite options */
$lang['userewrite_o_0'] = 'none';
$lang['userewrite_o_1'] = '.htaccess';
$lang['userewrite_o_2'] = 'DokuWiki internal';

/* deaccent options */
$lang['deaccent_o_0'] = 'off';
$lang['deaccent_o_1'] = 'remove accents';
$lang['deaccent_o_2'] = 'romanize';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib not available';
$lang['gdlib_o_1'] = 'Version 1.x';
$lang['gdlib_o_2'] = 'Autodetection';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'difference view';
$lang['rss_linkto_o_page']    = 'the revised page';
$lang['rss_linkto_o_rev']     = 'list of revisions';
$lang['rss_linkto_o_current'] = 'the current page';

