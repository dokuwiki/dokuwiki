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
  require_once(DOKU_INC.'inc/cache.php');

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
      $ret = p_cached_output($file,'xhtml',$id);
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
 * @deprecated
 * @author Harry Fuecks <hfuecks@gmail.com>
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
  $html = p_cached_output(localeFN($id));
  return $html;
}

/**
 *     *** DEPRECATED ***
 *
 * use p_cached_output()
 *
 * Returns the given file parsed to XHTML
 *
 * Uses and creates a cachefile
 *
 * @deprecated
 * @author Andreas Gohr <andi@splitbrain.org>
 * @todo   rewrite to use mode instead of hardcoded XHTML
 */
function p_cached_xhtml($file){
  return p_cached_output($file);
}

/**
 * Returns the given file parsed into the requested output format
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Chris Smith <chris@jalakai.co.uk>
 */
function p_cached_output($file, $format='xhtml', $id='') {
  global $conf;

  $cache = new cache_renderer($id, $file, $format);
  if ($cache->useCache()) {
    $parsed = $cache->retrieveCache();
    if($conf['allowdebug']) $parsed .= "\n<!-- cachefile {$cache->cache} used -->\n";
  } else {
    $parsed = p_render($format, p_cached_instructions($file,false,$id), $info);

    if ($info['cache']) {
      $cache->storeCache($parsed);               //save cachefile
      if($conf['allowdebug']) $parsed .= "\n<!-- no cachefile used, but created -->\n";
    }else{
      $cache->removeCache();                     //try to delete cachefile
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
function p_cached_instructions($file,$cacheonly=false,$id='') {
  global $conf;

  $cache = new cache_instructions($id, $file);

  if ($cacheonly || $cache->useCache()) {
    return $cache->retrieveCache();
  } else if (@file_exists($file)) {
    // no cache - do some work
    $ins = p_get_instructions(io_readfile($file));
    $cache->storeCache($ins);
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
  trigger_event('PARSER_WIKITEXT_PREPROCESS', $text);
  $p = $Parser->parse($text);
//  dbg($p);
  return $p;
}

/**
 * returns the metadata of a page
 *
 * @author Esther Brunner <esther@kaffeehaus.ch>
 */
function p_get_metadata($id, $key=false, $render=false){
  global $INFO;

  if ($id == $INFO['id'] && !empty($INFO['meta'])) {
    $meta = $INFO['meta'];
  } else {
    $file = metaFN($id, '.meta');

    if (@file_exists($file)) $meta = unserialize(io_readFile($file, false));
    else $meta = array();

    // metadata has never been rendered before - do it!
    if ($render && !$meta['description']['abstract']){
      $meta = p_render_metadata($id, $meta);
      io_saveFile($file, serialize($meta));
    }
  }

  // filter by $key
  if ($key){
    list($key, $subkey) = explode(' ', $key, 2);
    if (trim($subkey)) return $meta[$key][$subkey];
    else return $meta[$key];
  }

  return $meta;
}

/**
 * sets metadata elements of a page
 *
 * @author Esther Brunner <esther@kaffeehaus.ch>
 */
function p_set_metadata($id, $data, $render=false){
  if (!is_array($data)) return false;

  $orig = p_get_metadata($id);

  // render metadata first?
  if ($render) $meta = p_render_metadata($id, $orig);
  else $meta = $orig;

  // now add the passed metadata
  $protected = array('description', 'date', 'contributor');
  foreach ($data as $key => $value){

    // be careful with sub-arrays of $meta['relation']
    if ($key == 'relation'){
      foreach ($value as $subkey => $subvalue){
        $meta[$key][$subkey] = array_merge($meta[$key][$subkey], $subvalue);
      }

    // be careful with some senisitive arrays of $meta
    } elseif (in_array($key, $protected)){
      if (is_array($value)){
        #FIXME not sure if this is the intended thing:
        if(!is_array($meta[$key])) $meta[$key] = array($meta[$key]);
        $meta[$key] = array_merge($meta[$key], $value);
      }

    // no special treatment for the rest
    } else {
      $meta[$key] = $value;
    }
  }

  // save only if metadata changed
  if ($meta == $orig) return true;

  // check if current page metadata has been altered - if so sync the changes
  global $INFO;
  if ($id == $INFO['id'] && isset($INFO['meta'])) {
    $INFO['meta'] = $meta;
  }

  return io_saveFile(metaFN($id, '.meta'), serialize($meta));
}

/**
 * renders the metadata of a page
 *
 * @author Esther Brunner <esther@kaffeehaus.ch>
 */
function p_render_metadata($id, $orig){
  require_once DOKU_INC."inc/parser/metadata.php";

  // get instructions
  $instructions = p_cached_instructions(wikiFN($id),false,$id);

  // set up the renderer
  $renderer = & new Doku_Renderer_metadata();
  $renderer->meta = $orig;

  // loop through the instructions
  foreach ($instructions as $instruction){
    // execute the callback against the renderer
    call_user_func_array(array(&$renderer, $instruction[0]), $instruction[1]);
  }

  return $renderer->meta;
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

  if ($mode=='wiki') { msg("Renderer for $mode not valid",-1); return null; } //FIXME!! remove this line when inc/parser/wiki.php works.

  // Create the renderer
  if(!@file_exists(DOKU_INC."inc/parser/$mode.php")){
    msg("No renderer for $mode found",-1);
    return null;
  }

  require_once DOKU_INC."inc/parser/$mode.php";
  $rclass = "Doku_Renderer_$mode";
  if ( !class_exists($rclass) ) {
    trigger_error("Unable to resolve render class $rclass",E_USER_WARNING);
    msg("Renderer for $mode not valid",-1);
    return null;
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

  // Post process and return the output
  $data = array($mode,& $Renderer->doc);
  trigger_event('RENDERER_CONTENT_POSTPROCESS',$data);
  return $Renderer->doc;
}

/**
 * Gets the first heading from a file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_get_first_heading($id){
  global $conf;
  return $conf['useheading'] ? p_get_metadata($id,'title') : null;
}

/**
 * Wrapper for GeSHi Code Highlighter, provides caching of its output
 *
 * @author Christopher Smith <chris@jalakai.co.uk>
 */
function p_xhtml_cached_geshi($code, $language) {
  $cache = getCacheName($language.$code,".code");

  if (@file_exists($cache) && !$_REQUEST['purge'] &&
     (filemtime($cache) > filemtime(DOKU_INC . 'inc/geshi.php'))) {

    $highlighted_code = io_readFile($cache, false);
    @touch($cache);

  } else {

    require_once(DOKU_INC . 'inc/geshi.php');

    $geshi = new GeSHi($code, strtolower($language), DOKU_INC . 'inc/geshi');
    $geshi->set_encoding('utf-8');
    $geshi->enable_classes();
    $geshi->set_header_type(GESHI_HEADER_PRE);
    $geshi->set_overall_class("code $language");
    $geshi->set_link_target($conf['target']['extern']);

    $highlighted_code = $geshi->parse_code();

    io_saveFile($cache,$highlighted_code);
  }

  return $highlighted_code;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
