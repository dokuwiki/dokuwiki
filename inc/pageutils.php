<?php
/**
 * Utilities for handling pagenames
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Fetch the pageid
 *
 * Uses either standard $_REQUEST variable or extracts it from
 * the full request URI when userewrite is set to 2
 *
 * Returns $conf['start'] if no id was found.
 * 
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getID(){
  global $conf;

  $id = cleanID($_REQUEST['id']);
  
  //construct page id from request URI
  if(empty($id) && $conf['userewrite'] == 2){
    //get the script URL
    if($conf['basedir']){
      $script = $conf['basedir'].DOKU_SCRIPT;
    }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
      $script = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                              $_SERVER['SCRIPT_FILENAME']);
      $script = '/'.$script;
    }else{
      $script = $_SERVER['SCRIPT_NAME'];
    }

    //remove script URL and Querystring to gain the id
    if(preg_match('/^'.preg_quote($script,'/').'(.*)/',
                  $_SERVER['REQUEST_URI'], $match)){
      $id = preg_replace ('/\?.*/','',$match[1]);
    }
    $id = cleanID($id);
  }
  if(empty($id)) $id = $conf['start'];

  return $id;
}

/**
 * Remove unwanted chars from ID
 *
 * Cleans a given ID to only use allowed characters. Accented characters are
 * converted to unaccented ones
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function cleanID($id){
  global $conf;
  global $lang;
  $id = trim($id);
  $id = utf8_strtolower($id);

  //alternative namespace seperator
  $id = strtr($id,';',':');
  if($conf['useslash']){
    $id = strtr($id,'/',':');
  }else{
    $id = strtr($id,'/','_');
  }

  if($conf['deaccent']) $id = utf8_deaccent($id,-1);

  //remove specials
  $id = utf8_stripspecials($id,'_');

  //clean up
  $id = preg_replace('#_+#','_',$id);
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
  $file = './lang/'.$conf['lang'].'/'.$id.'.txt';
  if(!@file_exists($file)){
    //fall back to english
    $file = './lang/en/'.$id.'.txt';
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

//Setup VIM: ex: et ts=2 enc=utf-8 :
