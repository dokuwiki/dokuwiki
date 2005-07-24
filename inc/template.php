<?php
/**
 * DokuWiki template functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'conf/dokuwiki.php');

/**
 * Wrapper around htmlspecialchars()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    htmlspecialchars()
 */
function hsc($string){
  return htmlspecialchars($string);
}

/**
 * print a newline terminated string
 *
 * You can give an indention as optional parameter
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ptln($string,$intend=0){
  for($i=0; $i<$intend; $i++) print ' ';
  print"$string\n";
}

/**
 * Print the content
 *
 * This function is used for printing all the usual content
 * (defined by the global $ACT var) by calling the appropriate
 * outputfunction(s) from html.php
 *
 * Everything that doesn't use the default template isn't
 * handled by this function. ACL stuff is not done either.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_content(){
  global $ACT;
  global $TEXT;
  global $PRE;
  global $SUF;
  global $SUM;
  global $IDX;

  switch($ACT){
    case 'show':
      html_show();
      break;
    case 'preview':
      html_edit($TEXT);
      html_show($TEXT);
      break;
    case 'edit':
      html_edit();
      break;
    case 'wordblock':
      html_edit($TEXT,'wordblock');
      break;
    case 'search':
      html_search();
      break;
    case 'revisions':
      html_revisions();
      break;
    case 'diff':
      html_diff();
      break;
    case 'recent':
      $first = is_numeric($_REQUEST['first']) ? intval($_REQUEST['first']) : 0;
      html_recent($first);
      break;
    case 'index':
      html_index($IDX); #FIXME can this be pulled from globals? is it sanitized correctly?
      break;
    case 'backlink':
      html_backlinks();
      break;
    case 'conflict':
      html_conflict(con($PRE,$TEXT,$SUF),$SUM);
      html_diff(con($PRE,$TEXT,$SUF),false);
      break;
    case 'locked':
      html_locked();
      break;
    case 'login':
      html_login();
      break;
    case 'register':
      html_register();
      break;
    case 'denied':
      print p_locale_xhtml('denied');
			break;
    case 'admin':
      tpl_admin();
      break;
    default:
			msg("Failed to handle command: ".hsc($ACT),-1); 
  }
}

/**
 * Handle the admin page contents
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_admin(){
  switch($_REQUEST['page']){
		case 'acl':
			admin_acl_html();
			break;
    default:
			html_admin();
	}
}

/**
 * Print the correct HTML meta headers
 *
 * This has to go into the head section of your template.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_metaheaders(){
  global $ID;
  global $INFO;
  global $ACT;
  global $lang;
  global $conf;
  $it=2;

  // the usual stuff
  ptln('<meta name="generator" content="DokuWiki '.getVersion().'" />',$it);
  ptln('<link rel="start" href="'.DOKU_BASE.'" />',$it);
  ptln('<link rel="contents" href="'.wl($ID,'do=index').'" title="'.$lang['index'].'" />',$it);
  ptln('<link rel="alternate" type="application/rss+xml" title="Recent Changes" href="'.DOKU_BASE.'feed.php" />',$it);
  ptln('<link rel="alternate" type="application/rss+xml" title="Current Namespace" href="'.DOKU_BASE.'feed.php?mode=list&amp;ns='.$INFO['namespace'].'" />',$it);
  ptln('<link rel="alternate" type="text/html" title="Plain HTML" href="'.wl($ID,'do=export_html').'" />',$it);
  ptln('<link rel="alternate" type="text/plain" title="Wiki Markup" href="'.wl($ID, 'do=export_raw').'" />',$it);
  ptln('<link rel="stylesheet" media="screen" type="text/css" href="'.DOKU_BASE.'lib/styles/style.css" />',$it);

  // setup robot tags apropriate for different modes
  if( ($ACT=='show' || $ACT=='export_html') && !$REV){
    if($INFO['exists']){
      ptln('<meta name="date" content="'.date('Y-m-d\TH:i:sO',$INFO['lastmod']).'" />',$it);
      //delay indexing:
      if((time() - $INFO['lastmod']) >= $conf['indexdelay']){
        ptln('<meta name="robots" content="index,follow" />',$it);
      }else{
        ptln('<meta name="robots" content="noindex,nofollow" />',$it);
      }
    }else{
      ptln('<meta name="robots" content="noindex,follow" />',$it);
    }
  }else{
    ptln('<meta name="robots" content="noindex,nofollow" />',$it);
  }

  // include some JavaScript language strings
  ptln('<script language="javascript" type="text/javascript" charset="utf-8">',$it);
  ptln("  var alertText   = '".$lang['qb_alert']."'",$it);
  ptln("  var notSavedYet = '".$lang['notsavedyet']."'",$it);
  ptln("  var DOKU_BASE   = '".DOKU_BASE."'",$it);
  ptln('</script>',$it);
 
  // load the default JavaScript files
  ptln('<script language="javascript" type="text/javascript" charset="utf-8" src="'.
       DOKU_BASE.'lib/scripts/script.js"></script>',$it);
  ptln('<script language="javascript" type="text/javascript" charset="utf-8" src="'.
       DOKU_BASE.'lib/scripts/tw-sack.js"></script>',$it);
  ptln('<script language="javascript" type="text/javascript" charset="utf-8" src="'.
       DOKU_BASE.'lib/scripts/ajax.js"></script>',$it);

  // load spellchecker script if wanted
  if($conf['spellchecker'] && ($ACT=='edit' || $ACT=='preview')){
    ptln('<script language="javascript" type="text/javascript" charset="utf-8" src="'.
       DOKU_BASE.'lib/scripts/spellcheck.js"></script>',$it);
  }

  //FIXME include some default CSS ? IE FIX?
}

/**
 * Print a link
 *
 * Just builds a link but adds additional JavaScript needed for
 * the unsaved data check needed in the edit form.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_link($url,$name,$more=''){
  print '<a href="'.$url.'" onclick="return svchk()" onkeypress="return svchk()"';
  if ($more) print ' '.$more;
  print ">$name</a>";
}

/**
 * Prints a link to a WikiPage
 *
 * Wrapper around html_wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_pagelink($id,$name=NULL){
  print html_wikilink($id,$name);
}

/**
 * get the parent page
 *
 * Tries to find out which page is parent.
 * returns false if none is available
 *
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
function tpl_getparent($ID){
  global $conf;
    
  if ($ID != $conf['start']) {
    $idparts = explode(':', $ID);
    $pn = array_pop($idparts);    // get the page name

    for ($n=0; $n < 2; $n++) {
      if (count($idparts) == 0) {
        $ID = $conf['start'];     // go to topmost page
        break;
      }else{
        $ns = array_pop($idparts);     // get the last part of namespace
        if ($pn != $ns) {                 // are we already home?
          array_push($idparts, $ns, $ns); // no, then add a page with same name
          $ID = implode (':', $idparts); // as the namespace and recombine $ID
          break;
        }
      }
    }
    
    if (@file_exists(wikiFN($ID))) {
      return $ID;
    }
  }
  return false;
}

/**
 * Print one of the buttons
 *
 * Available Buttons are
 *
 *  edit    - edit/create/show button
 *  history - old revisions
 *  recent  - recent changes
 *  login    - login/logout button - if ACL enabled
 *  index     - The index
 *  admin      - admin page - if enough rights
 *  top         - a back to top button
 *  back        - a back to parent button - if available
 *  backtomedia - returns to the mediafile upload dialog
 *                after references have been displayed
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
function tpl_button($type){
  global $ID;
  global $NS;
  global $INFO;
  global $conf;

  switch($type){
    case 'edit':
      print html_editbutton();
      break;
    case 'history':
      print html_btn('revs',$ID,'o',array('do' => 'revisions'));
      break;
    case 'recent':
      print html_btn('recent','','r',array('do' => 'recent'));
      break;
    case 'index':
      print html_btn('index',$ID,'x',array('do' => 'index'));
      break;
    case 'back':
      if ($ID = tpl_getparent($ID)) {
        print html_btn('back',$ID,'b',array('do' => 'show'));
      }
      break;
    case 'top':
      print html_topbtn();
      break;
    case 'login':
      if($conf['useacl']){
        if($_SERVER['REMOTE_USER']){
          print html_btn('logout',$ID,'',array('do' => 'logout',));
        }else{
          print html_btn('login',$ID,'',array('do' => 'login'));
        }
      }
      break;
    case 'admin':
      if($INFO['perm'] == AUTH_ADMIN)
        print html_btn('admin',$ID,'',array('do' => 'admin'));
      break;
    case 'backtomedia':
      print html_backtomedia_button(array('ns' => $NS),'b');
      break;
		default:
			print '[unknown button type]';
  }
}

/**
 * Like the action buttons but links
 *
 * Available links are
 *
 *  edit    - edit/create/show button
 *  history - old revisions
 *  recent  - recent changes
 *  login   - login/logout button - if ACL enabled
 *  index   - The index
 *  admin   - admin page - if enough rights
 *  top     - a back to top button
 *  back    - a back to parent button - if available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @see    tpl_button
 */
