<?
/**
 * The DokuWiki parser
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  include_once("inc/common.php");
  include_once("inc/html.php");
  include_once("inc/format.php");
  require_once("lang/en/lang.php");
  require_once("lang/".$conf['lang']."/lang.php");

/**
 * The main parser function.
 *
 * Accepts raw data and returns valid xhtml
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */  
function parse($text){
  global $parser;
  global $conf;
  $table   = array();
  $hltable = array();

  //preparse
  $text = preparse($text,$table,$hltable);

  //padding with a newline
  $text  = "\n".$text."\n";

  #for link matching
  $urls = '(https?|telnet|gopher|file|wais|ftp|ed2k|irc)';
  $ltrs = '\w';
  $gunk = '/\#~:.?+=&%@!\-';
  $punc = '.:?\-;,';
  $host = $ltrs.$punc;
  $any  = $ltrs.$gunk.$punc;

  /* first pass */

  //preformated texts
  firstpass($table,$text,"#<nowiki>(.*?)</nowiki>#se","preformat('\\1','nowiki')");
  firstpass($table,$text,"#%%(.*?)%%#se","preformat('\\1','nowiki')");
  firstpass($table,$text,"#<code( (\w+))?>(.*?)</code>#se","preformat('\\3','code','\\2')");
  firstpass($table,$text,"#<file>(.*?)</file>#se","preformat('\\1','file')");

  // html and php includes
  firstpass($table,$text,"#<html>(.*?)</html>#se","preformat('\\1','html')");
  firstpass($table,$text,"#<php>(.*?)</php>#se","preformat('\\1','php')");

  // codeblocks
  firstpass($table,$text,"/(\n( {2,}|\t)[^\*\-\n ][^\n]+)(\n( {2,}|\t)[^\n]*)*/se","preformat('\\0','block')","\n");

  //check if toc is wanted
  if(!isset($parser['toc'])){
    if(strpos($text,'~~NOTOC~~')!== false){
      $text = str_replace('~~NOTOC~~','',$text);
      $parser['toc']  = false;
    }else{
      $parser['toc']  = true;
    }
  }

  //check if this file may be cached
  if(!isset($parser['cache'])){
    if(strpos($text,'~~NOCACHE~~')!=false){
      $text = str_replace('~~NOCACHE~~','',$text);
      $parser['cache']  = false;
    }else{
      $parser['cache']  = true;
    }
  }

  //headlines
  format_headlines($table,$hltable,$text);

  //links
  firstpass($table,$text,"#\[\[([^\]]+?)\]\]#ie","linkformat('\\1')");

  //media
  firstpass($table,$text,"/\{\{([^\}]+)\}\}/se","mediaformat('\\1')");

  //match full URLs (adapted from Perl cookbook)
  firstpass($table,$text,"#(\b)($urls://[$any]+?)([$punc]*[^$any])#ie","linkformat('\\2')",'\1','\4');

  //short www URLs 
  firstpass($table,$text,"#(\b)(www\.[$host]+?\.[$host]+?[$any]+?)([$punc]*[^$any])#ie","linkformat('http://\\2|\\2')",'\1','\3');

  //windows shares 
  firstpass($table,$text,"#([$gunk$punc\s])(\\\\\\\\[$host]+?\\\\[$any]+?)([$punc]*[^$any])#ie","linkformat('\\2')",'\1','\3');

  //short ftp URLs 
  firstpass($table,$text,"#(\b)(ftp\.[$host]+?\.[$host]+?[$any]+?)([$punc]*[^$any])#ie","linkformat('ftp://\\2')",'\1','\3');

  // email@domain.tld
  firstpass($table,$text,"#<([\w0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)>#ie", "linkformat('\\1@\\2')");

  //CamelCase if wanted
  if($conf['camelcase']){
    firstpass($table,$text,"#(\b)([A-Z]+[a-z]+[A-Z][A-Za-z]*)(\b)#se","linkformat('\\2')",'\1','\3');
  }

  $text = htmlspecialchars($text);

  //smileys
  smileys($table,$text);

  //acronyms
  acronyms($table,$text);

  /* second pass for simple formating */
  $text = simpleformat($text);
  
  /* third pass - insert the matches from 1st pass */
  reset($table);
  while (list($key, $val) = each($table)) {
    $text = str_replace($key,$val,$text);
  }

  /* remove empty paragraphs */
  $text = preg_replace('"<p>\n*</p>"','',$text);

  /* remove padding */
  $text = trim($text);
  return $text;
}

