<?
require_once("conf/dokuwiki.php");
require_once("inc/io.php");

//set up error reporting to sane values
error_reporting(E_ALL ^ E_NOTICE);

//make session rewrites XHTML compliant
ini_set('arg_separator.output', '&amp;');

//init session
session_name("DokuWiki");
session_start();

//kill magic quotes
if (get_magic_quotes_gpc()) {
  if (!empty($_GET))    remove_magic_quotes($_GET);
  if (!empty($_POST))   remove_magic_quotes($_POST);
  if (!empty($_COOKIE)) remove_magic_quotes($_COOKIE);
  if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
  if (!empty($_SESSION)) remove_magic_quotes($_SESSION);
  ini_set('magic_quotes_gpc', 0);
}
set_magic_quotes_runtime(0);
ini_set('magic_quotes_sybase',0);

function remove_magic_quotes(&$array) {
  foreach (array_keys($array) as $key) {
    if (is_array($array[$key])) {
      remove_magic_quotes($array[$key]);
    }else {
      $array[$key] = stripslashes($array[$key]);
    }
  } 
} 

//disable gzip if not available
if($conf['usegzip'] && !function_exists('gzopen')){
  $conf['usegzip'] = 0;
}

/* ---------------------------------------------------------------------------------- */

/**
 * This returns the full absolute URL to the directory where
 * DokuWiki is installed in (includes a trailing slash)
 */
function getBaseURL($abs=false){
  global $conf;
  //if canonical url enabled always return absolute
  if($conf['canonical']) $abs = true;

  //relative URLs are easy
  if(!$abs){
    $dir = dirname($_SERVER['PHP_SELF']).'/';
    $dir = preg_replace('#//#','/',$dir);
    $dir = preg_replace('#\/$#','/',$dir); #bugfix for weird WIN behaviour
    return $dir;
  }

  $port = ':'.$_SERVER['SERVER_PORT'];
  //remove port from hostheader as sent by IE
  $host = preg_replace('/:.*$/','',$_SERVER['HTTP_HOST']);

  // see if HTTPS is enabled - apache leaves this empty when not available,
  // IIS sets it to 'off', 'false' and 'disabled' are just guessing
  if (preg_match('/^(|off|false|disabled)$/i',$_SERVER['HTTPS'])){
    $proto = 'http://';
    if ($_SERVER['SERVER_PORT'] == '80') {
      $port='';
    }
  }else{
    $proto = 'https://';
    if ($_SERVER['SERVER_PORT'] == '443') {
      $port='';
    }
  }
  $dir = (dirname($_SERVER['PHP_SELF'])).'/';
  $dir = preg_replace('#//#','/',$dir);
  $dir = preg_replace('#\/$#','/',$dir); #bugfix for weird WIN behaviour

  return $proto.$host.$port.$dir;
}

/**
 * Returns info about the current document as associative
 * array.
 */
function pageinfo(){
  global $ID;
  global $REV;
  global $USERINFO;
  global $conf;

  if($_SERVER['REMOTE_USER']){
    $info['user']     = $_SERVER['REMOTE_USER'];
    $info['userinfo'] = $USERINFO;
    $info['perm']     = auth_quickaclcheck($ID);
  }else{
    $info['user']     = '';
    $info['perm']     = auth_aclcheck($ID,'',null);
  }

  $info['namespace'] = getNS($ID);
  $info['locked']    = checklock($ID);
  $info['filepath']  = realpath(wikiFN($ID,$REV));
  $info['exists']    = @file_exists($info['filepath']);
  if($REV && !$info['exists']){
    //check if current revision was meant
    $cur = wikiFN($ID);
    if(@file_exists($cur) && (@filemtime($cur) == $REV)){
      $info['filepath'] = realpath($cur);
      $info['exists']   = true;
      $REV = '';
    }
  }
  if($info['exists']){
    $info['writable'] = (is_writable($info['filepath']) &&
                         ($info['perm'] >= AUTH_EDIT));
  }else{
    $info['writable'] = ($info['perm'] >= AUTH_CREATE);
  }
  $info['editable']  = ($info['writable'] && empty($info['lock']));
  $info['lastmod']   = @filemtime($info['filepath']);

  return $info;
}

