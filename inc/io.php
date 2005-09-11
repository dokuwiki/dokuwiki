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

/**
 * Removes empty directories
 *
 * @todo use safemode hack
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_sweepNS($id){
  global $conf;

  //scan all namespaces
  while(($id = getNS($id)) !== false){
		$dir = $conf['datadir'].'/'.str_replace(':','/',$id);
    $dir = utf8_encodeFN($dir);

    //try to delete dir else return
    if(!@rmdir($dir)) return;
  }
}

/**
 * Returns content of $file as cleaned string.
 *
 * Uses gzip if extension is .gz
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_readFile($file){
  $ret = '';
  if(@file_exists($file)){
    if(substr($file,-3) == '.gz'){
      $ret = join('',gzfile($file));
    }else{
      $ret = join('',file($file));
    }
  }
  return cleanText($ret);
}

/**
 * Saves $content to $file.
 *
 * If the third parameter is set to true the given content
 * will be appended.
 *
 * Uses gzip if extension is .gz
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return bool true on success
 */
function io_saveFile($file,$content,$append=false){
  $mode = ($append) ? 'ab' : 'wb';

  io_makeFileDir($file);
  io_lock($file);
  if(substr($file,-3) == '.gz'){
    $fh = @gzopen($file,$mode.'9');
    if(!$fh){
      msg("Writing $file failed",-1);
      return false;
    }
    gzwrite($fh, $content);
    gzclose($fh);
  }else{
    $fh = @fopen($file,$mode);
    if(!$fh){
      msg("Writing $file failed",-1);
      return false;
    }
    fwrite($fh, $content);
    fclose($fh);
  }
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
 * @author Steven Danz <steven-danz@kc.rr.com>
 * @return bool true on success
 */
function io_deleteFromFile($file,$badline){
  if (!@file_exists($file)) return true;

  io_lock($file);

  // load into array
  if(substr($file,-3) == '.gz'){
    $lines = gzfile($file);
  }else{
    $lines = file($file);
  }

  // remove all matching lines
  $pos = array_search($badline,$lines); //return null or false if not found
  while(is_int($pos)){
    unset($lines[$pos]);
    $pos = array_search($badline,$lines);
  }

  if(count($lines)){
    $content = join('',$lines);
    if(substr($file,-3) == '.gz'){
      $fh = @gzopen($file,'wb9');
      if(!$fh){
        msg("Removing content from $file failed",-1);
        return false;
      }
      gzwrite($fh, $content);
      gzclose($fh);
    }else{
      $fh = @fopen($file,'wb');
      if(!$fh){
        msg("Removing content from $file failed",-1);
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
    $locked = @mkdir($lockDir);
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
 * Create the directory needed for the given file
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_makeFileDir($file){
  global $conf;

  $dir = dirname($file);
  umask($conf['dmask']);
  if(!is_dir($dir)){
    io_mkdir_p($dir) || msg("Creating directory $dir failed",-1);
  }
  umask($conf['umask']); 
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
  if (is_dir($target)||empty($target)) return 1; // best case check first
  if (@file_exists($target) && !is_dir($target)) return 0;
  //recursion
  if (io_mkdir_p(substr($target,0,strrpos($target,'/')))){
    if($conf['safemodehack']){
      $dir = preg_replace('/^'.preg_quote(realpath($conf['ftp']['root']),'/').'/','', $target);
      return io_mkdir_ftp($dir);
    }else{
      return @mkdir($target,0777); // crawl back up & create dir tree
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
  //set permissions (using the directory umask)
  @ftp_site($conn,sprintf("CHMOD %04o %s",(0777 - $conf['dmask']),$dir));

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
function io_download($url,$file,$useAttachment=false,$defaultName=''){
  $http = new DokuHTTPClient();
  $http->max_bodysize = 2*1024*1024; //max. 2MB
  $http->timeout = 25; //max. 25 sec

  $data = $http->get($url);
  if(!$data) return false;
  
  if ($useAttachment) {
    $name = '';
      if (isset($http->resp_headers['content-disposition'])) {
      $content_disposition = $http->resp_headers['content-disposition'];
      if (is_string($content_disposition) && 
          preg_match('/attachment;\s*filename\s*=\s*"([^"]*)"/i', $content_disposition, $match=array())) {
          
          $name = basename($match[1]);
      }
          
    }
    
    if (!$name) {
        if (!$defaultName) return false;
        $name = $defaultName;        
    }
    
    $file = $file.$name;
  }

  $fp = @fopen($file,"w");
  if(!$fp) return false;
  fwrite($fp,$data);
  fclose($fp);
  if ($useAttachment) return $name;
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


//Setup VIM: ex: et ts=2 enc=utf-8 :