function tpl_actionlink($type,$pre='',$suf=''){
  global $ID;
  global $INFO;
  global $REV;
  global $ACT;
  global $conf;
  global $lang;

  switch($type){
    case 'edit':
      #most complicated type - we need to decide on current action
      if($ACT == 'show' || $ACT == 'search'){
        if($INFO['writable']){
          if($INFO['exists']){
            tpl_link(wl($ID,'do=edit&amp;rev='.$REV),
                     $pre.$lang['btn_edit'].$suf,
                     'class="action" accesskey="e" rel="nofollow"');
          }else{
            tpl_link(wl($ID,'do=edit&amp;rev='.$REV),
                     $pre.$lang['btn_create'].$suf,
                     'class="action" accesskey="e" rel="nofollow"');
          }
        }else{
          tpl_link(wl($ID,'do=edit&amp;rev='.$REV),
                   $pre.$lang['btn_source'].$suf,
                   'class="action" accesskey="v" rel="nofollow"');
        }
      }else{
          tpl_link(wl($ID,'do=show'),
                   $pre.$lang['btn_show'].$suf,
                   'class="action" accesskey="v" rel="nofollow"');
      }
      break;
    case 'history':
      tpl_link(wl($ID,'do=revisions'),$pre.$lang['btn_revs'].$suf,'class="action" accesskey="o"');
      break;
    case 'recent':
      tpl_link(wl($ID,'do=recent'),$pre.$lang['btn_recent'].$suf,'class="action" accesskey="r"');
      break;
    case 'index':
      tpl_link(wl($ID,'do=index'),$pre.$lang['btn_index'].$suf,'class="action" accesskey="x"');
      break;
    case 'top':
      print '<a href="#top" class="action" accesskey="x">'.$pre.$lang['btn_top'].$suf.'</a>';
      break;
    case 'back':
      if ($ID = tpl_getparent($ID)) {
        tpl_link(wl($ID,'do=show'),$pre.$lang['btn_back'].$suf,'class="action" accesskey="b"');
      }
      break;
    case 'login':
      if($conf['useacl']){
        if($_SERVER['REMOTE_USER']){
          tpl_link(wl($ID,'do=logout'),$pre.$lang['btn_logout'].$suf,'class="action"');
        }else{
          tpl_link(wl($ID,'do=login'),$pre.$lang['btn_login'].$suf,'class="action"');
        }
      }
      break;
    case 'admin':
      if($INFO['perm'] == AUTH_ADMIN)
        tpl_link(wl($ID,'do=admin'),$pre.$lang['btn_admin'].$suf,'class="action"');
      break;
    default:
      print '[unknown link type]';
  }
}

