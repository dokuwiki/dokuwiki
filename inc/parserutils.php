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
  $ID = $id;

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
  $cache  = $conf['datadir'].'/_cache/xhtml/';
  $cache .= md5($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT']);
  $purge  = $conf['datadir'].'/_cache/purgefile';

  // check if cache can be used
  $cachetime = @filemtime($cache); // 0 if not exists

  if( @file_exists($file)                                             // does the source exist
      && $cachetime > @filemtime($file)                               // cache is fresh
      && ((time() - $cachetime) < $conf['cachetime'])                 // and is cachefile young enough
      && !isset($_REQUEST['purge'])                                   // no purge param was set
      && ($cachetime > @filemtime($purge))                            // and newer than the purgefile
      && ($cachetime > @filemtime(DOKU_INC.'conf/dokuwiki.php'))      // newer than the config file
      && ($cachetime > @filemtime(DOKU_INC.'conf/local.php'))         // newer than the local config file
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/xhtml.php'))   // newer than the renderer
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/parser.php'))  // newer than the parser
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/handler.php')))// newer than the handler
  {
    //well then use the cache
    $parsed = io_readfile($cache);
    $parsed .= "\n<!-- cachefile $cache used -->\n";
  }else{
    $parsed = p_render('xhtml', p_cached_instructions($file),$info); //try to use cached instructions

    if($info['cache']){
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
 * Returns the render instructions for a file
 *
 * Uses and creates a serialized cache file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function p_cached_instructions($file,$cacheonly=false){
  global $conf;
  $cache  = $conf['datadir'].'/_cache/instructions/';
  $cache .= md5($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT']);

  // check if cache can be used
  $cachetime = @filemtime($cache); // 0 if not exists

  // cache forced?
  if($cacheonly){
    if($cachetime){
      return unserialize(io_readfile($cache));
    }else{
      return NULL;
    }
  }

  if( @file_exists($file)                                             // does the source exist
      && $cachetime > @filemtime($file)                               // cache is fresh
      && !isset($_REQUEST['purge'])                                   // no purge param was set
      && ($cachetime > @filemtime(DOKU_INC.'conf/dokuwiki.php'))      // newer than the config file
      && ($cachetime > @filemtime(DOKU_INC.'conf/local.php'))         // newer than the local config file
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/parser.php'))  // newer than the parser
      && ($cachetime > @filemtime(DOKU_INC.'inc/parser/handler.php')))// newer than the handler
  {
    //well then use the cache
    return unserialize(io_readfile($cache));
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
  global $conf;

  require_once DOKU_INC . 'inc/parser/parser.php';
  
  // Create the parser
  $Parser = & new Doku_Parser();
  
  // Add the Handler
  $Parser->Handler = & new Doku_Handler();
  
  // Load all the modes
  $Parser->addMode('listblock',new Doku_Parser_Mode_ListBlock());
  $Parser->addMode('preformatted',new Doku_Parser_Mode_Preformatted()); 
  $Parser->addMode('notoc',new Doku_Parser_Mode_NoToc());
  $Parser->addMode('nocache',new Doku_Parser_Mode_NoCache());
  $Parser->addMode('header',new Doku_Parser_Mode_Header());
  $Parser->addMode('table',new Doku_Parser_Mode_Table());
  
  $formats = array (
      'strong', 'emphasis', 'underline', 'monospace',
      'subscript', 'superscript', 'deleted',
  );
  foreach ( $formats as $format ) {
      $Parser->addMode($format,new Doku_Parser_Mode_Formatting($format));
  }
  
  $Parser->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
  $Parser->addMode('footnote',new Doku_Parser_Mode_Footnote());
  $Parser->addMode('hr',new Doku_Parser_Mode_HR());
  
  $Parser->addMode('unformatted',new Doku_Parser_Mode_Unformatted());
  $Parser->addMode('php',new Doku_Parser_Mode_PHP());
  $Parser->addMode('html',new Doku_Parser_Mode_HTML());
  $Parser->addMode('code',new Doku_Parser_Mode_Code());
  $Parser->addMode('file',new Doku_Parser_Mode_File());
  $Parser->addMode('quote',new Doku_Parser_Mode_Quote());
  
  $Parser->addMode('smiley',new Doku_Parser_Mode_Smiley(array_keys(getSmileys())));
  $Parser->addMode('acronym',new Doku_Parser_Mode_Acronym(array_keys(getAcronyms())));
  #$Parser->addMode('wordblock',new Doku_Parser_Mode_Wordblock(getBadWords()));
  $Parser->addMode('entity',new Doku_Parser_Mode_Entity(array_keys(getEntities())));
  
  $Parser->addMode('multiplyentity',new Doku_Parser_Mode_MultiplyEntity());
  $Parser->addMode('quotes',new Doku_Parser_Mode_Quotes());

  if($conf['camelcase']){  
    $Parser->addMode('camelcaselink',new Doku_Parser_Mode_CamelCaseLink());
  }

  $Parser->addMode('internallink',new Doku_Parser_Mode_InternalLink());
  $Parser->addMode('rss',new Doku_Parser_Mode_RSS());
  $Parser->addMode('media',new Doku_Parser_Mode_Media());
  $Parser->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
  $Parser->addMode('emaillink',new Doku_Parser_Mode_EmailLink());
  $Parser->addMode('windowssharelink',new Doku_Parser_Mode_WindowsShareLink());
  //$Parser->addMode('filelink',new Doku_Parser_Mode_FileLink()); //FIXME ???
  $Parser->addMode('eol',new Doku_Parser_Mode_Eol());
  
  // Do the parsing
  $p    = $Parser->parse($text);
#  dbg($p);
  return $p;
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
        return $instruction[1][0];
      }
    }
  }
  return NULL;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