/**
 * Line by line preparser
 *
 * This preparses the text by walking it line by line. This
 * is the only place where linenumbers are still available (needed
 * for section edit. Some precautions have to be taken to not change
 * any noparse block.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function preparse($text,&$table,&$hltable){
  $lines = split("\n",$text);

  //prepare a tokens for paragraphs
  $po = mkToken();
  $table[$po] = "<p>";
  $pc = mkToken();
  $table[$pc] = "</p>";

  for ($l=0; $l<count($lines); $l++){
    //temporay line holder
    $line = $lines[$l];

    //look for end of multiline noparse areas
    if($noparse){
      if(preg_match("#^.*?$noparse#",$line)){
        $noparse = '';
        $line = preg_replace("#^.*?$noparse#",$line,1);
      }else{
        continue;
      }
    }

    if(!$noparse){
      //skip indented lines
      if(preg_match('#^(  |\t)#',$line)) continue;
      //remove norparse areas which open and close on the same line
      $line = preg_replace("#<nowiki>(.*?)</nowiki>#","",$line);
      $line = preg_replace("#%%(.*?)%%#","",$line);
      $line = preg_replace("#<code( (\w+))?>(.*?)</code>#","",$line);
      $line = preg_replace("#<file>(.*?)</file>#","",$line);
      $line = preg_replace("#<html>(.*?)</html>#","",$line);
      $line = preg_replace("#<php>(.*?)</php>#","",$line);
      //check for start of multiline noparse areas
      if(preg_match('#^.*?<(nowiki|code|php|html|file)( (\w+))?>#',$line,$matches)){
				list($noparse) = split(" ",$matches[1]); //remove options
        $noparse = '</'.$noparse.'>';
        continue;
      }elseif(preg_match('#^.*?%%#',$line)){
				$noparse = '%%';
        continue;
			}
    }

    //handle headlines
    if(preg_match('/^(\s)*(==+)(.+?)(==+)(\s*)$/',$lines[$l],$matches)){
      //get token
      $tk = tokenize_headline($hltable,$matches[2],$matches[3],$l);
      //replace line with token
      $lines[$l] = $tk;
    }

    //handle paragraphs
    if(empty($lines[$l])){
      $lines[$l] = "$pc\n$po";
    }
  }

  //reassemble full text
  $text = join("\n",$lines);
  //open first and close last paragraph
  $text = "$po\n$text\n$pc";

  return $text;
}

/**
 * Build TOC lookuptable
 *
 * This function adds some information about the given headline
 * to a lookuptable to be processed later. Returns a unique token
 * that idetifies the headline later
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function tokenize_headline(&$hltable,$pre,$hline,$lno){
  switch (strlen($pre)){
    case 2:
      $lvl = 5;
      break;
    case 3:
      $lvl = 4;
      break;
    case 4:
      $lvl = 3;
      break;
    case 5:
      $lvl = 2;
      break;
    default:
      $lvl = 1;
      break;
  }
  $token = mkToken();
  $hltable[] = array( 'name'  => htmlspecialchars(trim($hline)),
                      'level' => $lvl,
                      'line'  => $lno,
                      'token' => $token );
  return $token;
}

/**
 * Headline formatter
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function format_headlines(&$table,&$hltable,&$text){
  global $parser;
  global $conf;
  global $lang;
  global $ID;

  // walk the headline table prepared in preparsing
  $last  = 0;
  $cnt   = 0;
  $hashs = array();
  foreach($hltable as $hl){
    $cnt++;

    //make unique headlinehash
    $hash = cleanID($hl['name']);
    $i=2;
    while(in_array($hash,$hashs))
      $hash = cleanID($hl['name']).$i++;
    $hashs[] = $hash;

    // build headline
    $headline   = "</p>\n"; //close paragraph
    if($cnt - 1) $headline .= '</div>'; //no close on first HL
    $headline  .= '<a name="'.$hash.'"></a>';
    $headline  .= '<h'.$hl['level'].'>';
    $headline  .= $hl['name'];
    $headline  .= '</h'.$hl['level'].'>';
    $headline  .= '<div class="level'.$hl['level'].'">';
    $headline  .= "\n<p>"; //open new paragraph

    //remember for autoTOC
    if($hl['level'] <= $conf['maxtoclevel']){
      $content[]  = array('id'    => $hash,
                          'name'  => $hl['name'],
                          'level' => $hl['level']);
    }

    //add link for section edit for HLs 1, and 3
    if( ($hl['level'] <= $conf['maxseclevel']) &&
        ($hl['line'] - $last > 1)){
      $secedit = '<!-- SECTION ['.$last.'-'.($hl['line'] - 1).'] -->';
      $headline = $secedit.$headline;
      $last = $hl['line'];
    }

    //put headline into firstpasstable
    $table[$hl['token']] = $headline;
  }

  //add link for editing the last section
  if($last){
    $secedit = '<!-- SECTION ['.$last.'-] -->';
    $token    = mktoken();
    $text    .= $token;
    $table[$token] = $secedit; 
  }

  //close last div
  if ($cnt){
    $token = mktoken();
    $text .= $token;
    $table[$token] = '</div>';
  }

  //prepend toc
  if ($parser['toc'] && count($content) > 2){
    $token = mktoken();
    $text  = $token.$text;
    $table[$token] = html_toc($content);
  }
}

/**
 * Formats various link types using the functions from format.php
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function linkformat($match){
  global $conf;
  //unescape
  $match = str_replace('\\"','"',$match);

  //prepare variables for the formaters
	$link = array();
  list($link['url'],$link['name']) = split('\|',$match,2);
  $link['url']    = trim($link['url']);
  $link['name']   = trim($link['name']);
  $link['class']  = '';
  $link['target'] = '';
  $link['style']  = '';
  $link['pre']    = '';
  $link['suf']    = '';
  $link['more']   = '';

  //save real name for image check
  $realname = $link['name'];

  /* put it into the right formater */
  if(strpos($link['url'],'>')){
    // InterWiki
    $link = format_link_interwiki($link);
  }elseif(preg_match('#^([a-z0-9]+?){1}://#i',$link['url'])){
    // external URL
    $link = format_link_externalurl($link);
  }elseif(preg_match("/^\\\\\\\\([a-z0-9\-_.]+)\\\\(.+)$/",$link['url'])){
    // windows shares
    $link = format_link_windows($link);
  }elseif(preg_match('#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i',$link['url'])){
    // email
    $link = format_link_email($link);
  }else{
    // wiki link
    $link = format_link_wiki($link);
  }

  //is realname an image? use media formater
  if(preg_match('#^{{.*?\.(gif|png|jpe?g)(\?.*?)?\s*(\|.*?)?}}$#',$realname)){
    $link['name'] = substr($realname,2,-2);
    $link         = format_link_media($link);
  }

  // build the replacement with the variables set by the formaters
  return format_link_build($link);
}

