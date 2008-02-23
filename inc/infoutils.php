<?php
/**
 * Information and debugging functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');
if(!defined('DOKU_MESSAGEURL')) define('DOKU_MESSAGEURL','http://update.dokuwiki.org/check/');
require_once(DOKU_INC.'inc/HTTPClient.php');

/**
 * Check for new messages from upstream
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function checkUpdateMessages(){
    global $conf;
    global $INFO;
    if(!$conf['updatecheck']) return;
    if($conf['useacl'] && !$INFO['ismanager']) return;

    $cf = $conf['cachedir'].'/messages.txt';
    $lm = @filemtime($cf);

    // check if new messages needs to be fetched
    if($lm < time()-(60*60*24) || $lm < @filemtime(DOKU_CONF.'msg')){
        $num = @file(DOKU_CONF.'msg');
        $num = is_array($num) ? (int) $num[0] : 0;
        $http = new DokuHTTPClient();
        $http->timeout = 8;
        $data = $http->get(DOKU_MESSAGEURL.$num);
        io_saveFile($cf,$data);
    }else{
        $data = io_readFile($cf);
    }

    // show messages through the usual message mechanism
    $msgs = explode("\n%\n",$data);
    foreach($msgs as $msg){
        if($msg) msg($msg,2);
    }
}


/**
 * Return DokuWikis version
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getVersion(){
  //import version string
  if(@file_exists(DOKU_INC.'VERSION')){
    //official release
    return 'Release '.trim(io_readfile(DOKU_INC.'VERSION'));
  }elseif(is_dir(DOKU_INC.'_darcs')){
    //darcs checkout - read last 2000 bytes of inventory
    $sz   = filesize(DOKU_INC.'_darcs/inventory');
    $seek = max(0,$sz-2000);
    $fh   = fopen(DOKU_INC.'_darcs/inventory','rb');
    fseek($fh,$seek);
    $chunk = fread($fh,2000);
    fclose($fh);
    $inv = preg_grep('#\*\*\d{14}[\]$]#',explode("\n",$chunk));
    $cur = array_pop($inv);
    preg_match('#\*\*(\d{4})(\d{2})(\d{2})#',$cur,$matches);
    return 'Darcs '.$matches[1].'-'.$matches[2].'-'.$matches[3];
  }else{
    return 'snapshot?';
  }
}

/**
 * Run a few sanity checks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function check(){
  global $conf;
  global $INFO;

  msg('DokuWiki version: '.getVersion(),1);

  if(version_compare(phpversion(),'4.3.3','<')){
    msg('Your PHP version is too old ('.phpversion().' vs. 4.3.3+ recommended)',-1);
  }elseif(version_compare(phpversion(),'4.3.10','<')){
    msg('Consider upgrading PHP to 4.3.10 or higher for security reasons (your version: '.phpversion().')',0);
  }else{
    msg('PHP version '.phpversion(),1);
  }

  $mem = (int) php_to_byte(ini_get('memory_limit'));
  if($mem){
    if($mem < 16777216){
        msg('PHP is limited to less than 16MB RAM ('.$mem.' bytes). Increase memory_limit in php.ini',-1);
    }elseif($mem < 20971520){
        msg('PHP is limited to less than 20MB RAM ('.$mem.' bytes), you might encounter problems with bigger pages. Increase memory_limit in php.ini',-1);
    }elseif($mem < 33554432){
        msg('PHP is limited to less than 32MB RAM ('.$mem.' bytes), but that should be enough in most cases. If not, increase memory_limit in php.ini',0);
    }else{
        msg('More than 32MB RAM ('.$mem.' bytes) available.',1);
    }
  }


  if(is_writable($conf['changelog'])){
    msg('Changelog is writable',1);
  }else{
    if (@file_exists($conf['changelog'])) {
      msg('Changelog is not writable',-1);
    }
  }

  if (isset($conf['changelog_old']) && @file_exists($conf['changelog_old'])) {
    msg('Old changelog exists', 0);
  }

  if (@file_exists($conf['changelog'].'_failed')) {
    msg('Importing old changelog failed', -1);
  } else if (@file_exists($conf['changelog'].'_importing')) {
    msg('Importing old changelog now.', 0);
  } else if (@file_exists($conf['changelog'].'_import_ok')) {
    msg('Old changelog imported', 1);
    if (!plugin_isdisabled('importoldchangelog')) {
      msg('Importoldchangelog plugin not disabled after import', -1);
    }
  }

  if(is_writable($conf['datadir'])){
    msg('Datadir is writable',1);
  }else{
    msg('Datadir is not writable',-1);
  }

  if(is_writable($conf['olddir'])){
    msg('Attic is writable',1);
  }else{
    msg('Attic is not writable',-1);
  }

  if(is_writable($conf['mediadir'])){
    msg('Mediadir is writable',1);
  }else{
    msg('Mediadir is not writable',-1);
  }

  if(is_writable($conf['cachedir'])){
    msg('Cachedir is writable',1);
  }else{
    msg('Cachedir is not writable',-1);
  }

  if(is_writable($conf['lockdir'])){
    msg('Lockdir is writable',1);
  }else{
    msg('Lockdir is not writable',-1);
  }

  if($conf['authtype'] == 'plain'){
    if(is_writable(DOKU_CONF.'users.auth.php')){
      msg('conf/users.auth.php is writable',1);
    }else{
      msg('conf/users.auth.php is not writable',0);
    }
  }

  if(function_exists('mb_strpos')){
    if(defined('UTF8_NOMBSTRING')){
      msg('mb_string extension is available but will not be used',0);
    }else{
      msg('mb_string extension is available and will be used',1);
    }
  }else{
    msg('mb_string extension not available - PHP only replacements will be used',0);
  }

  if($conf['allowdebug']){
    msg('Debugging support is enabled. If you don\'t need it you should set $conf[\'allowdebug\'] = 0',-1);
  }else{
    msg('Debugging support is disabled',1);
  }

  if($INFO['userinfo']['name']){
    msg('You are currently logged in as '.$_SERVER['REMOTE_USER'].' ('.$INFO['userinfo']['name'].')',0);
    msg('You are part of the groups '.join($INFO['userinfo']['grps'],', '),0);
  }else{
    msg('You are currently not logged in',0);
  }

  msg('Your current permission for this page is '.$INFO['perm'],0);

  if(is_writable($INFO['filepath'])){
    msg('The current page is writable by the webserver',0);
  }else{
    msg('The current page is not writable by the webserver',0);
  }

  if($INFO['writable']){
    msg('The current page is writable by you',0);
  }else{
    msg('The current page is not writable by you',0);
  }
}

/**
 * print a message
 *
 * If HTTP headers were not sent yet the message is added
 * to the global message array else it's printed directly
 * using html_msgarea()
 *
 *
 * Levels can be:
 *
 * -1 error
 *  0 info
 *  1 success
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    html_msgarea
 */
