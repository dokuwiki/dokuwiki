<?php

/**
 * Metadata for configuration manager plugin
 *
 * Note: This file is loaded in Loader::loadMeta().
 *
 * Format:
 *   $meta[<setting name>] = array(<handler class id>,<param name> => <param value>);
 *
 *   <handler class id>  is the handler class name without the "setting_" prefix
 *
 * Defined classes (see core/Setting/*):
 *   Generic
 *   -------------------------------------------
 *   ''             - default class ('setting'), textarea, minimal input validation, setting output in quotes
 *   'string'       - single line text input, minimal input validation, setting output in quotes
 *   'numeric'      - text input, accepts numbers and arithmetic operators, setting output without quotes
 *                    if given the '_min' and '_max' parameters are used for validation
 *   'numericopt'   - like above, but accepts empty values
 *   'onoff'        - checkbox input, setting output  0|1
 *   'multichoice'  - select input (single choice), setting output with quotes, required _choices parameter
 *   'email'        - text input, input must conform to email address format, supports optional '_multiple'
 *                    parameter for multiple comma separated email addresses
 *   'password'     - password input, minimal input validation, setting output text in quotes, maybe encoded
 *                    according to the _code parameter
 *   'dirchoice'    - as multichoice, selection choices based on folders found at location specified in _dir
 *                    parameter (required). A pattern can be used to restrict the folders to only those which
 *                    match the pattern.
 *   'multicheckbox'- a checkbox for each choice plus an "other" string input, config file setting is a comma
 *                    separated list of checked choices
 *   'fieldset'     - used to group configuration settings, but is not itself a setting. To make this clear in
 *                    the language files the keys for this type should start with '_'.
 *   'array'        - a simple (one dimensional) array of string values, shown as comma separated list in the
 *                    config manager but saved as PHP array(). Values may not contain commas themselves.
 *                    _pattern matching on the array values supported.
 *   'regex'        - regular expression string, normally without delimiters; as for string, in addition tested
 *                    to see if will compile & run as a regex.  in addition to _pattern, also accepts _delimiter
 *                    (default '/') and _pregflags (default 'ui')
 *
 *  Single Setting
 *  -------------------------------------------------
 *   'savedir'     - as 'setting', input tested against initpath() (inc/init.php)
 *   'sepchar'     - as multichoice, selection constructed from string of valid values
 *   'authtype'    - as 'setting', input validated against a valid php file at expected location for auth files
 *   'im_convert'  - as 'setting', input must exist and be an im_convert module
 *   'disableactions' - as 'setting'
 *   'compression' - no additional parameters. checks php installation supports possible compression alternatives
 *   'licence'     - as multichoice, selection constructed from licence strings in language files
 *   'renderer'    - as multichoice, selection constructed from enabled renderer plugins which canRender()
 *   'authtype'    - as multichoice, selection constructed from the enabled auth plugins
 *
 *  Any setting commented or missing will use 'setting' class - text input, minimal validation, quoted output
 *
 * Defined parameters:
 *   '_caution'    - no value (default) or 'warning', 'danger', 'security'. display an alert along with the setting
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
 *   '_delimiter'  - string, default '/', a single character used as a delimiter for testing regex input values
 *   '_pregflags'  - string, default 'ui', valid preg pattern modifiers used when testing regex input values, for more
 *                   information see http://php.net/manual/en/reference.pcre.pattern.modifiers.php
 *   '_multiple'   - bool, allow multiple comma separated email values; optional for 'email', ignored by others
 *   '_other'      - how to handle other values (not listed in _choices). accepted values: 'always','exists','never'
 *                   default value 'always'. 'exists' only shows 'other' input field when the setting contains value(s)
 *                   not listed in choices (e.g. due to manual editing or update changing _choices).  This is safer than
 *                   'never' as it will not discard unknown/other values.
 *                   optional for 'multicheckbox', ignored by others
 *
 * The order of the settings influences the order in which they apppear in the config manager
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */

$meta['_basic'] = ['fieldset'];
$meta['title'] = ['string'];
$meta['start'] = ['string', '_caution' => 'warning', '_pattern' => '!^[^:;/]+$!']; // don't accept namespaces
$meta['lang'] = ['dirchoice', '_dir' => DOKU_INC . 'inc/lang/'];
$meta['template'] = ['dirchoice', '_dir' => DOKU_INC . 'lib/tpl/', '_pattern' => '/^[\w-]+$/'];
$meta['tagline'] = ['string'];
$meta['sidebar'] = ['string'];
$meta['license'] = ['license'];
$meta['savedir'] = ['savedir', '_caution' => 'danger'];
$meta['basedir'] = ['string', '_caution' => 'danger'];
$meta['baseurl'] = ['string', '_caution' => 'danger'];
$meta['cookiedir'] = ['string', '_caution' => 'danger'];
$meta['dmode'] = ['numeric', '_pattern' => '/0[0-7]{3,4}/']; // only accept octal representation
$meta['fmode'] = ['numeric', '_pattern' => '/0[0-7]{3,4}/']; // only accept octal representation
$meta['allowdebug'] = ['onoff', '_caution' => 'security'];

$meta['_display'] = ['fieldset'];
$meta['recent'] = ['numeric'];
$meta['recent_days'] = ['numeric'];
$meta['breadcrumbs'] = ['numeric', '_min' => 0];
$meta['youarehere'] = ['onoff'];
$meta['fullpath'] = ['onoff', '_caution' => 'security'];
$meta['typography'] = ['multichoice', '_choices' => [0, 1, 2]];
$meta['dformat'] = ['string'];
$meta['signature'] = ['string'];
$meta['showuseras'] = ['multichoice', '_choices' => ['loginname', 'username', 'username_link', 'email', 'email_link']];
$meta['toptoclevel'] = ['multichoice', '_choices' => [1, 2, 3, 4, 5]];   // 5 toc levels
$meta['tocminheads'] = ['multichoice', '_choices' => [0, 1, 2, 3, 4, 5, 10, 15, 20]];
$meta['maxtoclevel'] = ['multichoice', '_choices' => [0, 1, 2, 3, 4, 5]];
$meta['maxseclevel'] = ['multichoice', '_choices' => [0, 1, 2, 3, 4, 5]]; // 0 for no sec edit buttons
$meta['camelcase'] = ['onoff', '_caution' => 'warning'];
$meta['deaccent'] = ['multichoice', '_choices' => [0, 1, 2], '_caution' => 'warning'];
$meta['useheading'] = ['multichoice', '_choices' => [0, 'navigation', 'content', 1]];
$meta['sneaky_index'] = ['onoff'];
$meta['hidepages'] = ['regex'];

$meta['_authentication'] = ['fieldset'];
$meta['useacl'] = ['onoff', '_caution' => 'danger'];
$meta['autopasswd'] = ['onoff'];
$meta['authtype'] = ['authtype', '_caution' => 'danger'];
$meta['passcrypt'] = ['multichoice',
    '_choices' => [
        'smd5',
        'md5',
        'apr1',
        'sha1',
        'ssha',
        'lsmd5',
        'crypt',
        'mysql',
        'my411',
        'kmd5',
        'pmd5',
        'hmd5',
        'mediawiki',
        'bcrypt',
        'djangomd5',
        'djangosha1',
        'djangopbkdf2_sha1',
        'djangopbkdf2_sha256',
        'sha512',
        'argon2i',
        'argon2id']
];
$meta['defaultgroup'] = ['string'];
$meta['superuser'] = ['string', '_caution' => 'danger'];
$meta['manager'] = ['string'];
$meta['profileconfirm'] = ['onoff'];
$meta['rememberme'] = ['onoff'];
$meta['disableactions'] = ['disableactions',
    '_choices' => [
        'backlink',
        'index',
        'recent',
        'revisions',
        'search',
        'subscription',
        'register',
        'resendpwd',
        'profile',
        'profile_delete',
        'edit',
        'wikicode',
        'check',
        'rss'
    ],
    '_combine' => [
        'subscription' => ['subscribe', 'unsubscribe'],
        'wikicode' => ['source', 'export_raw']
    ]
];
$meta['auth_security_timeout'] = ['numeric'];
$meta['securecookie'] = ['onoff'];
$meta['samesitecookie'] = ['multichoice', '_choices' => ['', 'Lax', 'Strict', 'None']];
$meta['remote'] = ['onoff', '_caution' => 'security'];
$meta['remoteuser'] = ['string'];
$meta['remotecors'] = ['string', '_caution' => 'security'];

$meta['_anti_spam'] = ['fieldset'];
$meta['usewordblock'] = ['onoff'];
$meta['relnofollow'] = ['onoff'];
$meta['indexdelay'] = ['numeric'];
$meta['mailguard'] = ['multichoice', '_choices' => ['visible', 'hex', 'none']];
$meta['iexssprotect'] = ['onoff', '_caution' => 'security'];