/**
 * Simple text formating and typography is done here
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function simpleformat($text){
  global $conf;
  
  $text = preg_replace('/__(.+?)__/s','<u>\1</u>',$text);  //underline
  $text = preg_replace('/\/\/(.+?)\/\//s','<em>\1</em>',$text);  //emphasize
  $text = preg_replace('/\*\*(.+?)\*\*/s','<strong>\1</strong>',$text);  //bold
  $text = preg_replace('/\'\'(.+?)\'\'/s','<code>\1</code>',$text);  //code
  $text = preg_replace('#&lt;del&gt;(.*?)&lt;/del&gt;#is','<del>\1</del>',$text); //deleted
  $text = preg_replace('/^(\s)*----+(\s*)$/m',"</p>\n<hr noshade=\"noshade\" size=\"1\" />\n<p>",$text); //hr

  //sub and superscript
  $text = preg_replace('#&lt;sub&gt;(.*?)&lt;/sub&gt;#is','<sub>\1</sub>',$text);
  $text = preg_replace('#&lt;sup&gt;(.*?)&lt;/sup&gt;#is','<sup>\1</sup>',$text);
 
  //do quoting 
  $text = preg_replace("/\n((&gt;)[^\n]*?\n)+/se","'\n'.quoteformat('\\0').'\n'",$text);
  
  // Typography
  if($conf['typography']){
    $text = preg_replace('/([^-])--([^-])/s','\1&#8211;\2',$text); //endash
    $text = preg_replace('/([^-])---([^-])/s','\1&#8212;\2',$text); //emdash
    $text = preg_replace('/&quot;([^\"]+?)&quot;/s','&#8220;\1&#8221;',$text);  //curly quotes
    $text = preg_replace('/(\s)\'(\S)/m','\1&#8216;\2',$text);  //single open quote
    $text = preg_replace('/(\S)\'/','\1&#8217;',$text);  //single closing quote or apostroph
    $text = preg_replace('/\.\.\./','\1&#8230;\2',$text);  //ellipse
    $text = preg_replace('/(\d+)x(\d+)/i','\1&#215;\2',$text);  //640x480

    $text = preg_replace('/&gt;&gt;/i','&raquo;',$text);   // >>
    $text = preg_replace('/&lt;&lt;/i','&laquo;',$text);   // <<

    $text = preg_replace('/&lt;-&gt;/i','&#8596;',$text);  // <->
    $text = preg_replace('/&lt;-/i','&#8592;',$text);      // <-
    $text = preg_replace('/-&gt;/i','&#8594;',$text);      //  ->

    $text = preg_replace('/&lt;=&gt;/i','&#8660;',$text);  // <=>
    $text = preg_replace('/&lt;=/i','&#8656;',$text);      // <=
    $text = preg_replace('/=&gt;/i','&#8658;',$text);      //  =>

    $text = preg_replace('/\(c\)/i','&copy;',$text);      //  copyrigtht
    $text = preg_replace('/\(r\)/i','&reg;',$text);      //  registered
    $text = preg_replace('/\(tm\)/i','&trade;',$text);      //  trademark
  }

  //forced linebreaks
  $text = preg_replace('#\\\\\\\\(\s)#',"<br />\\1",$text);

  // lists (blocks leftover after blockformat)
  $text = preg_replace("/(\n( {2,}|\t)[\*\-][^\n]+)(\n( {2,}|\t)[^\n]*)*/se","\"\\n\".listformat('\\0')",$text);

  // tables
  $text = preg_replace("/\n(([\|\^][^\n]*?)+[\|\^] *\n)+/se","\"\\n\".tableformat('\\0')",$text);

  // footnotes
  $text = footnotes($text);

  // run custom text replacements
  $text = customs($text);

  return $text;
}

