<?php
/**
 * Utilities for collecting data from config files
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Harry Fuecks <hfuecks@gmail.com>
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

  require_once(DOKU_INC.'inc/confutils.php');
  require_once(DOKU_INC.'inc/pageutils.php');
  require_once(DOKU_INC.'inc/pluginutils.php');

/**
 * Returns the parsed Wikitext in XHTML for the given id and revision.
 *
 * If $excuse is true an explanation is returned if the file
 * wasn't found
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_wiki_xhtml($id, $rev='', $excuse=true){
  $file = wikiFN($id,$rev);
  $ret  = '';

  //ensure $id is in global $ID (needed for parsing)
  global $ID;
  $keep = $ID;
  $ID   = $id;

  if($rev){
    if(@file_exists($file)){
      $ret = p_render('xhtml',p_get_instructions(io_readfile($file)),$info); //no caching on old revisions
    }elseif($excuse){
      $ret = p_locale_xhtml('norev');
    }
  }else{
    if(@file_exists($file)){
      $ret = p_cached_xhtml($file);
    }elseif($excuse){
      $ret = p_locale_xhtml('newpage');
    }
  }

  //restore ID (just in case)
  $ID = $keep;

  return $ret;
}

/**
 * Returns starting summary for a page (e.g. the first few
 * paragraphs), marked up in XHTML.
 *
 * If $excuse is true an explanation is returned if the file
 * wasn't found
 *
 * @param string wiki page id
 * @param reference populated with page title from heading or page id
 * @author Andreas Gohr <hfuecks@gmail.com>
 */
function p_wiki_xhtml_summary($id, &$title, $rev='', $excuse=true){
  $file = wikiFN($id,$rev);
  $ret  = '';

  //ensure $id is in global $ID (needed for parsing)
  global $ID;
  $keep = $ID;
  $ID   = $id;

  if($rev){
    if(@file_exists($file)){
      //no caching on old revisions
      $ins = p_get_instructions(io_readfile($file));
    }elseif($excuse){
      $ret = p_locale_xhtml('norev');
      //restore ID (just in case)
      $ID = $keep;
      return $ret;
    }

  }else{

    if(@file_exists($file)){
      // The XHTML for a summary is not cached so use the instruction cache
      $ins = p_cached_instructions($file);
    }elseif($excuse){
      $ret = p_locale_xhtml('newpage');
      //restore ID (just in case)
      $ID = $keep;
      return $ret;
    }
  }

  $ret = p_render('xhtmlsummary',$ins,$info);

  if ( $info['sum_pagetitle'] ) {
    $title = $info['sum_pagetitle'];
  } else {
    $title = $id;
  }

  $ID = $keep;
  return $ret;
}

/**
 * Returns the specified local text in parsed format
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_locale_xhtml($id){
  //fetch parsed locale
  $html = p_cached_xhtml(localeFN($id));
  return $html;
}

/**
 * Returns the given file parsed to XHTML
 *
 * Uses and creates a cachefile
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @todo   rewrite to use mode instead of hardcoded XHTML
 */
