<?php
/**
 * Initialize some defaults needed for DokuWiki
 */

  // define the include path
  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

  // set up error reporting to sane values
  error_reporting(E_ALL ^ E_NOTICE);

  //prepare config array()
  global $conf;
  $conf = array();

  // load the config file(s)
  require_once(DOKU_INC.'conf/dokuwiki.php');
  @include_once(DOKU_INC.'conf/local.php');

  //prepare language array
  global $lang;
  $lang = array();

  //load the language files
  require_once(DOKU_INC.'inc/lang/en/lang.php');
  require_once(DOKU_INC.'inc/lang/'.$conf['lang'].'/lang.php');

  // define baseURL
  if(!defined('DOKU_BASE')) define('DOKU_BASE',getBaseURL());
  if(!defined('DOKU_URL'))  define('DOKU_URL',getBaseURL(true));

  // define Plugin dir
  if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

  // define main script
  if(!defined('DOKU_SCRIPT')) define('DOKU_SCRIPT','doku.php');

  // define Template baseURL
  if(!defined('DOKU_TPL')) define('DOKU_TPL',
                                  DOKU_BASE.'lib/tpl/'.$conf['template'].'/');

  // make session rewrites XHTML compliant
  @ini_set('arg_separator.output', '&amp;');

  // init session
  session_name("DokuWiki");
  if (!headers_sent()) session_start();

  // kill magic quotes
  if (get_magic_quotes_gpc()) {
    if (!empty($_GET))    remove_magic_quotes($_GET);
    if (!empty($_POST))   remove_magic_quotes($_POST);
    if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
    if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
    if (!empty($_SESSION)) remove_magic_quotes($_SESSION);
    @ini_set('magic_quotes_gpc', 0);
  }
  @set_magic_quotes_runtime(0);
  @ini_set('magic_quotes_sybase',0);

  // disable gzip if not available
  if($conf['usegzip'] && !function_exists('gzopen')){
    $conf['usegzip'] = 0;
  }

  // remember original umask
  $conf['oldumask'] = umask();

  // make absolute mediaweb
  if(!preg_match('#^(https?://|/)#i',$conf['mediaweb'])){
    $conf['mediaweb'] = getBaseURL().$conf['mediaweb'];
  }

  // make real paths and check them
  $conf['datadir']       = init_path($conf['datadir']);
  if(!$conf['datadir'])    die('Wrong datadir! Check config!');
  $conf['olddir']        = init_path($conf['olddir']);
  if(!$conf['olddir'])     die('Wrong olddir! Check config!');
  $conf['mediadir']      = init_path($conf['mediadir']);
  if(!$conf['mediadir'])   die('Wrong mediadir! Check config!');

  // automatic upgrade to script versions of certain files
  scriptify(DOKU_INC.'conf/users.auth');
  scriptify(DOKU_INC.'conf/acl.auth');


/**
 * returns absolute path
 *
 * This tries the given past first, then checks in DOKU_INC
 */
function init_path($path){
  $p = realpath($path);
  if(is_dir($p)) return $p;
  $p = realpath(DOKU_INC.$path);
  if(is_dir($p)) return $p;
  return '';
}

/**
 * remove magic quotes recursivly
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function remove_magic_quotes(&$array) {
  foreach (array_keys($array) as $key) {
    if (is_array($array[$key])) {
      remove_magic_quotes($array[$key]);
    }else {
      $array[$key] = stripslashes($array[$key]);
    }
  }
}

/**
 * Returns the full absolute URL to the directory where
 * DokuWiki is installed in (includes a trailing slash)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getBaseURL($abs=false){
  global $conf;
  //if canonical url enabled always return absolute
  if($conf['canonical']) $abs = true;

  if($conf['basedir']){
    $dir = $conf['basedir'].'/';
  }elseif($_SERVER['SCRIPT_NAME']){
    $dir = dirname($_SERVER['SCRIPT_NAME']).'/';
  }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
    $dir = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                         $_SERVER['SCRIPT_FILENAME']);
    $dir = dirname('/'.$dir).'/';
  }else{
    $dir = dirname($_SERVER['PHP_SELF']).'/';
  }

  $dir = str_replace('\\','/',$dir); #bugfix for weird WIN behaviour
  $dir = preg_replace('#//+#','/',$dir);
  
  //handle script in lib/exe dir
  $dir = preg_replace('!lib/exe/$!','',$dir);

  //finish here for relative URLs
  if(!$abs) return $dir;

  $port = ':'.$_SERVER['SERVER_PORT'];
  //remove port from hostheader as sent by IE
  $host = preg_replace('/:.*$/','',$_SERVER['HTTP_HOST']);

  // see if HTTPS is enabled - apache leaves this empty when not available,
  // IIS sets it to 'off', 'false' and 'disabled' are just guessing
  if (preg_match('/^(|off|false|disabled)$/i',$_SERVER['HTTPS'])){
    $proto = 'http://';
    if ($_SERVER['SERVER_PORT'] == '80') {
      $port='';
    }
  }else{
    $proto = 'https://';
    if ($_SERVER['SERVER_PORT'] == '443') {
      $port='';
    }
  }

  return $proto.$host.$port.$dir;
}

/**
 * Append a PHP extension to a given file and adds an exit call
 *
 * This is used to migrate some old configfiles. An added PHP extension
 * ensures the contents are not shown to webusers even if .htaccess files
 * do not work
 *
 * @author Jan Decaluwe <jan@jandecaluwe.com>
 */
function scriptify($file) {
  // checks
  if (!is_readable($file)) {
    return;
  }
  $fn = $file.'.php';
  if (@file_exists($fn)) {
    return;
  }
  $fh = fopen($fn, 'w');
  if (!$fh) {
    die($fn.' is not writable!');
  }
  // write php exit hack first
  fwrite($fh, "# $fn\n");
  fwrite($fh, '# <?php exit()?>'."\n");
  fwrite($fh, "# Don't modify the lines above\n");
  fwrite($fh, "#\n");
  // copy existing lines
  $lines = file($file);
  foreach ($lines as $line){
    fwrite($fh, $line);
  }
  fclose($fh);
  //try to rename the old file
  @rename($file,"$file.old");
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
