<?
/**
 * HTML output functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  include_once("inc/format.php");

/**
 * Convenience function to quickly build a wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_wikilink($url,$name='',$search=''){
  global $conf;
  $link         = array();
  $link['url']  = $url;
  $link['name'] = $name;
  $link         = format_link_wiki($link);

  if($search){
    ($conf['userewrite']) ? $link['url'].='?s=' : $link['url'].='&amp;s=';
    $link['url'] .= urlencode($search);
  }

  return format_link_build($link);
}

/**
 * The loginform
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_login(){
  global $lang;
  global $conf;
  global $ID;

  print parsedLocale('login');
  ?>
    <div align="center">
    <form action="<?=script()?>" accept-charset="<?=$lang['encoding']?>" method="post">
      <fieldset>
        <legend><?=$lang['btn_login']?></legend>
        <input type="hidden" name="id" value="<?=$ID?>" />
        <input type="hidden" name="do" value="login" />
        <label>
          <span><?=$lang['user']?></span>
          <input type="text" name="u" value="<?=formText($_REQUEST['u'])?>" class="edit" />
        </label><br />
        <label>
          <span><?=$lang['pass']?></span>
          <input type="password" name="p" class="edit" />
        </label><br />
        <input type="submit" value="<?=$lang['btn_login']?>" class="button" />
        <label for="remember" class="simple">
          <input type="checkbox" name="r" id="remember" value="1" />
          <span><?=$lang['remember']?></span>
        </label>
      </fieldset>
    </form>
  <?
    if($conf['openregister']){
      print '<p>';
      print $lang['reghere'];
      print ': <a href="'.wl($ID,'do=register').'" class="wikilink1">'.$lang['register'].'</a>';
      print '</p>';
    }
  ?>
    </div>
  <?
  if(@file_exists('includes/login.txt')){
    print io_cacheParse('includes/login.txt');
  }
}

/**
 * shows the edit/source/show button dependent on current mode
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_editbutton(){
  global $ID;
  global $REV;
  global $ACT;
  global $INFO;

  if($ACT == 'show' || $ACT == 'search'){
    if($INFO['writable']){
      if($INFO['exists']){
        $r = html_btn('edit',$ID,'e',array('do' => 'edit','rev' => $REV),'post');
      }else{
        $r = html_btn('create',$ID,'e',array('do' => 'edit','rev' => $REV),'post');
      }
    }else{
      $r = html_btn('source',$ID,'v',array('do' => 'edit','rev' => $REV),'post');
    }
  }else{
    $r = html_btn('show',$ID,'v',array('do' => 'show'));
  }
  return $r;
}

/**
 * prints a section editing button
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_secedit_button($section,$p){
  global $ID;
  global $lang;
  $secedit  = '';
  if($p) $secedit .= "</p>\n";
  $secedit .= '<div class="secedit">';
  $secedit .= html_btn('secedit',$ID,'',
                        array('do'      => 'edit',
                              'lines'   => "$section"),
                              'post');
  $secedit .= '</div>';
  if($p) $secedit .= "\n<p>";
  return $secedit;
}

/**
 * inserts section edit buttons if wanted or removes the markers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_secedit($text,$show=true){
  global $INFO;
  if($INFO['writable'] && $show){
    $text = preg_replace('#<!-- SECTION \[(\d+-\d+)\] -->#e',
                         "html_secedit_button('\\1',true)",
                         $text);
    $text = preg_replace('#<!-- SECTION \[(\d+-)\] -->#e',
                         "html_secedit_button('\\1',false)",
                         $text);
  }else{
    $text = preg_replace('#<!-- SECTION \[(\d*-\d*)\] -->#e','',$text);
  }
  return $text;
}

/**
 * displays the breadcrumbs trace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_breadcrumbs(){
  global $lang;
  global $conf; 

  //check if enabled
  if(!$conf['breadcrumbs']) return;
 
  $crumbs = breadcrumbs(); //setup crumb trace
  print '<div class="breadcrumbs">';
  print $lang['breadcrumb'].':';
  foreach ($crumbs as $crumb){
    print ' &raquo; ';
    print '<a href="'.wl($crumb).'" class="breadcrumbs" onclick="return svchk()" onkeypress="return svchk()" title="'.$crumb.'">'.noNS($crumb).'</a>';
  }
  print '</div>';
}

/**
 * display the HTML head and metadata
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_head(){
  global $ID;
  global $ACT;
  global $INFO;
  global $REV;
  global $conf;
  global $lang;
  
  print '<'.'?xml version="1.0"?'.">\n";
  print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"';
  print ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
  print "\n";
?>
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$conf['lang']?>" lang="<?=$conf['lang']?>" dir="ltr">
  <head>
    <title><?=$ID?> [<?=$conf['title']?>]</title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?=$lang['encoding']?>" />
    <meta name="generator" content="DokuWiki <?=getVersion()?>" />
    <link rel="stylesheet" media="screen" type="text/css" href="<?=getBaseURL()?>style.css" />
    <link rel="stylesheet" media="print" type="text/css" href="<?=getBaseURL()?>print.css" />
    <link rel="shortcut icon" href="<?=getBaseURL()?>images/favicon.ico" />
    <link rel="start" href="<?=wl()?>" />
    <link rel="contents" href="<?=wl($ID,'do=index')?>" title="<?=$lang['index']?>" />
    <link rel="alternate" type="application/rss+xml" title="Recent Changes" href="<?=getBaseURL()?>feed.php" />
    <link rel="alternate" type="application/rss+xml" title="Current Namespace" href="<?=getBaseURL()?>feed.php?mode=list&amp;ns=<?=$INFO['namespace']?>" />
    <link rel="alternate" type="text/html" title="Plain HTML" href="<?=wl($ID,'do=export_html')?>" />
    <link rel="alternate" type="text/plain" title="Wiki Markup" href="<?=wl($ID, 'do=export_raw')?>" />
<?
  if( ($ACT=='show' || $ACT=='export_html') && !$REV){
    if($INFO['exists']){
      print '    <meta name="robots" content="index,follow" />'."\n";
      print '    <meta name="date" content="'.date('Y-m-d\TH:i:sO',$INFO['lastmod']).'" />'."\n";
    }else{
      print '    <meta name="robots" content="noindex,follow" />'."\n";
    }
  }else{
    print '    <meta name="robots" content="noindex,nofollow" />'."\n";
  }
?>

    <script language="JavaScript" type="text/javascript">
      var alertText   = '<?=$lang['qb_alert']?>';
      var notSavedYet = '<?=$lang['notsavedyet']?>';
      var baseURL     = '<?=getBaseURL()?>';
    </script>
    <script language="JavaScript" type="text/javascript" src="<?=getBaseURL()?>script.js"></script>

    <!--[if gte IE 5]>
    <style type="text/css">
      /* that IE 5+ conditional comment makes this only visible in IE 5+ */
      img { behavior: url("<?=getBaseURL()?>pngbehavior.htc"); } /* IE bugfix for transparent PNGs */
    </style>
    <![endif]-->

    <?@include("includes/meta.html")?>
  </head>