/**
 * Footnote formating
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function footnotes($text){
  $num = 0;
  while (preg_match('/\(\((.+?)\)\)/s',$text,$match)){
    $num++;
    $fn    = $match[1];
    $linkt = '<a href="#fn'.$num.'" name="fnt'.$num.'" class="fn_top">'.$num.')</a>';
    $linkb = '<a href="#fnt'.$num.'" name="fn'.$num.'" class="fn_bot">'.$num.')</a>';

    $text  = preg_replace('/ ?\(\((.+?)\)\)/s',$linkt,$text,1);
    if($num == 1) $text .= '<div class="footnotes">';
    $text .= '<div class="fn">'.$linkb.' '.$fn.'</div>';
  }

  if($num) $text .= '</div>';
  return $text;
}

/**
 * Replace smileys with their graphic equivalents
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function smileys(&$table,&$text){
  $smileys = file('conf/smileys.conf');
  foreach($smileys as $smiley){
    $smiley = preg_replace('/#.*$/','',$smiley); //ignore comments
    $smiley = trim($smiley);
    if(empty($smiley)) continue;
    $sm     = preg_split('/\s+/',$smiley,2);
    $sm[1]  = '<img src="'.getBaseURL().'smileys/'.$sm[1].'" align="middle" alt="'.$sm[0].'" />';
    $sm[0]  = preg_quote($sm[0],'/');
    firstpass($table,$text,'/(\W)'.$sm[0].'(\W)/s',$sm[1],"\\1","\\2");
  }
}

/**
 * Add acronym tags to known acronyms
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function acronyms(&$table,&$text){
  $acronyms = file('conf/acronyms.conf');
  foreach($acronyms as $acro){
    $acro = preg_replace('/#.*$/','',$acro); //ignore comments
    $acro = trim($acro);
    if(empty($acro)) continue;
    list($ac,$desc) = preg_split('/\s+/',$acro,2);
    $ac   = preg_quote($ac,'/');
    firstpass($table,$text,'/(\b)('.$ac.')(\b)/s',"<acronym title=\"$desc\">\\2</acronym>","\\1","\\3");
  }
} 

/**
 * Apply custom text replacements
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function customs($text){
  $reps = file ('conf/custom.conf');
  foreach($reps as $rep){
    //strip comments only outside a regexp
    $rep = preg_replace('/#[^\/]*$/','',$rep); //ignore comments
    $rep = trim($rep);
    if(empty($rep)) continue;
    if(preg_match('#^(/.+/\w*)\s+\'(.*)\'$#',$rep,$matches)){
      $text = preg_replace($matches[1],$matches[2],$text);
    }
  }
  return $text;
}

/**
 * Replace regexp with token
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function firstpass(&$table,&$text,$regexp,$replace,$lpad='',$rpad=''){
  //extended regexps have to be disabled for inserting the token
  //and later reenabled when handling the actual code:
  $ext='';
  if(substr($regexp,-1) == 'e'){
    $ext='e';
    $regexp = substr($regexp,0,-1);
  }

  while(preg_match($regexp,$text,$matches)){
    $token = mkToken();
    $match = $matches[0];
    $text  = preg_replace($regexp,$lpad.$token.$rpad,$text,1);
    $table[$token] = preg_replace($regexp.$ext,$replace,$match);
  }
}

/**
 * create a random and hopefully unique token
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function mkToken(){
  return '~'.md5(uniqid(rand(), true)).'~';
}

/**
 * Do quote blocks
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function quoteformat($block){
  $block = trim($block);
  $lines = split("\n",$block);

  $lvl = 0;
  $ret = "";
  foreach ($lines as $line){
    //remove '>' and count them
    $cnt = 0;
    while(substr($line,0,4) == '&gt;'){
      $line = substr($line,4);
      $cnt++;
    }
    //compare to last level and open or close new divs if needed
		if($cnt > $lvl){
      $ret .= "</p>\n";
			for ($i=0; $i< $cnt - $lvl; $i++){
  			$ret .= '<div class="quote">';
      }
			$ret .= "\n<p>";
    }elseif($cnt < $lvl){
      $ret .= "\n</p>";
      for ($i=0; $i< $lvl - $cnt; $i++){
        $ret .= "</div>\n";
      }
			$ret .= "<p>\n";
    }elseif(empty($line)){
      $ret .= "</p>\n<p>";
    }
    //keep rest of line but trim left whitespaces
    $ret .= ltrim($line)."\n";
		//remember level
    $lvl = $cnt;
  }

  //close remaining divs
  $ret .= "</p>\n";
  for ($i=0; $i< $lvl; $i++){
    $ret .= "</div>\n";
  }
  $ret .= "<p>\n";

  return "$ret";
}

/**
 * format inline tables
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function tableformat($block) {
  $block = trim($block);
  $lines = split("\n",$block);
  $ret = "";
  //build a row array 
  $rows = array();
  for($r=0; $r < count($lines); $r++){
    $line = $lines[$r];
    //remove last seperator and trailing whitespace
    $line = preg_replace('/[\|\^]\s*$/', '', $line);
    $c = -1; //prepare colcounter)
    for($chr=0; $chr < strlen($line); $chr++){
      if($line[$chr] == '^'){
        $c++;
        $rows[$r][$c]['head'] = true;
        $rows[$r][$c]['data'] = '';
      }elseif($line[$chr] == '|'){
        $c++;
        $rows[$r][$c]['head'] = false;
        $rows[$r][$c]['data'] = '';
      }else{
        $rows[$r][$c]['data'].= $line[$chr];
      }
    }
  }

  //build table
  $ret .= "</p>\n<table class=\"inline\">\n";
  for($r=0; $r < count($rows); $r++){
    $ret .= "  <tr>\n";

    for ($c=0; $c < count($rows[$r]); $c++){
      $cspan=1;
      $data = trim($rows[$r][$c]['data']);
      $head = $rows[$r][$c]['head'];

      //join cells if next is empty
      while($c < count($rows[$r])-1 && $rows[$r][$c+1]['data'] == ''){
        $c++;
        $cspan++;
      }
      if($cspan > 1){
        $cspan = 'colspan="'.$cspan.'"';
      }else{
        $cspan = '';
      }

      if ($head) {
        $ret .= "    <th class=\"inline\" $cspan>$data</th>\n";
      } else {
        $ret .= "    <td class=\"inline\" $cspan>$data</td>\n";
      }
    }
    $ret .= "  </tr>\n";
  }
  $ret .= "</table>\n<p>";

  return $ret;
}

/**
 * format lists
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function listformat($block){
  //remove 1st newline 
  $block = substr($block,1);
  //unescape
  $block = str_replace('\\"','"',$block);

//dbg($block);

  //walk line by line
  $ret='';
  $lst=0;
  $lvl=0;
  $enc=0;
  $lines = split("\n",$block);

  //build an item array 
  $cnt=0;
  $items = array();
  foreach ($lines as $line){
    //get intendion level
    $lvl  = 0;
    $lvl += floor(strspn($line,' ')/2);
    $lvl += strspn($line,"\t");
    //remove indents
    $line = preg_replace('/^[ \t]+/','',$line);
    //get type of list
    (substr($line,0,1) == '-') ? $type='ol' : $type='ul';
    // remove bullet and following spaces
    $line = preg_replace('/^[*\-]\s*/','',$line);
    //add item to the list
    $items[$cnt]['level'] = $lvl;
    $items[$cnt]['type']  = $type;
    $items[$cnt]['text']  = $line;
    //increase counter
    $cnt++;
  }

  $level = 0;
  $opens = array();

  foreach ($items as $item){
    if( $item['level'] > $level ){
      //open new list
      $ret .= "\n<".$item['type'].">\n";
      array_push($opens,$item['type']);
    }elseif( $item['level'] < $level ){
      //close last item
      $ret .= "</li>\n";
      for ($i=0; $i<($level - $item['level']); $i++){
        //close higher lists
        $ret .= '</'.array_pop($opens).">\n</li>\n";
      }
    }elseif($item['type'] != $opens[count($opens)-1]){
      //close last list and open new
      $ret .= '</'.array_pop($opens).">\n</li>\n";
      $ret .= "\n<".$item['type'].">\n";
      array_push($opens,$item['type']);
    }else{
      //close last item
      $ret .= "</li>\n";
    }

    //remember current level and type
    $level = $item['level'];

    //print item
    $ret .= '<li class="level'.$item['level'].'">';
    $ret .= '<span class="li">'.$item['text'].'</span>';
  }

  //close remaining items and lists
  while ($open = array_pop($opens)){
    $ret .= "</li>\n";
    $ret .= '</'.$open.">\n";
  }
  return "</p>\n".$ret."\n<p>";
}