/**
 * Print the search form
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_searchform(){
  global $lang;
  global $ACT;
  
  print '<form action="'.wl().'" accept-charset="utf-8" class="search" name="search" onsubmit="return svchk()">';
  print '<input type="hidden" name="do" value="search" />';
  print '<input type="text" ';
  
  if ($ACT == 'search')
    print 'value="'.$_REQUEST['id'].'" '; /* keep search input as long as user stays on search page */
    
  print 'id="qsearch_in" accesskey="f" name="id" class="edit" onkeyup="ajax_qsearch.call(\'qsearch_in\',\'qsearch_out\')" />';
  print '<input type="submit" value="'.$lang['btn_search'].'" class="button" />';
  print '<div id="qsearch_out" class="ajax_qsearch" onclick="this.style.display=\'none\'"></div>';
  print '</form>';
}

/**
 * Print the breadcrumbs trace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_breadcrumbs(){
  global $lang;
  global $conf;

  //check if enabled
  if(!$conf['breadcrumbs']) return;

  $crumbs = breadcrumbs(); //setup crumb trace

  //reverse crumborder in right-to-left mode
  if($lang['direction'] == 'rtl') $crumbs = array_reverse($crumbs,true);

  //render crumbs, highlight the last one
  print $lang['breadcrumb'].':';
  $last = count($crumbs);
  $i = 0;
  foreach ($crumbs as $id => $name){
    $i++;
    print ' <span class="bcsep">&raquo;</span> ';
    if ($i == $last) print '<span class="curid">';
    tpl_link(wl($id),$name,'class="breadcrumbs" title="'.$id.'"');
    if ($i == $last) print '</span>';
  }
}

/**
 * Hierarchical breadcrumbs
 *
 * This code was suggested as replacement for the usual breadcrumbs
 * trail in the Wiki and was modified by me.
 * It only makes sense with a deep site structure.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://wiki.splitbrain.org/wiki:tipsandtricks:hierarchicalbreadcrumbs
 * @todo   May behave starngely in RTL languages
 */
