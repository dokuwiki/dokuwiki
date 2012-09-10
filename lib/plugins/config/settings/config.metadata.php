<?php
/**
 * Metadata for configuration manager plugin
 *
 * Note:  This file should be included within a function to ensure it
 *        doesn't clash with the settings it is describing.
 *
 * Format:
 *   $meta[<setting name>] = array(<handler class id>,<param name> => <param value>);
 *
 *   <handler class id>  is the handler class name without the "setting_" prefix
 *
 * Defined classes:
 *   Generic (source: settings/config.class.php)
 *   -------------------------------------------
 *   ''             - default class ('setting'), textarea, minimal input validation, setting output in quotes
 *   'string'       - single line text input, minimal input validation, setting output in quotes
 *   'numeric'      - text input, accepts numbers and arithmetic operators, setting output without quotes
 *                    if given the '_min' and '_max' parameters are used for validation
 *   'numericopt'   - like above, but accepts empty values
 *   'onoff'        - checkbox input, setting output  0|1
 *   'multichoice'  - select input (single choice), setting output with quotes, required _choices parameter
 *   'email'        - text input, input must conform to email address format, setting output in quotes
 *   'richemail'    - text input, input must conform to email address format but accepts variables and
 *                    emails with a real name prepended (when email address is given in <>)
 *   'password'     - password input, minimal input validation, setting output text in quotes, maybe encoded
 *                    according to the _code parameter
 *   'dirchoice'    - as multichoice, selection choices based on folders found at location specified in _dir
 *                    parameter (required). A pattern can be used to restrict the folders to only those which
 *                    match the pattern.
 *   'multicheckbox'- a checkbox for each choice plus an "other" string input, config file setting is a comma
 *                    separated list of checked choices
 *   'fieldset'     - used to group configuration settings, but is not itself a setting. To make this clear in
 *                    the language files the keys for this type should start with '_'.
 *
 *  Single Setting (source: settings/extra.class.php)
 *  -------------------------------------------------
 *   'savedir'     - as 'setting', input tested against initpath() (inc/init.php)
 *   'sepchar'     - as multichoice, selection constructed from string of valid values
 *   'authtype'    - as 'setting', input validated against a valid php file at expected location for auth files
 *   'im_convert'  - as 'setting', input must exist and be an im_convert module
 *   'disableactions' - as 'setting'
 *   'compression' - no additional parameters. checks php installation supports possible compression alternatives
 *
 *  Any setting commented or missing will use 'setting' class - text input, minimal validation, quoted output
 *
 * Defined parameters:
 *   '_pattern'    - string, a preg pattern. input is tested against this pattern before being accepted
 *                   optional all classes, except onoff & multichoice which ignore it
 *   '_choices'    - array of choices. used to populate a selection box. choice will be replaced by a localised
 *                   language string, indexed by  <setting name>_o_<choice>, if one exists
 *                   required by 'multichoice' & 'multicheckbox' classes, ignored by others
 *   '_dir'        - location of directory to be used to populate choice list
 *                   required by 'dirchoice' class, ignored by other classes
 *   '_combine'    - complimentary output setting values which can be combined into a single display checkbox
 *                   optional for 'multicheckbox', ignored by other classes
 *   '_code'       - encoding method to use, accepted values: 'base64','uuencode','plain'.  defaults to plain.
 *   '_min'        - minimum numeric value, optional for 'numeric' and 'numericopt', ignored by others
 *   '_max'        - maximum numeric value, optional for 'numeric' and 'numericopt', ignored by others
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */
// ---------------[ settings for settings ]------------------------------
$config['format']  = 'php';      // format of setting files, supported formats: php
$config['varname'] = 'conf';     // name of the config variable, sans $

// this string is written at the top of the rewritten settings file,
// !! do not include any comment indicators !!
// this value can be overriden when calling save_settings() method
$config['heading'] = 'Dokuwiki\'s Main Configuration File - Local Settings';