<?
}

/**
 * Displays a button (using it's own form)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_btn($name,$id,$akey,$params,$method='get'){
  global $conf;
  global $lang;
  
  $label = $lang['btn_'.$name];
  
  $ret = '';

  //filter id (without urlencoding)
  $id = idfilter($id,false);

  //make nice URLs even for buttons  
  $link = getBaseURL().'/';
  $link = preg_replace('#//$#','/',$link);
  if(!$conf['userewrite']){
    $script = $link.'doku.php';
    $params['id'] = $id;
  }else{
    $script = $link.$id;
  }
  
  $ret .= '<form class="button" method="'.$method.'" action="'.$script.'" onsubmit="return svchk()">';
  
  reset($params);
  while (list($key, $val) = each($params)) {
    $ret .= '<input type="hidden" name="'.$key.'" ';
    $ret .= 'value="'.htmlspecialchars($val).'" />';
  }
  
  $ret .= '<input type="submit" value="'.htmlspecialchars($label).'" class="button" ';
  if($akey){
    $ret .= 'title="ALT+'.strtoupper($akey).'" ';
    $ret .= 'accesskey="'.$akey.'" ';
  }
  $ret .= '/>';
  $ret .= '</form>';

  return $ret;
}

/**
 * Check for the given permission or prints an error
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_acl($perm){
  global $INFO;
  if($INFO['perm'] >= $perm) return true;

  print parsedLocale('denied');
  return false;
}

/**
 * Displays the overall page header and calls html_head()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_header(){
  global $ID;
  global $REV;
  global $lang;
  global $conf;
  html_head();
?>
<body>
  <div class="all">
  <?
    @include("includes/topheader.html");
    html_msgarea();
  ?>
  <div class="stylehead">
    <div class="header">
      <div class="pagename">
        [[<a href="<?=wl($ID,'do=backlink')?>" onclick="return svchk()" onkeypress="return svchk()"><?=$ID?></a>]]
      </div>
      <div class="logo">
        <a href="<?=wl()?>" name="top" accesskey="h" title="[ALT+H]" onclick="return svchk()" onkeypress="return svchk()"><?=$conf['title']?></a>
      </div>
    </div>
    <?@include("includes/header.html")?>

    <div class="bar" id="bar_top">
      <div class="bar-left" id="bar_topleft">
        <?=html_editbutton()?>
        <?=html_btn(revs,$ID,'r',array('do' => 'revisions'))?>
      </div>

      <div class="bar-right" id="bar_topright">
        <?=html_btn(recent,'','r',array('do' => 'recent'))?>
        <form action="<?=wl()?>" accept-charset="<?=$lang['encoding']?>">
          <input type="hidden" name="do" value="search" />
          <input type="text" name="id" class="edit" />
          <input type="submit" value="<?=$lang['btn_search']?>" class="button" />
        </form>&nbsp;
      </div>
    </div>
  
    <?
      flush();
      html_breadcrumbs();
      @include("includes/pageheader.html");
    ?>
  </div>
  <div class="page">
  <!-- wikipage start -->
<?
}

/**
 * display document and user info
 *
 * Displays some Metadata like who's logged in and the last modified
 * date - do not confuse this with the HTML meta header.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_metainfo(){
  global $conf;
  global $lang;
  global $INFO;
  global $REV;

  $fn = $INFO['filepath'];
  if(!$conf['fullpath']){
    if($REV){
      $fn = str_replace(realpath($conf['olddir']).DIRECTORY_SEPARATOR,'',$fn);
    }else{
      $fn = str_replace(realpath($conf['datadir']).DIRECTORY_SEPARATOR,'',$fn);
    }
  }
  $date = date($conf['dformat'],$INFO['lastmod']);

  print '<div class="meta">';
  if($_SERVER['REMOTE_USER']){
    print '<div class="user">';
    print $lang['loggedinas'].': '.$_SERVER['REMOTE_USER'];
    print '</div>';
  }
  print ' &nbsp; ';
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
  print '</div>';
}

/**
 * Diplay the overall footer
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_footer(){
  global $ID;
  global $REV;
  global $INFO;
  global $lang;
  global $conf;
?>
  <!-- wikipage stop -->
  </div>
  <div class="clearer">&nbsp;</div>
  <div class="stylefoot">
    <?
      flush();
      @include("includes/pagefooter.html");
      html_metainfo();
    ?>
    <div class="bar" id="bar_bottom">
      <div class="bar-left" id="bar_bottomleft">
        <?=html_editbutton()?>
        <?=html_btn(revs,$ID,'r',array('do' => 'revisions'))?>
      </div>
    
      <div class="bar-right" id="bar_bottomright">
        <?
          if($conf['useacl']){
            if($_SERVER['REMOTE_USER']){
              print html_btn('logout',$ID,'',array('do' => 'logout',));
            }else{
              print html_btn('login',$ID,'',array('do' => 'login'));
            }
          }
        ?>
        <?=html_btn(index,$ID,'x',array('do' => 'index'))?>
        <a href="#top"><input type="button" class="button" value="<?=$lang['btn_top']?>" /></a>&nbsp;
      </div>
    </div>
  </div>
  <?@include("includes/footer.html")?>
  </div>
  </body>
  </html>
<?
}

/**
 * Print the table of contents
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_toc($toc){
  global $lang;
  $ret  = '';
  $ret .= '<div class="toc">';
  $ret .=   '<div class="tocheader">';
  $ret .=      $lang['toc'];
  $ret .=     ' <script type="text/javascript">';
  $ret .=     'showTocToggle("+","-")';
  $ret .=     '</script>';
  $ret .=   '</div>';
  $ret .=   '<div id="tocinside">';
  $ret .=   html_buildlist($toc,'toc','html_list_toc');
  $ret .=   '</div>';
  $ret .= '</div>';
  return $ret;
}

/**
 * TOC item formatter
 *
 * User function for html_buildlist()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_list_toc($item){
  $ret  = '';
  $ret .= '<a href="#'.$item['id'].'" class="toc">';
  $ret .= $item['name'];
  $ret .= '</a>';
  return $ret;
}

/**
 * show a wiki page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_show($text=''){
  global $ID;
  global $REV;
  global $HIGH;
  //disable section editing for old revisions or in preview
  if($text || $REV){
    global $parser;
    $parser['secedit'] = false;
  }

  if ($text){
    //PreviewHeader
    print parsedLocale('preview');
    print '<div class="preview">';
    print html_secedit(parse($text),false);
    print '</div>';
  }else{
    if ($REV) print parsedLocale('showrev');
    $html = parsedWiki($ID,$REV,true);
    $html = html_secedit($html);
    print html_hilight($html,$HIGH);
  }
}

/**
 * Highlights searchqueries in HTML code
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_hilight($html,$query){
  $queries = preg_split ("/\s/",$query,-1,PREG_SPLIT_NO_EMPTY);
  foreach ($queries as $q){
    $q = preg_quote($q,'/');
    $html = preg_replace("/((<[^>]*)|$q)/ie", '"\2"=="\1"? "\1":"<span class=\"search_hit\">\1</span>"', $html);
  }
  return $html;
}

/**
 * Run a search and display the result
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_search(){
  require_once("inc/search.php");
  global $conf;
  global $QUERY;
  global $ID;
  global $lang;

  print parsedLocale('searchpage');
  flush();

  //do quick pagesearch
  $data = array();
  search($data,$conf['datadir'],'search_pagename',array(query => cleanID($QUERY)));
  if(count($data)){
    sort($data);
    print '<div class="search_quickresult">';
    print '<b>'.$lang[quickhits].':</b><br />';
    foreach($data as $row){
      print '<div class="search_quickhits">';
      print html_wikilink(':'.$row['id'],$row['id']);
      print '</div> ';
    }
    //clear float (see http://www.complexspiral.com/publications/containing-floats/)
    print '<div class="clearer">&nbsp;</div>';
    print '</div>';
  }
  flush();

  //do fulltext search
  $data = array();
  search($data,$conf['datadir'],'search_fulltext',array(query => utf8_strtolower($QUERY)));
  if(count($data)){
    usort($data,'sort_search_fulltext');
    foreach($data as $row){
      print '<div class="search_result">';
      print html_wikilink(':'.$row['id'],$row['id'],$QUERY);
      print ': <span class="search_cnt">'.$row['count'].' '.$lang['hits'].'</span><br />';
      print '<div class="search_snippet">'.$row['snippet'].'</div>';
      print '</div>';
    }
  }else{
    print '<div align="center">'.$lang['nothingfound'].'</div>';
  }
}

/**
 * Display error on locked pages
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_locked($ip){
  global $ID;
  global $conf;
  global $lang;
  
  $locktime = filemtime(wikiFN($ID).'.lock');
  $expire = @date($conf['dformat'], $locktime + $conf['locktime'] );
  $min    = round(($conf['locktime'] - (time() - $locktime) )/60);

  print parsedLocale('locked');
  print '<ul>';
  print '<li><b>'.$lang['lockedby'].':</b> '.$ip.'</li>';
  print '<li><b>'.$lang['lockexpire'].':</b> '.$expire.' ('.$min.' min)</li>';
  print '</ul>';
}

/**
 * list old revisions
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_revisions(){
  global $ID;
  global $INFO;
  global $conf;
  global $lang;
  $revisions = getRevisions($ID); 
  $date = @date($conf['dformat'],$INFO['lastmod']);
  
  print parsedLocale('revisions');
  print '<ul>';
  if($INFO['exists']){
    print '<li>';
    print $date.' <a class="wikilink1" href="'.wl($ID).'">'.$ID.'</a> ';

    print $INFO['sum'];
    print ' <span class="user">(';
    print $INFO['ip'];
    if($INFO['user']) print ' '.$INFO['user'];
    print ')</span> ';

    print '('.$lang['current'].')';
    print '</li>';
  }

  foreach($revisions as $rev){
    $date = date($conf['dformat'],$rev);
    $info = getRevisionInfo($ID,$rev);

    print '<li>';
    print $date.' <a class="wikilink1" href="'.wl($ID,"rev=$rev").'">'.$ID.'</a> ';
    print $info['sum'];
    print ' <span class="user">(';
    print $info['ip'];
    if($info['user']) print ' '.$info['user'];
    print ')</span> ';

    print '<a href="'.wl($ID,"rev=$rev,do=diff").'">';
    print '<img src="'.getBaseURL().'images/diff.png" border="0" width="15" height="11" title="'.$lang['diff'].'" />';
    print '</a>';
    print '</li>';
  }
  print '</ul>';
}

/**
 * display recent changes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_recent(){
  global $conf;
  $recents = getRecents(0,true);

  print parsedLocale('recent');
  print '<ul>';
  foreach(array_keys($recents) as $id){
    $date = date($conf['dformat'],$recents[$id]['date']);
    print '<li>';
    print $date.' '.html_wikilink($id,$id);
    print ' '.htmlspecialchars($recents[$id]['sum']);
    print ' <span class="user">(';
    print $recents[$id]['ip'];
    if($recents[$id]['user']) print ' '.$recents[$id]['user'];
    print ')</span>';
    print '</li>';
  }
  print '</ul>';
}

/**
 * Display page index
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_index($ns){
  require_once("inc/search.php");
  global $conf;
  global $ID;
  $dir = $conf['datadir'];
  $ns  = cleanID($ns);
  if(empty($ns)){
    $ns = dirname(str_replace(':','/',$ID));
    if($ns == '.') $ns ='';
  }
  $ns  = str_replace(':','/',$ns);

  print parsedLocale('index');

  $data = array();
  search($data,$conf['datadir'],'search_index',array('ns' => $ns));
  print html_buildlist($data,'idx','html_list_index','html_li_index');
}

/**
 * Index item formatter
 *
 * User function for html_buildlist()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_list_index($item){
  $ret = '';
  $base = ':'.$item['id'];
  $base = substr($base,strrpos($base,':')+1);
  if($item['type']=='d'){
    $ret .= '<a href="'.wl($ID,'idx='.$item['id']).'" class="idx_dir">';
    $ret .= $base;
    $ret .= '</a>';
  }else{
    $ret .= html_wikilink(':'.$item['id']);
  }
  return $ret;
}

/**
 * Index List item
 *
 * This user function is used in html_build_lidt to build the
 * <li> tags for namespaces when displaying the page index
 * it gives different classes to opened or closed "folders"
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_li_index($item){
  if($item['type'] == "f"){
    return '<li class="level'.$item['level'].'">';
  }elseif($item['open']){
    return '<li class="open">';
  }else{
    return '<li class="closed">';
  }
}

/**
 * Default List item
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_li_default($item){
  return '<li class="level'.$item['level'].'">';
}

/**
 * Build an unordered list
 *
 * Build an unordered list from the given $data array
 * Each item in the array has to have a 'level' property
 * the item itself gets printed by the given $func user
 * function. The second and optional function is used to
 * print the <li> tag. Both user function need to accept
 * a single item.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_buildlist($data,$class,$func,$lifunc='html_li_default'){
  $level = 0;
  $opens = 0;
  $ret   = '';

  foreach ($data as $item){

    if( $item['level'] > $level ){
      //open new list
      $ret .= "\n<ul class=\"$class\">\n";
    }elseif( $item['level'] < $level ){
      //close last item
      $ret .= "</li>\n";
      for ($i=0; $i<($level - $item['level']); $i++){
        //close higher lists
        $ret .= "</ul>\n</li>\n";
      }
    }else{
      //close last item
      $ret .= "</li>\n";
    }

    //remember current level 
    $level = $item['level'];

    //print item
    $ret .= $lifunc($item); //user function
    $ret .= '<span class="li">';
    $ret .= $func($item); //user function
    $ret .= '</span>';
  }

  //close remaining items and lists
  for ($i=0; $i < $level; $i++){
    $ret .= "</li></ul>\n";
  }

  return $ret;
}

/**
 * display backlinks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_backlinks(){
  require_once("inc/search.php");
  global $ID;
  global $conf;

  if(preg_match('#^(.*):(.*)$#',$ID,$matches)){
    $opts['ns']   = $matches[1];
    $opts['name'] = $matches[2];
  }else{
    $opts['ns']   = '';
    $opts['name'] = $ID;
  }

  print parsedLocale('backlinks');

  $data = array();
  search($data,$conf['datadir'],'search_backlinks',$opts);
  sort($data);

  print '<ul class="idx">';
  foreach($data as $row){
    print '<li>';
    print html_wikilink(':'.$row['id'],$row['id']);
    print '</li>';
  }
  print '</ul>';
}

/**
 * show diff
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_diff($text='',$intro=true){
  require_once("inc/DifferenceEngine.php");
  global $ID;
  global $REV;
  global $lang;
  global $conf;
  if($text){
    $df  = new Diff(split("\n",htmlspecialchars(rawWiki($ID,''))),
                    split("\n",htmlspecialchars(cleanText($text))));
    $left  = '<a class="wikilink1" href="'.wl($ID).'">'.
              $ID.' '.date($conf['dformat'],@filemtime(wikiFN($ID))).'</a>'.
              $lang['current'];
    $right = $lang['yours'];
  }else{
    $df  = new Diff(split("\n",htmlspecialchars(rawWiki($ID,$REV))),
                    split("\n",htmlspecialchars(rawWiki($ID,''))));
    $left  = '<a class="wikilink1" href="'.wl($ID,"rev=$REV").'">'.
              $ID.' '.date($conf['dformat'],$REV).'</a>';
    $right = '<a class="wikilink1" href="'.wl($ID).'">'.
              $ID.' '.date($conf['dformat'],@filemtime(wikiFN($ID))).'</a> '.
              $lang['current'];
  }
  $tdf = new TableDiffFormatter();
  if($intro) print parsedLocale('diff');
  ?>
    <table class="diff" width="100%">
      <tr>
        <td colspan="2" width="50%" class="diff-header">
          <?=$left?>
        </td>
        <td colspan="2" width="50%" class="diff-header">
          <?=$right?>
        </td>
      </tr>
      <?=$tdf->format($df)?>
    </table>
  <?
}

/**
 * show warning on conflict detection
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_conflict($text,$summary){
  global $ID;
  global $lang;

  print parsedLocale('conflict');
  ?>
  <form name="editform" method="post" action="<?=script()?>" accept-charset="<?=$lang['encoding']?>">
  <input type="hidden" name="id" value="<?=$ID?>" />
  <input type="hidden" name="wikitext" value="<?=formText($text)?>" />
  <input type="hidden" name="summary" value="<?=formText($summary)?>" />
  
  <div align="center">
    <input class="button" type="submit" name="do" value="<?=$lang['btn_save']?>" accesskey="s" title="[ALT+S]" />
    <input class="button" type="submit" name="do" value="<?=$lang['btn_cancel']?>" />
  </div>
  </form>
  <br /><br /><br /><br />
  <?
}

/**
 * Prints the global message array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_msgarea(){
  global $MSG;

  if(!isset($MSG)) return;

  foreach($MSG as $msg){
    print '<div class="'.$msg['lvl'].'">';
    print $msg['msg'];
    print '</div>';
  }
}

/**
 * Prints the registration form
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_register(){
  global $lang;
  global $ID;

  print parsedLocale('register');
?>
  <div align="center">
  <form name="register" method="post" action="<?=wl($ID)?>" accept-charset="<?=$lang['encoding']?>">
  <input type="hidden" name="do" value="register" />
  <input type="hidden" name="save" value="1" />
  <fieldset>
    <legend><?=$lang['register']?></legend>
    <label>
      <?=$lang['user']?>
      <input type="text" name="login" class="edit" size="50" value="<?=formText($_POST['login'])?>" />
    </label><br />
    <label>
      <?=$lang['fullname']?>
      <input type="text" name="fullname" class="edit" size="50" value="<?=formText($_POST['fullname'])?>" />
    </label><br />
    <label>
      <?=$lang['email']?>
      <input type="text" name="email" class="edit" size="50" value="<?=formText($_POST['email'])?>" />
    </label><br />
    <input type="submit" class="button" value="<?=$lang['register']?>" />
  </fieldset>
  </form>
  </div>
<?
}

/**
 * This displays the edit form (lots of logic included)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_edit($text=null,$include='edit'){ //FIXME: include needed?
  global $ID;
  global $REV;
  global $DATE;
  global $RANGE;
  global $PRE;
  global $SUF;
  global $INFO;
  global $SUM;
  global $lang;
  global $conf;

  //check for create permissions first
  if(!$INFO['exists'] && !html_acl(AUTH_CREATE)) return;

  //set summary default
  if(!$SUM){
    if($REV){
      $SUM = $lang['restored'];
    }elseif(!$INFO['exists']){
      $SUM = $lang['created'];
    }
  }

  //no text? Load it!
  if(!isset($text)){
    $pr = false; //no preview mode
    if($RANGE){
      list($PRE,$text,$SUF) = rawWikiSlices($RANGE,$ID,$REV);
    }else{
      $text = rawWiki($ID,$REV);
    }
  }else{
    $pr = true; //preview mode
  }

  $wr = $INFO['writable'];
  if($wr){
    if ($REV) print parsedLocale('editrev');
    print parsedLocale($include);
  }else{
    print parsedLocale('read');
    $ro='readonly="readonly"';
  }
  if(!$DATE) $DATE = $INFO['lastmod'];
?>
  <form name="editform" method="post" action="<?=script()?>" accept-charset="<?=$lang['encoding']?>" onsubmit="return svchk()">
  <input type="hidden" name="id"   value="<?=$ID?>" />
  <input type="hidden" name="rev"  value="<?=$REV?>" />
  <input type="hidden" name="date" value="<?=$DATE?>" />
  <input type="hidden" name="prefix" value="<?=formText($PRE)?>" />
  <input type="hidden" name="suffix" value="<?=formText($SUF)?>" />
  <table style="width:99%">
    <tr>
      <td class="toolbar" colspan="3">
        <?if($wr){?>
        <script language="JavaScript" type="text/javascript">
          <?/* sets changed to true when previewed */?>
          textChanged = <? ($pr) ? print 'true' : print 'false' ?>;
          
          formatButton('images/bold.png','<?=$lang['qb_bold']?>','**','**','<?=$lang['qb_bold']?>','b');
          formatButton('images/italic.png','<?=$lang['qb_italic']?>',"\/\/","\/\/",'<?=$lang['qb_italic']?>','i');
          formatButton('images/underline.png','<?=$lang['qb_underl']?>','__','__','<?=$lang['qb_underl']?>','u');
          formatButton('images/code.png','<?=$lang['qb_code']?>','\'\'','\'\'','<?=$lang['qb_code']?>','c');

          formatButton('images/fonth1.png','<?=$lang['qb_h1']?>','====== ',' ======\n','<?=$lang['qb_h1']?>','1');
          formatButton('images/fonth2.png','<?=$lang['qb_h2']?>','===== ',' =====\n','<?=$lang['qb_h2']?>','2');
          formatButton('images/fonth3.png','<?=$lang['qb_h3']?>','==== ',' ====\n','<?=$lang['qb_h3']?>','3');
          formatButton('images/fonth4.png','<?=$lang['qb_h4']?>','=== ',' ===\n','<?=$lang['qb_h4']?>','4');
          formatButton('images/fonth5.png','<?=$lang['qb_h5']?>','== ',' ==\n','<?=$lang['qb_h5']?>','5');

          formatButton('images/link.png','<?=$lang['qb_link']?>','[[',']]','<?=$lang['qb_link']?>','l');
          formatButton('images/extlink.png','<?=$lang['qb_extlink']?>','[[',']]','http://www.example.com|<?=$lang['qb_extlink']?>');

          formatButton('images/list.png','<?=$lang['qb_ol']?>','  - ','\n','<?=$lang['qb_ol']?>');
          formatButton('images/list_ul.png','<?=$lang['qb_ul']?>','  * ','\n','<?=$lang['qb_ul']?>');

          insertButton('images/rule.png','<?=$lang['qb_hr']?>','----\n');
          mediaButton('images/image.png','<?=$lang['qb_media']?>','m','<?=$INFO['namespace']?>');

          <?
          if($conf['useacl'] && $_SERVER['REMOTE_USER']){
            echo "insertButton('images/sig.png','".$lang['qb_sig']."','".html_signature()."','y');";
          }
          ?>
        </script>
        <?}?>
      </td>
    </tr>
    <tr>
      <td colspan="3">
        <textarea name="wikitext" id="wikitext" <?=$ro?> cols="80" rows="10" class="edit" onchange="textChanged = true;" tabindex="1"><?="\n".formText($text)?></textarea>
      </td>
    </tr>
    <tr>
      <td>
      <?if($wr){?>
        <input class="button" type="submit" name="do" value="<?=$lang['btn_save']?>" accesskey="s" title="[ALT+S]" onclick="textChanged=false" onkeypress="textChanged=false" tabindex="3" />
        <input class="button" type="submit" name="do" value="<?=$lang['btn_preview']?>" accesskey="p" title="[ALT+P]" onclick="textChanged=false" onkeypress="textChanged=false" tabindex="4" />
        <input class="button" type="submit" name="do" value="<?=$lang['btn_cancel']?>" tabindex="5" />
      <?}?>
      </td>
      <td>
      <?if($wr){?>
        <?=$lang['summary']?>:
        <input type="text" class="edit" name="summary" size="50" value="<?=formText($SUM)?>" tabindex="2" />
      <?}?>
      </td>
      <td align="right">
        <script type="text/javascript">
          showSizeCtl();
          <?if($wr){?>
            init_locktimer(<?=$conf['locktime']-60?>,'<?=$lang['willexpire']?>');
            document.editform.wikitext.focus();
          <?}?>
        </script>
      </td>
    </tr>
  </table>
  </form>
