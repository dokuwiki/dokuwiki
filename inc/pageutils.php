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

  $id = $_REQUEST[$param];

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
 * @param  string  $id    The pageid to clean
 * @param  boolean $ascii Force ASCII
 */
function cleanID($id,$ascii=false){
  global $conf;
  global $lang;
  static $sepcharpat = null;

  $sepchar = $conf['sepchar'];
  if($sepcharpat == null) // build string only once to save clock cycles
    $sepcharpat = '#\\'.$sepchar.'+#';

  $id = trim($id);
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

  return($id);
}

/**
 * Return namespacepart of a wiki ID
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getNS($id){
 if(strpos($id,':')!==false){
   return substr($id,0,strrpos($id,':'));
 }
 return false;
}

/**
 * Returns the ID without the namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function noNS($id){
  return preg_replace('/.*:/','',$id);
}

/**
 * returns the full path to the datafile specified by ID and
 * optional revision
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function wikiFN($id,$rev=''){
  global $conf;
  $id = cleanID($id);
  $id = str_replace(':','/',$id);
  if(empty($rev)){
    $fn = $conf['datadir'].'/'.utf8_encodeFN($id).'.txt';
  }else{
    $fn = $conf['olddir'].'/'.utf8_encodeFN($id).'.'.$rev.'.txt';
    if($conf['usegzip'] && !@file_exists($fn)){
      //return gzip if enabled and plaintext doesn't exist
      $fn .= '.gz';
    }
  }
  return $fn;
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
 * Returns a full media id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resolve_mediaid($ns,&$page,&$exists){
  global $conf;

  //if links starts with . add current namespace
  if($page{0} == '.'){
    $page = $ns.':'.substr($page,1);
  }

  //if link contains no namespace. add current namespace (if any)
  if($ns !== false && strpos($page,':') === false){
    $page = $ns.':'.$page;
  }

  $page   = cleanID($page);
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

  //if links starts with . add current namespace
  if($page{0} == '.'){
    $page = $ns.':'.substr($page,1);
  }

  //if link contains no namespace. add current namespace (if any)
  if($ns !== false && strpos($page,':') === false){
    $page = $ns.':'.$page;
  }

  //keep hashlink if exists then clean both parts
  list($page,$hash) = split('#',$page,2);
  $page = cleanID($page);
  $hash = cleanID($hash);

  $file = wikiFN($page);

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

//Setup VIM: ex: et ts=2 enc=utf-8 :
