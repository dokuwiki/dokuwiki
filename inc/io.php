<?
require_once("inc/common.php");
require_once("inc/parser.php");




/**
 * Returns the parsed text from the given sourcefile. Uses cache
 * if exists. Creates it if not.
 */
function io_cacheParse($file){
  global $conf;
  global $CACHEGROUP;
  global $parser; //we read parser options
  $parsed = '';
  $cache  = $conf['datadir'].'/.cache/';
  $cache .= md5($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'].$CACHEGROUP);
  $purge  = $conf['datadir'].'/.cache/purgefile';

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
 * Returns content of $file as cleaned string. Uses gzip if extension
 * is .gz
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
 * Saves $content to $file. Uses gzip if extension
 * is .gz
 *
 * returns true on success
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
 */
function io_makeFileDir($file){
  global $conf;

  $dir  = dirname($file);
  umask($conf['dmask']);
  if(!is_dir($dir)){
    io_mkdir_p($dir) || msg("Creating directory $dir failed",-1);
  }
  umask($conf['umask']); 
}

/**
 * Creates a directory hierachy.
 *
 * @see    http://www.php.net/manual/en/function.mkdir.php
 * @author <saint@corenova.com>
 */
function io_mkdir_p($target){
  if (is_dir($target)||empty($target)) return 1; // best case check first
  if (@file_exists($target) && !is_dir($target)) return 0;
  if (io_mkdir_p(substr($target,0,strrpos($target,'/'))))
    return @mkdir($target,0777); // crawl back up & create dir tree
  return 0;
}

/**
 * Runs an external command and returns it's output as string
 * inspired by a patch by Harry Brueckner <harry_b@eml.cc>
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

?>