/* DEPRECATED
// ---------------[ setting files ]--------------------------------------
// these values can be string expressions, they will be eval'd before use
$file['local']     = "DOKU_CONF.'local.php'";            // mandatory (file doesn't have to exist)
$file['default']   = "DOKU_CONF.'dokuwiki.php'";         // optional
$file['protected'] = "DOKU_CONF.'local.protected.php'";  // optional
 */

// test value (FIXME, remove before publishing)
//$meta['test']     = array('multichoice','_choices' => array(''));

// --------------[ setting metadata ]------------------------------------
// - for description of format and fields see top of file
// - order the settings in the order you wish them to appear
// - any settings not mentioned will come after the last setting listed and
//   will use the default class with no parameters

$meta['_basic']   = array('fieldset');
$meta['title']    = array('string');
$meta['start']    = array('string','_pattern' => '!^[^:;/]+$!'); // don't accept namespaces
$meta['lang']     = array('dirchoice','_dir' => DOKU_INC.'inc/lang/');
$meta['template'] = array('dirchoice','_dir' => DOKU_INC.'lib/tpl/','_pattern' => '/^[\w-]+$/');
$meta['license']  = array('license');
$meta['savedir']  = array('savedir');
$meta['basedir']  = array('string');
$meta['baseurl']  = array('string');
$meta['cookiedir'] = array('string');
$meta['dmode']    = array('numeric','_pattern' => '/0[0-7]{3,4}/'); // only accept octal representation
$meta['fmode']    = array('numeric','_pattern' => '/0[0-7]{3,4}/'); // only accept octal representation
$meta['allowdebug']  = array('onoff');

$meta['_display']    = array('fieldset');
$meta['recent']      = array('numeric');
$meta['breadcrumbs'] = array('numeric','_min' => 0);
$meta['youarehere']  = array('onoff');
$meta['fullpath']    = array('onoff');
$meta['typography']  = array('multichoice','_choices' => array(0,1,2));
$meta['dformat']     = array('string');
$meta['signature']   = array('string');
$meta['showuseras']  = array('multichoice','_choices' => array('loginname','username','email','email_link'));
$meta['toptoclevel'] = array('multichoice','_choices' => array(1,2,3,4,5));   // 5 toc levels
$meta['tocminheads'] = array('multichoice','_choices' => array(0,1,2,3,4,5,10,15,20));
$meta['maxtoclevel'] = array('multichoice','_choices' => array(0,1,2,3,4,5));
$meta['maxseclevel'] = array('multichoice','_choices' => array(0,1,2,3,4,5)); // 0 for no sec edit buttons
$meta['camelcase']   = array('onoff');
$meta['deaccent']    = array('multichoice','_choices' => array(0,1,2));
$meta['useheading']  = array('multichoice','_choices' => array(0,'navigation','content',1));
$meta['refcheck']    = array('onoff');
$meta['refshow']     = array('numeric');

$meta['_authentication'] = array('fieldset');
$meta['useacl']      = array('onoff');
$meta['autopasswd']  = array('onoff');
$meta['authtype']    = array('authtype');
$meta['passcrypt']   = array('multichoice','_choices' => array('smd5','md5','apr1','sha1','ssha','crypt','mysql','my411','kmd5','pmd5','hmd5'));
$meta['defaultgroup']= array('string');
$meta['superuser']   = array('string');
$meta['manager']     = array('string');
$meta['profileconfirm'] = array('onoff');
$meta['rememberme'] = array('onoff');
$meta['registernotify'] = array('email');
$meta['disableactions'] = array('disableactions',
                                '_choices' => array('backlink','index','recent','revisions','search','subscription','register','resendpwd','profile','edit','wikicode','check'),
                                '_combine' => array('subscription' => array('subscribe','unsubscribe'), 'wikicode' => array('source','export_raw')));
$meta['sneaky_index'] = array('onoff');
$meta['auth_security_timeout'] = array('numeric');
$meta['securecookie'] = array('onoff');
$meta['xmlrpc']       = array('onoff');
$meta['xmlrpcuser']   = array('string');

