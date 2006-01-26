<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']       = 'Configuration Settings ...'; 

$lang['error']      = 'Settings not updated due to an invalid value, please review your changes and resubmit.
                       <br />The incorrect value(s) will be shown surrounded by a red border.';
$lang['updated']    = 'Settings updated successfully.';
$lang['nochoice']   = '(no other choices available)';
$lang['locked']     = 'The settings file can not be updated, if this is unintentional, <br />
                       ensure the local settings file name and permissions are correct.';

// settings prompts
$lang['umask']       = 'new file permission mask';      //set the umask for new files
$lang['dmask']       = 'new folder permission mask';    //directory mask accordingly
$lang['lang']        = 'language';                      //your language
$lang['basedir']     = 'base directory';     //absolute dir from serveroot - blank for autodetection
$lang['baseurl']     = 'base url';           //URL to server including protocol - blank for autodetect
$lang['savedir']     = 'save directory';     //where to store all the files
$lang['start']       = 'start page name';    //name of start page
$lang['title']       = 'wiki title';         //what to show in the title
$lang['template']    = 'template';           //see tpl directory
$lang['fullpath']    = 'use full path';      //show full path of the document or relative to datadir only? 0|1
$lang['recent']      = 'recent changes';     //how many entries to show in recent
$lang['breadcrumbs'] = 'breadcrumbs';        //how many recent visited pages to show
$lang['typography']  = 'typography';         //convert quotes, dashes and stuff to typographic equivalents? 0|1
$lang['htmlok']      = 'allow embedded html';//may raw HTML be embedded? This may break layout and XHTML validity 0|1
$lang['phpok']       = 'allow embedded php'; //may PHP code be embedded? Never do this on the internet! 0|1
$lang['dformat']     = 'date format';        //dateformat accepted by PHPs date() function
$lang['signature']   = 'signature';          //signature see wiki:langig for details
$lang['toptoclevel'] = 'top toc level';      //Level starting with and below to include in AutoTOC (max. 5)
$lang['maxtoclevel'] = 'max toc level';      //Up to which level include into AutoTOC (max. 5)
$lang['maxseclevel'] = 'max section edit level';   //Up to which level create editable sections (max. 5)
$lang['camelcase']   = 'use camelcase for links';  //Use CamelCase for linking? (I don't like it) 0|1
$lang['deaccent']    = 'deaccent in pagenames';    //convert accented chars to unaccented ones in pagenames?
$lang['useheading']  = 'use first heading';        //use the first heading in a page as its name
$lang['refcheck']    = 'media reference check';    //check for references before deleting media files
$lang['refshow']     = 'media references to show'; //how many references should be shown, 5 is a good value
$lang['allowdebug']  = 'allow debug (disable!)';   //make debug possible, disable after install! 0|1

$lang['usewordblock']= 'block spam based on words';  //block spam based on words? 0|1
$lang['indexdelay']  = 'time delay before indexing'; //allow indexing after this time (seconds) default is 5 days
$lang['relnofollow'] = 'use rel="nofollow"';         //use rel="nofollow" for external links?
$lang['mailguard']   = 'obfuscate email addresses';  //obfuscate email addresses against spam harvesters?

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */
$lang['useacl']      = 'use ACL';                //Use Access Control Lists to restrict access?
$lang['openregister']= 'open register';          //Should users to be allowed to register?
$lang['autopasswd']  = 'autogenerate passwords'; //autogenerate passwords and email them to user
$lang['authtype']    = 'authentication backend'; //which authentication backend should be used
$lang['passcrypt']   = 'password encryption';    //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$lang['defaultgroup']= 'default group';          //Default groups new Users are added to
$lang['superuser']   = 'superuser';              //The admin can be user or @group
$lang['profileconfirm'] = 'profile confirm';     //Require current password to langirm changes to user profile

/* Advanced Options */
$lang['userewrite']  = 'use nice URLs';             //this makes nice URLs: 0: off 1: .htaccess 2: internal
$lang['useslash']    = 'use slash';                 //use slash instead of colon? only when rewrite is on
$lang['sepchar']     = 'page name word separator';  //word separator character in page names; may be a
$lang['canonical']   = 'use fully canonical URLs';  //Should all URLs use full canonical http://... style?
$lang['autoplural']  = 'auto-plural';               //try (non)plural form of nonexisting files?
$lang['usegzip']     = 'use gzip (for attic)';      //gzip old revisions?
$lang['cachetime']   = 'max. age for cache (sec)';  //maximum age for cachefile in seconds (defaults to a day)
$lang['purgeonadd']  = 'purge cache on add';        //purge cache when a new file is added (needed for up to date links)
$lang['locktime']    = 'max. age for lock files (sec)';  //maximum age for lockfiles (defaults to 15 minutes)
$lang['notify']      = 'notify email address';      //send change info to this email (leave blank for nobody)
$lang['mailfrom']    = 'wiki mail from';            //use this email when sending mails
$lang['gdlib']       = 'GD Lib version';              //the GDlib version (0, 1 or 2) 2 tries to autodetect
$lang['im_convert']  = 'imagemagick path';            //path to ImageMagicks convert (will be used instead of GD)
$lang['spellchecker']= 'enable spellchecker';         //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$lang['subscribers'] = 'enable subscription support'; //enable change notice subscription support
$lang['compress']    = 'Compress CSS & javascript files';  //Strip whitespaces and comments from Styles and JavaScript? 1|0
$lang['hidepages']   = 'Hide matching pages (regex)';      //Regexp for pages to be skipped from RSS, Search and Recent Changes
$lang['send404']     = 'Send "HTTP404/Page Not Found"';    //Send a HTTP 404 status for non existing pages?
$lang['sitemap']     = 'Generate google sitemap (days)';   //Create a google sitemap? How often? In days.

$lang['rss_type']    = 'rss feed type';             //type of RSS feed to provide, by default:
$lang['rss_linkto']  = 'rss links to';              //what page RSS entries link to:

//Set target to use when creating links - leave empty for same window
$lang['target____wiki']      = 'target for internal links';
$lang['target____interwiki'] = 'target for interwiki links';
$lang['target____extern']    = 'target for external links';
$lang['target____media']     = 'target for media links';
$lang['target____windows']   = 'target for windows links';

//Proxy setup - if your Server needs a proxy to access the web set these
$lang['proxy____host'] = 'proxy - host';
$lang['proxy____port'] = 'proxy - port';
$lang['proxy____user'] = 'proxy - user name';
$lang['proxy____pass'] = 'proxy - password';
$lang['proxy____ssl']  = 'proxy - ssl';

/* Safemode Hack */
$lang['safemodehack'] = 'enable safemode hack';  //read http://wiki.splitbrain.org/wiki:safemodehack !
$lang['ftp____host'] = 'ftp - host';
$lang['ftp____port'] = 'ftp - port';
$lang['ftp____user'] = 'ftp - user name';
$lang['ftp____pass'] = 'ftp - password';
$lang['ftp____root'] = 'ftp - root directory';

/* userewrite options */
$lang['userewrite_o_0'] = 'none';
$lang['userewrite_o_1'] = 'htaccess';
$lang['userewrite_o_2'] = 'dokuwiki';

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