function msg($message,$lvl=0,$line='',$file=''){
  global $MSG;
  $errors[-1] = 'error';
  $errors[0]  = 'info';
  $errors[1]  = 'success';
  $errors[2]  = 'notify';

  if($line || $file) $message.=' ['.basename($file).':'.$line.']';

  if(!headers_sent()){
    if(!isset($MSG)) $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
  }else{
    $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
    if(function_exists('html_msgarea')){
      html_msgarea();
    }else{
      print "ERROR($lvl) $message";
    }
  }
}

/**
 * print debug messages
 *
 * little function to print the content of a var
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function dbg($msg,$hidden=false){
  (!$hidden) ? print '<pre class="dbg">' : print "<!--\n";
  print_r($msg);
  (!$hidden) ? print '</pre>' : print "\n-->";
}

/**
 * Print info to a log file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function dbglog($msg){
  global $conf;
  $file = $conf['cachedir'].'/debug.log';
  $fh = fopen($file,'a');
  if($fh){
    fwrite($fh,date('H:i:s ').$_SERVER['REMOTE_ADDR'].': '.$msg."\n");
    fclose($fh);
  }
}

/**
 * Print a reversed, prettyprinted backtrace
 *
 * @author Gary Owen <gary_owen@bigfoot.com>
 */
function dbg_backtrace(){
  // Get backtrace
  $backtrace = debug_backtrace();

  // Unset call to debug_print_backtrace
  array_shift($backtrace);

  // Iterate backtrace
  $calls = array();
  $depth = count($backtrace) - 1;
  foreach ($backtrace as $i => $call) {
    $location = $call['file'] . ':' . $call['line'];
    $function = (isset($call['class'])) ?
    $call['class'] . $call['type'] . $call['function'] : $call['function'];

    $params = array();
    if (isset($call['args'])){
        foreach($call['args'] as $arg){
            if(is_object($arg)){
                $params[] = '[Object '.get_class($arg).']';
            }elseif(is_array($arg)){
                $params[] = '[Array]';
            }elseif(is_null($arg)){
                $param[] = '[NULL]';
            }else{
                $params[] = (string) '"'.$arg.'"';
            }
        }
    }
    $params = implode(', ',$params);

    $calls[$depth - $i] = sprintf('%s(%s) called at %s',
                          $function,
                          str_replace("\n", '\n', $params),
                          $location);
  }
  ksort($calls);

  return implode("\n", $calls);
}