/**
 * adds a message to the global message array
 *
 * Levels can be:
 *
 * -1 error
 *  0 info
 *  1 success
 */
function msg($message,$lvl=0){
  global $MSG;
  $errors[-1] = 'error';
  $errors[0]  = 'info';
  $errors[1]  = 'success';

  if(!isset($MSG)) $MSG = array();
  $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
}

/**
 * This builds the breadcrumbstrail and returns it as array
 */
function breadcrumbs(){
  global $ID;
  global $ACT;
  global $conf;
  $crumbs = $_SESSION[$conf['title']]['bc'];
  
  //first visit?
  if (!is_array($crumbs)){
    $crumbs = array();
  }
  //we only save on show and existing wiki documents
  if($ACT != 'show' || !@file_exists(wikiFN($ID))){
    $_SESSION[$conf['title']]['bc'] = $crumbs;
    return $crumbs;
  }
  //remove ID from array
  $pos = array_search($ID,$crumbs);
  if($pos !== false && $pos !== null){
    array_splice($crumbs,$pos,1);
  }

  //add to array
  $crumbs[] =$ID;
  //reduce size
  while(count($crumbs) > $conf['breadcrumbs']){
    array_shift($crumbs);
  }
  //save to session
  $_SESSION[$conf['title']]['bc'] = $crumbs;
  return $crumbs;
}

/**
 * This is run on a ID before it is outputted somewhere
 * currently used to replace the colon with something else
 * on Windows systems and to have proper URL encoding
 */
function idfilter($id){
  global $conf;
  if ($conf['useslash'] && $conf['userewrite']){
    $id = strtr($id,':','/');
  }elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' &&
      $conf['userewrite']) {
    $id = strtr($id,':',';');
  }
  $id = urlencode($id);
  $id = str_replace('%3A',':',$id); //keep as colon
  $id = str_replace('%2F','/',$id); //keep as slash
  return $id;
}

/**
 * This builds a link to a wikipage (using getBaseURL)
 */
function wl($id='',$more='',$script='doku.php',$canonical=false){
  global $conf;
  $more = str_replace(',','&amp;',$more);

  $id    = idfilter($id);
  $xlink = getBaseURL($canonical);

  if(!$conf['userewrite']){
    $xlink .= $script;
    $xlink .= '?id='.$id;
    if($more) $xlink .= '&amp;'.$more;
  }else{
    $xlink .= $id;
    if($more) $xlink .= '?'.$more;
  }
  
  return $xlink;
}

/**
 * Just builds a link to a script
 */
function script($script='doku.php'){
  $link = getBaseURL();
  $link .= $script;
  return $link;
}

/**
 * Return namespacepart of a wiki ID
 */
function getNS($id){
 if(strpos($id,':')!==false){
   return substr($id,0,strrpos($id,':'));
 }
 return false;
}

/**
 * Returns the id without the namespace
 */
function noNS($id){
  return preg_replace('/.*:/','',$id);
}

/**
 * Checks the wikitext against a list of blocked expressions
 * returns true if the text contains any bad words
 */
function checkwordblock(){
  global $TEXT;
  global $conf;

  if(!$conf['usewordblock']) return false;

  $blocks = file('conf/wordblock.conf');
  $re = array();
  #build regexp from blocks
  foreach($blocks as $block){
    $block = preg_replace('/#.*$/','',$block);
    $block = trim($block);
    if(empty($block)) continue;
    $re[]  = $block;
  }
  if(preg_match('#('.join('|',$re).')#si',$TEXT)) return true;
  return false;
}

/**
 * Returns the IP of the client including X-Forwarded-For
 * Proxy Headers
 */
function clientIP(){
  $my = $_SERVER['REMOTE_ADDR'];
  if($_SERVER['HTTP_X_FORWARDED_FOR']){
    $my .= ' ('.$_SERVER['HTTP_X_FORWARDED_FOR'].')';
  }
  return $my;
}

