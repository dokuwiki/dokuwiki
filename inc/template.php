<?php
/**
 * DokuWiki template functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_CONF.'dokuwiki.php');

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
 * Returns the path to the given template, uses
 * default one if the custom version doesn't exist
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function template($tpl){
  global $conf;

  if(@is_readable(DOKU_INC.'lib/tpl/'.$conf['template'].'/'.$tpl))
    return DOKU_INC.'lib/tpl/'.$conf['template'].'/'.$tpl;

  return DOKU_INC.'lib/tpl/default/'.$tpl;
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
    case 'resendpwd':
      html_resendpwd();
      break;
    case 'denied':
      print p_locale_xhtml('denied');
      break;
    case 'profile' :
      html_updateprofile();
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

    $plugin = NULL;
    if ($_REQUEST['page']) {
        $pluginlist = plugin_list('admin');

        if (in_array($_REQUEST['page'], $pluginlist)) {

          // attempt to load the plugin
          $plugin =& plugin_load('admin',$_REQUEST['page']);
        }
    }

    if ($plugin !== NULL)
        $plugin->html();
    else
        html_admin();
}

/**
 * Print the correct HTML meta headers
 *
 * This has to go into the head section of your template.
 *
 * @param  boolean $alt Should feeds and alternative format links be added?
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_metaheaders($alt=true){
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

  if($alt){
    ptln('<link rel="alternate" type="application/rss+xml" title="Recent Changes" href="'.DOKU_BASE.'feed.php" />',$it);
    ptln('<link rel="alternate" type="application/rss+xml" title="Current Namespace" href="'.DOKU_BASE.'feed.php?mode=list&amp;ns='.$INFO['namespace'].'" />',$it);
    ptln('<link rel="alternate" type="text/html" title="Plain HTML" href="'.wl($ID,'do=export_xhtml').'" />',$it);
    ptln('<link rel="alternate" type="text/plain" title="Wiki Markup" href="'.wl($ID, 'do=export_raw').'" />',$it);
  }

  // setup robot tags apropriate for different modes
  if( ($ACT=='show' || $ACT=='export_xhtml') && !$REV){
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

  // load stylesheets
  ptln('<link rel="stylesheet" media="screen" type="text/css" href="'.DOKU_BASE.'lib/exe/css.php" />',$it);
  ptln('<link rel="stylesheet" media="print" type="text/css" href="'.DOKU_BASE.'lib/exe/css.php?print=1" />',$it);

  // load javascript
  $js_edit  = ($ACT=='edit' || $ACT=='preview') ? 1 : 0;
  $js_write = ($INFO['writable']) ? 1 : 0;
  if($js_edit && $js_write){
    ptln('<script type="text/javascript" charset="utf-8">',$it);
    ptln("NS='".$INFO['namespace']."';",$it+2);
    if($conf['useacl'] && $_SERVER['REMOTE_USER']){
      require_once(DOKU_INC.'inc/toolbar.php');
      ptln("SIG='".toolbar_signature()."';",$it+2);
    }
    ptln('</script>',$it);
  }
  ptln('<script type="text/javascript" charset="utf-8" src="'.
       DOKU_BASE.'lib/exe/js.php?edit='.$js_edit.'&amp;write='.$js_write.'"></script>',$it);
}

/**
 * Print a link
 *
 * Just builds a link.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_link($url,$name,$more=''){
  print '<a href="'.$url.'" ';
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
 *  edit        - edit/create/show button
 *  history     - old revisions
 *  recent      - recent changes
 *  login       - login/logout button - if ACL enabled
 *  index       - The index
 *  admin       - admin page - if enough rights
 *  top         - a back to top button
 *  back        - a back to parent button - if available
 *  backtomedia - returns to the mediafile upload dialog
 *                after references have been displayed
 *  backlink    - links to the list of backlinks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
function tpl_button($type){
  global $ACT;
  global $ID;
  global $NS;
  global $INFO;
  global $conf;
  global $auth;

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
      if ($parent = tpl_getparent($ID)) {
        print html_btn('back',$parent,'b',array('do' => 'show'));
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
    case 'subscription':
      if($conf['useacl'] && $ACT == 'show' && $conf['subscribers'] == 1){
        if($_SERVER['REMOTE_USER']){
          if($INFO['subscribed']){
            print html_btn('unsubscribe',$ID,'',array('do' => 'unsubscribe',));
          } else {
            print html_btn('subscribe',$ID,'',array('do' => 'subscribe',));
          }
        }
      }
      break;
    case 'backlink':
      print html_btn('backlink',$ID,'',array('do' => 'backlink'));
      break;
    case 'profile':
      if($conf['useacl'] && $_SERVER['REMOTE_USER'] &&
         $auth->canDo('Profile') && ($ACT!='profile')){
        print html_btn('profile',$ID,'',array('do' => 'profile'));
      }
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
 * backlink - links to the list of backlinks
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
  global $auth;

  switch($type){
    case 'edit':
      #most complicated type - we need to decide on current action
      if($ACT == 'show' || $ACT == 'search'){
        if($INFO['writable']){
          if($INFO['exists']){
            tpl_link(wl($ID,'do=edit&amp;rev='.$REV),
                     $pre.$lang['btn_edit'].$suf,
                     'class="action edit" accesskey="e" rel="nofollow"');
          }else{
            tpl_link(wl($ID,'do=edit&amp;rev='.$REV),
                     $pre.$lang['btn_create'].$suf,
                     'class="action create" accesskey="e" rel="nofollow"');
          }
        }else{
          tpl_link(wl($ID,'do=edit&amp;rev='.$REV),
                   $pre.$lang['btn_source'].$suf,
                   'class="action source" accesskey="v" rel="nofollow"');
        }
      }else{
          tpl_link(wl($ID,'do=show'),
                   $pre.$lang['btn_show'].$suf,
                   'class="action show" accesskey="v" rel="nofollow"');
      }
      break;
    case 'history':
      tpl_link(wl($ID,'do=revisions'),$pre.$lang['btn_revs'].$suf,'class="action revisions" accesskey="o"');
      break;
    case 'recent':
      tpl_link(wl($ID,'do=recent'),$pre.$lang['btn_recent'].$suf,'class="action recent" accesskey="r"');
      break;
    case 'index':
      tpl_link(wl($ID,'do=index'),$pre.$lang['btn_index'].$suf,'class="action index" accesskey="x"');
      break;
    case 'top':
      print '<a href="#dokuwiki__top" class="action top" accesskey="x">'.$pre.$lang['btn_top'].$suf.'</a>';
      break;
    case 'back':
      if ($ID = tpl_getparent($ID)) {
        tpl_link(wl($ID,'do=show'),$pre.$lang['btn_back'].$suf,'class="action back" accesskey="b"');
      }
      break;
    case 'login':
      if($conf['useacl']){
        if($_SERVER['REMOTE_USER']){
          tpl_link(wl($ID,'do=logout'),$pre.$lang['btn_logout'].$suf,'class="action logout"');
        }else{
          tpl_link(wl($ID,'do=login'),$pre.$lang['btn_login'].$suf,'class="action logout"');
        }
      }
      break;
    case 'admin':
      if($INFO['perm'] == AUTH_ADMIN)
        tpl_link(wl($ID,'do=admin'),$pre.$lang['btn_admin'].$suf,'class="action admin"');
      break;
   case 'subscribe':
   case 'subscription':
      if($conf['useacl'] && $ACT == 'show' && $conf['subscribers'] == 1){
        if($_SERVER['REMOTE_USER']){
          if($INFO['subscribed']) {
            tpl_link(wl($ID,'do=unsubscribe'),$pre.$lang['btn_unsubscribe'].$suf,'class="action unsubscribe"');
          } else {
            tpl_link(wl($ID,'do=subscribe'),$pre.$lang['btn_subscribe'].$suf,'class="action subscribe"');
          }
        }
      }
      break;
    case 'backlink':
      tpl_link(wl($ID,'do=backlink'),$pre.$lang['btn_backlink'].$suf, 'class="action backlink"');
      break;
    case 'profile':
      if($conf['useacl'] && $_SERVER['REMOTE_USER'] &&
         $auth->canDo('Profile') && ($ACT!='profile')){
        tpl_link(wl($ID,'do=profile'),$pre.$lang['btn_profile'].$suf, 'class="action profile"');
      }
      break;
    default:
      print '[unknown link type]';
  }
}

/**
 * Print the search form
 *
 * If the first parameter is given a div with the ID 'qsearch_out' will
 * be added which instructs the ajax pagequicksearch to kick in and place
 * its output into this div. The second parameter controls the propritary
 * attribute autocomplete. If set to false this attribute will be set with an
 * value of "off" to instruct the browser to disable it's own built in
 * autocompletion feature (MSIE and Firefox)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_searchform($ajax=true,$autocomplete=true){
  global $lang;
  global $ACT;

  print '<form action="'.wl().'" accept-charset="utf-8" class="search" id="dw__search"><div class="no">';
  print '<input type="hidden" name="do" value="search" />';
  print '<input type="text" ';
  if($ACT == 'search') print 'value="'.htmlspecialchars($_REQUEST['id']).'" ';
  if(!$autocomplete) print 'autocomplete="off" ';
  print 'id="qsearch__in" accesskey="f" name="id" class="edit" />';
  print '<input type="submit" value="'.$lang['btn_search'].'" class="button" />';
  if($ajax) print '<div id="qsearch__out" class="ajax_qsearch JSpopup"></div>';
  print '</div></form>';
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
 * This code was suggested as replacement for the usual breadcrumbs.
 * It only makes sense with a deep site structure.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Nigel McNie <oracle.shinoda@gmail.com>
 * @author Sean Coates <sean@caedmon.net>
 * @link   http://wiki.splitbrain.org/wiki:tipsandtricks:hierarchicalbreadcrumbs
 * @todo   May behave strangely in RTL languages
 */
