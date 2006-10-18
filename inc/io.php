<?php
/**
 * File IO functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/HTTPClient.php');
  require_once(DOKU_INC.'inc/events.php');
  require_once(DOKU_INC.'inc/utf8.php');

/**
 * Removes empty directories
 *
 * Sends IO_NAMESPACE_DELETED events for 'pages' and 'media' namespaces.
 * Event data:
 * $data[0]    ns: The colon separated namespace path minus the trailing page name.
 * $data[1]    ns_type: 'pages' or 'media' namespace tree.
 *
 * @todo use safemode hack
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function io_sweepNS($id,$basedir='datadir'){
  global $conf;
  $types = array ('datadir'=>'pages', 'mediadir'=>'media');
  $ns_type = (isset($types[$basedir])?$types[$basedir]:false);

  //scan all namespaces
  while(($id = getNS($id)) !== false){
    $dir = $conf[$basedir].'/'.utf8_encodeFN(str_replace(':','/',$id));

    //try to delete dir else return
    if(@rmdir($dir)) {
      if ($ns_type!==false) {
        $data = array($id, $ns_type);
        trigger_event('IO_NAMESPACE_DELETED', $data);
      }
    } else { return; }
  }
}

/**
 * Used to read in a DokuWiki page from file, and send IO_WIKIPAGE_READ events.
 *
 * Generates the action event which delegates to io_readFile().
 * Action plugins are allowed to modify the page content in transit.
 * The file path should not be changed.
 *
 * Event data:
 * $data[0]    The raw arguments for io_readFile as an array.
 * $data[1]    ns: The colon separated namespace path minus the trailing page name. (false if root ns)
 * $data[2]    page_name: The wiki page name.
 * $data[3]    rev: The page revision, false for current wiki pages.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function io_readWikiPage($file, $id, $rev=false) {
    if (empty($rev)) { $rev = false; }
    $data = array(array($file, false), getNS($id), noNS($id), $rev);
    return trigger_event('IO_WIKIPAGE_READ', $data, '_io_readWikiPage_action', false);
}

/**
 * Callback adapter for io_readFile().
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function _io_readWikiPage_action($data) {
    if (is_array($data) && is_array($data[0]) && count($data[0])===2) {
        return call_user_func_array('io_readFile', $data[0]);
    } else {
        return ''; //callback error
    }
}

/**
 * Returns content of $file as cleaned string.
 *
 * Uses gzip if extension is .gz
 *
 * If you want to use the returned value in unserialize
 * be sure to set $clean to false!
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_readFile($file,$clean=true){
  $ret = '';
  if(@file_exists($file)){
    if(substr($file,-3) == '.gz'){
      $ret = join('',gzfile($file));
    }else if(substr($file,-4) == '.bz2'){
      $ret = bzfile($file);
    }else{
      $ret = join('',file($file));
    }
  }
  if($clean){
    return cleanText($ret);
  }else{
    return $ret;
  }
}
/**
* Returns the content of a .bz2 compressed file as string
* @author marcel senf <marcel@rucksackreinigung.de>
*/

function bzfile($file){
  $bz = bzopen($file,"r");
  while (!feof($bz)){
    //8192 seems to be the maximum buffersize?
	  $str = $str . bzread($bz,8192);
  }
  bzclose($bz);
  return $str;
}


/**
 * Used to write out a DokuWiki page to file, and send IO_WIKIPAGE_WRITE events.
 *
 * This generates an action event and delegates to io_saveFile().
 * Action plugins are allowed to modify the page content in transit.
 * The file path should not be changed.
 * (The append parameter is set to false.)
 *
 * Event data:
 * $data[0]    The raw arguments for io_saveFile as an array.
 * $data[1]    ns: The colon separated namespace path minus the trailing page name. (false if root ns)
 * $data[2]    page_name: The wiki page name.
 * $data[3]    rev: The page revision, false for current wiki pages.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function io_writeWikiPage($file, $content, $id, $rev=false) {
    if (empty($rev)) { $rev = false; }
    if ($rev===false) { io_createNamespace($id); } // create namespaces as needed
    $data = array(array($file, $content, false), getNS($id), noNS($id), $rev);
    return trigger_event('IO_WIKIPAGE_WRITE', $data, '_io_writeWikiPage_action', false);
}

/**
 * Callback adapter for io_saveFile().
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function _io_writeWikiPage_action($data) {
    if (is_array($data) && is_array($data[0]) && count($data[0])===3) {
        return call_user_func_array('io_saveFile', $data[0]);
    } else {
        return false; //callback error
    }
}

/**
 * Saves $content to $file.
 *
 * If the third parameter is set to true the given content
 * will be appended.
 *
 * Uses gzip if extension is .gz
 * and bz2 if extension is .bz2
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return bool true on success
 */