/**
 * Checks if a given page is currently locked by anyone for editing.
 * removes stale lockfiles
 */
function checklock($id){
  global $conf;
  $lock = wikiFN($id).'.lock';
  
  //no lockfile
  if(!@file_exists($lock)) return false;
  
  //lockfile expired
  if((time() - filemtime($lock)) > $conf['locktime']){
    unlink($lock);
    return false;
  }
  
  //my own lock
  $ip = io_readFile($lock);
  if( ($ip == clientIP()) || ($ip == $_SERVER['REMOTE_USER']) ){
    return false;
  }
  
  return $ip;
}

/**
 * Locks a page for editing
 */
function lock($id){
  $lock = wikiFN($id).'.lock';
  if($_SERVER['REMOTE_USER']){
    io_saveFile($lock,$_SERVER['REMOTE_USER']);
  }else{
    io_saveFile($lock,clientIP());
  }
}

/**
 * Unlocks a page if it was locked by the user
 *
 * return true if a lock was removed
 */
function unlock($id){
  $lock = wikiFN($id).'.lock';
  if(@file_exists($lock)){
    $ip = io_readFile($lock);
    if( ($ip == clientIP()) || ($ip == $_SERVER['REMOTE_USER']) ){
      @unlink($lock);
      return true;
    }
  }
  return false;
}

/**
 * Cleans a given ID to only use allowed characters. Accented characters are
 * converted to unaccented ones
 */
function cleanID($id){
  global $conf;
  global $lang;
  $id = trim($id);
  $id = strtolower($id);

  //alternative namespace seperator
  $id = strtr($id,';',':');
  $id = strtr($id,'/',':');

  if(!$conf['localnames']){
    if($lang['encoding'] == 'iso-8859-15'){
      // replace accented chars with unaccented ones
      // this may look strange on your terminal - just don't touch
      $id = strtr(
      strtr($id,
       'ŠŽšžŸÀÁÂÃÅÇÈÉÊËÌÍÎÏÑÒÓÔÕØÙÚÛÝàáâãåçèéêëìíîïñòóôõøùúûýÿ',
       'szszyaaaaaceeeeiiiinooooouuuyaaaaaceeeeiiiinooooouuuyy'),
       array('Þ' => 'th', 'þ' => 'th', 'Ð' => 'dh', 'ð' => 'dh', 'ß' => 'ss',
             'Œ' => 'oe', 'œ' => 'oe', 'Æ' => 'ae', 'æ' => 'ae', 'µ' => 'u',
             'ü' => 'ue', 'ö' => 'oe', 'ä' => 'ae', 'Ü' => 'ue', 'Ö' => 'ö',
             'Ä' => 'ae'));
    }
    $WORD = 'a-z';
  }else{
    $WORD = '\w';
  }

  //special chars left will be converted to _
  $id = preg_replace('#[^'.$WORD.'0-9:\-\.]#','_',$id);
  $id = preg_replace('#__#','_',$id);
  $id = preg_replace('#:+#',':',$id);
  $id = trim($id,':._-');
  $id = preg_replace('#:[:\._\-]+#',':',$id);

  return($id);
}

/**
 * returns the full path to the datafile specified by ID and
 * optional revision
 */
function wikiFN($id,$rev=''){
  global $conf;
  $id = cleanID($id);
  $id = str_replace(':','/',$id);
  if(empty($rev)){
    return $conf['datadir'].'/'.$id.'.txt';
  }else{
    $fn = $conf['olddir'].'/'.$id.'.'.$rev.'.txt';
    if(!$conf['usegzip'] || @file_exists($fn)){
      //return plaintext if exists or gzip is disabled
      return $fn;
    }else{
      return $fn.'.gz';
    }
  }
}

/**
 * Returns the full filepath to a localized textfile if local
 * version isn't found the english one is returned
 */
