<?php
/**
 * Initialize some defaults needed for DokuWiki
 */

  // define the include path
  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

  // load the config file(s)
  require_once(DOKU_INC.'conf/dokuwiki.php');
  @include_once(DOKU_INC.'conf/local.php');

  //prepare language array
  $lang = array();

  // define baseURL
  if(!defined('DOKU_BASE')) define('DOKU_BASE',getBaseURL());
  if(!defined('DOKU_URL'))  define('DOKU_URL',getBaseURL(true));

  // define main script
  if(!defined('DOKU_SCRIPT')) define('DOKU_SCRIPT','doku.php');

  // set up error reporting to sane values
  error_reporting(E_ALL ^ E_NOTICE);

  // make session rewrites XHTML compliant
  ini_set('arg_separator.output', '&amp;');

  // init session
  session_name("DokuWiki");
  session_start();

  // kill magic quotes
  if (get_magic_quotes_gpc()) {
    if (!empty($_GET))    remove_magic_quotes($_GET);
    if (!empty($_POST))   remove_magic_quotes($_POST);
    if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
    if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
    if (!empty($_SESSION)) remove_magic_quotes($_SESSION);
    ini_set('magic_quotes_gpc', 0);
  }
  set_magic_quotes_runtime(0);
  ini_set('magic_quotes_sybase',0);

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
  $conf['datadir']       = realpath($conf['datadir']);
  if(!$conf['datadir'])    msg('Wrong datadir!',-1);
  $conf['olddir']        = realpath($conf['olddir']);
  if(!$conf['olddir'])     msg('Wrong olddir!',-1);
  $conf['mediadir']      = realpath($conf['mediadir']);
  if(!$conf['mediadir'])   msg('Wrong mediadir!',-1);

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

  $dir = dirname($_SERVER['PHP_SELF']).'/';

  $dir = str_replace('\\','/',$dir); #bugfix for weird WIN behaviour
  $dir = preg_replace('#//+#','/',$dir);

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


?>
