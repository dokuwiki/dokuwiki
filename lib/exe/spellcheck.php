<?php
/**
 * DokuWiki Spellcheck AJAX backend 
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Licence info: This spellchecker is inspired by code by Garrison Locke available
 * at http://www.broken-notebook.com/spell_checker/index.php (licensed under the Terms
 * of an BSD license). The code in this file was nearly completly rewritten for DokuWiki
 * and is licensed under GPL version 2 (See COPYING for details).
 *
 * Original Copyright notice follows:
 *
 * Copyright (c) 2005, Garrison Locke
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice, 
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice, 
 *     this list of conditions and the following disclaimer in the documentation 
 *     and/or other materials provided with the distribution.
 *   * Neither the name of the http://www.broken-notebook.com nor the names of its 
 *     contributors may be used to endorse or promote products derived from this 
 *     software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
 * OF SUCH DAMAGE.
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && $HTTP_RAW_POST_DATA){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once (DOKU_INC.'inc/init.php');
session_write_close();
require_once (DOKU_INC.'inc/utf8.php');
require_once (DOKU_INC.'inc/aspell.php');

header('Content-Type: text/plain; charset=utf-8');

//create spell object
$spell = new Aspell($conf['lang'],null,'utf-8');
$spell->setMode(PSPELL_FAST);

//call the requested function
$call = 'spell_'.$_POST['call'];
if(function_exists($call)){
  $call();
}else{
  print "The called function does not exist!";
}


function spell_check() {
  global $spell;
  $string = $_POST['data'];
  $misspell = false;

  // for streamlined line endings
  $string = preg_replace("/(\015\012)|(\015)/","\012",$string);
  $string = htmlspecialchars($string);

  // we need the text as array later
  $data = explode("\n",$string);


  // keep some words from being checked (use blanks to preserve the offset)
  // FIXME doesn't work yet... however with the used html mode of aspell this isn't really needed
/*  $string = preg_replace('!<\?(code|del|file)( \+)?>!e','spellclean(\\1)',$string); */
//  $string = preg_replace('!()!e','spellclean(\\1)',$string);

  // run aspell in terse sgml mode
  if(!$spell->runAspell($string,$out,$err,array('!','+html'))){
  //if(!$spell->runAspell($string,$out,$err)){
    print '2'; //to indicate an error
    print "An error occured while trying to run the spellchecker:\n";
    print $err;
    return;
  }

  // go through the result
  $lines = split("\n",$out);
  $rcnt  = count($lines)-1;    // aspell result count
  $lcnt  = count($data)+1;     // original line counter


  for($i=$rcnt; $i>=0; $i--){
    $line = trim($lines[$i]);
    if($line[0] == '@') continue; // comment
    if($line[0] == '*') continue; // no mistake in this word
    if($line[0] == '+') continue; // root of word was found
    if(empty($line)){
      // empty line -> new source line
      $lcnt--;
      continue;
    }
    if(preg_match('/^[&\?] ([^ ]+) (\d+) (\d+): (.*)/',$line,$match)){
      // match with suggestions
      $word = $match[1];
      $off  = $match[3]-1;
      $sug  = split(', ',$match[4]);
      $len  = utf8_strlen($word);
      $misspell = true;


      $data[$lcnt] = utf8_substr_replace($data[$lcnt], spell_formatword($word,$sug) , $off, $len);
      continue;
    }
    if(preg_match('/^# ([^ ]+) (\d+)/',$line,$match)){
      // match without suggestions
      $word = $match[1];
      $off  = $match[2]-1;
      $len  = utf8_strlen($word);
      $misspell = true;

      $data[$lcnt] = utf8_substr_replace($data[$lcnt], spell_formatword($word) , $off, $len);
      continue;
    }
    //still here - couldn't parse output
    print '2';
    print "The spellchecker output couldn't be parsed.\n";
    print "Line $i:".$line;
    return;
  }

  // the first char returns the spell info
  if($misspell){
    $string = '1'.join('<br />',$data);
  }else{
    $string = '0'.join('<br />',$data);
  }

  // encode multibyte chars as entities for broken Konqueror
  $string = utf8_tohtml($string);

  //output 
  print $string;
}

function spell_formatword($word,$suggestions=null){
  static $i = 1;

  if(is_array($suggestions)){
    //restrict to maximum of 7 elements
    $suggestions = array_slice($suggestions,0,7);
    $suggestions = array_map('htmlspecialchars',$suggestions);

    //konqueror's broken UTF-8 handling needs this
    $suggestions = array_map('utf8_tohtml',$suggestions);
    $suggestions = array_map('urlencode',$suggestions);

    $suggestions = array_map('addslashes',$suggestions);
    $sug = ",'".join("','",$suggestions)."'"; //build javascript args
  }else{
    $sug = '';
  }

  $link = '<a href="javascript:ajax_spell.suggest('.$i.$sug.')" '.
          'class="spell_error" id="spell_error'.$i.'">'.htmlspecialchars($word).'</a>';
  $i++;
  return $link;
}

function spell_resume(){
  $text = $_POST['data'];

  //some browsers insert newlines instead of spaces
  $text = preg_replace("/(\r\n|\n|\r)/", ' ', $text);
  $text = preg_replace("=<br */?>=i", "\n", $text);

  // remove HTML tags
  $text = strip_tags($text);

  // restore quoted special chars
  $text = unhtmlspecialchars($text);

  // but protect '&' (gets removed in JS later)
  $text = str_replace('&','&amp;',$text);
  // encode multibyte chars as entities for broken Konqueror
  $text = utf8_tohtml($text);


  // output
  print $text;
}

function spell_suggest(){
  $id   = $_POST['id'];
  $word = $_POST['word'];


  print $id."\n".$word;
}

/**
 * Reverse htmlspecialchars
 *
 * @author <donwilson at gmail dot com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function unhtmlspecialchars($string, $quotstyle=ENT_COMPAT){
  $string = str_replace ( '&amp;', '&', $string );
  $string = str_replace ( '&lt;', '<', $string );
  $string = str_replace ( '&gt;', '>', $string );
  
  if($quotstyle != ENT_NOQUOTES){
    $string = str_replace ( '&quot;', '\"', $string );
  } 
  if($quotstyle == ENT_QUOTES){
    $string = str_replace ( '&#39;', '\'', $string );
    $string = str_replace ( '&#039;', '\'', $string );
  }

  return $string;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