function localeFN($id){
  global $conf;
  $file = './lang/'.$conf['lang'].'/'.$id.'.txt';
  if(!@file_exists($file)){
    //fall back to english
    $file = './lang/en/'.$id.'.txt';
  }
  return cleanText($file);
}

/**
 * convert line ending to unix format
 *
 * @see: formText() for 2crlf conversion
 */
function cleanText($text){
  $text = preg_replace("/(\015\012)|(\015)/","\012",$text);
  return $text;
}

/**
 * Prepares text for print in Webforms by encoding special chars.
 * It also converts line endings to Windows format which is
 * pseudo standard for webforms. 
 *
 * @see: cleanText() for 2unix conversion
 */
function formText($text){
  $text = preg_replace("/\012/","\015\012",$text);
  return htmlspecialchars($text);
}

/**
 * Returns the specified textfile in parsed format
 */
function parsedLocale($id){
  //disable section editing
  global $parser;
  $se = $parser['secedit'];
  $parser['secedit'] = false;
  //fetch parsed locale
  $html = io_cacheParse(localeFN($id));
  //reset section editing
  $parser['secedit'] = $se;
  return $html;
}

/**
 * Returns the specified textfile in parsed format
 */
function rawLocale($id){
  return io_readFile(localeFN($id));
}


/**
 * Returns the parsed Wikitext for the given id and revision. If $excuse
 * is true an explanation is returned if the file wasn't found
 */
function parsedWiki($id,$rev='',$excuse=true){
  $file = wikiFN($id,$rev);
  $ret  = '';
  
  //ensure $id is in global $ID (needed for parsing)
  global $ID;
  $ID = $id;
  
  if($rev){
    if(@file_exists($file)){
      $ret = parse(io_readFile($file));
    }elseif($excuse){
      $ret = parsedLocale('norev');
    }
  }else{
    if(@file_exists($file)){
      $ret = io_cacheParse($file);
    }elseif($excuse){
      $ret = parsedLocale('newpage');
    }
  }
  return $ret;
}

/**
 * Returns the raw WikiText
 */
function rawWiki($id,$rev=''){
  return io_readFile(wikiFN($id,$rev));
}

/**
 * Returns the raw Wiki Text in three slices. The range parameter
 * Need to have the form "from-to" and gives the range of the section.
 * The returned order is prefix, section and suffix.
 */
function rawWikiSlices($range,$id,$rev=''){
  list($from,$to) = split('-',$range,2);
  $text = io_readFile(wikiFN($id,$rev));
  $text = split("\n",$text);
  if(!$from) $from = 0;
  if(!$to)   $to   = count($text);

  $slices[0] = join("\n",array_slice($text,0,$from));
  $slices[1] = join("\n",array_slice($text,$from,$to + 1  - $from));
  $slices[2] = join("\n",array_slice($text,$to+1));

  return $slices;
}

/**
 * function to join the text slices with correct lineendings again.
 * When the pretty parameter is set to true it adds additional empty
 * lines between sections if needed (used on saving).
 */
function con($pre,$text,$suf,$pretty=false){

  if($pretty){
    if($pre && substr($pre,-1) != "\n") $pre .= "\n";
    if($suf && substr($text,-1) != "\n") $text .= "\n";
  }

  if($pre) $pre .= "\n";
  if($suf) $text .= "\n";
  return $pre.$text.$suf;
}

/**
 * little function to print the content of a var
 */
function dbg($msg,$hidden=false){
  (!$hidden) ? print '<pre class="dbg">' : print "<!--\n";
  print_r($msg);
  (!$hidden) ? print '</pre>' : print "\n-->";
}

/**
 * Add's an entry to the changelog
 */
function addLogEntry($id,$summary=""){
  global $conf;
  $id     = cleanID($id);
  $date   = time();
  $remote = $_SERVER['REMOTE_ADDR'];
  $user   = $_SERVER['REMOTE_USER'];

  $logline = join("\t",array($date,$remote,$id,$user,$summary))."\n";

  $fh = fopen($conf['changelog'],'a');
  if($fh){
    fwrite($fh,$logline);
    fclose($fh);
  }
}

