<?php
/**
 * File IO functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/common.php');

/**
 * Returns the parsed text from the given sourcefile. Uses cache
 * if exists. Creates it if not.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @deprecated -> parserutils
 */
function io_cacheParse($file){
  trigger_error("deprecated io_cacheParse called");

  global $conf;
  global $CACHEGROUP;
  global $parser; //we read parser options
  $parsed = '';
  $cache  = $conf['datadir'].'/_cache/';
  $cache .= md5($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'].$CACHEGROUP);
  $purge  = $conf['datadir'].'/_cache/purgefile';

  // check if cache can be used
  $cachetime = @filemtime($cache);

  if(   @file_exists($cache)                          // does the cachefile exist
     && @file_exists($file)                           // and does the source exist
     && !isset($_REQUEST['purge'])                    // no purge param was set
     && filesize($cache)                              // and contains the cachefile any data
     && ((time() - $cachetime) < $conf['cachetime'])  // and is cachefile young enough
     && ($cachetime > filemtime($file))               // and newer than the source
     && ($cachetime > @filemtime($purge))             // and newer than the purgefile
     && ($cachetime > filemtime('conf/dokuwiki.php')) // and newer than the config file
     && ($cachetime > @filemtime('conf/local.php'))   // and newer than the local config file
     && ($cachetime > filemtime('inc/parser.php'))    // and newer than the parser
     && ($cachetime > filemtime('inc/format.php')))   // and newer than the formating functions
  {
    $parsed  = io_readFile($cache); //give back cache
    $parsed .= "\n<!-- cachefile $cache used -->\n";
  }elseif(@file_exists($file)){
    $parsed = parse(io_readFile($file)); //sets global parseroptions
    if($parser['cache']){
      io_saveFile($cache,$parsed); //save cachefile
      $parsed .= "\n<!-- no cachefile used, but created -->\n";
    }else{
      @unlink($cache); //try to delete cachefile
      $parsed .= "\n<!-- no cachefile used, caching forbidden -->\n";
    }
  }

  return $parsed;
}

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
 * Uses gzip if extension is .gz
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return bool true on success
 */
function io_saveFile($file,$content){
  io_makeFileDir($file);
  if(substr($file,-3) == '.gz'){
    $fh = @gzopen($file,'wb9');
    if(!$fh){
      msg("Writing $file failed",-1);
      return false;
    }
    gzwrite($fh, $content);
    gzclose($fh);
  }else{
    $fh = @fopen($file,'wb');
    if(!$fh){
      msg("Writing $file failed",-1);
      return false;
    }
    fwrite($fh, $content);
    fclose($fh);
  }
  return true;
}

/**
 * Create the directory needed for the given file
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_makeFileDir($file){
  global $conf;

  $dir = dirname($file);
  if($conf['safemodehack']){
    $dir = preg_replace('/^'.preg_quote(realpath($conf['ftp']['root']),'/').'/','',$dir);
  }
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
      return io_mkdir_ftp($target);
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

//FIXME silence those commands again!
  //create directory
  $ok = ftp_mkdir($conn, $dir);
  //set permissions (using the directory umask)
  ftp_site($conn,sprintf("CHMOD %04o %s",(0777 - $conf['dmask']),$dir));

  ftp_close($conn);
  return $ok;
}

/**
 * downloads a file from the net and saves it to the given location
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @todo   Add size limit
 */
function io_download($url,$file){
  $fp = @fopen($url,"rb");
  if(!$fp) return false;

  $kb  = 0;
  $now = time();

  while(!feof($fp)){
    if($kb++ > 2048 || (time() - $now) > 45){
      //abort on 2 MB and timeout on 45 sec
      return false;
    }
    $cont.= fread($fp,1024);
  }
  fclose($fp);

  $fp2 = @fopen($file,"w");
  if(!$fp2) return false;
  fwrite($fp2,$cont);
  fclose($fp2);
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
