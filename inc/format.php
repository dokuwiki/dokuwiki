<?
/**
 * link format functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  require_once("conf/dokuwiki.php");
  require_once("inc/common.php");


/**
 * Assembles all parts defined by the link formater below
 * Returns HTML for the link
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_build($link){
  //make sure the url is XHTML compliant
  $link['url'] = str_replace('&','&amp;',$link['url']);
  $link['url'] = str_replace('&amp;amp;','&amp;',$link['url']);
  //remove double encodings in titles
  $link['title'] = str_replace('&amp;amp;','&amp;',$link['title']);

  $ret  = '';
  $ret .= $link['pre'];
  $ret .= '<a href="'.$link['url'].'"';
  if($link['class'])  $ret .= ' class="'.$link['class'].'"';
  if($link['target']) $ret .= ' target="'.$link['target'].'"';
  if($link['title'])  $ret .= ' title="'.$link['title'].'"';
  if($link['style'])  $ret .= ' style="'.$link['style'].'"';
  if($link['more'])   $ret .= ' '.$link['more'];
  $ret .= '>';
  $ret .= $link['name'];
  $ret .= '</a>';
  $ret .= $link['suf'];
  return $ret;
}

/**
 * Link Formaters
 *
 * Each of these functions need to set
 *
 * $link['url']    URL to use in href=""
 * $link['name']   HTML to enclose in <a> with proper special char encoding
 * $link['class']  CSS class to set on link
 * $link['target'] which target to use (blank) for current window
 * $link['style']  Additonal style attribute set with style=""
 * $link['title']  Title to set with title="" 
 * $link['pre']    HTML to prepend to link
 * $link['suf']    HTML to append to link
 * $link['more']   Additonal HTML to include into the anchortag
 *
 */