/**
 * returns an array of recently changed files using the
 * changelog
 */
function getRecents($num=0,$incdel=false){
  global $conf;
  $recent = array();
  if(!$num) $num = $conf['recent'];

  $loglines = file($conf['changelog']);
  rsort($loglines); //reverse sort on timestamp

  foreach ($loglines as $line){
    $line = rtrim($line);        //remove newline
    if(empty($line)) continue;   //skip empty lines
    $info = split("\t",$line);   //split into parts
    //add id if not in yet and file still exists and is allowed to read
    if(!$recent[$info[2]] && 
       (@file_exists(wikiFN($info[2])) || $incdel) &&
       (auth_quickaclcheck($info[2]) >= AUTH_READ)
      ){
      $recent[$info[2]]['date'] = $info[0];
      $recent[$info[2]]['ip']   = $info[1];
      $recent[$info[2]]['user'] = $info[3];
      $recent[$info[2]]['sum']  = $info[4];
      $recent[$info[2]]['del']  = !@file_exists(wikiFN($info[2]));
    }
    if(count($recent) >= $num){
      break; //finish if enough items found
    }
  }
  return $recent;
}

/**
 * Saves a wikitext by calling io_saveFile
 */
function saveWikiText($id,$text,$summary){
  global $conf;
  global $lang;
  umask($conf['umask']);
  // ignore if no changes were made
  if($text == rawWiki($id,'')){
    return;
  }

  $file = wikiFN($id);
  $old  = saveOldRevision($id);

  if (empty($text)){
    // remove empty files
    @unlink($file);
    $del = true;
    $summary = $lang['deleted']; //autoset summary on deletion
  }else{
    // save file (datadir is created in io_saveFile)
    io_saveFile($file,$text);
    $del = false;
  }

  addLogEntry($id,$summary);
  notify($id,$old,$summary);
  
  //purge cache on add by updating the purgefile
  if($conf['purgeonadd'] && (!$old || $del)){
    io_saveFile($conf['datadir'].'/.cache/purgefile',time());
  }
}

/**
 * moves the current version to the attic and returns its
 * revision date
 */
function saveOldRevision($id){
	global $conf;
  umask($conf['umask']);
  $oldf = wikiFN($id);
  if(!@file_exists($oldf)) return '';
  $date = filemtime($oldf);
  $newf = wikiFN($id,$date);
  if(substr($newf,-3)=='.gz'){
    io_saveFile($newf,rawWiki($id));
  }else{
    io_makeFileDir($newf);
    copy($oldf, $newf);
  }
  return $date;
}

/**
 * Sends a notify mail to the wikiadmin when a page was
 * changed
 */
function notify($id,$rev="",$summary=""){
  global $lang;
  global $conf;
  $hdrs ='';
  if(empty($conf['notify'])) return; //notify enabled?
  
  $text = rawLocale('mailtext');
  $text = str_replace('@DATE@',date($conf['dformat']),$text);
  $text = str_replace('@BROWSER@',$_SERVER['HTTP_USER_AGENT'],$text);
  $text = str_replace('@IPADDRESS@',$_SERVER['REMOTE_ADDR'],$text);
  $text = str_replace('@HOSTNAME@',gethostbyaddr($_SERVER['REMOTE_ADDR']),$text);
  $text = str_replace('@NEWPAGE@',wl($id,'','',true),$text);
  $text = str_replace('@DOKUWIKIURL@',getBaseURL(true),$text);
  $text = str_replace('@SUMMARY@',$summary,$text);
  
  if($rev){
    $subject = $lang['mail_changed'].' '.$id;
    $text = str_replace('@OLDPAGE@',wl($id,"rev=$rev",'',true),$text);
    require_once("inc/DifferenceEngine.php");
    $df  = new Diff(split("\n",rawWiki($id,$rev)),
                    split("\n",rawWiki($id)));
    $dformat = new UnifiedDiffFormatter();
    $diff    = $dformat->format($df);
  }else{
    $subject=$lang['mail_newpage'].' '.$id;
    $text = str_replace('@OLDPAGE@','none',$text);
    $diff = rawWiki($id);
  }
  $text = str_replace('@DIFF@',$diff,$text);

  if (!empty($conf['mailfrom'])) {
    $hdrs = 'From: '.$conf['mailfrom']."\n";
  }
  @mail($conf['notify'],$subject,$text,$hdrs);
}

