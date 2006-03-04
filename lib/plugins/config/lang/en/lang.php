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

$lang['fmode']       = 'file creation mode';
$lang['dmode']       = 'directory creation mode';
$lang['lang']        = 'language';
$lang['basedir']     = 'base directory';
$lang['baseurl']     = 'base url';
$lang['savedir']     = 'save directory';
$lang['start']       = 'start page name';
$lang['title']       = 'wiki title';
$lang['template']    = 'template';
$lang['fullpath']    = 'use full path';
$lang['recent']      = 'recent changes';
$lang['breadcrumbs'] = 'breadcrumbs';
$lang['youarehere']  = 'hierarchical breadcrumbs';
$lang['typography']  = 'typography';
$lang['htmlok']      = 'allow embedded html';
$lang['phpok']       = 'allow embedded php';
$lang['dformat']     = 'date format';
$lang['signature']   = 'signature';
$lang['toptoclevel'] = 'top toc level';
$lang['maxtoclevel'] = 'max toc level';
$lang['maxseclevel'] = 'max section edit level';
$lang['camelcase']   = 'use camelcase for links';
$lang['deaccent']    = 'deaccent in pagenames';
$lang['useheading']  = 'use first heading';
$lang['refcheck']    = 'media reference check';
$lang['refshow']     = 'media references to show';
$lang['allowdebug']  = 'allow debug (disable!)';

$lang['usewordblock']= 'block spam based on words';
$lang['indexdelay']  = 'time delay before indexing';
$lang['relnofollow'] = 'use rel="nofollow"';
$lang['mailguard']   = 'obfuscate email addresses';

/* Authentication Options */
$lang['useacl']      = 'use ACL';
$lang['openregister']= 'open register';
$lang['autopasswd']  = 'autogenerate passwords';
$lang['resendpasswd']= 'allow resend password';
$lang['authtype']    = 'authentication backend';
$lang['passcrypt']   = 'password encryption';
$lang['defaultgroup']= 'default group';
$lang['superuser']   = 'superuser';
$lang['profileconfirm'] = 'profile confirm';

/* Advanced Options */
$lang['userewrite']  = 'use nice URLs';
$lang['useslash']    = 'use slash';
$lang['sepchar']     = 'page name word separator';
$lang['canonical']   = 'use fully canonical URLs';
$lang['autoplural']  = 'auto-plural';
$lang['usegzip']     = 'use gzip (for attic)';
$lang['cachetime']   = 'max. age for cache (sec)';
$lang['purgeonadd']  = 'purge cache on add';
$lang['locktime']    = 'max. age for lock files (sec)';
$lang['notify']      = 'notify email address';
$lang['mailfrom']    = 'wiki mail from';
$lang['gdlib']       = 'GD Lib version';
$lang['im_convert']  = 'imagemagick path';
$lang['spellchecker']= 'enable spellchecker';
$lang['subscribers'] = 'enable subscription support';
$lang['compress']    = 'Compress CSS & javascript files';
$lang['hidepages']   = 'Hide matching pages (regex)';
$lang['send404']     = 'Send "HTTP 404/Page Not Found"';
$lang['sitemap']     = 'Generate google sitemap (days)';

$lang['rss_type']    = 'rss feed type';
$lang['rss_linkto']  = 'rss links to';

/* Target options */
$lang['target____wiki']      = 'target for internal links';
$lang['target____interwiki'] = 'target for interwiki links';
$lang['target____extern']    = 'target for external links';
$lang['target____media']     = 'target for media links';
$lang['target____windows']   = 'target for windows links';

/* Proxy Options */
$lang['proxy____host'] = 'proxy - host';
$lang['proxy____port'] = 'proxy - port';
$lang['proxy____user'] = 'proxy - user name';
$lang['proxy____pass'] = 'proxy - password';
$lang['proxy____ssl']  = 'proxy - ssl';

/* Safemode Hack */
$lang['safemodehack'] = 'enable safemode hack';
$lang['ftp____host'] = 'ftp - host';
$lang['ftp____port'] = 'ftp - port';
$lang['ftp____user'] = 'ftp - user name';
$lang['ftp____pass'] = 'ftp - password';
$lang['ftp____root'] = 'ftp - root directory';

/* userewrite options */
$lang['userewrite_o_0'] = 'none';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'dokuwiki';

/* deaccent options */
$lang['deaccent_o_0'] = 'off';
$lang['deaccent_o_1'] = 'remove accents';
$lang['deaccent_o_2'] = 'romanize';

/* gdlib options */
$lang['gdlib_o_0'] = 'GD Lib not available';
$lang['gdlib_o_1'] = 'version 1.x';
$lang['gdlib_o_2'] = 'autodetect';

/* rss_type options */
$lang['rss_type_o_rss']  = 'RSS 0.91';
$lang['rss_type_o_rss1'] = 'RSS 1.0';
$lang['rss_type_o_rss2'] = 'RSS 2.0';
$lang['rss_type_o_atom'] = 'Atom 0.3';

/* rss_linkto options */
$lang['rss_linkto_o_diff']    = 'list of differences';
$lang['rss_linkto_o_page']    = 'the revised page';
$lang['rss_linkto_o_rev']     = 'list of revisions';
$lang['rss_linkto_o_current'] = 'the current page';

