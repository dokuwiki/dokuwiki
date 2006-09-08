<?php
/**
 * Utilities for handling pagenames
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @todo       Combine similar functions like {wiki,media,meta}FN()
 */

/**
 * Fetch the an ID from request
 *
 * Uses either standard $_REQUEST variable or extracts it from
 * the full request URI when userewrite is set to 2
 *
 * For $param='id' $conf['start'] is returned if no id was found.
 * If the second parameter is true (default) the ID is cleaned.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getID($param='id',$clean=true){
  global $conf;

  $id = isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;

  //construct page id from request URI
  if(empty($id) && $conf['userewrite'] == 2){
    //get the script URL
    if($conf['basedir']){
      $relpath = '';
      if($param != 'id') {
        $relpath = 'lib/exe/';
      }
      $script = $conf['basedir'].$relpath.basename($_SERVER['SCRIPT_FILENAME']);
    }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
      $script = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                              $_SERVER['SCRIPT_FILENAME']);
      $script = '/'.$script;
    }else{
      $script = $_SERVER['SCRIPT_NAME'];
    }

    //clean script and request (fixes a windows problem)
    $script  = preg_replace('/\/\/+/','/',$script);
    $request = preg_replace('/\/\/+/','/',$_SERVER['REQUEST_URI']);

    //remove script URL and Querystring to gain the id
    if(preg_match('/^'.preg_quote($script,'/').'(.*)/',$request, $match)){
      $id = preg_replace ('/\?.*/','',$match[1]);
    }
    $id = urldecode($id);
    //strip leading slashes
    $id = preg_replace('!^/+!','',$id);
  }
  if($clean) $id = cleanID($id);
  if(empty($id) && $param=='id') $id = $conf['start'];

  return $id;
}

/**
 * Remove unwanted chars from ID
 *
 * Cleans a given ID to only use allowed characters. Accented characters are
 * converted to unaccented ones
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string  $raw_id    The pageid to clean
 * @param  boolean $ascii     Force ASCII
 */
function cleanID($raw_id,$ascii=false){
  global $conf;
  global $lang;
  static $sepcharpat = null;

  global $cache_cleanid;
  $cache = & $cache_cleanid;

  // check if it's already in the memory cache
  if (isset($cache[$raw_id])) {
    return $cache[$raw_id];
	}

  $sepchar = $conf['sepchar'];
  if($sepcharpat == null) // build string only once to save clock cycles
    $sepcharpat = '#\\'.$sepchar.'+#';

  $id = trim($raw_id);
  $id = utf8_strtolower($id);

  //alternative namespace seperator
  $id = strtr($id,';',':');
  if($conf['useslash']){
    $id = strtr($id,'/',':');
  }else{
    $id = strtr($id,'/',$sepchar);
  }

  if($conf['deaccent'] == 2 || $ascii) $id = utf8_romanize($id);
  if($conf['deaccent'] || $ascii) $id = utf8_deaccent($id,-1);

  //remove specials
  $id = utf8_stripspecials($id,$sepchar,'\*');

  if($ascii) $id = utf8_strip($id);

  //clean up
  $id = preg_replace($sepcharpat,$sepchar,$id);
  $id = preg_replace('#:+#',':',$id);
  $id = trim($id,':._-');
  $id = preg_replace('#:[:\._\-]+#',':',$id);

  $cache[$raw_id] = $id;
  return($id);
}

