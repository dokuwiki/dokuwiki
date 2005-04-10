<?php
/**
 *
 * @todo maybe wrap in class
 * @todo rename to helper
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

require_once(DOKU_INC.'inc/utils.php');

function parse_to_instructions($text){
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
  
  // FIXME These need data files...
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
  $Parser->addMode('media',new Doku_Parser_Mode_Media());
  $Parser->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
  $Parser->addMode('email',new Doku_Parser_Mode_Email());
  $Parser->addMode('windowssharelink',new Doku_Parser_Mode_WindowsShareLink());
  //$Parser->addMode('filelink',new Doku_Parser_Mode_FileLink()); //FIXME ???
  $Parser->addMode('eol',new Doku_Parser_Mode_Eol());
  
  // Do the parsing
  return $Parser->parse($text);
}  

function render_as_xhtml($instructions){

  // Create the renderer
  require_once DOKU_INC . 'inc/parser/xhtml.php';
  $Renderer = & new Doku_Renderer_XHTML();

  //FIXME add data
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
  // Return the output
  return $Renderer->doc;
}

/**
 * Returns a full page id
 *
 * @todo move to renderer? 
 */
function resolve_pageid(&$page,&$exists){
  global $ID;
  global $conf;
  $ns = getNS($ID);

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

  $exists = false;

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
  if(!empty($hash)) $page.'#'.$hash;
}

?>