/**
 * Handle preformatted blocks
 *
 * Uses GeSHi for syntax highlighting
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function preformat($text,$type,$option=''){
  global $conf;
  //unescape
  $text = str_replace('\\"','"',$text);
  
  if($type == 'php' && !$conf['phpok']) $type='file';
  if($type == 'html' && !$conf['htmlok']) $type='file';
  
  switch ($type){
    case 'php':
        ob_start();
        eval($text);
        $text = ob_get_contents();
        ob_end_clean();
      break;
    case 'html':
      break;
    case 'nowiki':
      $text = htmlspecialchars($text);
      break;
    case 'file':
      $text = htmlspecialchars($text);
      $text = "</p>\n<pre class=\"file\">".$text."</pre>\n<p>";
      break;
    case 'code':
      if(empty($option)){
        $text = htmlspecialchars($text);
        $text = '<pre class="code">'.$text.'</pre>';
      }else{
        //strip leading blank line
        $text = preg_replace('/^\s*?\n/','',$text);
        //use geshi for highlighting
        require_once("inc/geshi.php");
        $geshi = new GeSHi($text, strtolower($option), "inc/geshi");
        $geshi->enable_classes();
        $geshi->set_header_type(GESHI_HEADER_PRE);
				$geshi->set_overall_class('code');
        $geshi->set_link_target($conf['target']['extern']);
        $text = $geshi->parse_code();
      }
      $text = "</p>\n".$text."\n<p>";
      break;
    case 'block':
      $text  = substr($text,1);   //remove 1st newline
      $lines = split("\n",$text); //break into lines
      $text  = '';
      foreach($lines as $line){
        $text .= substr($line,2)."\n"; //remove indents
      }
      $text = htmlspecialchars($text);
      $text = "</p>\n<pre class=\"pre\">".$text."</pre>\n<p>";
      break;
  }
  return $text;
}

/**
 * Format embedded media (images)
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function mediaformat($text){
  global $conf;

  //unescape
  $text = str_replace('\\"','"',$text);

  // format RSS
  if(substr($text,0,4) == 'rss>'){
    return format_rss(substr($text,4));
  }

  //handle normal media stuff
  $link = array();
  $link['name'] = $text;
  $link         = format_link_media($link);
	return format_link_build($link);
}

?>