/**
 * Return namespacepart of a wiki ID
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getNS($id){
  $pos = strrpos($id,':');
  if($pos!==false){
    return substr($id,0,$pos);
  }
  return false;
}

/**
 * Returns the ID without the namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function noNS($id) {
  $pos = strrpos($id, ':');
  if ($pos!==false) {
    return substr($id, $pos+1);
  } else {
    return $id;
  }
}

/**
 * returns the full path to the datafile specified by ID and
 * optional revision
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function wikiFN($raw_id,$rev='',$clean=true){
  global $conf;

  global $cache_wikifn;
  $cache = & $cache_wikifn;

  if (isset($cache[$raw_id]) && isset($cache[$raw_id][$rev])) {
    return $cache[$raw_id][$rev];
  }

  $id = $raw_id;

  if ($clean) $id = cleanID($id);
  $id = str_replace(':','/',$id);
  if(empty($rev)){
    $fn = $conf['datadir'].'/'.utf8_encodeFN($id).'.txt';
  }else{
    $fn = $conf['olddir'].'/'.utf8_encodeFN($id).'.'.$rev.'.txt';
    if($conf['compression']){
      //test for extensions here, we want to read both compressions
       if (@file_exists($fn . '.gz')){
          $fn .= '.gz';
       }else if(@file_exists($fn . '.bz2')){
          $fn .= '.bz2';
       }else{
          //file doesnt exist yet, so we take the configured extension
          $fn .= '.' . $conf['compression'];
       }
    }
  }

  if (!isset($cache[$raw_id])) { $cache[$raw_id] = array(); }
  $cache[$raw_id][$rev] = $fn;
  return $fn;
}

/**
 * Returns the full path to the file for locking the page while editing.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function wikiLockFN($id) {
  global $conf;
  return $conf['lockdir'].'/'.md5(cleanID($id)).'.lock';
}


/**
 * returns the full path to the meta file specified by ID and extension
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 */
function metaFN($id,$ext){
  global $conf;
  $id = cleanID($id);
  $id = str_replace(':','/',$id);
  $fn = $conf['metadir'].'/'.utf8_encodeFN($id).$ext;
  return $fn;
}

/**
 * returns an array of full paths to all metafiles of a given ID
 *
 * @author Esther Brunner <esther@kaffeehaus.ch>
 */
function metaFiles($id){
   $name   = noNS($id);
   $dir    = metaFN(getNS($id),'');
   $files  = array();

   $dh = @opendir($dir);
   if(!$dh) return $files;
   while(($file = readdir($dh)) !== false){
     if(strpos($file,$name.'.') === 0 && !is_dir($dir.$file))
       $files[] = $dir.$file;
   }
   closedir($dh);

   return $files;
}

/**
 * returns the full path to the mediafile specified by ID
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function mediaFN($id){
  global $conf;
  $id = cleanID($id);
  $id = str_replace(':','/',$id);
    $fn = $conf['mediadir'].'/'.utf8_encodeFN($id);
  return $fn;
}

/**
 * Returns the full filepath to a localized textfile if local
 * version isn't found the english one is returned
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function localeFN($id){
  global $conf;
  $file = DOKU_INC.'inc/lang/'.$conf['lang'].'/'.$id.'.txt';
  if(!@file_exists($file)){
    //fall back to english
    $file = DOKU_INC.'inc/lang/en/'.$id.'.txt';
  }
  return $file;
}

/**
 * Resolve relative paths in IDs
 *
 * Do not call directly use resolve_mediaid or resolve_pageid
 * instead
 *
 * Partyly based on a cleanPath function found at
 * http://www.php.net/manual/en/function.realpath.php#57016
 *
 * @author <bart at mediawave dot nl>
 */
function resolve_id($ns,$id,$clean=true){
  // if the id starts with a dot we need to handle the
  // relative stuff
  if($id{0} == '.'){
    // normalize initial dots without a colon
    $id = preg_replace('/^(\.+)(?=[^:\.])/','\1:',$id);
    // prepend the current namespace
    $id = $ns.':'.$id;

    // cleanup relatives
    $result = array();
    $pathA  = explode(':', $id);
    if (!$pathA[0]) $result[] = '';
    foreach ($pathA AS $key => $dir) {
      if ($dir == '..') {
        if (end($result) == '..') {
          $result[] = '..';
        } elseif (!array_pop($result)) {
          $result[] = '..';
        }
      } elseif ($dir && $dir != '.') {
        $result[] = $dir;
      }
    }
    if (!end($pathA)) $result[] = '';
    $id = implode(':', $result);
  }elseif($ns !== false && strpos($id,':') === false){
    //if link contains no namespace. add current namespace (if any)
    $id = $ns.':'.$id;
  }

  if($clean) $id = cleanID($id);
  return $id;
}