function getRevisions($id){
  $revd = dirname(wikiFN($id,'foo'));
  $revs = array();
  $clid = cleanID($id);
  if(strrpos($clid,':')) $clid = substr($clid,strrpos($clid,':')+1); //remove path

  if (is_dir($revd) && $dh = opendir($revd)) {
    while (($file = readdir($dh)) !== false) {
      if (is_dir($revd.'/'.$file)) continue;
      if (preg_match('/^'.$clid.'\.(\d+)\.txt(\.gz)?$/',$file,$match)){
        $revs[]=$match[1];
      }
    }
    closedir($dh);
  }
  rsort($revs);
  return $revs;
}

/**
 * downloads a file from the net and saves it to the given location
 */
function download($url,$file){
  $fp = @fopen($url,"rb");
  if(!$fp) return false;

  while(!feof($fp)){
    $cont.= fread($fp,1024);
  }
  fclose($fp);

  $fp2 = @fopen($file,"w");
  if(!$fp2) return false;
  fwrite($fp2,$cont);
  fclose($fp2);
  return true;
} 

/**
 * extracts the query from a google referer
 */
function getGoogleQuery(){
  $url = parse_url($_SERVER['HTTP_REFERER']);

  if(!preg_match("#google\.#i",$url['host'])) return '';
  $query = array();
  parse_str($url['query'],$query);

  return $query['q'];
}

/**
 * This function tries the locales given in the
 * language file
 */
function setCorrectLocale(){
  global $conf;
  global $lang;

  $enc = strtoupper($lang['encoding']);
  foreach ($lang['locales'] as $loc){
    //try locale
    if(@setlocale(LC_ALL,$loc)) return;
    //try loceale with encoding
    if(@setlocale(LC_ALL,"$loc.$enc")) return;
  }
  //still here? try to set from environment
  @setlocale(LC_ALL,"");
}

/**
* Return the human readable size of a file
*
* @param       int    $size   A file size
* @param       int    $dec    A number of decimal places
* @author      Martin Benjamin <b.martin@cybernet.ch>
* @author      Aidan Lister <aidan@php.net>
* @version     1.0.0
*/
function filesize_h($size, $dec = 1)
{
  $sizes = array('B', 'KB', 'MB', 'GB');
  $count = count($sizes);
  $i = 0;
    
  while ($size >= 1024 && ($i < $count - 1)) {
    $size /= 1024;
    $i++;
  }

  return round($size, $dec) . ' ' . $sizes[$i];
}

function check(){
  global $conf;
  global $INFO;

  if(is_writable($conf['changelog'])){
    msg('Changelog is writable',1);
  }else{
    msg('Changelog is not writable',-1);
  }

  if(is_writable($conf['datadir'])){
    msg('Datadir is writable',1);
  }else{
    msg('Datadir is not writable',-1);
  }

  if(is_writable($conf['olddir'])){
    msg('Attic is writable',1);
  }else{
    msg('Attic is not writable',-1);
  }

  if(is_writable($conf['mediadir'])){
    msg('Mediadir is writable',1);
  }else{
    msg('Mediadir is not writable',-1);
  }

  if(is_writable('conf/users.auth')){
    msg('conf/users.auth is writable',1);
  }else{
    msg('conf/users.auth is not writable',0);
  }
 
  msg('Your current permission for this page is '.$INFO['perm'],0);

  if(is_writable($INFO['filepath'])){
    msg('The current page is writable by the webserver',0);
  }else{
    msg('The current page is not writable by the webserver',0);
  }

  if($INFO['writable']){
    msg('The current page is writable by you',0);
  }else{
    msg('The current page is not writable you',0);
  }
}
?>
