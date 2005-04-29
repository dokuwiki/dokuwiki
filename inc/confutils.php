<?php
/**
 * Utilities for collecting data from config files
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Harry Fuecks <hfuecks@gmail.com>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

/**
 * Returns the (known) extension and mimetype of a given filename
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function mimetype($file){
  $ret    = array(false,false); // return array
  $mtypes = getMimeTypes();     // known mimetypes
  $exts   = join('|',array_keys($mtypes));  // known extensions (regexp)
  if(preg_match('#\.('.$exts.')$#i',$file,$matches)){
    $ext = strtolower($matches[1]);
  }

  if($ext && $mtypes[$ext]){
    $ret = array($ext, $mtypes[$ext]);
  }

  return $ret;
}

/**
 * returns a hash of mimetypes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getMimeTypes() {
  static $mime = NULL;
  if ( !$mime ) {
    $mime = confToHash(DOKU_INC . 'conf/mime.conf');
  }
  return $mime;
}

/**
 * returns a hash of acronyms
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getAcronyms() {
  static $acronyms = NULL;
  if ( !$acronyms ) {
    $acronyms = confToHash(DOKU_INC . 'conf/acronyms.conf');
  }
  return $acronyms;
}

/**
 * returns a hash of smileys
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getSmileys() {
  static $smileys = NULL;
  if ( !$smileys ) {
    $smileys = confToHash(DOKU_INC . 'conf/smileys.conf');
  }
  return $smileys;
}

/**
 * returns a hash of entities
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getEntities() {
  static $entities = NULL;
  if ( !$entities ) {
    $entities = confToHash(DOKU_INC . 'conf/entities.conf');
  }
  return $entities;
}

/**
 * returns a hash of interwikilinks
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getInterwiki() {
  static $wikis = NULL;
  if ( !$wikis ) {
    $wikis = confToHash(DOKU_INC . 'conf/interwiki.conf',true);
  }
  //add sepecial case 'this'
  $wikis['this'] = DOKU_URL.'{NAME}';
  return $wikis;
}

/**
 * Builds a hash from a configfile
 *
 * If $lower is set to true all hash keys are converted to
 * lower case.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function confToHash($file,$lower=false) {
  $conf = array();
  $lines = @file( $file );
  if ( !$lines ) return $conf;

  foreach ( $lines as $line ) {
    //ignore comments
    $line = preg_replace('/[^&]?#.*$/','',$line);
    $line = trim($line);
    if(empty($line)) continue;
    $line = preg_split('/\s+/',$line,2);
    // Build the associative array
    if($lower){
      $conf[strtolower($line[0])] = $line[1];
    }else{
      $conf[$line[0]] = $line[1];
    }
  }
    
  return $conf;
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