$meta['_editing'] = ['fieldset'];
$meta['usedraft'] = ['onoff'];
$meta['locktime'] = ['numeric'];
$meta['cachetime'] = ['numeric'];

$meta['_links'] = ['fieldset'];
$meta['target____wiki'] = ['string'];
$meta['target____interwiki'] = ['string'];
$meta['target____extern'] = ['string'];
$meta['target____media'] = ['string'];
$meta['target____windows'] = ['string'];

$meta['_media'] = ['fieldset'];
$meta['mediarevisions'] = ['onoff'];
$meta['gdlib'] = ['multichoice', '_choices' => [0, 1, 2]];
$meta['im_convert'] = ['im_convert'];
$meta['jpg_quality'] = ['numeric', '_pattern' => '/^100$|^[1-9]?\d$/'];  //(0-100)
$meta['fetchsize'] = ['numeric'];
$meta['refcheck'] = ['onoff'];

$meta['_notifications'] = ['fieldset'];
$meta['subscribers'] = ['onoff'];
$meta['subscribe_time'] = ['numeric'];
$meta['notify'] = ['email', '_multiple' => true];
$meta['registernotify'] = ['email', '_multiple' => true];
$meta['mailfrom'] = ['email', '_placeholders' => true];
$meta['mailreturnpath'] = ['email', '_placeholders' => true];
$meta['mailprefix'] = ['string'];
$meta['htmlmail'] = ['onoff'];
$meta['dontlog'] = ['disableactions', '_choices' => ['error', 'debug', 'deprecated']];
$meta['logretain'] = ['numeric', '_min' => 0, '_pattern' => '/^\d+$/'];

$meta['_syndication'] = ['fieldset'];
$meta['sitemap'] = ['numeric'];
$meta['rss_type'] = ['multichoice', '_choices' => ['rss', 'rss1', 'rss2', 'atom', 'atom1']];
$meta['rss_linkto'] = ['multichoice', '_choices' => ['diff', 'page', 'rev', 'current']];
$meta['rss_content'] = ['multichoice', '_choices' => ['abstract', 'diff', 'htmldiff', 'html']];
$meta['rss_media'] = ['multichoice', '_choices' => ['both', 'pages', 'media']];
$meta['rss_update'] = ['numeric'];
$meta['rss_show_summary'] = ['onoff'];
$meta['rss_show_deleted'] = ['onoff'];

$meta['_advanced'] = ['fieldset'];
$meta['updatecheck'] = ['onoff'];
$meta['userewrite'] = ['multichoice', '_choices' => [0, 1, 2], '_caution' => 'danger'];
$meta['useslash'] = ['onoff'];
$meta['sepchar'] = ['sepchar', '_caution' => 'warning'];
$meta['canonical'] = ['onoff'];
$meta['fnencode'] = ['multichoice', '_choices' => ['url', 'safe', 'utf-8'], '_caution' => 'warning'];
$meta['autoplural'] = ['onoff'];
$meta['compress'] = ['onoff'];
$meta['cssdatauri'] = ['numeric', '_pattern' => '/^\d+$/'];
$meta['gzip_output'] = ['onoff'];
$meta['send404'] = ['onoff'];
$meta['compression'] = ['compression', '_caution' => 'warning'];
$meta['broken_iua'] = ['onoff'];
$meta['xsendfile'] = ['multichoice', '_choices' => [0, 1, 2, 3], '_caution' => 'warning'];
$meta['renderer_xhtml'] = ['renderer', '_format' => 'xhtml', '_choices' => ['xhtml'], '_caution' => 'warning'];
$meta['readdircache'] = ['numeric'];
$meta['search_nslimit'] = ['numeric', '_min' => 0];
$meta['search_fragment'] = ['multichoice', '_choices' => ['exact', 'starts_with', 'ends_with', 'contains']];
$meta['trustedproxy'] = ['regex'];

$meta['_feature_flags'] = ['fieldset'];
$meta['defer_js'] = ['onoff'];
$meta['hidewarnings'] = ['onoff'];

$meta['_network'] = ['fieldset'];
$meta['dnslookups'] = ['onoff'];
$meta['jquerycdn'] = ['multichoice', '_choices' => [0, 'jquery', 'cdnjs']];
$meta['proxy____host'] = ['string', '_pattern' => '#^(|[a-z0-9\-\.+]+)$#i'];
$meta['proxy____port'] = ['numericopt'];
$meta['proxy____user'] = ['string'];
$meta['proxy____pass'] = ['password', '_code' => 'base64'];
$meta['proxy____ssl'] = ['onoff'];
$meta['proxy____except'] = ['string'];
