<?php
/**
 * Initialize some defaults needed for DokuWiki
 */

  // define the include path
  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

  // define config path (packagers may want to change this to /etc/dokuwiki/)
  if(!defined('DOKU_CONF')) define('DOKU_CONF',DOKU_INC.'conf/');

  // set up error reporting to sane values
  error_reporting(E_ALL ^ E_NOTICE);

  //prepare config array()
  global $conf;
  $conf = array();

  // load the config file(s)
  require_once(DOKU_CONF.'dokuwiki.php');
  if(@file_exists(DOKU_CONF.'local.php')){
    require_once(DOKU_CONF.'local.php');
  }

  //prepare language array
  global $lang;
  $lang = array();

  //load the language files
  require_once(DOKU_INC.'inc/lang/en/lang.php');
  if ( $conf['lang'] && $conf['lang'] != 'en' ) {
    require_once(DOKU_INC.'inc/lang/'.$conf['lang'].'/lang.php');
  }

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

  // define real Template directory
  if(!defined('DOKU_TPLINC')) define('DOKU_TPLINC',
                                  DOKU_INC.'lib/tpl/'.$conf['template'].'/');

  // make session rewrites XHTML compliant
  @ini_set('arg_separator.output', '&amp;');

  // init session
  if (!headers_sent() && !defined('NOSESSION')){
    session_name("DokuWiki");
    session_start();
  }

  // kill magic quotes
  if (get_magic_quotes_gpc() && !defined('MAGIC_QUOTES_STRIPPED')) {
    if (!empty($_GET))    remove_magic_quotes($_GET);
    if (!empty($_POST))   remove_magic_quotes($_POST);
    if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
    if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
    if (!empty($_SESSION)) remove_magic_quotes($_SESSION);
    @ini_set('magic_quotes_gpc', 0);
    define('MAGIC_QUOTES_STRIPPED',1);
  }
  @set_magic_quotes_runtime(0);
  @ini_set('magic_quotes_sybase',0);

  // disable gzip if not available
  if($conf['usegzip'] && !function_exists('gzopen')){
    $conf['usegzip'] = 0;
  }

  // Legacy support for old umask/dmask scheme
  if(isset($conf['dmask'])) {
    unset($conf['dmask']);
    unset($conf['fmask']);
    unset($conf['umask']);
  }

  // Set defaults for fmode, dmode and umask.
  if(!isset($conf['fmode'])) {
    $conf['fmode'] = 0666;
  }
  if(!isset($conf['dmode'])) {
    $conf['dmode'] = 0777;
  }
  if(!isset($conf['umask'])) {
    $conf['umask'] = umask();
  }

  // Precalculate the fmask and dmask, so we can set later.
  if(($conf['umask'] != umask()) or ($conf['fmode'] != 0666)) {
    $conf['fmask'] = $conf['fmode'] & ~$conf['umask'];
  }
  if(($conf['umask'] != umask()) or ($conf['dmode'] != 0666)) {
    $conf['dmask'] = $conf['dmode'] & ~$conf['umask'];
  }

  // make real paths and check them
  init_paths();
  init_files();

  // automatic upgrade to script versions of certain files
  scriptify(DOKU_CONF.'users.auth');
  scriptify(DOKU_CONF.'acl.auth');


/**
 * Checks paths from config file
 */
function init_paths(){
  global $conf;

  $paths = array('datadir'   => 'pages',
                 'olddir'    => 'attic',
                 'mediadir'  => 'media',
                 'metadir'   => 'meta',
                 'cachedir'  => 'cache',
                 'lockdir'   => 'locks',
                 'changelog' => 'changes.log');

  foreach($paths as $c => $p){
    if(!$conf[$c])   $conf[$c] = $conf['savedir'].'/'.$p;
    $conf[$c]        = init_path($conf[$c]);
    if(!$conf[$c])   nice_die("The $c does not exist, isn't accessable or writable.
                               Check your config and permission settings!");
  }
}

/**
 * Checks the existance of certain files and creates them if missing.
 */
function init_files(){
  global $conf;

  $files = array( $conf['cachedir'].'/word.idx',
                  $conf['cachedir'].'/page.idx',
                  $conf['cachedir'].'/index.idx');

  foreach($files as $file){
    if(!@file_exists($file)){
      $fh = @fopen($file,'a');
      if($fh){
        fclose($fh);
        if(isset($conf['fmask'])) { chmod($file, $conf['fmask']); }
      }else{
        nice_die("$file is not writable. Check your permissions settings!");
      }
    }
  }
}

/**
 * Returns absolute path
 *
 * This tries the given path first, then checks in DOKU_INC.
 * Check for accessability on directories as well.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function init_path($path){
  // check existance
  $p = realpath($path);
  if(!@file_exists($p)){
    $p = realpath(DOKU_INC.$path);
    if(!@file_exists($p)){
      return '';
    }
  }

  // check writability
  if(!@is_writable($p)){
    return '';
  }

  // check accessability (execute bit) for directories
  if(@is_dir($p) && !@file_exists("$p/.")){
    return '';
  }

  return $p;
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
  }elseif(substr($_SERVER['SCRIPT_NAME'],-4) == '.php'){
    $dir = dirname($_SERVER['SCRIPT_NAME']).'/';
  }elseif(substr($_SERVER['PHP_SELF'],-4) == '.php'){
    $dir = dirname($_SERVER['PHP_SELF']).'/';
  }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
    $dir = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                         $_SERVER['SCRIPT_FILENAME']);
    $dir = dirname('/'.$dir).'/';
  }else{
    $dir = './'; //probably wrong
  }

  $dir = str_replace('\\','/',$dir); #bugfix for weird WIN behaviour
  $dir = preg_replace('#//+#','/',$dir);

  //handle script in lib/exe dir
  $dir = preg_replace('!lib/exe/$!','',$dir);

  //finish here for relative URLs
  if(!$abs) return $dir;

  //use config option if available
  if($conf['baseurl']) return $conf['baseurl'].$dir;

  //split hostheader into host and port
  list($host,$port) = explode(':',$_SERVER['HTTP_HOST']);
  if(!$port)  $port = $_SERVER['SERVER_PORT'];
  if(!$port)  $port = 80;

  // see if HTTPS is enabled - apache leaves this empty when not available,
  // IIS sets it to 'off', 'false' and 'disabled' are just guessing
  if (preg_match('/^(|off|false|disabled)$/i',$_SERVER['HTTPS'])){
    $proto = 'http://';
    if ($port == '80') {
      $port='';
    }
  }else{
    $proto = 'https://';
    if ($port == '443') {
      $port='';
    }
  }

  if($port) $port = ':'.$port;

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
    nice_die($fn.' is not writable. Check your permission settings!');
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

/**
 * print a nice message even if no styles are loaded yet.
 */
function nice_die($msg){
  echo<<<EOT
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
  <html>
    <head><title>DokuWiki Setup Error</title></head>
    <body style="font-family: Arial, sans-serif">
      <div style="width:60%; margin: auto; background-color: #fcc;
                  border: 1px solid #faa; padding: 0.5em 1em;">
      <h1 style="font-size: 120%">DokuWiki Setup Error</h1>
      <p>$msg</p>
      </div>
    </body>
  </html>
EOT;
  exit;
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