function p_cached_xhtml($file){
  global $conf;
  $cache  = getCacheName($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'],'.xhtml');
  $purge  = $conf['cachedir'].'/purgefile';

  // check if cache can be used
  $cachetime = @filemtime($cache); // 0 if not exists

  if( @file_exists($file)                                             // does the source exist
      && $cachetime > @filemtime($file)                               // cache is fresh
      && ((time() - $cachetime) < $conf['cachetime'])                 // and is cachefile young enough
      && !isset($_REQUEST['purge'])                                   // no purge param was set
      && ($cachetime > @filemtime($purge))                            // and newer than the purgefile
      && ($cachetime > @filemtime(DOKU_CONF.'dokuwiki.php'))      // newer than the config file
      && ($cachetime > @filemtime(DOKU_CONF.'local.php'))         // newer than the local config file
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/xhtml.php'))   // newer than the renderer
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/parser.php'))  // newer than the parser
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/handler.php')))// newer than the handler
  {
    //well then use the cache
    $parsed = io_readfile($cache);
    if($conf['allowdebug']) $parsed .= "\n<!-- cachefile $cache used -->\n";
  }else{
    $parsed = p_render('xhtml', p_cached_instructions($file),$info); //try to use cached instructions

    if($info['cache']){
      io_saveFile($cache,$parsed); //save cachefile
      if($conf['allowdebug']) $parsed .= "\n<!-- no cachefile used, but created -->\n";
    }else{
      @unlink($cache); //try to delete cachefile
      if($conf['allowdebug']) $parsed .= "\n<!-- no cachefile used, caching forbidden -->\n";
    }
  }

  return $parsed;
}

/**
 * Returns the render instructions for a file
 *
 * Uses and creates a serialized cache file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_cached_instructions($file,$cacheonly=false){
  global $conf;
  $cache  = getCacheName($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'],'.i');

  // check if cache can be used
  $cachetime = @filemtime($cache); // 0 if not exists

  // cache forced?
  if($cacheonly){
    if($cachetime){
      return unserialize(io_readfile($cache,false));
    }else{
      return array();
    }
  }

  if( @file_exists($file)                                             // does the source exist
      && $cachetime > @filemtime($file)                               // cache is fresh
      && !isset($_REQUEST['purge'])                                   // no purge param was set
      && ($cachetime > @filemtime(DOKU_CONF.'dokuwiki.php'))      // newer than the config file
      && ($cachetime > @filemtime(DOKU_CONF.'local.php'))         // newer than the local config file
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/parser.php'))  // newer than the parser
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/handler.php')))// newer than the handler
  {
    //well then use the cache
    return unserialize(io_readfile($cache,false));
  }elseif(@file_exists($file)){
    // no cache - do some work
    $ins = p_get_instructions(io_readfile($file));
    io_savefile($cache,serialize($ins));
    return $ins;
  }

  return NULL;
}

/**
 * turns a page into a list of instructions
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_get_instructions($text){

  $modes = p_get_parsermodes();

  // Create the parser
  $Parser = & new Doku_Parser();

  // Add the Handler
  $Parser->Handler = & new Doku_Handler();

  //add modes to parser
  foreach($modes as $mode){
    $Parser->addMode($mode['mode'],$mode['obj']);
  }

  // Do the parsing
  $p    = $Parser->parse($text);
//  dbg($p);
  return $p;
}

/**
 * returns all available parser syntax modes in correct order
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_get_parsermodes(){
  global $conf;

  //reuse old data
  static $modes = null;
  if($modes != null){
    return $modes;
  }

  //import parser classes and mode definitions
  require_once DOKU_INC . 'inc/parser/parser.php';

  // we now collect all syntax modes and their objects, then they will
  // be sorted and added to the parser in correct order
  $modes = array();

  // add syntax plugins
  $pluginlist = plugin_list('syntax');
  if(count($pluginlist)){
    global $PARSER_MODES;
    $obj = null;
    foreach($pluginlist as $p){
      if(!$obj =& plugin_load('syntax',$p)) continue; //attempt to load plugin into $obj
      $PARSER_MODES[$obj->getType()][] = "plugin_$p"; //register mode type
      //add to modes
      $modes[] = array(
                   'sort' => $obj->getSort(),
                   'mode' => "plugin_$p",
                   'obj'  => $obj,
                 );
      unset($obj); //remove the reference
    }
  }

  // add default modes
  $std_modes = array('listblock','preformatted','notoc','nocache',
                     'header','table','linebreak','footnote','hr',
                     'unformatted','php','html','code','file','quote',
                     'internallink','rss','media','externallink',
                     'emaillink','windowssharelink','eol');
  if($conf['typography']){
    $std_modes[] = 'quotes';
    $std_modes[] = 'multiplyentity';
  }
  foreach($std_modes as $m){
    $class = "Doku_Parser_Mode_$m";
    $obj   = new $class();
    $modes[] = array(
                 'sort' => $obj->getSort(),
                 'mode' => $m,
                 'obj'  => $obj
               );
  }

  // add formatting modes
  $fmt_modes = array('strong','emphasis','underline','monospace',
                     'subscript','superscript','deleted');
  foreach($fmt_modes as $m){
    $obj   = new Doku_Parser_Mode_formatting($m);
    $modes[] = array(
                 'sort' => $obj->getSort(),
                 'mode' => $m,
                 'obj'  => $obj
               );
  }

  // add modes which need files
  $obj     = new Doku_Parser_Mode_smiley(array_keys(getSmileys()));
  $modes[] = array('sort' => $obj->getSort(), 'mode' => 'smiley','obj'  => $obj );
  $obj     = new Doku_Parser_Mode_acronym(array_keys(getAcronyms()));
  $modes[] = array('sort' => $obj->getSort(), 'mode' => 'acronym','obj'  => $obj );
  $obj     = new Doku_Parser_Mode_entity(array_keys(getEntities()));
  $modes[] = array('sort' => $obj->getSort(), 'mode' => 'entity','obj'  => $obj );


  // add optional camelcase mode
  if($conf['camelcase']){
    $obj     = new Doku_Parser_Mode_camelcaselink();
    $modes[] = array('sort' => $obj->getSort(), 'mode' => 'camelcaselink','obj'  => $obj );
  }

  //sort modes
  usort($modes,'p_sort_modes');

  return $modes;
}

/**
 * Callback function for usort
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_sort_modes($a, $b){
  if($a['sort'] == $b['sort']) return 0;
  return ($a['sort'] < $b['sort']) ? -1 : 1;
}

/**
 * Renders a list of instruction to the specified output mode
 *
 * In the $info array are informations from the renderer returned
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_render($mode,$instructions,& $info){
  if(is_null($instructions)) return '';

  // Create the renderer
  if(!@file_exists(DOKU_INC."inc/parser/$mode.php")){
    msg("No renderer for $mode found",-1);
    return null;
  }

  require_once DOKU_INC."inc/parser/$mode.php";
  $rclass = "Doku_Renderer_$mode";
  if ( !class_exists($rclass) ) {
    trigger_error("Unable to resolve render class $rclass",E_USER_ERROR);
  }
  $Renderer = & new $rclass(); #FIXME any way to check for class existance?

  $Renderer->smileys = getSmileys();
  $Renderer->entities = getEntities();
  $Renderer->acronyms = getAcronyms();
  $Renderer->interwiki = getInterwiki();
  #$Renderer->badwords = getBadWords();

  // Loop through the instructions
  foreach ( $instructions as $instruction ) {
      // Execute the callback against the Renderer
      call_user_func_array(array(&$Renderer, $instruction[0]),$instruction[1]);
  }

  //set info array
  $info = $Renderer->info;

  // Return the output
  return $Renderer->doc;
}

/**
 * Gets the first heading from a file
 *
 * @author Jan Decaluwe <jan@jandecaluwe.com>
 */
function p_get_first_heading($id){
  $file = wikiFN($id);
  if (@file_exists($file)) {
    $instructions = p_cached_instructions($file,true);
    foreach ( $instructions as $instruction ) {
      if ($instruction[0] == 'header') {
        return trim($instruction[1][0]);
      }
    }
  }
  return NULL;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