$meta['_anti_spam']  = array('fieldset');
$meta['usewordblock']= array('onoff');
$meta['relnofollow'] = array('onoff');
$meta['indexdelay']  = array('numeric');
$meta['mailguard']   = array('multichoice','_choices' => array('visible','hex','none'));
$meta['iexssprotect']= array('onoff');

$meta['_editing']    = array('fieldset');
$meta['usedraft']    = array('onoff');
$meta['htmlok']      = array('onoff');
$meta['phpok']       = array('onoff');
$meta['notify']      = array('email', '_multiple' => true);
$meta['subscribers'] = array('onoff');
$meta['subscribe_time'] = array('numeric');
$meta['locktime']    = array('numeric');
$meta['cachetime']   = array('numeric');

$meta['_links']    = array('fieldset');
$meta['target____wiki']      = array('string');
$meta['target____interwiki'] = array('string');
$meta['target____extern']    = array('string');
$meta['target____media']     = array('string');
$meta['target____windows']   = array('string');

$meta['_media']      = array('fieldset');
$meta['mediarevisions']  = array('onoff');
$meta['gdlib']       = array('multichoice','_choices' => array(0,1,2));
$meta['im_convert']  = array('im_convert');
$meta['jpg_quality'] = array('numeric','_pattern' => '/^100$|^[1-9]?[0-9]$/');  //(0-100)
$meta['fetchsize']   = array('numeric');

$meta['_advanced']   = array('fieldset');
$meta['updatecheck'] = array('onoff');
$meta['userewrite']  = array('multichoice','_choices' => array(0,1,2));
$meta['useslash']    = array('onoff');
$meta['sepchar']     = array('sepchar');
$meta['canonical']   = array('onoff');
$meta['fnencode']    = array('multichoice','_choices' => array('url','safe','utf-8'));
$meta['autoplural']  = array('onoff');
$meta['mailfrom']    = array('richemail');
$meta['mailprefix']  = array('string');
$meta['compress']    = array('onoff');
$meta['cssdatauri']  = array('numeric','_pattern' => '/^\d+$/');
$meta['gzip_output'] = array('onoff');
$meta['hidepages']   = array('string');
$meta['send404']     = array('onoff');
$meta['compression'] = array('compression');
$meta['sitemap']     = array('numeric');
$meta['rss_type']    = array('multichoice','_choices' => array('rss','rss1','rss2','atom','atom1'));
$meta['rss_linkto']  = array('multichoice','_choices' => array('diff','page','rev','current'));
$meta['rss_content'] = array('multichoice','_choices' => array('abstract','diff','htmldiff','html'));
$meta['rss_update']  = array('numeric');
$meta['recent_days'] = array('numeric');
$meta['rss_show_summary'] = array('onoff');
$meta['broken_iua']  = array('onoff');
$meta['xsendfile']   = array('multichoice','_choices' => array(0,1,2,3));
$meta['renderer_xhtml'] = array('renderer','_format' => 'xhtml','_choices' => array('xhtml'));
$meta['readdircache'] = array('numeric');

$meta['_network']    = array('fieldset');
$meta['proxy____host'] = array('string','_pattern' => '#^(|[a-z0-9\-\.+]+)$#i');
$meta['proxy____port'] = array('numericopt');
$meta['proxy____user'] = array('string');
$meta['proxy____pass'] = array('password','_code' => 'base64');
$meta['proxy____ssl']  = array('onoff');
$meta['proxy____except'] = array('string');
$meta['safemodehack'] = array('onoff');
$meta['ftp____host']  = array('string','_pattern' => '#^(|[a-z0-9\-\.+]+)$#i');
$meta['ftp____port']  = array('numericopt');
$meta['ftp____user']  = array('string');
$meta['ftp____pass']  = array('password','_code' => 'base64');
$meta['ftp____root']  = array('string');

