<?php
/**
 * HTML output functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

  require_once(DOKU_INC.'inc/parserutils.php');

/**
 * Convenience function to quickly build a wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_wikilink($id,$name=NULL,$search=''){
  require_once(DOKU_INC.'inc/parser/xhtml.php');
  static $xhtml_renderer = NULL;
  if(is_null($xhtml_renderer)){
    $xhtml_renderer = new Doku_Renderer_xhtml(); 
  }

  return $xhtml_renderer->internallink($id,$name,$search,true);
}

/**
 * Helps building long attribute lists
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_attbuild($attributes){
  $ret = '';
  foreach ( $attributes as $key => $value ) {
    $ret .= $key.'="'.formtext($value).'" ';
  }
  return trim($ret);
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

  print p_locale_xhtml('login');
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
/*
 FIXME provide new hook
  if(@file_exists('includes/login.txt')){
    print io_cacheParse('includes/login.txt');
  }
*/
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
#  if($p) $secedit .= "</p>\n";
  $secedit .= '<div class="secedit">';
  $secedit .= html_btn('secedit',$ID,'',
                        array('do'      => 'edit',
                              'lines'   => "$section"),
                              'post');
  $secedit .= '</div>';
#  if($p) $secedit .= "\n<p>";
  return $secedit;
}

