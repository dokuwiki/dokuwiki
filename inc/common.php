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
  require_once(DOKU_INC.'inc/utf8.php');
  require_once(DOKU_INC.'inc/mail.php');
  require_once(DOKU_INC.'inc/parserutils.php');

/**
 * These constants are used with the recents function
 */
define('RECENTS_SKIP_DELETED',2);
define('RECENTS_SKIP_MINORS',4);
define('RECENTS_SKIP_SUBSPACES',8);

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

  if($_SERVER['REMOTE_USER']){
    $info['userinfo']   = $USERINFO;
    $info['perm']       = auth_quickaclcheck($ID);
    $info['subscribed'] = is_subscribed($ID,$_SERVER['REMOTE_USER']);

    // if some outside auth were used only REMOTE_USER is set
    if(!$info['userinfo']['name']){
      $info['userinfo']['name'] = $_SERVER['REMOTE_USER'];
    }
  }else{
    $info['perm']       = auth_aclcheck($ID,'',null);
    $info['subscribed'] = false;
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
  $info['rev'] = $REV;
  if($info['exists']){
    $info['writable'] = (is_writable($info['filepath']) &&
                         ($info['perm'] >= AUTH_EDIT));
  }else{
    $info['writable'] = ($info['perm'] >= AUTH_CREATE);
  }
  $info['editable']  = ($info['writable'] && empty($info['lock']));
  $info['lastmod']   = @filemtime($info['filepath']);

  //who's the editor
  if($REV){
    $revinfo = getRevisionInfo($ID,$REV);
  }else{
    $revinfo = getRevisionInfo($ID,$info['lastmod']);
  }
  $info['ip']     = $revinfo['ip'];
  $info['user']   = $revinfo['user'];
  $info['sum']    = $revinfo['sum'];
  $info['minor']  = $revinfo['minor'];

  if($revinfo['user']){
    $info['editor'] = $revinfo['user'];
  }else{
    $info['editor'] = $revinfo['ip'];
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
 * @author Andreas Gohr
 */
function buildAttributes($params){
  $url = '';
  foreach($params as $key => $val){
    $url .= $key.'="';
    $url .= htmlspecialchars ($val);
    $url .= '" ';
  }
  return $url;
}


/**
 * print a message
 *
 * If HTTP headers were not sent yet the message is added
 * to the global message array else it's printed directly
 * using html_msgarea()
 *
 *
 * Levels can be:
 *
 * -1 error
 *  0 info
 *  1 success
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    html_msgarea
 */
function msg($message,$lvl=0,$line='',$file=''){
  global $MSG;
  $errors[-1] = 'error';
  $errors[0]  = 'info';
  $errors[1]  = 'success';

  if($line || $file) $message.=' ['.basename($file).':'.$line.']';

  if(!headers_sent()){
    if(!isset($MSG)) $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
  }else{
    $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
    if(function_exists('html_msgarea')){
      html_msgarea();
    }else{
      print "ERROR($lvl) $message";
    }
  }
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
  $crumbs = $_SESSION[$conf['title']]['bc'];

  //first visit?
  if (!is_array($crumbs)){
    $crumbs = array();
  }
  //we only save on show and existing wiki documents
  $file = wikiFN($ID);
  if($ACT != 'show' || !@file_exists($file)){
    $_SESSION[$conf['title']]['bc'] = $crumbs;
    return $crumbs;
  }

  // page names
  $name = noNS($ID);
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
  $_SESSION[$conf['title']]['bc'] = $crumbs;
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
 * Build a link to a media file
 *
 * Will return a link to the detail page if $direct is false
 */
function ml($id='',$more='',$direct=true,$sep='&amp;'){
  global $conf;
  if(is_array($more)){
    $more = buildURLparams($more,$sep);
  }else{
    $more = str_replace(',',$sep,$more);
  }

  $xlink = DOKU_BASE;

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

  $wordblocks = getWordblocks();
  //how many lines to read at once (to work around some PCRE limits)
  if(version_compare(phpversion(),'4.3.0','<')){
    //old versions of PCRE define a maximum of parenthesises even if no
    //backreferences are used - the maximum is 99
    //this is very bad performancewise and may even be too high still
    $chunksize = 40;
  }else{
    //read file in chunks of 600 - this should work around the
    //MAX_PATTERN_SIZE in modern PCRE
    $chunksize = 400;
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
    if(preg_match('#('.join('|',$re).')#si',$TEXT, $match=array())) {
      return true;
    }
  }
  return false;
}

/**
 * Return the IP of the client
 *
 * Honours X-Forwarded-For Proxy Headers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function clientIP(){
  $my = $_SERVER['REMOTE_ADDR'];
  if($_SERVER['HTTP_X_FORWARDED_FOR']){
    $my .= ' ('.$_SERVER['HTTP_X_FORWARDED_FOR'].')';
  }
  return $my;
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
 * Lock a page for editing
 *
 * @author Andreas Gohr <andi@splitbrain.org>
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
 * Unlock a page if it was locked by the user
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @return bool true if a lock was removed
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
  $text = preg_replace("/\012/","\015\012",$text);
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
  return io_readFile(wikiFN($id,$rev));
}

/**
 * Returns the pagetemplate contents for the ID's namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pageTemplate($id){
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
  $text = io_readFile(wikiFN($id,$rev));
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
 * print debug messages
 *
 * little function to print the content of a var
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function dbg($msg,$hidden=false){
  (!$hidden) ? print '<pre class="dbg">' : print "<!--\n";
  print_r($msg);
  (!$hidden) ? print '</pre>' : print "\n-->";
}

/**
 * Add's an entry to the changelog
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function addLogEntry($date,$id,$summary='',$minor=false){
  global $conf;

  if(!@is_writable($conf['changelog'])){
    msg($conf['changelog'].' is not writable!',-1);
    return;
  }

  if(!$date) $date = time(); //use current time if none supplied
  $remote = $_SERVER['REMOTE_ADDR'];
  $user   = $_SERVER['REMOTE_USER'];

  if($conf['useacl'] && $user && $minor){
    $summary = '*'.$summary;
  }else{
    $summary = ' '.$summary;
  }

  $logline = join("\t",array($date,$remote,$id,$user,$summary))."\n";
  io_saveFile($conf['changelog'],$logline,true);
}

/**
 * Checks an summary entry if it was a minor edit
 *
 * The summary is cleaned of the marker char
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function isMinor(&$summary){
  if(substr($summary,0,1) == '*'){
    $summary = substr($summary,1);
    return true;
  }
  $summary = trim($summary);
  return false;
}

/**
 * Internal function used by getRecents
 *
 * don't call directly
 *
 * @see getRecents()
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function _handleRecent($line,$ns,$flags){
  static $seen  = array();         //caches seen pages and skip them
  if(empty($line)) return false;   //skip empty lines

  // split the line into parts
  list($dt,$ip,$id,$usr,$sum) = explode("\t",$line);

  // skip seen ones
  if($seen[$id]) return false;
  $recent = array();

  // check minors
  if(isMinor($sum)){
    // skip minors
    if($flags & RECENTS_SKIP_MINORS) return false;
    $recent['minor'] = true;
  }else{
    $recent['minor'] = false;
  }

  // remember in seen to skip additional sights
  $seen[$id] = 1;

  // check if it's a hidden page
  if(isHiddenPage($id)) return false;

  // filter namespace
  if (($ns) && (strpos($id,$ns.':') !== 0)) return false;

  // exclude subnamespaces
  if (($flags & RECENTS_SKIP_SUBSPACES) && (getNS($id) != $ns)) return false;

  // check ACL
  if (auth_quickaclcheck($id) < AUTH_READ) return false;

  // check existance
  if(!@file_exists(wikiFN($id))){
    if($flags & RECENTS_SKIP_DELETED){
      return false;
    }else{
      $recent['del'] = true;
    }
  }else{
    $recent['del'] = false;
  }

  $recent['id']   = $id;
  $recent['date'] = $dt;
  $recent['ip']   = $ip;
  $recent['user'] = $usr;
  $recent['sum']  = $sum;

  return $recent;
}


/**
 * returns an array of recently changed files using the
 * changelog
 *
 * The following constants can be used to control which changes are
 * included. Add them together as needed.
 *
 * RECENTS_SKIP_DELETED   - don't include deleted pages
 * RECENTS_SKIP_MINORS    - don't include minor changes
 * RECENTS_SKIP_SUBSPACES - don't include subspaces
 *
 * @param int    $first   number of first entry returned (for paginating
 * @param int    $num     return $num entries
 * @param string $ns      restrict to given namespace
 * @param bool   $flags   see above
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getRecents($first,$num,$ns='',$flags=0){
  global $conf;
  $recent = array();
  $count  = 0;

  if(!$num)
    return $recent;

  if(!@is_readable($conf['changelog'])){
    msg($conf['changelog'].' is not readable',-1);
    return $recent;
  }

  $fh  = fopen($conf['changelog'],'r');
  $buf = '';
  $csz = 4096;                              //chunksize
  fseek($fh,0,SEEK_END);                    // jump to the end
  $pos = ftell($fh);                        // position pointer

  // now read backwards into buffer
  while($pos > 0){
    $pos -= $csz;                           // seek to previous chunk...
    if($pos < 0) {                          // ...or rest of file
      $csz += $pos;
      $pos = 0;
    }

    fseek($fh,$pos);

    $buf = fread($fh,$csz).$buf;            // prepend to buffer

    $lines = explode("\n",$buf);            // split buffer into lines

    if($pos > 0){
      $buf = array_shift($lines);           // first one may be still incomplete
    }

    $cnt = count($lines);
    if(!$cnt) continue;                     // no lines yet

    // handle lines
    for($i = $cnt-1; $i >= 0; $i--){
      $rec = _handleRecent($lines[$i],$ns,$flags);
      if($rec !== false){
        if(--$first >= 0) continue;         // skip first entries
        $recent[] = $rec;
        $count++;

        // break while when we have enough entries
        if($count >= $num){
          $pos = 0; // will break the while loop
          break;    // will break the for loop
        }
      }
    }
  }// end of while

  fclose($fh);
  return $recent;
}

/**
 * gets additonal informations for a certain pagerevison
 * from the changelog
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getRevisionInfo($id,$rev){
  global $conf;

  if(!$rev) return(null);

  $info = array();
  if(!@is_readable($conf['changelog'])){
    msg($conf['changelog'].' is not readable',-1);
    return $recent;
  }
  $loglines = file($conf['changelog']);
  $loglines = preg_grep("/$rev\t\d+\.\d+\.\d+\.\d+\t$id\t/",$loglines);
  $loglines = array_reverse($loglines); //reverse sort on timestamp (shouldn't be needed)
  $line = split("\t",$loglines[0]);
  $info['date']  = $line[0];
  $info['ip']    = $line[1];
  $info['user']  = $line[3];
  $info['sum']   = $line[4];
  $info['minor'] = isMinor($info['sum']);
  return $info;
}

/**
 * Saves a wikitext by calling io_saveFile
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function saveWikiText($id,$text,$summary,$minor=false){
  global $conf;
  global $lang;
  // ignore if no changes were made
  if($text == rawWiki($id,'')){
    return;
  }

  $file = wikiFN($id);
  $old  = saveOldRevision($id);

  if (empty($text)){
    // remove empty file
    @unlink($file);
    // remove any meta info
    $mfiles = metaFiles($id);
    foreach ($mfiles as $mfile) {
      if (file_exists($mfile)) @unlink($mfile);
    }
    $del = true;
    // autoset summary on deletion
    if(empty($summary)) $summary = $lang['deleted'];
    // unlock early
    unlock($id);
    // remove empty namespaces
    io_sweepNS($id);
  }else{
    // save file (datadir is created in io_saveFile)
    io_saveFile($file,$text);
    $del = false;
  }

  addLogEntry(@filemtime($file),$id,$summary,$minor);
  // send notify mails
  notify($id,'admin',$old,$summary,$minor);
  notify($id,'subscribers',$old,$summary,$minor);

  //purge cache on add by updating the purgefile
  if($conf['purgeonadd'] && (!$old || $del)){
    io_saveFile($conf['cachedir'].'/purgefile',time());
  }
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
  if(substr($newf,-3)=='.gz'){
    io_saveFile($newf,rawWiki($id));
  }else{
    io_makeFileDir($newf);
    copy($oldf, $newf);
  }
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
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function notify($id,$who,$rev='',$summary='',$minor=false){
  global $lang;
  global $conf;

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
  }else{
    return; //just to be safe
  }

  $text = str_replace('@DATE@',date($conf['dformat']),$text);
  $text = str_replace('@BROWSER@',$_SERVER['HTTP_USER_AGENT'],$text);
  $text = str_replace('@IPADDRESS@',$_SERVER['REMOTE_ADDR'],$text);
  $text = str_replace('@HOSTNAME@',gethostbyaddr($_SERVER['REMOTE_ADDR']),$text);
  $text = str_replace('@NEWPAGE@',wl($id,'',true),$text);
  $text = str_replace('@PAGE@',$id,$text);
  $text = str_replace('@TITLE@',$conf['title'],$text);
  $text = str_replace('@DOKUWIKIURL@',DOKU_URL,$text);
  $text = str_replace('@SUMMARY@',$summary,$text);
  $text = str_replace('@USER@',$_SERVER['REMOTE_USER'],$text);

  if($rev){
    $subject = $lang['mail_changed'].' '.$id;
    $text = str_replace('@OLDPAGE@',wl($id,"rev=$rev",true),$text);
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

  mail_send($to,$subject,$text,$conf['mailfrom'],'',$bcc);
}

/**
 * Return a list of available page revisons
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getRevisions($id){
  $revd = dirname(wikiFN($id,'foo'));
  $revs = array();
  $clid = cleanID($id);
  if(strrpos($clid,':')) $clid = substr($clid,strrpos($clid,':')+1); //remove path
  $clid = utf8_encodeFN($clid);

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
 * Return DokuWikis version
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getVersion(){
  //import version string
  if(@file_exists('VERSION')){
    //official release
    return 'Release '.trim(io_readfile(DOKU_INC.'/VERSION'));
  }elseif(is_dir('_darcs')){
    //darcs checkout
    $inv = file('_darcs/inventory');
    $inv = preg_grep('#\*\*\d{14}[\]$]#',$inv);
    $cur = array_pop($inv);
    preg_match('#\*\*(\d{4})(\d{2})(\d{2})#',$cur,$matches);
    return 'Darcs '.$matches[1].'-'.$matches[2].'-'.$matches[3];
  }else{
    return 'snapshot?';
  }
}

/**
 * Run a few sanity checks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function check(){
  global $conf;
  global $INFO;

  msg('DokuWiki version: '.getVersion(),1);

  if(version_compare(phpversion(),'4.3.0','<')){
    msg('Your PHP version is too old ('.phpversion().' vs. 4.3.+ recommended)',-1);
  }elseif(version_compare(phpversion(),'4.3.10','<')){
    msg('Consider upgrading PHP to 4.3.10 or higher for security reasons (your version: '.phpversion().')',0);
  }else{
    msg('PHP version '.phpversion(),1);
  }

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

  if(is_writable($conf['cachedir'])){
    msg('Cachedir is writable',1);
  }else{
    msg('Cachedir is not writable',-1);
  }

  if(is_writable(DOKU_CONF.'users.auth.php')){
    msg('conf/users.auth.php is writable',1);
  }else{
    msg('conf/users.auth.php is not writable',0);
  }

  if(function_exists('mb_strpos')){
    if(defined('UTF8_NOMBSTRING')){
      msg('mb_string extension is available but will not be used',0);
    }else{
      msg('mb_string extension is available and will be used',1);
    }
  }else{
    msg('mb_string extension not available - PHP only replacements will be used',0);
  }

  if($conf['allowdebug']){
    msg('Debugging support is enabled. If you don\'t need it you should set $conf[\'allowdebug\'] = 0',-1);
  }else{
    msg('Debugging support is disabled',1);
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
  if (file_exists($file)) {
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