function tpl_youarehere(){
  global $conf;
  global $ID;
  global $lang;

  
  $parts     = explode(':', $ID);

  print $lang['breadcrumb'].': ';

  //always print the startpage  
  if( $a_part[0] != $conf['start'] )
    tpl_link(wl($conf['start']),$conf['start'],'title="'.$conf['start'].'"');

  $page = ''; 
  foreach ($parts as $part){
	  print ' &raquo; ';
    $page .= $part;

    if(file_exists(wikiFN($page))){
      tpl_link(wl($page),$part,'title="'.$page.'"');
    }else{
      print $page;
    }

    $page .= ':';
  }
}

/**
 * Print info if the user is logged in
 * and show full name in that case
 *
 * Could be enhanced with a profile link in future?
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_userinfo(){
  global $lang;
  global $INFO;
  if($_SERVER['REMOTE_USER'])
    print $lang['loggedinas'].': '.$INFO['userinfo']['name'];
}

/**
 * Print some info about the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_pageinfo(){
  global $conf;
  global $lang;
  global $INFO;
  global $REV;

  // prepare date and path
  $fn = $INFO['filepath'];
  if(!$conf['fullpath']){
    if($REV){
      $fn = str_replace(realpath($conf['olddir']).DIRECTORY_SEPARATOR,'',$fn);
    }else{
      $fn = str_replace(realpath($conf['datadir']).DIRECTORY_SEPARATOR,'',$fn);
    }
  }
  $fn = utf8_decodeFN($fn);
  $date = date($conf['dformat'],$INFO['lastmod']);

  // print it
  if($INFO['exists']){
    print $fn;
    print ' &middot; ';
    print $lang['lastmod'];
    print ': ';
    print $date;
    if($INFO['editor']){
      print ' '.$lang['by'].' ';
      print $INFO['editor'];
    }
    if($INFO['locked']){
      print ' &middot; ';
      print $lang['lockedby'];
      print ': ';
      print $INFO['locked'];
    }
  }
}

/**
 * Print a list of namespaces containing media files
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_medianamespaces(){
	global $conf;

  $data = array();
  search($data,$conf['mediadir'],'search_namespaces',array());
  print html_buildlist($data,'idx',media_html_list_namespaces);
}

/**
 * Print a list of mediafiles in the current namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_mediafilelist(){
  global $conf;
  global $lang;
  global $NS;
  global $AUTH;
  $dir = utf8_encodeFN(str_replace(':','/',$NS));

  $data = array();
  search($data,$conf['mediadir'],'search_media',array(),$dir);

  if(!count($data)){
    ptln('<div class="nothing">'.$lang['nothingfound'].'<div>');
    return;
  }

  ptln('<ul>',2);
  foreach($data as $item){
    ptln('<li>',4);
    ptln('<a href="javascript:mediaSelect(\''.$item['id'].'\')">'.
         utf8_decodeFN($item['file']).
         '</a>',6);

    //prepare deletion button
    if($AUTH >= AUTH_DELETE){
      $ask  = $lang['del_confirm'].'\\n';
      $ask .= $item['id'];

      $del = '<a href="'.DOKU_BASE.'lib/exe/media.php?delete='.urlencode($item['id']).'" '.
             'onclick="return confirm(\''.$ask.'\')" onkeypress="return confirm(\''.$ask.'\')">'.
             '<img src="'.DOKU_BASE.'lib/images/del.png" alt="'.$lang['btn_delete'].'" '.
             'align="bottom" title="'.$lang['btn_delete'].'" /></a>';
    }else{
      $del = '';
    }

    if($item['isimg']){
      $w = $item['info'][0];
      $h = $item['info'][1];

      ptln('('.$w.'&#215;'.$h.' '.filesize_h($item['size']).')',6);
      ptln($del.'<br />',6);
      ptln('<a href="javascript:mediaSelect(\''.$item['id'].'\')">');

      if($w>120){
        print '<img src="'.DOKU_BASE.'lib/exe/fetch.php?w=120&amp;media='.urlencode($item['id']).'" width="120" />';
      }else{
        print '<img src="'.DOKU_BASE.'lib/exe/fetch.php?media='.urlencode($item['id']).'" width="'.$w.'" height="'.$h.'" />';
      }
      print '</a>';

    }else{
      ptln ('('.filesize_h($item['size']).')',6);
      ptln($del,6);
    }
    ptln('</li>',4);
  }
  ptln('</ul>',2);
}

/**
 * show references to a media file
 * References uses the same visual as search results and share
 * their CSS tags except pagenames won't be links.
 *
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
function tpl_showreferences(&$data){
  global $lang;

  $hidden=0; //count of hits without read permission
  
  if(count($data)){
    usort($data,'sort_search_fulltext');
    foreach($data as $row){
      if(auth_quickaclcheck($row['id']) >= AUTH_READ){
        print '<div class="search_result">';
        print '<span class="mediaref_ref">'.$row['id'].'</span>';
        print ': <span class="search_cnt">'.$row['count'].' '.$lang['hits'].'</span><br />';
        print '<div class="search_snippet">'.$row['snippet'].'</div>';
        print '</div>';
      }else
        $hidden++;
    }
    if ($hidden){
      print '<div class="mediaref_hidden">'.$lang['ref_hidden'].'</div>';
    }
  }
}

/**
 * Print the media upload form if permissions are correct
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_mediauploadform(){
  global $NS;
  global $UPLOADOK;
  global $AUTH;
  global $lang;

  if(!$UPLOADOK) return;

  ptln('<form action="'.DOKU_BASE.'lib/exe/media.php" name="upload"'.
       ' method="post" enctype="multipart/form-data">',2);
  ptln($lang['txt_upload'].':<br />',4);
  ptln('<input type="file" name="upload" class="edit" onchange="suggestWikiname();" />',4);
  ptln('<input type="hidden" name="ns" value="'.hsc($NS).'" /><br />',4);
  ptln($lang['txt_filename'].'<br />',4);
  ptln('<input type="text" name="id" class="edit" />',4);
  ptln('<input type="submit" class="button" value="'.$lang['btn_upload'].'" accesskey="s" />',4);
  if($AUTH >= AUTH_DELETE){
    ptln('<label for="ow" class="simple"><input type="checkbox" name="ow" value="1" id="ow">'.$lang['txt_overwrt'].'</label>',4);
  }
  ptln('</form>',2);
}

/**
 * Prints the name of the given page (current one if none given).
 *
 * If useheading is enabled this will use the first headline else
 * the given ID is printed.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_pagetitle($id=null){
  global $conf;
  if(is_null($id)){
    global $ID;
    $id = $ID;
  }

  $name = $id;
  if ($conf['useheading']) {
    $title = p_get_first_heading($id);
    if ($title) $name = $title;
  }
  print hsc($name);
}

/**
 * Returns the requested EXIF/IPTC tag from the current image
 *
 * If $tags is an array all given tags are tried until a
 * value is found. If no value is found $alt is returned.
 *
 * Which texts are known is defined in the functions _exifTagNames
 * and _iptcTagNames() in inc/jpeg.php (You need to prepend IPTC
 * to the names of the latter one)
 *
 * Only allowed in: detail.php
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_img_getTag($tags,$alt=''){
  // Init Exif Reader
  global $SRC;
  static $meta = null;
  if(is_null($meta)) $meta = new JpegMeta($SRC);
  if($meta === false) return $alt;
  $info = $meta->getField($tags);
  if($info == false) return $alt;
  return $info;
}

/**
 * Prints the image with a link to the full sized version
 *
 * Only allowed in: detail.php
 */
function tpl_img($maxwidth=900,$maxheight=700){
  global $IMG;
  $w = tpl_img_getTag('File.Width');
  $h = tpl_img_getTag('File.Height');

  //resize to given max values
  $ratio = 0;
  if($w > $h){
    if($w > $maxwidth){
      $ratio = $maxwidth/$w;
    }elseif($h > $maxheight){
      $ratio = $maxheight/$h;
    }
  }else{
    if($h > $maxheight){
      $ratio = $maxheight/$h;
    }elseif($w > $maxwidth){
      $ratio = $maxwidth/$w;
    }
  }
  if($ratio){
    $w = floor($ratio*$w);
    $h = floor($ratio*$h);
  }

  //FIXME add alt attribute, classes

  $url=DOKU_BASE.'lib/exe/fetch.php?cache='.urlencode($_REQUEST['cache']).
       '&amp;media='.urlencode($IMG);

  $alt=tpl_img_getTag('Simple.Title');

  print '<a href="'.$url.'">';
  print '<img src="'.$url.'&amp;w='.$w.'&amp;h='.$w.'" width="'.$w.'" height="'.$h.'" />';
  print '</a>';
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