<?
}

/**
 * prepares the signature string as configured in the config
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_signature(){
  global $conf;
  global $INFO;

  $sig = $conf['signature'];
  $sig = strftime($sig);
  $sig = str_replace('@USER@',$_SERVER['REMOTE_USER'],$sig);
  $sig = str_replace('@NAME@',$INFO['userinfo']['name'],$sig);
  $sig = str_replace('@MAIL@',$INFO['userinfo']['mail'],$sig);
  $sig = str_replace('@DATE@',date($conf['dformat']),$sig);
  return $sig;
}

/**
 * prints some debug info
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_debug(){
  global $conf;
  global $lang;
  //remove sensitive data
  $cnf = $conf;
  $cnf['auth']='***';
  $cnf['notify']='***';

  print '<html><body>';

  print '<p>When reporting bugs please send all the following ';
  print 'output as a mail to andi@splitbrain.org ';
  print 'The best way to do this is to save this page in your browser</p>';

  print '<b>$_SERVER:</b><pre>';
  print_r($_SERVER);
  print '</pre>';

  print '<b>$conf:</b><pre>';
  print_r($cnf);
  print '</pre>';

  print '<b>abs baseURL:</b><pre>';
  print getBaseURL(true);
  print '</pre>';
  
  print '<b>rel baseURL:</b><pre>';
  print dirname($_SERVER['PHP_SELF']).'/';
  print '</pre>';

  print '<b>PHP Version:</b><pre>';
  print phpversion();
  print '</pre>';

  print '<b>locale:</b><pre>';
  print setlocale(LC_ALL,0);
  print '</pre>';

  print '<b>encoding:</b><pre>';
  print $lang['encoding'];
  print '</pre>';

  print '<b>Environment:</b><pre>';
  print_r($_ENV);
  print '</pre>';

  print '<b>PHP settings:</b><pre>';
  $inis = ini_get_all();
  print_r($inis);
  print '</pre>';

  print '</body></html>';
}

?>