/**
 * format wiki links
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_wiki($link){
  global $conf;
  global $ID; //we use this to get the current namespace
  //obvious setup
  $link['target'] = $conf['target']['wiki'];
  $link['style']  = '';
  $link['pre']    = '';
  $link['suf']   = '';
  $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';

  //if links starts with . add current namespace if any
  if(strpos($link['url'],'.')===0){
    $ns = substr($ID,0,strrpos($ID,':')); #get current ns
    $link['url'] = $ns.':'.substr($link['url'],1);
  }

  //if link contains no namespace. add current namespace (if any)
  if(strpos($ID,':')!==false  && strpos($link['url'],':') === false){
    $ns = substr($ID,0,strrpos($ID,':')); #get current ns
    $link['url'] = $ns.':'.$link['url'];
  }

  //keep hashlink if exists
  list($link['url'],$hash) = split('#',$link['url'],2);
  $hash = cleanID($hash);

  //use link without namespace as name
  if(empty($link['name'])) $link['name'] = preg_replace('/.*:/','',$link['url']);
  $link['name'] = htmlspecialchars($link['name']);

  $link['url'] = cleanID($link['url']);
  $link['title'] = $link['url'];

  //set class depending on existance
  $file = wikiFN($link['url']);
  if(@file_exists($file)){
    $link['class']="wikilink1";
  }else{
    if($conf['autoplural']){
      //try plural/nonplural
      if(substr($link['url'],-1) == 's'){
        $try = substr($link['url'],0,-1);
      }else{
        $try = $link['url'].'s';
      }
      $file = wikiFN($try);
      //check if the other form exists
      if(@file_exists($file)){
        $link['class']="wikilink1";
        $link['url'] = $try;
      }else{
        $link['class']="wikilink2";
      }
    }else{
      //no autoplural is wanted
      $link['class']="wikilink2";
    }
  }

  //construct the full link
  $link['url'] = wl($link['url']);

  //add hash if exists
  if($hash) $link['url'] .= '#'.$hash;

  return $link;
}

/**
 * format external URLs
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_externalurl($link){
  global $conf;
  //simple setup
  $link['class']  = 'urlextern';
  $link['target'] = $conf['target']['extern'];
  $link['pre']    = '';
  $link['suf']    = '';
  $link['style']  = '';
  $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
  $link['url']    = $link['url']; //keep it
  $link['title']  = htmlspecialchars($link['url']);
  if(!$link['name']) $link['name'] = htmlspecialchars($link['url']);
  //thats it :-)
  return $link;
}

/**
 * format windows share links
 *
 * this only works in IE :-(
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_windows($link){
  global $conf;
  global $lang;
  //simple setup
  $link['class']  = 'windows';
  $link['target'] = $conf['target']['windows'];
  $link['pre']    = '';
  $link['suf']   = '';
  $link['style']  = '';
  //Display error on browsers other than IE
  $link['more'] = 'onclick="if(document.all == null){alert(\''.htmlspecialchars($lang['nosmblinks'],ENT_QUOTES).'\');}" onkeypress="if(document.all == null){alert(\''.htmlspecialchars($lang['nosmblinks'],ENT_QUOTES).'\');}"';

  if(!$link['name']) $link['name'] = htmlspecialchars($link['url']);
  $link['title'] = htmlspecialchars($link['url']);
  $link['url']   = str_replace('\\','/',$link['url']);
  $link['url']   = 'file:///'.$link['url'];

  return $link;
}

/**
 * format email addresses
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_email($link){
  global $conf;
  //simple setup
  $link['class']  = 'mail';
  $link['target'] = '';
  $link['pre']    = '';
  $link['suf']   = '';
  $link['style']  = '';
  $link['more']   = '';

  $link['name']   = htmlspecialchars($link['name']);
  
  //shields up
  if($conf['mailguard']=='visible'){
    //the mail name gets some visible encoding
    $link['url'] = str_replace('@',' [at] ',$link['url']);
    $link['url'] = str_replace('.',' [dot] ',$link['url']);
    $link['url'] = str_replace('-',' [dash] ',$link['url']);
  }elseif($conf['mailguard']=='hex'){
    for ($x=0; $x < strlen($link['url']); $x++) {
      $encode .= '&#x' . bin2hex($link['url'][$x]).';';
    }
    $link['url'] = $encode;
  }
  
  $link['title'] = $link['url'];
  if(!$link['name']) $link['name'] = $link['url'];
  $link['url']   = 'mailto:'.$link['url'];

  return $link;
}

/**
 * format interwiki links
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_interwiki($link){
  global $conf;

  //obvious ones
  $link['class']  = 'interwiki';
  $link['target'] = $conf['target']['interwiki'];
  $link['pre']    = '';
  $link['suf']    = '';
  $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';

  //get interwiki short name
  list($wiki,$link['url']) = split('>',$link['url'],2);
  $wiki   = strtolower(trim($wiki)); //always use lowercase
  $link['url']   = trim($link['url']);
  if(!$link['name']) $link['name'] = $link['url'];

  //encode special chars
  $link['name']   = htmlspecialchars($link['name']);

  //set default to google
  $url = 'http://www.google.com/search?q=';
  $ico = 'google';

  //load interwikilinks
  //FIXME: loading this once may enhance speed a little bit
  $iwlinks = file('conf/interwiki.conf');

  //add special case 'this'
  $iwlinks[] = 'this '.getBaseURL(true).'{NAME}'; 
  
  //go through iwlinks and find URL for wiki
  foreach ($iwlinks as $line){
    $line = preg_replace('/#.*/','',$line); //skip comments
    $line = trim($line);
    list($iw,$iwurl) = preg_split('/\s+/',$line);
    if(!$iw or !$iwurl) continue; //skip broken or empty lines
    //check for match 
    if(strtolower($iw) == $wiki){
      $ico = $wiki;
      $url = $iwurl;
      break;
    }
  }

  //if ico exists set additonal style
  if(@file_exists('interwiki/'.$ico.'.png')){
    $link['style']='background: transparent url('.getBaseURL().'interwiki/'.$ico.'.png) 0px 1px no-repeat;';
  }elseif(@file_exists('interwiki/'.$ico.'.gif')){
    $link['style']='background: transparent url('.getBaseURL().'interwiki/'.$ico.'.gif) 0px 1px no-repeat;';
  }

  //do we stay at the same server? Use local target
  if( strpos($url,getBaseURL(true)) === 0 ){
    $link['target'] = $conf['target']['wiki'];
  }

  //replace placeholder
  if(preg_match('#\{(URL|NAME|SCHEME|HOST|PORT|PATH|QUERY)\}#',$url)){
    //use placeholders
    $url = str_replace('{URL}',urlencode($link['url']),$url);
    $url = str_replace('{NAME}',$link['url'],$url);
    $parsed = parse_url($link['url']);
    if(!$parsed['port']) $parsed['port'] = 80;
    $url = str_replace('{SCHEME}',$parsed['scheme'],$url);
    $url = str_replace('{HOST}',$parsed['host'],$url);
    $url = str_replace('{PORT}',$parsed['port'],$url);
    $url = str_replace('{PATH}',$parsed['path'],$url);
    $url = str_replace('{QUERY}',$parsed['query'],$url);
    $link['url'] = $url;
  }else{
    //default
    $link['url'] = $url.urlencode($link['url']);
  }

  $link['title'] = htmlspecialchars($link['url']);

  //done :-)
  return $link;
}