function io_saveFile($file,$content,$append=false){
  global $conf;
  $mode = ($append) ? 'ab' : 'wb';

  $fileexists = @file_exists($file);
  io_makeFileDir($file);
  io_lock($file);
  if(substr($file,-3) == '.gz'){
    $fh = @gzopen($file,$mode.'9');
    if(!$fh){
      msg("Writing $file failed",-1);
      io_unlock($file);
      return false;
    }
    gzwrite($fh, $content);
    gzclose($fh);
  }else if(substr($file,-4) == '.bz2'){
    $fh = @bzopen($file,$mode);
    if(!$fh){
      msg("Writing $file failed", -1);
      io_unlock($file);
      return false;
    }
    bzwrite($fh, $content);
    bzclose($fh);
  }else{
    $fh = @fopen($file,$mode);
    if(!$fh){
      msg("Writing $file failed",-1);
      io_unlock($file);
      return false;
    }
    fwrite($fh, $content);
    fclose($fh);
  }

  if(!$fileexists and !empty($conf['fperm'])) chmod($file, $conf['fperm']);
  io_unlock($file);
  return true;
}

/**
 * Delete exact linematch for $badline from $file.
 *
 * Be sure to include the trailing newline in $badline
 *
 * Uses gzip if extension is .gz
 *
 * 2005-10-14 : added regex option -- Christopher Smith <chris@jalakai.co.uk>
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 * @return bool true on success
 */
function io_deleteFromFile($file,$badline,$regex=false){
  if (!@file_exists($file)) return true;

  io_lock($file);

  // load into array
  if(substr($file,-3) == '.gz'){
    $lines = gzfile($file);
  }else{
    $lines = file($file);
  }

  // remove all matching lines
  if ($regex) {
    $lines = preg_grep($badline,$lines,PREG_GREP_INVERT);
  } else {
    $pos = array_search($badline,$lines); //return null or false if not found
    while(is_int($pos)){
      unset($lines[$pos]);
      $pos = array_search($badline,$lines);
    }
  }

  if(count($lines)){
    $content = join('',$lines);
    if(substr($file,-3) == '.gz'){
      $fh = @gzopen($file,'wb9');
      if(!$fh){
        msg("Removing content from $file failed",-1);
        io_unlock($file);
        return false;
      }
      gzwrite($fh, $content);
      gzclose($fh);
    }else{
      $fh = @fopen($file,'wb');
      if(!$fh){
        msg("Removing content from $file failed",-1);
        io_unlock($file);
        return false;
      }
      fwrite($fh, $content);
      fclose($fh);
    }
  }else{
    @unlink($file);
  }

  io_unlock($file);
  return true;
}

/**
 * Tries to lock a file
 *
 * Locking is only done for io_savefile and uses directories
 * inside $conf['lockdir']
 *
 * It waits maximal 3 seconds for the lock, after this time
 * the lock is assumed to be stale and the function goes on
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function io_lock($file){
  global $conf;
  // no locking if safemode hack
  if($conf['safemodehack']) return;

  $lockDir = $conf['lockdir'].'/'.md5($file);
  @ignore_user_abort(1);

  $timeStart = time();
  do {
    //waited longer than 3 seconds? -> stale lock
    if ((time() - $timeStart) > 3) break;
    $locked = @mkdir($lockDir, $conf['dmode']);
    if($locked){
      if(!empty($conf['dperm'])) chmod($lockDir, $conf['dperm']);
      break;
    }
    usleep(50);
  } while ($locked === false);
}

/**
 * Unlocks a file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function io_unlock($file){
  global $conf;
  // no locking if safemode hack
  if($conf['safemodehack']) return;

  $lockDir = $conf['lockdir'].'/'.md5($file);
  @rmdir($lockDir);
  @ignore_user_abort(0);
}

/**
 * Create missing namespace directories and send the IO_NAMESPACE_CREATED events
 * in the order of directory creation. (Parent directories first.)
 *
 * Event data:
 * $data[0]    ns: The colon separated namespace path minus the trailing page name.
 * $data[1]    ns_type: 'pages' or 'media' namespace tree.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function io_createNamespace($id, $ns_type='pages') {
    // verify ns_type
    $types = array('pages'=>'wikiFN', 'media'=>'mediaFN');
    if (!isset($types[$ns_type])) {
        trigger_error('Bad $ns_type parameter for io_createNamespace().');
        return;
    }
    // make event list
    $missing = array();
    $ns_stack = explode(':', $id);
    $ns = $id;
    $tmp = dirname( $file = call_user_func($types[$ns_type], $ns) );
    while (!@is_dir($tmp) && !(@file_exists($tmp) && !is_dir($tmp))) {
        array_pop($ns_stack);
        $ns = implode(':', $ns_stack);
        if (strlen($ns)==0) { break; }
        $missing[] = $ns;
        $tmp = dirname(call_user_func($types[$ns_type], $ns));
    }
    // make directories
    io_makeFileDir($file);
    // send the events
    $missing = array_reverse($missing); // inside out
    foreach ($missing as $ns) {
        $data = array($ns, $ns_type);
        trigger_event('IO_NAMESPACE_CREATED', $data);
    }
}

/**
 * Create the directory needed for the given file
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_makeFileDir($file){
  global $conf;

  $dir = dirname($file);
  if(!@is_dir($dir)){
    io_mkdir_p($dir) || msg("Creating directory $dir failed",-1);
  }
}

/**
 * Creates a directory hierachy.
 *
 * @link    http://www.php.net/manual/en/function.mkdir.php
 * @author  <saint@corenova.com>
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_mkdir_p($target){
  global $conf;
  if (@is_dir($target)||empty($target)) return 1; // best case check first
  if (@file_exists($target) && !is_dir($target)) return 0;
  //recursion
  if (io_mkdir_p(substr($target,0,strrpos($target,'/')))){
    if($conf['safemodehack']){
      $dir = preg_replace('/^'.preg_quote(realpath($conf['ftp']['root']),'/').'/','', $target);
      return io_mkdir_ftp($dir);
    }else{
      $ret = @mkdir($target,$conf['dmode']); // crawl back up & create dir tree
      if($ret && $conf['dperm']) chmod($target, $conf['dperm']);
      return $ret;
    }
  }
  return 0;
}

/**
 * Creates a directory using FTP
 *
 * This is used when the safemode workaround is enabled
 *
 * @author <andi@splitbrain.org>
 */