function tpl_youarehere(){
  global $conf;
  global $ID;
  global $lang;

  //check if enabled
  if(!$conf['youarehere']) return;

  $parts     = explode(':', $ID);

  print $lang['youarehere'].': ';

  //always print the startpage
  if( $a_part[0] != $conf['start']){
    if($conf['useheading']){
      $pageName = p_get_first_heading($conf['start']);
    }else{
      $pageName = $conf['start'];
    }
    tpl_link(wl($conf['start']),$pageName,'title="'.$pageName.'"');
  }

  $page = '';
  foreach ($parts as $part){
        // Skip startpage if already done
        if ($part == $conf['start']) continue;

          print ' &raquo; ';
    $page .= $part;

    if(file_exists(wikiFN($page))){
      if($conf['useheading']){
        $pageName = p_get_first_heading($page);
        $partName = $pageName;
      }else{
        $pageName = $page;
        $partName = $part;
      }
      tpl_link(wl($page),$partName,'title="'.$pageName.'"');
    }else{
      // Print the link, but mark as not-existing, as for other non-existing links
      tpl_link(wl($page),$part,'title="'.$page.'" class="wikilink2"');
      //print $page;
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
    ptln('<div class="nothing">'.$lang['nothingfound'].'</div>');
    return;
  }

  ptln('<ul>',2);
  foreach($data as $item){
    if(!$item['isimg']){
      // add file icons
      list($ext,$mime) = mimetype($item['file']);
      $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);
      $class = ' class="mediafile mf_'.$class.'"';
    }

    ptln('<li><div class="li">',4);
    ptln('<a href="javascript:mediaSelect(\':'.$item['id'].'\')"'.$class.'>'.
         utf8_decodeFN($item['file']).
         '</a>',6);

    //prepare deletion button
    if($AUTH >= AUTH_DELETE){
      $ask  = addslashes($lang['del_confirm']).'\\n';
      $ask .= addslashes($item['id']);

      $del = '<a href="'.DOKU_BASE.'lib/exe/media.php?delete='.rawurlencode($item['id']).'" '.
             'onclick="return confirm(\''.$ask.'\')" onkeypress="return confirm(\''.$ask.'\')">'.
             '<img src="'.DOKU_BASE.'lib/images/del.png" alt="'.$lang['btn_delete'].'" '.
             'title="'.$lang['btn_delete'].'" /></a>';
    }else{
      $del = '';
    }

    if($item['isimg']){
      $w = (int) $item['meta']->getField('File.Width');
      $h = (int) $item['meta']->getField('File.Height');

      ptln('('.$w.'&#215;'.$h.' '.filesize_h($item['size']).')',6);
      ptln($del.'<br />',6);
      ptln('<div class="imagemeta">',6);

      //build thumbnail
      print '<a href="javascript:mediaSelect(\':'.$item['id'].'\')">';

      if($w>120 || $h>120){
        $ratio = $item['meta']->getResizeRatio(120);
        $w = floor($w * $ratio);
        $h = floor($h * $ratio);
      }

      $src = ml($item['id'],array('w'=>$w,'h'=>$h));

      $p = array();
      $p['width']  = $w;
      $p['height'] = $h;
      $p['alt']    = $item['id'];
      $p['class']  = 'thumb';
      $att = buildAttributes($p);

      print '<img src="'.$src.'" '.$att.' />';
      print '</a>';

      //read EXIF/IPTC data
      $t = $item['meta']->getField('IPTC.Headline');
      if($t) print '<strong>'.htmlspecialchars($t).'</strong><br />';

      $t = $item['meta']->getField(array('IPTC.Caption','EXIF.UserComment',
                                         'EXIF.TIFFImageDescription',
                                         'EXIF.TIFFUserComment'));
      if($t) print htmlspecialchars($t).'<br />';

      $t = $item['meta']->getField(array('IPTC.Keywords','IPTC.Category'));
      if($t) print '<em>'.htmlspecialchars($t).'</em><br />';

      //add edit button
      if($AUTH >= AUTH_UPLOAD && $item['meta']->getField('File.Mime') == 'image/jpeg'){
        print '<a href="'.DOKU_BASE.'lib/exe/media.php?edit='.rawurlencode($item['id']).'">';
        print '<img src="'.DOKU_BASE.'lib/images/edit.gif" alt="'.$lang['metaedit'].'" title="'.$lang['metaedit'].'" />';
        print '</a>';
      }

      ptln('</div>',6);
    }else{
      ptln ('('.filesize_h($item['size']).')',6);
      ptln($del,6);
    }
    ptln('</div></li>',4);
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

  ptln('<form action="'.DOKU_BASE.'lib/exe/media.php" id="dw__upload"'.
       ' method="post" enctype="multipart/form-data">',2);
  ptln($lang['txt_upload'].':<br />',4);
  ptln('<input type="file" name="upload" class="edit" onchange="suggestWikiname();" />',4);
  ptln('<input type="hidden" name="ns" value="'.hsc($NS).'" /><br />',4);
  ptln($lang['txt_filename'].'<br />',4);
  ptln('<input type="text" name="id" class="edit" />',4);
  ptln('<input type="submit" class="button" value="'.$lang['btn_upload'].'" accesskey="s" />',4);
  if($AUTH >= AUTH_DELETE){
    ptln('<label for="dw__ow"><input type="checkbox" name="ow" value="1" id="dw__ow" />'.$lang['txt_overwrt'].'</label>',4);
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
 * Only allowed in: detail.php, mediaedit.php
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
function tpl_img($maxwidth=0,$maxheight=0){
  global $IMG;
  $w = tpl_img_getTag('File.Width');
  $h = tpl_img_getTag('File.Height');

  //resize to given max values
  $ratio = 1;
  if($w >= $h){
    if($maxwidth && $w >= $maxwidth){
      $ratio = $maxwidth/$w;
    }elseif($maxheight && $h > $maxheight){
      $ratio = $maxheight/$h;
    }
  }else{
    if($maxheight && $h >= $maxheight){
      $ratio = $maxheight/$h;
    }elseif($maxwidth && $w > $maxwidth){
      $ratio = $maxwidth/$w;
    }
  }
  if($ratio){
    $w = floor($ratio*$w);
    $h = floor($ratio*$h);
  }

  //prepare URLs
  $url=ml($IMG,array('cache'=>$_REQUEST['cache']));
  $src=ml($IMG,array('cache'=>$_REQUEST['cache'],'w'=>$w,'h'=>$h));

  //prepare attributes
  $alt=tpl_img_getTag('Simple.Title');
  $p = array();
  if($w) $p['width']  = $w;
  if($h) $p['height'] = $h;
         $p['class']  = 'img_detail';
  if($alt){
    $p['alt']   = $alt;
    $p['title'] = $alt;
  }else{
    $p['alt'] = '';
  }
  $p = buildAttributes($p);

  print '<a href="'.$url.'">';
  print '<img src="'.$src.'" '.$p.'/>';
  print '</a>';
}

/**
 * This function inserts a 1x1 pixel gif which in reality
 * is the inexer function.
 *
 * Should be called somewhere at the very end of the main.php
 * template
 */
function tpl_indexerWebBug(){
  global $ID;
  global $INFO;
  if(!$INFO['exists']) return;

  if(isHiddenPage($ID)) return; //no need to index hidden pages

  $p = array();
  $p['src']    = DOKU_BASE.'lib/exe/indexer.php?id='.rawurlencode($ID).
                 '&'.time();
  $p['width']  = 1;
  $p['height'] = 1;
  $p['alt']    = '';
  $att = buildAttributes($p);
  print "<img $att />";
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