/**
 * inserts section edit buttons if wanted or removes the markers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_secedit($text,$show=true){
  global $INFO;
  if($INFO['writable'] && $show && !$INFO['rev']){
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
 * Just the back to top button (in it's own form)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_topbtn(){
  global $lang;

  $ret  = '';
  $ret  = '<a href="#top"><input type="button" class="button" value="'.$lang['btn_top'].'" onclick="window.scrollTo(0, 0)" /></a>';

  return $ret;
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
  if($conf['userewrite'] == 2){
    $script = DOKU_BASE.DOKU_SCRIPT.'/'.$id;
  }elseif($conf['userewrite']){
    $script = DOKU_BASE.$id;
  }else{
    $script = DOKU_BASE.DOKU_SCRIPT;
    $params['id'] = $id;
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
function html_show($txt=''){
  global $ID;
  global $REV;
  global $HIGH;
  //disable section editing for old revisions or in preview
  if($txt || $REV){
    $secedit = false;
  }else{
    $secedit = true;
  }
  
  if ($txt){
    //PreviewHeader
    print p_locale_xhtml('preview');
    print '<div class="preview">';
    print html_secedit(p_render('xhtml',p_get_instructions($txt),$info),$secedit);
    print '</div>';

  }else{
    if ($REV) print p_locale_xhtml('showrev');
    $html = p_wiki_xhtml($ID,$REV,true);
    $html = html_secedit($html,$secedit);
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
  require_once(DOKU_INC.'inc/search.php');
  global $conf;
  global $QUERY;
  global $ID;
  global $lang;

  print p_locale_xhtml('searchpage');
  flush();

  //show progressbar
  print '<div align="center">';
  print '<script language="JavaScript" type="text/javascript">';
  print 'showLoadBar();';
  print '</script>';
  print '<br /></div>';

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
    print '<div class="nothing">'.$lang['nothingfound'].'</div>';
  }

  //hide progressbar
  print '<script language="JavaScript" type="text/javascript">';
  print 'hideLoadBar();';
  print '</script>';
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

  print p_locale_xhtml('locked');
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
  
  print p_locale_xhtml('revisions');
  print '<ul>';
  if($INFO['exists']){
    print '<li>';

    print $date;

    print ' <img src="'.DOKU_BASE.'images/blank.gif" border="0" width="15" height="11" alt="" /> ';

    print '<a class="wikilink1" href="'.wl($ID).'">'.$ID.'</a> ';

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

    print $date;

    print ' <a href="'.wl($ID,"rev=$rev,do=diff").'">';
    print '<img src="'.DOKU_BASE.'images/diff.png" border="0" width="15" height="11" title="'.$lang['diff'].'" />';
    print '</a> ';

    print '<a class="wikilink1" href="'.wl($ID,"rev=$rev").'">'.$ID.'</a> ';

    print htmlspecialchars($info['sum']);
    print ' <span class="user">(';
    print $info['ip'];
    if($info['user']) print ' '.$info['user'];
    print ')</span>';

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
  global $lang;
  $recents = getRecents(0,true);

  print p_locale_xhtml('recent');
  print '<ul>';
  foreach(array_keys($recents) as $id){
    $date = date($conf['dformat'],$recents[$id]['date']);
    print '<li>';

    print $date.' ';

    print '<a href="'.wl($id,"do=diff").'">';
    print '<img src="'.DOKU_BASE.'images/diff.png" border="0" width="15" height="11" title="'.$lang['diff'].'" />';
    print '</a> ';

    print '<a href="'.wl($id,"do=revisions").'">';
    print '<img src="'.DOKU_BASE.'images/history.png" border="0" width="12" height="14" title="'.$lang['btn_revs'].'" />';
    print '</a> ';

    print html_wikilink($id,$id);

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
  require_once(DOKU_INC.'inc/search.php');
  global $conf;
  global $ID;
  $dir = $conf['datadir'];
  $ns  = cleanID($ns);
  #fixme use appropriate function
  if(empty($ns)){
    $ns = dirname(str_replace(':','/',$ID));
    if($ns == '.') $ns ='';
  }
  $ns  = utf8_encodeFN(str_replace(':','/',$ns));

  print p_locale_xhtml('index');

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
      for($i=0; $i<($item['level'] - $level); $i++){
        if ($i) $ret .= "<li class=\"clear\">\n";
        $ret .= "\n<ul class=\"$class\">\n";
      }
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
  require_once(DOKU_INC.'inc/search.php');
  global $ID;
  global $conf;

  if(preg_match('#^(.*):(.*)$#',$ID,$matches)){
    $opts['ns']   = $matches[1];
    $opts['name'] = $matches[2];
  }else{
    $opts['ns']   = '';
    $opts['name'] = $ID;
  }

  print p_locale_xhtml('backlinks');

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
  require_once(DOKU_INC.'inc/DifferenceEngine.php');
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
    if($REV){
      $r = $REV;
    }else{
      //use last revision if none given
      $revs = getRevisions($ID);
      $r = $revs[0];
    }

    $df  = new Diff(split("\n",htmlspecialchars(rawWiki($ID,$r))),
                    split("\n",htmlspecialchars(rawWiki($ID,''))));
    $left  = '<a class="wikilink1" href="'.wl($ID,"rev=$r").'">'.
              $ID.' '.date($conf['dformat'],$r).'</a>';
    $right = '<a class="wikilink1" href="'.wl($ID).'">'.
              $ID.' '.date($conf['dformat'],@filemtime(wikiFN($ID))).'</a> '.
              $lang['current'];
  }
  $tdf = new TableDiffFormatter();
  if($intro) print p_locale_xhtml('diff');
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

  print p_locale_xhtml('conflict');
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

  print p_locale_xhtml('register');
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
    if ($REV) print p_locale_xhtml('editrev');
    print p_locale_xhtml($include);
  }else{
    print p_locale_xhtml('read');
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
        <textarea name="wikitext" id="wikitext" <?=$ro?> cols="80" rows="10" class="edit" onchange="textChanged = true;" onkeyup="summaryCheck();" tabindex="1"><?="\n".formText($text)?></textarea>
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
        <input type="text" class="edit" name="summary" id="summary" size="50" onkeyup="summaryCheck();" value="<?=formText($SUM)?>" tabindex="2" />
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
  $cnf['ftp']='***';

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

  print '<b>DOKU_BASE:</b><pre>';
  print DOKU_BASE;
  print '</pre>';
  
  print '<b>abs DOKU_BASE:</b><pre>';
  print DOKU_URL;
  print '</pre>';
  
  print '<b>rel DOKU_BASE:</b><pre>';
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

/**
 * Print the admin overview page
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function html_admin(){
  global $ID;
  global $lang;

  print p_locale_xhtml('admin');

  ptln('<ul class="admin">');

  // currently ACL only - more to come
  ptln('<li><a href="'.wl($ID,'do=admin&amp;page=acl').'">'.$lang['admin_acl'].'</a></li>');

  ptln('</ul>');
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