function io_mkdir_ftp($dir){
  global $conf;

  if(!function_exists('ftp_connect')){
    msg("FTP support not found - safemode workaround not usable",-1);
    return false;
  }

  $conn = @ftp_connect($conf['ftp']['host'],$conf['ftp']['port'],10);
  if(!$conn){
    msg("FTP connection failed",-1);
    return false;
  }

  if(!@ftp_login($conn, $conf['ftp']['user'], $conf['ftp']['pass'])){
    msg("FTP login failed",-1);
    return false;
  }

  //create directory
  $ok = @ftp_mkdir($conn, $dir);
  //set permissions
  @ftp_site($conn,sprintf("CHMOD %04o %s",$conf['dmode'],$dir));

  @ftp_close($conn);
  return $ok;
}

/**
 * downloads a file from the net and saves it
 *
 * if $useAttachment is false,
 * - $file is the full filename to save the file, incl. path
 * - if successful will return true, false otherwise

 * if $useAttachment is true,
 * - $file is the directory where the file should be saved
 * - if successful will return the name used for the saved file, false otherwise
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Chris Smith <chris@jalakai.co.uk>
 */
function io_download($url,$file,$useAttachment=false,$defaultName='',$maxSize=2097152){
  global $conf;
  $http = new DokuHTTPClient();
  $http->max_bodysize = $maxSize;
  $http->timeout = 25; //max. 25 sec

  $data = $http->get($url);
  if(!$data) return false;

  if ($useAttachment) {
    $name = '';
      if (isset($http->resp_headers['content-disposition'])) {
      $content_disposition = $http->resp_headers['content-disposition'];
      $match=array();
      if (is_string($content_disposition) &&
          preg_match('/attachment;\s*filename\s*=\s*"([^"]*)"/i', $content_disposition, $match)) {

          $name = basename($match[1]);
      }

    }

    if (!$name) {
        if (!$defaultName) return false;
        $name = $defaultName;
    }

    $file = $file.$name;
  }

  $fileexists = @file_exists($file);
  $fp = @fopen($file,"w");
  if(!$fp) return false;
  fwrite($fp,$data);
  fclose($fp);
  if(!$fileexists and $conf['fperm']) chmod($file, $conf['fperm']);
  if ($useAttachment) return $name;
  return true;
}

/**
 * Windows compatible rename
 *
 * rename() can not overwrite existing files on Windows
 * this function will use copy/unlink instead
 */
function io_rename($from,$to){
  global $conf;
  if(!@rename($from,$to)){
    if(@copy($from,$to)){
      if($conf['fperm']) chmod($to, $conf['fperm']);
      @unlink($from);
      return true;
    }
    return false;
  }
  return true;
}


/**
 * Runs an external command and returns it's output as string
 *
 * @author Harry Brueckner <harry_b@eml.cc>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @deprecated
 */
function io_runcmd($cmd){
  $fh = popen($cmd, "r");
  if(!$fh) return false;
  $ret = '';
  while (!feof($fh)) {
    $ret .= fread($fh, 8192);
  }
  pclose($fh);
  return $ret;
}

/**
 * Search a file for matching lines
 *
 * This is probably not faster than file()+preg_grep() but less
 * memory intensive because not the whole file needs to be loaded
 * at once.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $file    The file to search
 * @param  string $pattern PCRE pattern
 * @param  int    $max     How many lines to return (0 for all)
 * @param  bool   $baxkref When true returns array with backreferences instead of lines
 * @return matching lines or backref, false on error
 */
function io_grep($file,$pattern,$max=0,$backref=false){
  $fh = @fopen($file,'r');
  if(!$fh) return false;
  $matches = array();

  $cnt  = 0;
  $line = '';
  while (!feof($fh)) {
    $line .= fgets($fh, 4096);  // read full line
    if(substr($line,-1) != "\n") continue;

    // check if line matches
    if(preg_match($pattern,$line,$match)){
      if($backref){
        $matches[] = $match;
      }else{
        $matches[] = $line;
      }
      $cnt++;
    }
    if($max && $max == $cnt) break;
    $line = '';
  }
  fclose($fh);
  return $matches;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
