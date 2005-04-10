<?php
/**
 * Utilities for collecting data from config files
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Harry Fuecks <hfuecks@gmail.com>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

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
    $wikis = confToHash(DOKU_INC . 'conf/interwiki.conf');
  }
  return $wikis;
}

/**
 * Builds a hash from a configfile
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function confToHash($file) {
  $conf = array();
  $lines = @file( $file );
  if ( !$lines ) {
    return $conf;
  }

  foreach ( $lines as $line ) {
    //ignore comments
    $line = preg_replace('/[^&]#.*$/','',$line);
    $line = trim($line);
    if(empty($line)) continue;
    $line = preg_split('/\s+/',$line,2);
    // Build the associative array
    $conf[$line[0]] = $line[1];
  }
    
  return $conf;
}
