<?php
/**
 * This is DokuWiki's Main Configuration file
 *
 * All the default values are kept here, you should not modify it but use
 * a local.php file instead to override the settings from here.
 *
 * This is a piece of PHP code so PHP syntax applies!
 *
 * For help with the configuration see http://www.splitbrain.org/dokuwiki/wiki:config
 */


/* Datastorage and Permissions */

$conf['fmode']       = 0644;              //set file creation mode
$conf['dmode']       = 0755;              //set directory creation mode
$conf['lang']        = 'en';              //your language
$conf['basedir']     = '';                //absolute dir from serveroot - blank for autodetection
$conf['baseurl']     = '';                //URL to server including protocol - blank for autodetect
$conf['savedir']     = './data';          //where to store all the files
$conf['allowdebug']  = 0;                 //allow debug output, enable if needed 0|1

/* Display Options */

$conf['start']       = 'start';           //name of start page
$conf['title']       = 'DokuWiki';        //what to show in the title
$conf['template']    = 'default';         //see tpl directory
$conf['fullpath']    = 0;                 //show full path of the document or relative to datadir only? 0|1
$conf['recent']      = 20;                //how many entries to show in recent
$conf['breadcrumbs'] = 10;                //how many recent visited pages to show
$conf['youarehere']  = 0;                 //show "You are here" navigation? 0|1
$conf['typography']  = 1;                 //convert quotes, dashes and stuff to typographic equivalents? 0|1
$conf['htmlok']      = 0;                 //may raw HTML be embedded? This may break layout and XHTML validity 0|1
$conf['phpok']       = 0;                 //may PHP code be embedded? Never do this on the internet! 0|1
$conf['dformat']     = 'Y/m/d H:i';       //dateformat accepted by PHPs date() function
$conf['signature']   = ' --- //[[@MAIL@|@NAME@]] @DATE@//'; //signature see wiki:config for details
$conf['toptoclevel'] = 1;                 //Level starting with and below to include in AutoTOC (max. 5)
$conf['maxtoclevel'] = 3;                 //Up to which level include into AutoTOC (max. 5)
$conf['maxseclevel'] = 3;                 //Up to which level create editable sections (max. 5)
$conf['camelcase']   = 0;                 //Use CamelCase for linking? (I don't like it) 0|1
$conf['deaccent']    = 1;                 //deaccented chars in pagenames (1) or romanize (2) or keep (0)?
$conf['useheading']  = 0;                 //use the first heading in a page as its name
$conf['refcheck']    = 1;                 //check for references before deleting media files
$conf['refshow']     = 0;                 //how many references should be shown, 5 is a good value

/* Antispam Features */

$conf['usewordblock']= 1;                 //block spam based on words? 0|1
$conf['indexdelay']  = 60*60*24*5;        //allow indexing after this time (seconds) default is 5 days
$conf['relnofollow'] = 1;                 //use rel="nofollow" for external links?
$conf['mailguard']   = 'hex';             //obfuscate email addresses against spam harvesters?
                                          //valid entries are:
                                          //  'visible' - replace @ with [at], . with [dot] and - with [dash]
                                          //  'hex'     - use hex entities to encode the mail address
                                          //  'none'    - do not obfuscate addresses

/* Authentication Options - read http://www.splitbrain.org/dokuwiki/wiki:acl */

$conf['useacl']      = 0;                //Use Access Control Lists to restrict access?
$conf['autopasswd']  = 1;                //autogenerate passwords and email them to user
$conf['authtype']    = 'plain';          //which authentication backend should be used
$conf['passcrypt']   = 'smd5';           //Used crypt method (smd5,md5,sha1,ssha,crypt,mysql,my411)
$conf['defaultgroup']= 'user';           //Default groups new Users are added to
$conf['superuser']   = '!!not set!!';    //The admin can be user or @group
$conf['profileconfirm'] = '1';           //Require current password to confirm changes to user profile
$conf['disableactions'] = '';            //comma separated list of actions to disable

