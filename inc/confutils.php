<?php
/**
 * Utilities for collecting data from config files
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Harry Fuecks <hfuecks@gmail.com>
 */


/**
 * Returns the (known) extension and mimetype of a given filename
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function mimetype($file){
  $ret    = array(false,false,false); // return array
  $mtypes = getMimeTypes();     // known mimetypes
  $exts   = join('|',array_keys($mtypes));  // known extensions (regexp)
  if(preg_match('#\.('.$exts.')$#i',$file,$matches)){
    $ext = strtolower($matches[1]);
  }

  if($ext && $mtypes[$ext]){
    if($mtypes[$ext][0] == '!'){
        $ret = array($ext, substr($mtypes[$ext],1), true);
    }else{
        $ret = array($ext, $mtypes[$ext], false);
    }
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
    $mime = confToHash(DOKU_CONF.'mime.conf');
    if (@file_exists(DOKU_CONF.'mime.local.conf')) {
      $local = confToHash(DOKU_CONF.'mime.local.conf');
      $mime = array_merge($mime, $local);
    }
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
    $acronyms = confToHash(DOKU_CONF.'acronyms.conf');
    if (@file_exists(DOKU_CONF.'acronyms.local.conf')) {
      $local = confToHash(DOKU_CONF.'acronyms.local.conf');
      $acronyms = array_merge($acronyms, $local);
    }
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
    $smileys = confToHash(DOKU_CONF.'smileys.conf');
    if (@file_exists(DOKU_CONF.'smileys.local.conf')) {
      $local = confToHash(DOKU_CONF.'smileys.local.conf');
      $smileys = array_merge($smileys, $local);
    }
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
    $entities = confToHash(DOKU_CONF.'entities.conf');
    if (@file_exists(DOKU_CONF.'entities.local.conf')) {
      $local = confToHash(DOKU_CONF.'entities.local.conf');
      $entities = array_merge($entities, $local);
    }
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
    $wikis = confToHash(DOKU_CONF.'interwiki.conf',true);
    if (@file_exists(DOKU_CONF.'interwiki.local.conf')) {
      $local = confToHash(DOKU_CONF.'interwiki.local.conf');
      $wikis = array_merge($wikis, $local);
    }
  }
  //add sepecial case 'this'
  $wikis['this'] = DOKU_URL.'{NAME}';
  return $wikis;
}

/**
 * returns array of wordblock patterns
 *
 */
function getWordblocks() {
  static $wordblocks = NULL;
  if ( !$wordblocks ) {
    $wordblocks = file(DOKU_CONF.'wordblock.conf');
    if (@file_exists(DOKU_CONF.'wordblock.local.conf')) {
      $local = file(DOKU_CONF.'wordblock.local.conf');
      $wordblocks = array_merge($wordblocks, $local);
    }
  }
  return $wordblocks;
}


function getSchemes() {
  static $schemes = NULL;
  if ( !$schemes ) {
    $schemes = file(DOKU_CONF.'scheme.conf');
    if (@file_exists(DOKU_CONF.'scheme.local.conf')) {
      $local = file(DOKU_CONF.'scheme.local.conf');
      $schemes = array_merge($schemes, $local);
    }
  }
  $schemes = array_map('trim', $schemes);
  $schemes = preg_replace('/^#.*/', '', $schemes);
  $schemes = array_filter($schemes);
  return $schemes;
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
    //ignore comments (except escaped ones)
    $line = preg_replace('/(?<![&\\\\])#.*$/','',$line);
    $line = str_replace('\\#','#',$line);
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

/**
 * check if the given action was disabled in config
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @returns boolean true if enabled, false if disabled
 */
function actionOK($action){
  static $disabled = null;
  if(is_null($disabled)){
    global $conf;

    // prepare disabled actions array and handle legacy options
    $disabled = explode(',',$conf['disableactions']);
    $disabled = array_map('trim',$disabled);
    if(isset($conf['openregister']) && !$conf['openregister']) $disabled[] = 'register';
    if(isset($conf['resendpasswd']) && !$conf['resendpasswd']) $disabled[] = 'resendpwd';
    if(isset($conf['subscribers']) && !$conf['subscribers']) {
        $disabled[] = 'subscribe';
        $disabled[] = 'subscribens';
    }
    $disabled = array_unique($disabled);
  }

  return !in_array($action,$disabled);
}

/**
 * check if headings should be used as link text for the specified link type
 *
 * @author Chris Smith <chris@jalakai.co.uk> 
 *
 * @param   string  $linktype   'content'|'navigation', content applies to links in wiki text
 *                                                      navigation applies to all other links
 * @returns boolean             true if headings should be used for $linktype, false otherwise
 */
function useHeading($linktype) {
  static $useHeading = null;

  if (is_null($useHeading)) {
    global $conf;

    if (!empty($conf['useheading'])) {
      switch ($conf['useheading']) {
        case 'content'    : $useHeading['content'] = true; break;
        case 'navigation' : $useHeading['navigation'] = true; break;
        default:
          $useHeading['content'] = true;
          $useHeading['navigation'] = true;
      }
    } else {
      $useHeading = array();
    }
  }

  return (!empty($useHeading[$linktype]));
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