/**
 * Returns a full media id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resolve_mediaid($ns,&$page,&$exists){
  $page   = resolve_id($ns,$page);
  $file   = mediaFN($page);
  $exists = @file_exists($file);
}

/**
 * Returns a full page id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resolve_pageid($ns,&$page,&$exists){
  global $conf;
  $exists = false;

  //keep hashlink if exists then clean both parts
  if (strpos($page,'#')) {
    list($page,$hash) = split('#',$page,2);
  } else {
    $hash = '';
  }
  $hash = cleanID($hash);
  $page = resolve_id($ns,$page,false); // resolve but don't clean, yet

  // get filename (calls clean itself)
  $file = wikiFN($page);

  // if ends with colon we have a namespace link
  if(substr($page,-1) == ':'){
    if(@file_exists(wikiFN($page.$conf['start']))){
      // start page inside namespace
      $page = $page.$conf['start'];
      $exists = true;
    }elseif(@file_exists(wikiFN($page.noNS(cleanID($page))))){
      // page named like the NS inside the NS
      $page = $page.noNS(cleanID($page));
      $exists = true;
    }elseif(@file_exists(wikiFN($page))){
      // page like namespace exists
      $page = $page;
      $exists = true;
    }else{
      // fall back to default
      $page = $page.$conf['start'];
    }
  }else{
    //check alternative plural/nonplural form
    if(!@file_exists($file)){
      if( $conf['autoplural'] ){
        if(substr($page,-1) == 's'){
          $try = substr($page,0,-1);
        }else{
          $try = $page.'s';
        }
        if(@file_exists(wikiFN($try))){
          $page   = $try;
          $exists = true;
        }
      }
    }else{
      $exists = true;
    }
  }

  // now make sure we have a clean page
  $page = cleanID($page);

  //add hash if any
  if(!empty($hash)) $page .= '#'.$hash;
}

/**
 * Returns the name of a cachefile from given data
 *
 * The needed directory is created by this function!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $data  This data is used to create a unique md5 name
 * @param string $ext   This is appended to the filename if given
 * @return string       The filename of the cachefile
 */
function getCacheName($data,$ext=''){
  global $conf;
  $md5  = md5($data);
  $file = $conf['cachedir'].'/'.$md5{0}.'/'.$md5.$ext;
  io_makeFileDir($file);
  return $file;
}

/**
 * Checks a pageid against $conf['hidepages']
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function isHiddenPage($id){
  global $conf;
  if(empty($conf['hidepages'])) return false;

  if(preg_match('/'.$conf['hidepages'].'/ui',':'.$id)){
    return true;
  }
  return false;
}

/**
 * Reverse of isHiddenPage
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function isVisiblePage($id){
  return !isHiddenPage($id);
}

/**
 * Checks and sets HTTP headers for conditional HTTP requests
 *
 * @author   Simon Willison <swillison@gmail.com>
 * @link     http://simon.incutio.com/archive/2003/04/23/conditionalGet
 * @param    timestamp $timestamp lastmodified time of the cache file
 * @returns  void or void with previously header() commands executed
 */
function http_conditionalRequest($timestamp){
  // A PHP implementation of conditional get, see
  //   http://fishbowl.pastiche.org/archives/001132.html
  $last_modified = substr(date('r', $timestamp), 0, -5).'GMT';
  $etag = '"'.md5($last_modified).'"';
  // Send the headers
  header("Last-Modified: $last_modified");
  header("ETag: $etag");
  // See if the client has provided the required headers
  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
    $if_modified_since = stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']);
  }else{
    $if_modified_since = false;
  }

  if (isset($_SERVER['HTTP_IF_NONE_MATCH'])){
    $if_none_match = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
  }else{
    $if_none_match = false;
  }

  if (!$if_modified_since && !$if_none_match){
    return;
  }

  // At least one of the headers is there - check them
  if ($if_none_match && $if_none_match != $etag) {
    return; // etag is there but doesn't match
  }

  if ($if_modified_since && $if_modified_since != $last_modified) {
    return; // if-modified-since is there but doesn't match
  }

  // Nothing has changed since their last request - serve a 304 and exit
  header('HTTP/1.0 304 Not Modified');
  exit;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