/* Advanced Options */

$conf['updatecheck'] = 1;                //automatically check for new releases?
$conf['userewrite']  = 0;                //this makes nice URLs: 0: off 1: .htaccess 2: internal
$conf['useslash']    = 0;                //use slash instead of colon? only when rewrite is on
$conf['usedraft']    = 1;                //automatically save a draft while editing (0|1)
$conf['sepchar']     = '_';              //word separator character in page names; may be a
                                         //  letter, a digit, '_', '-', or '.'.
$conf['canonical']   = 0;                //Should all URLs use full canonical http://... style?
$conf['autoplural']  = 0;                //try (non)plural form of nonexisting files?
$conf['compression'] = 'gz';             //compress old revisions: (0: off) ('gz': gnuzip) ('bz2': bzip)
                                         //  bz2 generates smaller files, but needs more cpu-power
$conf['cachetime']   = 60*60*24;         //maximum age for cachefile in seconds (defaults to a day)
$conf['locktime']    = 15*60;            //maximum age for lockfiles (defaults to 15 minutes)
$conf['fetchsize']   = 0;                //maximum size (bytes) fetch.php may download from extern, disabled by default
$conf['notify']      = '';               //send change info to this email (leave blank for nobody)
$conf['registernotify'] = '';            //send info about newly registered users to this email (leave blank for nobody)
$conf['mailfrom']    = '';               //use this email when sending mails
$conf['gzip_output'] = 0;                //use gzip content encodeing for the output xhtml (if allowed by browser)
$conf['gdlib']       = 2;                //the GDlib version (0, 1 or 2) 2 tries to autodetect
$conf['im_convert']  = '';               //path to ImageMagicks convert (will be used instead of GD)
$conf['jpg_quality'] = '70';             //quality of compression when scaling jpg images (0-100)
$conf['spellchecker']= 0;                //enable Spellchecker (needs PHP >= 4.3.0 and aspell installed)
$conf['subscribers'] = 0;                //enable change notice subscription support
$conf['compress']    = 1;                //Strip whitespaces and comments from Styles and JavaScript? 1|0
$conf['hidepages']   = '';               //Regexp for pages to be skipped from RSS, Search and Recent Changes
$conf['send404']     = 0;                //Send a HTTP 404 status for non existing pages?
$conf['sitemap']     = 0;                //Create a google sitemap? How often? In days.
$conf['rss_type']    = 'rss1';           //type of RSS feed to provide, by default:
                                         //  'rss'  - RSS 0.91
                                         //  'rss1' - RSS 1.0
                                         //  'rss2' - RSS 2.0
                                         //  'atom' - Atom 0.3
$conf['rss_linkto'] = 'diff';            //what page RSS entries link to:
                                         //  'diff'    - page showing revision differences
                                         //  'page'    - the revised page itself
                                         //  'rev'     - page showing all revisions
                                         //  'current' - most recent revision of page
$conf['rss_update'] = 5*60;              //Update the RSS feed every n minutes (defaults to 5 minutes)
$conf['recent_days'] = 7;                //How many days of recent changes to keep. (days)

//Set target to use when creating links - leave empty for same window
$conf['target']['wiki']      = '';
$conf['target']['interwiki'] = '';
$conf['target']['extern']    = '';
$conf['target']['media']     = '';
$conf['target']['windows']   = '';

//Proxy setup - if your Server needs a proxy to access the web set these
$conf['proxy']['host'] = '';
$conf['proxy']['port'] = '';
$conf['proxy']['user'] = '';
$conf['proxy']['pass'] = '';
$conf['proxy']['ssl']  = 0;

/* Safemode Hack */

$conf['safemodehack'] = 0;               //read http://wiki.splitbrain.org/wiki:safemodehack !
$conf['ftp']['host'] = 'localhost';
$conf['ftp']['port'] = '21';
$conf['ftp']['user'] = 'user';
$conf['ftp']['pass'] = 'password';
$conf['ftp']['root'] = '/home/user/htdocs';

