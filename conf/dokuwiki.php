<?
/**
 * This is DokuWiki's Main Configuration file
 * This is a piece of PHP code so PHP syntax applies!
 *
 * For help with the configuration see http://www.splitbrain.org/dokuwiki/wiki:config
 */


/* Datastorage and Permissions */

$conf['umask']       = 0111;              //set the umask for new files
$conf['dmask']       = 0000;              //directory mask accordingly
$conf['lang']        = 'en';              //your language
$conf['datadir']     = './data';          //where to store the data
$conf['olddir']      = './attic';         //where to store old revisions
$conf['mediadir']    = './media';         //where to store media files
$conf['mediaweb']    = 'media';           //access to media from the web
$conf['changelog']   = './changes.log';   //change log
$conf['uploadtypes'] = 'gif|jpe?g|png|zip|pdf|tar(\.gz)?|tgz'; //regexp of allowed filetypes to upload

/* Display Options */

$conf['start']       = 'start';           //name of start page
$conf['title']       = 'DokuWiki';        //what to show in the title
$conf['fullpath']    = 0;                 //show full path of the document or relative to datadir only? 0|1
$conf['recent']      = 20;                //how many entries to show in recent
$conf['breadcrumbs'] = 10;                //how many recent visited pages to show
$conf['typography']  = 1;                 //convert quotes, dashes and stuff to typographic equivalents? 0|1
$conf['htmlok']      = 0;                 //may raw HTML be embedded? This may break layout and XHTML validity 0|1
$conf['phpok']       = 0;                 //may PHP code be embedded? Never do this on the internet! 0|1
$conf['dformat']     = 'Y/m/d H:i';       //dateformat accepted by PHPs date() function
$conf['signature']   = ' --- //[[@MAIL@|@NAME@]] @DATE@//'; //signature see wiki:config for details
$conf['maxtoclevel'] = 3;                 //Up to which level include into AutoTOC (max. 5)
$conf['maxseclevel'] = 3;                 //Up to which level create editable sections (max. 5)
$conf['camelcase']   = 0;                 //Use CamelCase for linking? (I don't like it) 0|1
$conf['deaccent']    = 1;                 //convert accented chars to unaccented ones in pagenames?

/* Antispam Features */

$conf['usewordblock']= 1;                 //block spam based on words? 0|1
$conf['mailguard']   = 'hex';             //obfuscate email addresses against spam harvesters?
                                          //valid entries are:
                                          //  'visible' - replace @ with [at], . with [dot] and - with [dash]
                                          //  'hex'     - use hex entities to encode the mail address
                                          //  'none'    - do not obfuscate addresses

/* Authentication Options */
$conf['useacl']      = 0;                //Use Access Control Lists to restrict access?
$conf['openregister']= 1;                //Should users to be allowed to register?
$conf['authtype']    = 'plain';          //which authentication DB should be used (currently plain only)
$conf['defaultgroup']= 'user';           //Default groups new Users are added to

/* Advanced Options */
$conf['userewrite']  = 0;                //this makes nice URLs but you need to enable it in .htaccess first 0|1
$conf['useslash']    = 0;                //use slash instead of colon? only when rewrite is on
$conf['canonical']   = 0;                //Should all URLs use full canonical http://... style?
$conf['autoplural']  = 0;                //try (non)plural form of nonexisting files?
$conf['usegzip']     = 1;                //gzip old revisions?
$conf['cachetime']   = 60*60*24;         //maximum age for cachefile in seconds (defaults to a day)
$conf['purgeonadd']  = 1;                //purge cache when a new file is added (needed for up to date links)
$conf['locktime']    = 15*60;            //maximum age for lockfiles (defaults to 15 minutes)
$conf['notify']      = '';               //send change info to this email (leave blank for nobody)
$conf['mailfrom']    = '';               //use this email when sending mails
$conf['gdlib']       = 2;                //the GDlib version (0, 1 or 2) 2 tries to autodetect

//Set target to use when creating links - leave empty for same window
$conf['target']['wiki']      = '';
$conf['target']['interwiki'] = '_blank';
$conf['target']['extern']    = '_blank';
$conf['target']['media']     = '';
$conf['target']['windows']   = '';

//this includes a local config file if exist which make upgrading more easy - just don't touch this
@include("conf/local.php");

//a small bugfix for some browsers/proxies just don't touch this either
$lang = array();
?>
