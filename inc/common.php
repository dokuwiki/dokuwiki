<?php
/**
 * Common DokuWiki functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_CONF.'dokuwiki.php');
require_once(DOKU_INC.'inc/io.php');
require_once(DOKU_INC.'inc/changelog.php');
require_once(DOKU_INC.'inc/utf8.php');
require_once(DOKU_INC.'inc/mail.php');
require_once(DOKU_INC.'inc/parserutils.php');
require_once(DOKU_INC.'inc/infoutils.php');

/**
 * These constants are used with the recents function
 */
define('RECENTS_SKIP_DELETED',2);
define('RECENTS_SKIP_MINORS',4);
define('RECENTS_SKIP_SUBSPACES',8);

/**
 * Wrapper around htmlspecialchars()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    htmlspecialchars()
 */
function hsc($string){
  return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * print a newline terminated string
 *
 * You can give an indention as optional parameter
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ptln($string,$intend=0){
  echo str_repeat(' ', $intend)."$string\n";
}

/**
 * strips control characters (<32) from the given string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function stripctl($string){
  return preg_replace('/[\x00-\x1F]+/s','',$string);
}

/**
 * Return info about the current document as associative
 * array.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pageinfo(){
  global $ID;
  global $REV;
  global $USERINFO;
  global $conf;

  // include ID & REV not redundant, as some parts of DokuWiki may temporarily change $ID, e.g. p_wiki_xhtml
  // FIXME ... perhaps it would be better to ensure the temporary changes weren't necessary
  $info['id'] = $ID;
  $info['rev'] = $REV;

  if($_SERVER['REMOTE_USER']){
    $info['userinfo']   = $USERINFO;
    $info['perm']       = auth_quickaclcheck($ID);
    $info['subscribed'] = is_subscribed($ID,$_SERVER['REMOTE_USER']);
    $info['client']     = $_SERVER['REMOTE_USER'];

    // set info about manager/admin status
    $info['isadmin']   = false;
    $info['ismanager'] = false;
    if($info['perm'] == AUTH_ADMIN){
      $info['isadmin']   = true;
      $info['ismanager'] = true;
    }elseif(auth_ismanager()){
      $info['ismanager'] = true;
    }

    // if some outside auth were used only REMOTE_USER is set
    if(!$info['userinfo']['name']){
      $info['userinfo']['name'] = $_SERVER['REMOTE_USER'];
    }

  }else{
    $info['perm']       = auth_aclcheck($ID,'',null);
    $info['subscribed'] = false;
    $info['client']     = clientIP(true);
  }

  $info['namespace'] = getNS($ID);
  $info['locked']    = checklock($ID);
  $info['filepath']  = realpath(wikiFN($ID));
  $info['exists']    = @file_exists($info['filepath']);
  if($REV){
    //check if current revision was meant
    if($info['exists'] && (@filemtime($info['filepath'])==$REV)){
      $REV = '';
    }else{
      //really use old revision
      $info['filepath'] = realpath(wikiFN($ID,$REV));
      $info['exists']   = @file_exists($info['filepath']);
    }
  }
  $info['rev'] = $REV;
  if($info['exists']){
    $info['writable'] = (is_writable($info['filepath']) &&
                         ($info['perm'] >= AUTH_EDIT));
  }else{
    $info['writable'] = ($info['perm'] >= AUTH_CREATE);
  }
  $info['editable']  = ($info['writable'] && empty($info['lock']));
  $info['lastmod']   = @filemtime($info['filepath']);

  //load page meta data
  $info['meta'] = p_get_metadata($ID);

  //who's the editor
  if($REV){
    $revinfo = getRevisionInfo($ID, $REV, 1024);
  }else{
    if (is_array($info['meta']['last_change'])) {
       $revinfo = $info['meta']['last_change'];
    } else {
      $revinfo = getRevisionInfo($ID, $info['lastmod'], 1024);
      // cache most recent changelog line in metadata if missing and still valid
      if ($revinfo!==false) {
        $info['meta']['last_change'] = $revinfo;
        p_set_metadata($ID, array('last_change' => $revinfo));
      }
    }
  }
  //and check for an external edit
  if($revinfo!==false && $revinfo['date']!=$info['lastmod']){
    // cached changelog line no longer valid
    $revinfo = false;
    $info['meta']['last_change'] = $revinfo;
    p_set_metadata($ID, array('last_change' => $revinfo));
  }

  $info['ip']     = $revinfo['ip'];
  $info['user']   = $revinfo['user'];
  $info['sum']    = $revinfo['sum'];
  // See also $INFO['meta']['last_change'] which is the most recent log line for page $ID.
  // Use $INFO['meta']['last_change']['type']===DOKU_CHANGE_TYPE_MINOR_EDIT in place of $info['minor'].

  if($revinfo['user']){
    $info['editor'] = $revinfo['user'];
  }else{
    $info['editor'] = $revinfo['ip'];
  }

  // draft
  $draft = getCacheName($info['client'].$ID,'.draft');
  if(@file_exists($draft)){
    if(@filemtime($draft) < @filemtime(wikiFN($ID))){
      // remove stale draft
      @unlink($draft);
    }else{
      $info['draft'] = $draft;
    }
  }

  return $info;
}

/**
 * Build an string of URL parameters
 *
 * @author Andreas Gohr
 */
function buildURLparams($params, $sep='&amp;'){
  $url = '';
  $amp = false;
  foreach($params as $key => $val){
    if($amp) $url .= $sep;

    $url .= $key.'=';
    $url .= rawurlencode($val);
    $amp = true;
  }
  return $url;
}

/**
 * Build an string of html tag attributes
 *
 * Skips keys starting with '_', values get HTML encoded
 *
 * @author Andreas Gohr
 */
function buildAttributes($params){
  $url = '';
  foreach($params as $key => $val){
    if($key{0} == '_') continue;

    $url .= $key.'="';
    $url .= htmlspecialchars ($val);
    $url .= '" ';
  }
  return $url;
}


/**
 * This builds the breadcrumb trail and returns it as array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function breadcrumbs(){
  // we prepare the breadcrumbs early for quick session closing
  static $crumbs = null;
  if($crumbs != null) return $crumbs;

  global $ID;
  global $ACT;
  global $conf;
  $crumbs = $_SESSION[DOKU_COOKIE]['bc'];

  //first visit?
  if (!is_array($crumbs)){
    $crumbs = array();
  }
  //we only save on show and existing wiki documents
  $file = wikiFN($ID);
  if($ACT != 'show' || !@file_exists($file)){
    $_SESSION[DOKU_COOKIE]['bc'] = $crumbs;
    return $crumbs;
  }

  // page names
  $name = noNSorNS($ID);
  if ($conf['useheading']) {
    // get page title
    $title = p_get_first_heading($ID);
    if ($title) {
      $name = $title;
    }
  }

  //remove ID from array
  if (isset($crumbs[$ID])) {
    unset($crumbs[$ID]);
  }

  //add to array
  $crumbs[$ID] = $name;
  //reduce size
  while(count($crumbs) > $conf['breadcrumbs']){
    array_shift($crumbs);
  }
  //save to session
  $_SESSION[DOKU_COOKIE]['bc'] = $crumbs;
  return $crumbs;
}

/**
 * Filter for page IDs
 *
 * This is run on a ID before it is outputted somewhere
 * currently used to replace the colon with something else
 * on Windows systems and to have proper URL encoding
 *
 * Urlencoding is ommitted when the second parameter is false
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idfilter($id,$ue=true){
  global $conf;
  if ($conf['useslash'] && $conf['userewrite']){
    $id = strtr($id,':','/');
  }elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' &&
      $conf['userewrite']) {
    $id = strtr($id,':',';');
  }
  if($ue){
    $id = rawurlencode($id);
    $id = str_replace('%3A',':',$id); //keep as colon
    $id = str_replace('%2F','/',$id); //keep as slash
  }
  return $id;
}

/**
 * This builds a link to a wikipage
 *
 * It handles URL rewriting and adds additional parameter if
 * given in $more
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function wl($id='',$more='',$abs=false,$sep='&amp;'){
  global $conf;
  if(is_array($more)){
    $more = buildURLparams($more,$sep);
  }else{
    $more = str_replace(',',$sep,$more);
  }

  $id    = idfilter($id);
  if($abs){
    $xlink = DOKU_URL;
  }else{
    $xlink = DOKU_BASE;
  }

  if($conf['userewrite'] == 2){
    $xlink .= DOKU_SCRIPT.'/'.$id;
    if($more) $xlink .= '?'.$more;
  }elseif($conf['userewrite']){
    $xlink .= $id;
    if($more) $xlink .= '?'.$more;
  }else{
    $xlink .= DOKU_SCRIPT.'?id='.$id;
    if($more) $xlink .= $sep.$more;
  }

  return $xlink;
}

/**
 * This builds a link to an alternate page format
 *
 * Handles URL rewriting if enabled. Follows the style of wl().
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function exportlink($id='',$format='raw',$more='',$abs=false,$sep='&amp;'){
  global $conf;
  if(is_array($more)){
    $more = buildURLparams($more,$sep);
  }else{
    $more = str_replace(',',$sep,$more);
  }

  $format = rawurlencode($format);
  $id = idfilter($id);
  if($abs){
    $xlink = DOKU_URL;
  }else{
    $xlink = DOKU_BASE;
  }

  if($conf['userewrite'] == 2){
    $xlink .= DOKU_SCRIPT.'/'.$id.'?do=export_'.$format;
    if($more) $xlink .= $sep.$more;
  }elseif($conf['userewrite'] == 1){
    $xlink .= '_export/'.$format.'/'.$id;
    if($more) $xlink .= '?'.$more;
  }else{
    $xlink .= DOKU_SCRIPT.'?do=export_'.$format.$sep.'id='.$id;
    if($more) $xlink .= $sep.$more;
  }

  return $xlink;
}

/**
 * Build a link to a media file
 *
 * Will return a link to the detail page if $direct is false
 */
function ml($id='',$more='',$direct=true,$sep='&amp;',$abs=false){
  global $conf;
  if(is_array($more)){
    $more = buildURLparams($more,$sep);
  }else{
    $more = str_replace(',',$sep,$more);
  }

  if($abs){
    $xlink = DOKU_URL;
  }else{
    $xlink = DOKU_BASE;
  }

  // external URLs are always direct without rewriting
  if(preg_match('#^(https?|ftp)://#i',$id)){
    $xlink .= 'lib/exe/fetch.php';
    if($more){
      $xlink .= '?'.$more;
      $xlink .= $sep.'media='.rawurlencode($id);
    }else{
      $xlink .= '?media='.rawurlencode($id);
    }
    return $xlink;
  }

  $id = idfilter($id);

  // decide on scriptname
  if($direct){
    if($conf['userewrite'] == 1){
      $script = '_media';
    }else{
      $script = 'lib/exe/fetch.php';
    }
  }else{
    if($conf['userewrite'] == 1){
      $script = '_detail';
    }else{
      $script = 'lib/exe/detail.php';
    }
  }

  // build URL based on rewrite mode
   if($conf['userewrite']){
     $xlink .= $script.'/'.$id;
     if($more) $xlink .= '?'.$more;
   }else{
     if($more){
       $xlink .= $script.'?'.$more;
       $xlink .= $sep.'media='.$id;
     }else{
       $xlink .= $script.'?media='.$id;
     }
   }

  return $xlink;
}



/**
 * Just builds a link to a script
 *
 * @todo   maybe obsolete
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function script($script='doku.php'){
#  $link = getBaseURL();
#  $link .= $script;
#  return $link;
  return DOKU_BASE.DOKU_SCRIPT;
}

/**
 * Spamcheck against wordlist
 *
 * Checks the wikitext against a list of blocked expressions
 * returns true if the text contains any bad words
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function checkwordblock(){
  global $TEXT;
  global $conf;

  if(!$conf['usewordblock']) return false;

  // we prepare the text a tiny bit to prevent spammers circumventing URL checks
  $text = preg_replace('!(\b)(www\.[\w.:?\-;,]+?\.[\w.:?\-;,]+?[\w/\#~:.?+=&%@\!\-.:?\-;,]+?)([.:?\-;,]*[^\w/\#~:.?+=&%@\!\-.:?\-;,])!i','\1http://\2 \2\3',$TEXT);

  $wordblocks = getWordblocks();
  //how many lines to read at once (to work around some PCRE limits)
  if(version_compare(phpversion(),'4.3.0','<')){
    //old versions of PCRE define a maximum of parenthesises even if no
    //backreferences are used - the maximum is 99
    //this is very bad performancewise and may even be too high still
    $chunksize = 40;
  }else{
    //read file in chunks of 200 - this should work around the
    //MAX_PATTERN_SIZE in modern PCRE
    $chunksize = 200;
  }
  while($blocks = array_splice($wordblocks,0,$chunksize)){
    $re = array();
    #build regexp from blocks
    foreach($blocks as $block){
      $block = preg_replace('/#.*$/','',$block);
      $block = trim($block);
      if(empty($block)) continue;
      $re[]  = $block;
    }
    if(preg_match('#('.join('|',$re).')#si',$text)) {
      return true;
    }
  }
  return false;
}

/**
 * Return the IP of the client
 *
 * Honours X-Forwarded-For and X-Real-IP Proxy Headers
 *
 * It returns a comma separated list of IPs if the above mentioned
 * headers are set. If the single parameter is set, it tries to return
 * a routable public address, prefering the ones suplied in the X
 * headers
 *
 * @param  boolean $single If set only a single IP is returned
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function clientIP($single=false){
  $ip = array();
  $ip[] = $_SERVER['REMOTE_ADDR'];
  if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    $ip = array_merge($ip,explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']));
  if(!empty($_SERVER['HTTP_X_REAL_IP']))
    $ip = array_merge($ip,explode(',',$_SERVER['HTTP_X_REAL_IP']));

  // remove any non-IP stuff
  $cnt = count($ip);
  $match = array();
  for($i=0; $i<$cnt; $i++){
    if(preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$ip[$i],$match)) {
      $ip[$i] = $match[0];
    } else {
      $ip[$i] = '';
    }
    if(empty($ip[$i])) unset($ip[$i]);
  }
  $ip = array_values(array_unique($ip));
  if(!$ip[0]) $ip[0] = '0.0.0.0'; // for some strange reason we don't have a IP

  if(!$single) return join(',',$ip);

  // decide which IP to use, trying to avoid local addresses
  $ip = array_reverse($ip);
  foreach($ip as $i){
    if(preg_match('/^(127\.|10\.|192\.168\.|172\.((1[6-9])|(2[0-9])|(3[0-1]))\.)/',$i)){
      continue;
    }else{
      return $i;
    }
  }
  // still here? just use the first (last) address
  return $ip[0];
}

/**
 * Checks if a given page is currently locked.
 *
 * removes stale lockfiles
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function checklock($id){
  global $conf;
  $lock = wikiLockFN($id);

  //no lockfile
  if(!@file_exists($lock)) return false;

  //lockfile expired
  if((time() - filemtime($lock)) > $conf['locktime']){
    @unlink($lock);
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
 * Lock a page for editing
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function lock($id){
  $lock = wikiLockFN($id);
  if($_SERVER['REMOTE_USER']){
    io_saveFile($lock,$_SERVER['REMOTE_USER']);
  }else{
    io_saveFile($lock,clientIP());
  }
}

/**
 * Unlock a page if it was locked by the user
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @return bool true if a lock was removed
 */
function unlock($id){
  $lock = wikiLockFN($id);
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
 * convert line ending to unix format
 *
 * @see    formText() for 2crlf conversion
 * @author Andreas Gohr <andi@splitbrain.org>
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
 * @see    cleanText() for 2unix conversion
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function formText($text){
  $text = str_replace("\012","\015\012",$text);
  return htmlspecialchars($text);
}

/**
 * Returns the specified local text in raw format
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rawLocale($id){
  return io_readFile(localeFN($id));
}

/**
 * Returns the raw WikiText
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rawWiki($id,$rev=''){
  return io_readWikiPage(wikiFN($id, $rev), $id, $rev);
}

/**
 * Returns the pagetemplate contents for the ID's namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pageTemplate($data){
  $id = $data[0];
  global $conf;
  global $INFO;
  $tpl = io_readFile(dirname(wikiFN($id)).'/_template.txt');
  $tpl = str_replace('@ID@',$id,$tpl);
  $tpl = str_replace('@NS@',getNS($id),$tpl);
  $tpl = str_replace('@PAGE@',strtr(noNS($id),'_',' '),$tpl);
  $tpl = str_replace('@USER@',$_SERVER['REMOTE_USER'],$tpl);
  $tpl = str_replace('@NAME@',$INFO['userinfo']['name'],$tpl);
  $tpl = str_replace('@MAIL@',$INFO['userinfo']['mail'],$tpl);
  $tpl = str_replace('@DATE@',date($conf['dformat']),$tpl);
  return $tpl;
}


/**
 * Returns the raw Wiki Text in three slices.
 *
 * The range parameter needs to have the form "from-to"
 * and gives the range of the section in bytes - no
 * UTF-8 awareness is needed.
 * The returned order is prefix, section and suffix.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rawWikiSlices($range,$id,$rev=''){
  list($from,$to) = split('-',$range,2);
  $text = io_readWikiPage(wikiFN($id, $rev), $id, $rev);
  if(!$from) $from = 0;
  if(!$to)   $to   = strlen($text)+1;

  $slices[0] = substr($text,0,$from-1);
  $slices[1] = substr($text,$from-1,$to-$from);
  $slices[2] = substr($text,$to);

  return $slices;
}

/**
 * Joins wiki text slices
 *
 * function to join the text slices with correct lineendings again.
 * When the pretty parameter is set to true it adds additional empty
 * lines between sections if needed (used on saving).
 *
 * @author Andreas Gohr <andi@splitbrain.org>
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
 * Saves a wikitext by calling io_writeWikiPage.
 * Also directs changelog and attic updates.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function saveWikiText($id,$text,$summary,$minor=false){
  /* Note to developers:
     This code is subtle and delicate. Test the behavior of
     the attic and changelog with dokuwiki and external edits
     after any changes. External edits change the wiki page
     directly without using php or dokuwiki.
  */
  global $conf;
  global $lang;
  global $REV;
  // ignore if no changes were made
  if($text == rawWiki($id,'')){
    return;
  }

  $file = wikiFN($id);
  $old = @filemtime($file); // from page
  $wasRemoved = empty($text);
  $wasCreated = !@file_exists($file);
  $wasReverted = ($REV==true);
  $newRev = false;
  $oldRev = getRevisions($id, -1, 1, 1024); // from changelog
  $oldRev = (int)(empty($oldRev)?0:$oldRev[0]);
  if(!@file_exists(wikiFN($id, $old)) && @file_exists($file) && $old>=$oldRev) {
    // add old revision to the attic if missing
    saveOldRevision($id);
    // add a changelog entry if this edit came from outside dokuwiki
    if ($old>$oldRev) {
      addLogEntry($old, $id, DOKU_CHANGE_TYPE_EDIT, $lang['external_edit'], '', array('ExternalEdit'=>true));
      // remove soon to be stale instructions
      $cache = new cache_instructions($id, $file);
      $cache->removeCache();
    }
  }

  if ($wasRemoved){
    // pre-save deleted revision
    @touch($file);
    clearstatcache();
    $newRev = saveOldRevision($id);
    // remove empty file
    @unlink($file);
    // remove old meta info...
    $mfiles = metaFiles($id);
    $changelog = metaFN($id, '.changes');
    foreach ($mfiles as $mfile) {
      // but keep per-page changelog to preserve page history
      if (@file_exists($mfile) && $mfile!==$changelog) { @unlink($mfile); }
    }
    $del = true;
    // autoset summary on deletion
    if(empty($summary)) $summary = $lang['deleted'];
    // remove empty namespaces
    io_sweepNS($id, 'datadir');
    io_sweepNS($id, 'mediadir');
  }else{
    // save file (namespace dir is created in io_writeWikiPage)
    io_writeWikiPage($file, $text, $id);
    // pre-save the revision, to keep the attic in sync
    $newRev = saveOldRevision($id);
    $del = false;
  }

  // select changelog line type
  $extra = '';
  $type = DOKU_CHANGE_TYPE_EDIT;
  if ($wasReverted) {
    $type = DOKU_CHANGE_TYPE_REVERT;
    $extra = $REV;
  }
  else if ($wasCreated) { $type = DOKU_CHANGE_TYPE_CREATE; }
  else if ($wasRemoved) { $type = DOKU_CHANGE_TYPE_DELETE; }
  else if ($minor && $conf['useacl'] && $_SERVER['REMOTE_USER']) { $type = DOKU_CHANGE_TYPE_MINOR_EDIT; } //minor edits only for logged in users

  addLogEntry($newRev, $id, $type, $summary, $extra);
  // send notify mails
  notify($id,'admin',$old,$summary,$minor);
  notify($id,'subscribers',$old,$summary,$minor);

  // update the purgefile (timestamp of the last time anything within the wiki was changed)
  io_saveFile($conf['cachedir'].'/purgefile',time());
}

/**
 * moves the current version to the attic and returns its
 * revision date
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function saveOldRevision($id){
  global $conf;
  $oldf = wikiFN($id);
  if(!@file_exists($oldf)) return '';
  $date = filemtime($oldf);
  $newf = wikiFN($id,$date);
  io_writeWikiPage($newf, rawWiki($id), $id, $date);
  return $date;
}

/**
 * Sends a notify mail on page change
 *
 * @param  string  $id       The changed page
 * @param  string  $who      Who to notify (admin|subscribers)
 * @param  int     $rev      Old page revision
 * @param  string  $summary  What changed
 * @param  boolean $minor    Is this a minor edit?
 * @param  array   $replace  Additional string substitutions, @KEY@ to be replaced by value
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function notify($id,$who,$rev='',$summary='',$minor=false,$replace=array()){
  global $lang;
  global $conf;
  global $INFO;

  // decide if there is something to do
  if($who == 'admin'){
    if(empty($conf['notify'])) return; //notify enabled?
    $text = rawLocale('mailtext');
    $to   = $conf['notify'];
    $bcc  = '';
  }elseif($who == 'subscribers'){
    if(!$conf['subscribers']) return; //subscribers enabled?
    if($conf['useacl'] && $_SERVER['REMOTE_USER'] && $minor) return; //skip minors
    $bcc  = subscriber_addresslist($id);
    if(empty($bcc)) return;
    $to   = '';
    $text = rawLocale('subscribermail');
  }elseif($who == 'register'){
    if(empty($conf['registernotify'])) return;
    $text = rawLocale('registermail');
    $to   = $conf['registernotify'];
    $bcc  = '';
  }else{
    return; //just to be safe
  }

  $text = str_replace('@DATE@',date($conf['dformat']),$text);
  $text = str_replace('@BROWSER@',$_SERVER['HTTP_USER_AGENT'],$text);
  $text = str_replace('@IPADDRESS@',$_SERVER['REMOTE_ADDR'],$text);
  $text = str_replace('@HOSTNAME@',gethostbyaddr($_SERVER['REMOTE_ADDR']),$text);
  $text = str_replace('@NEWPAGE@',wl($id,'',true,'&'),$text);
  $text = str_replace('@PAGE@',$id,$text);
  $text = str_replace('@TITLE@',$conf['title'],$text);
  $text = str_replace('@DOKUWIKIURL@',DOKU_URL,$text);
  $text = str_replace('@SUMMARY@',$summary,$text);
  $text = str_replace('@USER@',$_SERVER['REMOTE_USER'],$text);

  foreach ($replace as $key => $substitution) {
    $text = str_replace('@'.strtoupper($key).'@',$substitution, $text);
  }

  if($who == 'register'){
    $subject = $lang['mail_new_user'].' '.$summary;
  }elseif($rev){
    $subject = $lang['mail_changed'].' '.$id;
    $text = str_replace('@OLDPAGE@',wl($id,"rev=$rev",true,'&'),$text);
    require_once(DOKU_INC.'inc/DifferenceEngine.php');
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
  $subject = '['.$conf['title'].'] '.$subject;

  $from = $conf['mailfrom'];
  $from = str_replace('@USER@',$_SERVER['REMOTE_USER'],$from);
  $from = str_replace('@NAME@',$INFO['userinfo']['name'],$from);
  $from = str_replace('@MAIL@',$INFO['userinfo']['mail'],$from);

  mail_send($to,$subject,$text,$from,'',$bcc);
}

/**
 * extracts the query from a google referer
 *
 * @todo   should be more generic and support yahoo et al
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getGoogleQuery(){
  $url = parse_url($_SERVER['HTTP_REFERER']);
  if(!$url) return '';

  if(!preg_match("#google\.#i",$url['host'])) return '';
  $query = array();
  parse_str($url['query'],$query);

  return $query['q'];
}

/**
 * Try to set correct locale
 *
 * @deprecated No longer used
 * @author     Andreas Gohr <andi@splitbrain.org>
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
function filesize_h($size, $dec = 1){
  $sizes = array('B', 'KB', 'MB', 'GB');
  $count = count($sizes);
  $i = 0;

  while ($size >= 1024 && ($i < $count - 1)) {
    $size /= 1024;
    $i++;
  }

  return round($size, $dec) . ' ' . $sizes[$i];
}

/**
 * return an obfuscated email address in line with $conf['mailguard'] setting
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Christopher Smith <chris@jalakai.co.uk>
 */
function obfuscate($email) {
  global $conf;

  switch ($conf['mailguard']) {
    case 'visible' :
      $obfuscate = array('@' => ' [at] ', '.' => ' [dot] ', '-' => ' [dash] ');
      return strtr($email, $obfuscate);

    case 'hex' :
      $encode = '';
      for ($x=0; $x < strlen($email); $x++) $encode .= '&#x' . bin2hex($email{$x}).';';
      return $encode;

    case 'none' :
    default :
      return $email;
  }
}

/**
 * Let us know if a user is tracking a page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function is_subscribed($id,$uid){
  $file=metaFN($id,'.mlist');
  if (@file_exists($file)) {
    $mlist = file($file);
    $pos = array_search($uid."\n",$mlist);
    return is_int($pos);
  }

  return false;
}

/**
 * Return a string with the email addresses of all the
 * users subscribed to a page
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 */
function subscriber_addresslist($id){
  global $conf;
  global $auth;

  $emails = '';

  if (!$conf['subscribers']) return;

  $mlist = array();
  $file=metaFN($id,'.mlist');
  if (@file_exists($file)) {
    $mlist = file($file);
  }
  if(count($mlist) > 0) {
    foreach ($mlist as $who) {
      $who = rtrim($who);
      $info = $auth->getUserData($who);
      $level = auth_aclcheck($id,$who,$info['grps']);
      if ($level >= AUTH_READ) {
        if (strcasecmp($info['mail'],$conf['notify']) != 0) {
          if (empty($emails)) {
            $emails = $info['mail'];
          } else {
            $emails = "$emails,".$info['mail'];
          }
        }
      }
    }
  }

  return $emails;
}

/**
 * Removes quoting backslashes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function unslash($string,$char="'"){
  return str_replace('\\'.$char,$char,$string);
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