/**
 * format embedded media
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_link_media($link){
  global $conf;

  $link['class']  = 'media';
  $link['style']  = '';
  $link['pre']    = '';
  $link['suf']    = '';
  $link['more']   = 'onclick="return svchk()" onkeypress="return svchk()"';
  $class          = 'media';

  list($link['name'],$title) = split('\|',$link['name'],2);
  $t = htmlspecialchars($title);

  //set alignment from spaces
  if(substr($link['name'],0,1)==' ' && substr($link['name'],-1,1)==' '){
    $link['pre'] = "</p>\n<div align=\"center\">";
    $link['suf'] = "</div>\n<p>";
  }elseif(substr($link['name'],0,1)==' '){
    #$a = ' align="right"';
    $class = 'mediaright';
  }elseif(substr($link['name'],-1,1)==' '){
    #$a = ' align="left"';
    $class = 'medialeft';
  }else{
    $a = ' align="middle"';
  }
  $link['name'] = trim($link['name']);
  
  //split into src and parameters
  list($src,$param) = split('\?',$link['name'],2);
  //parse width and height
  if(preg_match('#(\d*)(x(\d*))?#i',$param,$size)){
    if($size[1]) $w = $size[1];
    if($size[3]) $h = $size[3];
  }

  //check for nocache param
  $nocache = preg_match('/nocache/i',$param);
  //do image caching, resizing and src rewriting
  $cache = $src;
  $isimg = img_cache($cache,$src,$w,$h,$nocache);

  //set link to src if none given 
  if(!$link['url']){
    $link['url'] = getBaseURL().$src;
    $link['target'] = $conf['target']['media'];
  }

  //prepare name
  if($isimg){
           $link['name'] = '<img src="'.$cache.'"';
    if($w) $link['name'] .= ' width="'.$w.'"';
    if($h) $link['name'] .= ' height="'.$h.'"';
    if($t) $link['name'] .= ' title="'.$t.'"';
    if($a) $link['name'] .= $a;
           $link['name'] .= ' class="'.$class.'" border="0" alt="'.$t.'" />';
  }else{
    if($t){
      $link['name'] = $t;
    }else{
      $link['name'] = basename($src);
    }
  }

  return $link;
}

/**
 * Build an URL list from a RSS feed
 *
 * Uses magpie
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function format_rss($url){
  global $lang;
  define('MAGPIE_CACHE_ON', false); //we do our own caching
  define('MAGPIE_DIR', 'inc/magpie/');
  require_once(MAGPIE_DIR.'/rss_fetch.inc');

  //disable warning while fetching
  $elvl = error_reporting(E_ERROR);
  $rss  = fetch_rss($url);
  error_reporting($elvl);

  $ret = '<ul class="rss">';
  if($rss){
    foreach ($rss->items as $item ) {
      $link         = array();
      $link['url']  = $item['link'];
      $link['name'] = $item['title'];
      $link         = format_link_externalurl($link);
      $ret         .= '<li>'.format_link_build($link).'</li>';
    }
  }else{
    $link['url']  = $url;
    $link         = format_link_externalurl($link);
    $ret         .= '<li>';
    $ret         .= '<em>'.$lang['rssfailed'].'</em>';
    $ret         .= format_link_build($link);
    $ret         .= '</li>';
  }
  $ret .= '</ul>';
  return $ret;
}

/**
 * Create cache images
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function img_cache(&$csrc,&$src,&$w,&$h,$nocache){
  global $conf;
 
  //container for various paths
  $f['full']['web'] = $src;
  $f['resz']['web'] = $src;
  $f['full']['fs']  = $src;
  $f['resz']['fs']  = $src;

  //generate cachename
  $md5 = md5($src);

  //check if it is an image
  if(preg_match('#\.(jpe?g|gif|png)$#i',$src,$match)){
    $ext   = strtolower($match[1]);
    $isimg = true;
  }

  //check if it is external or a local mediafile
  if(preg_match('#^([a-z0-9]+?)://#i',$src)){
    $isurl = true;
  }else{
    $src = str_replace(':','/',$src);
    $src = utf8_encodeFN($src);
    $f['full']['web'] = $conf['mediaweb'].'/'.$src;
    $f['resz']['web'] = $conf['mediaweb'].'/'.$src;
    $f['full']['fs']  = $conf['mediadir'].'/'.$src;
    $f['resz']['fs']  = $conf['mediadir'].'/'.$src;
  }

  //download external images if allowed
  if($isurl && $isimg && !$nocache){
    $cache = $conf['mediadir']."/.cache/$md5.$ext";
    if (@file_exists($cache) || download($src,$cache)){
      $f['full']['web'] = $conf['mediaweb']."/.cache/$md5.$ext";
      $f['resz']['web'] = $conf['mediaweb']."/.cache/$md5.$ext";
      $f['full']['fs']  = $conf['mediadir']."/.cache/$md5.$ext";
      $f['resz']['fs']  = $conf['mediadir']."/.cache/$md5.$ext";
      $isurl = false;
    }
  }

  //for local images (cached or media) do resizing
  if($isimg && (!$isurl)){
    if($w){
      $info = getImageSize($f['full']['fs']);
      //if $h not given calcualte it with correct aspect ratio
      if(!$h){
        $h = round(($w * $info[1]) / $info[0]);
      }
      $cache = $conf['mediadir'].'/.cache/'.$md5.'.'.$w.'x'.$h.'.'.$ext;
      //delete outdated cachefile
      if(@file_exists($cache) && (filemtime($cache)<filemtime($f['full']['fs']))){
        unlink($cache);
      }
      //check if a resized cachecopy exists else create one
      if(@file_exists($cache) || img_resize($ext,$f['full']['fs'],$info[0],$info[1],$cache,$w,$h)){
        $f['resz']['web'] = $conf['mediaweb'].'/.cache/'.$md5.'.'.$w.'x'.$h.'.'.$ext;
        $f['resz']['fs']  = $conf['mediadir'].'/.cache/'.$md5.'.'.$w.'x'.$h.'.'.$ext;
      }
    }else{
      //if no new size was given just return the img size
      $info = getImageSize($f['full']['fs']);
      $w = $info[0];
      $h = $info[1];
    }
    //urlencode (yes! secondtime! with force!)
    $f['full']['web'] = utf8_encodeFN($f['full']['web'],false);
    $f['resz']['web'] = utf8_encodeFN($f['resz']['web'],false);
  }

  //set srcs
  $src  = $f['full']['web'];
  $csrc = $f['resz']['web'];
  return $isimg;
}

/**
 * resize images
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function img_resize($ext,$from,$from_w,$from_h,$to,$to_w,$to_h){
  // create cachedir
  io_makeFileDir($to);

  // create an image of the given filetype
  if ($ext == 'jpg' || $ext == 'jpeg'){
    if(!function_exists("imagecreatefromjpeg")) return false;
    $image = @imagecreateFromjpeg($from);
  }elseif($ext == 'png') {
    if(!function_exists("imagecreatefrompng")) return false;
    $image = @imagecreatefrompng($from);
  }elseif($ext == 'gif') {
    if(!function_exists("imagecreatefromgif")) return false;
    $image = @imagecreatefromgif($from);
  }
  if(!$image) return false;

  if(function_exists("imagecreatetruecolor")){
    $newimg = @imagecreatetruecolor ($to_w, $to_h);
  }
  if(!$newimg) $newimg = @imagecreate($to_w, $to_h);
  if(!$newimg) return false;

  //try resampling first
  if(function_exists("imagecopyresampled")){
    if(!@imagecopyresampled($newimg, $image, 0, 0, 0, 0, $to_w, $to_h, $from_w, $from_h)) {
      imagecopyresized($newimg, $image, 0, 0, 0, 0, $to_w, $to_h, $from_w, $from_h);
    }
  }else{
    imagecopyresized($newimg, $image, 0, 0, 0, 0, $to_w, $to_h, $from_w, $from_h);
  }
  
  if ($ext == 'jpg' || $ext == 'jpeg'){
    if(!function_exists("imagejpeg")) return false;
    return imagejpeg($newimg, $to, 70);
  }elseif($ext == 'png') {
    if(!function_exists("imagepng")) return false;
    return imagepng($newimg, $to);
  }elseif($ext == 'gif') {
    if(!function_exists("imagegif")) return false;
    return imagegif($newimg, $to);
  }

  return false;
}

?>
