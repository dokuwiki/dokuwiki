<?php
/**
 * HTML output functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
if(!defined('NL')) define('NL',"\n");
require_once(DOKU_INC.'inc/parserutils.php');

/**
 * Convenience function to quickly build a wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_wikilink($id,$name=NULL,$search=''){
  static $xhtml_renderer = NULL;
  if(is_null($xhtml_renderer)){
    require_once(DOKU_INC.'inc/parser/xhtml.php');
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
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @triggers HTML_LOGINFORM_INJECTION
 */
function html_login(){
  global $lang;
  global $conf;
  global $ID;
  global $auth;

  print p_locale_xhtml('login');
  ?>
    <div class="centeralign">
    <form action="<?php echo script()?>" accept-charset="<?php echo $lang['encoding']?>"
          method="post" id="dw__login">
      <fieldset>
        <legend><?php echo $lang['btn_login']?></legend>
        <input type="hidden" name="id" value="<?php echo $ID?>" />
        <input type="hidden" name="do" value="login" />
        <label class="block">
          <span><?php echo $lang['user']?></span>
          <input type="text" name="u" value="<?php echo formText($_REQUEST['u'])?>"
                 class="edit" id="focus__this" />
        </label><br />
        <label class="block">
          <span><?php echo $lang['pass']?></span>
          <input type="password" name="p" class="edit" />
        </label><br />

        <?php //bad and dirty event insert hook
        $evdata = array();
        trigger_event('HTML_LOGINFORM_INJECTION', $evdata);
        ?>

        <label for="remember__me" class="simple">
          <input type="checkbox" name="r" id="remember__me" value="1" />
          <span><?php echo $lang['remember']?></span>
        </label>
        <input type="submit" value="<?php echo $lang['btn_login']?>" class="button" />
      </fieldset>
    </form>
  <?php
    if($auth->canDo('addUser') && actionOK('register')){
      print '<p>';
      print $lang['reghere'];
      print ': <a href="'.wl($ID,'do=register').'" rel="nofollow" class="wikilink1">'.$lang['register'].'</a>';
      print '</p>';
    }

    if ($auth->canDo('modPass') && actionOK('resendpwd')) {
      print '<p>';
      print $lang['pwdforget'];
      print ': <a href="'.wl($ID,'do=resendpwd').'" rel="nofollow" class="wikilink1">'.$lang['btn_resendpwd'].'</a>';
      print '</p>';
    }
  ?>
    </div>
  <?php
/*
 FIXME provide new hook
  if(@file_exists('includes/login.txt')){
    print io_cacheParse('includes/login.txt');
  }
*/
}

/**
 * prints a section editing button
 * used as a callback in html_secedit
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_secedit_button($matches){
  global $ID;
  global $INFO;

  $section = $matches[2];
  $name = $matches[1];

  $secedit  = '';
  $secedit .= '<div class="secedit">';
  $secedit .= html_btn('secedit',$ID,'',
                        array('do'      => 'edit',
                              'lines'   => "$section",
                              'rev' => $INFO['lastmod']),
                              'post', $name);
  $secedit .= '</div>';
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
    $text = preg_replace_callback('#<!-- SECTION "(.*?)" \[(\d+-\d*)\] -->#',
                         'html_secedit_button', $text);
  }else{
    $text = preg_replace('#<!-- SECTION "(.*?)" \[(\d+-\d*)\] -->#','',$text);
  }

  return $text;
}

/**
 * Just the back to top button (in its own form)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_topbtn(){
  global $lang;

  $ret  = '';
  $ret  = '<a class="nolink" href="#dokuwiki__top"><input type="button" class="button" value="'.$lang['btn_top'].'" onclick="window.scrollTo(0, 0)" title="'.$lang['btn_top'].'" /></a>';

  return $ret;
}

/**
 * Displays a button (using its own form)
 * If tooltip exists, the access key tooltip is replaced.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_btn($name,$id,$akey,$params,$method='get',$tooltip=''){
  global $conf;
  global $lang;

  $label = $lang['btn_'.$name];

  $ret = '';
  $tip = '';

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

  $ret .= '<form class="button" method="'.$method.'" action="'.$script.'"><div class="no">';

  if(is_array($params)){
    reset($params);
    while (list($key, $val) = each($params)) {
      $ret .= '<input type="hidden" name="'.$key.'" ';
      $ret .= 'value="'.htmlspecialchars($val).'" />';
    }
  }

  if ($tooltip!='') {
      $tip = htmlspecialchars($tooltip);
  }else{
      $tip = htmlspecialchars($label);
  }

  $ret .= '<input type="submit" value="'.htmlspecialchars($label).'" class="button" ';
  if($akey){
    $tip .= ' [ALT+'.strtoupper($akey).']';
    $ret .= 'accesskey="'.$akey.'" ';
  }
  $ret .= 'title="'.$tip.'" ';
  $ret .= '/>';
  $ret .= '</div></form>';

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
    print '<br id="scroll__here" />';
    print p_locale_xhtml('preview');
    print '<div class="preview">';
    print html_secedit(p_render('xhtml',p_get_instructions($txt),$info),$secedit);
    print '<div class="clearer"></div>';
    print '</div>';

  }else{
    if ($REV) print p_locale_xhtml('showrev');
    $html = p_wiki_xhtml($ID,$REV,true);
    $html = html_secedit($html,$secedit);
    print html_hilight($html,$HIGH);
  }
}

/**
 * ask the user about how to handle an exisiting draft
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_draft(){
  global $INFO;
  global $ID;
  global $lang;
  global $conf;
  $draft = unserialize(io_readFile($INFO['draft'],false));
  $text  = cleanText(con($draft['prefix'],$draft['text'],$draft['suffix'],true));

  echo p_locale_xhtml('draft');
  ?>
  <form id="dw__editform" method="post" action="<?php echo script()?>"
   accept-charset="<?php echo $lang['encoding']?>"><div class="no">
    <input type="hidden" name="id"   value="<?php echo $ID?>" />
    <input type="hidden" name="date" value="<?php echo $draft['date']?>" /></div>
    <textarea name="wikitext" id="wiki__text" readonly="readonly" cols="80" rows="10" class="edit"><?php echo "\n".formText($text)?></textarea>

    <div id="draft__status"><?php echo $lang['draftdate'].' '.date($conf['dformat'],filemtime($INFO['draft']))?></div>

    <input class="button" type="submit" name="do[recover]" value="<?php echo $lang['btn_recover']?>" tabindex="1" />
    <input class="button" type="submit" name="do[draftdel]" value="<?php echo $lang['btn_draftdel']?>" tabindex="2" />
    <input class="button" type="submit" name="do[show]" value="<?php echo $lang['btn_cancel']?>" tabindex="3" />
  </form>
  <?php
}

/**
 * Highlights searchqueries in HTML code
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function html_hilight($html,$query){
  //split at common delimiters
  $queries = preg_split ('/[\s\'"\\\\`()\]\[?:!\.{};,#+*<>\\/]+/',$query,-1,PREG_SPLIT_NO_EMPTY);
  foreach ($queries as $q){
     $q = preg_quote($q,'/');
     $html = preg_replace_callback("/((<[^>]*)|$q)/i",'html_hilight_callback',$html);
  }
  return $html;
}

/**
 * Callback used by html_hilight()
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function html_hilight_callback($m) {
  $hlight = unslash($m[0]);
  if ( !isset($m[2])) {
    $hlight = '<span class="search_hit">'.$hlight.'</span>';
  }
  return $hlight;
}

/**
 * Run a search and display the result
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_search(){
  require_once(DOKU_INC.'inc/search.php');
  require_once(DOKU_INC.'inc/fulltext.php');
  global $conf;
  global $QUERY;
  global $ID;
  global $lang;

  print p_locale_xhtml('searchpage');
  flush();

  //check if search is restricted to namespace
  if(preg_match('/([^@]*)@([^@]*)/',$QUERY,$match)) {
      $id = cleanID($match[1]);
      if(empty($id)) {
        print '<div class="nothing">'.$lang['nothingfound'].'</div>';
        flush();
        return;
      }
  } else {
      $id = cleanID($QUERY);
  }

  //show progressbar
  print '<div class="centeralign" id="dw__loading">'.NL;
  print '<script type="text/javascript" charset="utf-8"><!--//--><![CDATA[//><!--'.NL;
  print 'showLoadBar();'.NL;
  print '//--><!]]></script>'.NL;
  print '<br /></div>'.NL;
  flush();

  //do quick pagesearch
  $data = array();

  $data = ft_pageLookup($id);
  if(count($data)){
    sort($data);
    print '<div class="search_quickresult">';
    print '<h3>'.$lang['quickhits'].':</h3>';
    print '<ul class="search_quickhits">';
    foreach($data as $id){
      print '<li> ';
      print html_wikilink(':'.$id,$conf['useheading']?NULL:$id);
      print '</li> ';
    }
    print '</ul> ';
    //clear float (see http://www.complexspiral.com/publications/containing-floats/)
    print '<div class="clearer">&nbsp;</div>';
    print '</div>';
  }
  flush();

  //do fulltext search
  $data = ft_pageSearch($QUERY,$poswords);
  if(count($data)){
    $num = 1;
    foreach($data as $id => $cnt){
      print '<div class="search_result">';
      print html_wikilink(':'.$id,$conf['useheading']?NULL:$id,$poswords);
      print ': <span class="search_cnt">'.$cnt.' '.$lang['hits'].'</span><br />';
      if($num < 15){ // create snippets for the first number of matches only #FIXME add to conf ?
        print '<div class="search_snippet">'.ft_snippet($id,$poswords).'</div>';
      }
      print '</div>';
      flush();
      $num++;
    }
  }else{
    print '<div class="nothing">'.$lang['nothingfound'].'</div>';
  }

  //hide progressbar
  print '<script type="text/javascript" charset="utf-8"><!--//--><![CDATA[//><!--'.NL;
  print 'hideLoadBar("dw__loading");'.NL;
  print '//--><!]]></script>'.NL;
  flush();
}

/**
 * Display error on locked pages
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_locked(){
  global $ID;
  global $conf;
  global $lang;
  global $INFO;

  $locktime = filemtime(wikiLockFN($ID));
  $expire = @date($conf['dformat'], $locktime + $conf['locktime'] );
  $min    = round(($conf['locktime'] - (time() - $locktime) )/60);

  print p_locale_xhtml('locked');
  print '<ul>';
  print '<li><div class="li"><strong>'.$lang['lockedby'].':</strong> '.$INFO['locked'].'</li>';
  print '<li><div class="li"><strong>'.$lang['lockexpire'].':</strong> '.$expire.' ('.$min.' min)</div></li>';
  print '</ul>';
}

/**
 * list old revisions
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function html_revisions($first=0){
  global $ID;
  global $INFO;
  global $conf;
  global $lang;
  /* we need to get one additionally log entry to be able to
   * decide if this is the last page or is there another one.
   * see html_recent()
   */
  $revisions = getRevisions($ID, $first, $conf['recent']+1);
  if(count($revisions)==0 && $first!=0){
    $first=0;
    $revisions = getRevisions($ID, $first, $conf['recent']+1);;
  }
  $hasNext = false;
  if (count($revisions)>$conf['recent']) {
    $hasNext = true;
    array_pop($revisions); // remove extra log entry
  }

  $date = @date($conf['dformat'],$INFO['lastmod']);

  print p_locale_xhtml('revisions');
  print '<ul>';
  if($INFO['exists'] && $first==0){
    print (isset($INFO['meta']) && isset($INFO['meta']['last_change']) && $INFO['meta']['last_change']['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) ? '<li class="minor">' : '<li>';
    print '<div class="li">';

    print $date;

    print ' <img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" /> ';

    print '<a class="wikilink1" href="'.wl($ID).'">'.$ID.'</a> ';

    print ' &ndash; ';
    print $INFO['sum'];
    print ' <span class="user">';
    print (empty($INFO['editor']))?('('.$lang['external_edit'].')'):$INFO['editor'];
    print '</span> ';

    print '('.$lang['current'].')';
    print '</div>';
    print '</li>';
  }

  foreach($revisions as $rev){
    $date = date($conf['dformat'],$rev);
    $info = getRevisionInfo($ID,$rev,true);

    print ($info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) ? '<li class="minor">' : '<li>';
    print '<div class="li">';
    print $date;

    if(@file_exists(wikiFN($ID,$rev))){
      print ' <a href="'.wl($ID,"rev=$rev,do=diff").'">';
      $p = array();
      $p['src']    = DOKU_BASE.'lib/images/diff.png';
      $p['width']  = 15;
      $p['height'] = 11;
      $p['title']  = $lang['diff'];
      $p['alt']    = $lang['diff'];
      $att = buildAttributes($p);
      print "<img $att />";
      print '</a> ';

      print '<a class="wikilink1" href="'.wl($ID,"rev=$rev").'">'.$ID.'</a>';
    }else{
      print ' <img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" /> ';
      print $ID;
    }

    print ' &ndash; ';
    print htmlspecialchars($info['sum']);
    print ' <span class="user">';
    if($info['user']){
      print $info['user'];
    }else{
      print $info['ip'];
    }
    print '</span>';

    print '</div>';
    print '</li>';
  }
  print '</ul>';

  print '<div class="pagenav">';
  $last = $first + $conf['recent'];
  if ($first > 0) {
    $first -= $conf['recent'];
    if ($first < 0) $first = 0;
    print '<div class="pagenav-prev">';
    print html_btn('newer',$ID,"p",array('do' => 'revisions', 'first' => $first));
    print '</div>';
  }
  if ($hasNext) {
    print '<div class="pagenav-next">';
    print html_btn('older',$ID,"n",array('do' => 'revisions', 'first' => $last));
    print '</div>';
  }
  print '</div>';

}

/**
 * display recent changes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function html_recent($first=0){
  global $conf;
  global $lang;
  global $ID;
  /* we need to get one additionally log entry to be able to
   * decide if this is the last page or is there another one.
   * This is the cheapest solution to get this information.
   */
  $recents = getRecents($first,$conf['recent'] + 1,getNS($ID));
  if(count($recents) == 0 && $first != 0){
    $first=0;
    $recents = getRecents($first,$conf['recent'] + 1,getNS($ID));
  }
  $hasNext = false;
  if (count($recents)>$conf['recent']) {
    $hasNext = true;
    array_pop($recents); // remove extra log entry
  }

  print p_locale_xhtml('recent');
  print '<ul>';

  foreach($recents as $recent){
    $date = date($conf['dformat'],$recent['date']);
    print ($recent['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) ? '<li class="minor">' : '<li>';
    print '<div class="li">';

    print $date.' ';

    print '<a href="'.wl($recent['id'],"do=diff").'">';
    $p = array();
    $p['src']    = DOKU_BASE.'lib/images/diff.png';
    $p['width']  = 15;
    $p['height'] = 11;
    $p['title']  = $lang['diff'];
    $p['alt']    = $lang['diff'];
    $att = buildAttributes($p);
    print "<img $att />";
    print '</a> ';

    print '<a href="'.wl($recent['id'],"do=revisions").'">';
    $p = array();
    $p['src']    = DOKU_BASE.'lib/images/history.png';
    $p['width']  = 12;
    $p['height'] = 14;
    $p['title']  = $lang['btn_revs'];
    $p['alt']    = $lang['btn_revs'];
    $att = buildAttributes($p);
    print "<img $att />";
    print '</a> ';

    print html_wikilink(':'.$recent['id'],$conf['useheading']?NULL:$recent['id']);
    print ' &ndash; '.htmlspecialchars($recent['sum']);

    print ' <span class="user">';
    if($recent['user']){
      print $recent['user'];
    }else{
      print $recent['ip'];
    }
    print '</span>';

    print '</div>';
    print '</li>';
  }
  print '</ul>';

  print '<div class="pagenav">';
  $last = $first + $conf['recent'];
  if ($first > 0) {
    $first -= $conf['recent'];
    if ($first < 0) $first = 0;
    print '<div class="pagenav-prev">';
    print html_btn('newer','',"p",array('do' => 'recent', 'first' => $first));
    print '</div>';
  }
  if ($hasNext) {
    print '<div class="pagenav-next">';
    print html_btn('older','',"n",array('do' => 'recent', 'first' => $last));
    print '</div>';
  }
  print '</div>';
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
  global $ID;
  $ret = '';
  $base = ':'.$item['id'];
  $base = substr($base,strrpos($base,':')+1);
  if($item['type']=='d'){
    $ret .= '<a href="'.wl($ID,'idx='.$item['id']).'" class="idx_dir"><strong>';
    $ret .= $base;
    $ret .= '</strong></a>';
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
 * Both user functions can be given as array to point to
 * a member of an object.
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
    $ret .= call_user_func($lifunc,$item);
    $ret .= '<div class="li">';

    $ret .= call_user_func($func,$item);
    $ret .= '</div>';
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
  require_once(DOKU_INC.'inc/fulltext.php');
  global $ID;
  global $conf;

  print p_locale_xhtml('backlinks');

  $data = ft_backlinks($ID);

  print '<ul class="idx">';
  foreach($data as $blink){
    print '<li><div class="li">';
    print html_wikilink(':'.$blink,$conf['useheading']?NULL:$blink);
    print '</div></li>';
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
    $df  = new Diff(explode("\n",htmlspecialchars(rawWiki($ID,''))),
                    explode("\n",htmlspecialchars(cleanText($text))));
    $left  = '<a class="wikilink1" href="'.wl($ID).'">'.
              $ID.' '.date($conf['dformat'],@filemtime(wikiFN($ID))).'</a>'.
              $lang['current'];
    $right = $lang['yours'];
  }else{
    //check if current revision exist
    if(!@file_exists(wikiFN($ID))){
      $revs = getRevisions($ID, 0, 2);
      $rc = $revs[1];
    }
    if($REV){
      $r = $REV;
    }else{
      if(empty($revs)){
        //use last revision if none given
        $revs = getRevisions($ID, 0, 1);
      }
      $r = $revs[0];
    }

    if($r){
      $df  = new Diff(explode("\n",htmlspecialchars(rawWiki($ID,$r))),
                      explode("\n",htmlspecialchars(rawWiki($ID,''))));
      $left  = '<a class="wikilink1" href="'.wl($ID,"rev=$r").'">'.
                $ID.' '.date($conf['dformat'],(isset($rc) ? $rc : $r)).'</a>';
    }else{
      $df  = new Diff(array(''),
                      explode("\n",htmlspecialchars(rawWiki($ID,''))));
      $left  = '<a class="wikilink1" href="'.wl($ID).'">'.
                $ID.'</a>';
    }
    $right = '<a class="wikilink1" href="'.wl($ID).'">'.
              $ID.' '.date($conf['dformat'],(isset($rc) ? $r : @filemtime(wikiFN($ID)))).'</a> '.
              $lang['current'];
  }
  $tdf = new TableDiffFormatter();
  if($intro) print p_locale_xhtml('diff');
  ?>
    <table class="diff">
      <tr>
        <th colspan="2">
          <?php echo $left?>
        </th>
        <th colspan="2">
          <?php echo $right?>
        </th>
      </tr>
      <?php echo $tdf->format($df)?>
    </table>
  <?php
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
  <form id="dw__editform" method="post" action="<?php echo script()?>" accept-charset="<?php echo $lang['encoding']?>">
  <div class="centeralign">
    <input type="hidden" name="id" value="<?php echo $ID?>" />
    <input type="hidden" name="wikitext" value="<?php echo formText($text)?>" />
    <input type="hidden" name="summary" value="<?php echo formText($summary)?>" />

    <input class="button" type="submit" name="do[save]" value="<?php echo $lang['btn_save']?>" accesskey="s" title="<?php echo $lang['btn_save']?> [ALT+S]" />
    <input class="button" type="submit" name="do[cancel]" value="<?php echo $lang['btn_cancel']?>" />
  </div>
  </form>
  <br /><br /><br /><br />
  <?php
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
 * @triggers HTML_REGISTERFORM_INJECTION
 */
function html_register(){
  global $lang;
  global $conf;
  global $ID;

  print p_locale_xhtml('register');
?>
  <div class="centeralign">
  <form id="dw__register" method="post" action="<?php echo wl($ID)?>" accept-charset="<?php echo $lang['encoding']?>">
  <fieldset>
    <input type="hidden" name="do" value="register" />
    <input type="hidden" name="save" value="1" />

    <legend><?php echo $lang['register']?></legend>
    <label class="block">
      <?php echo $lang['user']?>
      <input type="text" name="login" class="edit" size="50" value="<?php echo formText($_POST['login'])?>" />
    </label><br />

    <?php
      if (!$conf['autopasswd']) {
    ?>
      <label class="block">
        <?php echo $lang['pass']?>
        <input type="password" name="pass" class="edit" size="50" />
      </label><br />
      <label class="block">
        <?php echo $lang['passchk']?>
        <input type="password" name="passchk" class="edit" size="50" />
      </label><br />
    <?php
      }
    ?>

    <label class="block">
      <?php echo $lang['fullname']?>
      <input type="text" name="fullname" class="edit" size="50" value="<?php echo formText($_POST['fullname'])?>" />
    </label><br />
    <label class="block">
      <?php echo $lang['email']?>
      <input type="text" name="email" class="edit" size="50" value="<?php echo formText($_POST['email'])?>" />
    </label><br />
    <?php //bad and dirty event insert hook
    $evdata = array();
    trigger_event('HTML_REGISTERFORM_INJECTION', $evdata);
    ?>
    <input type="submit" class="button" value="<?php echo $lang['register']?>" />
  </fieldset>
  </form>
  </div>
<?php
}

/**
 * Print the update profile form
 *
 * @author Christopher Smith <chris@jalakai.co.uk>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_updateprofile(){
  global $lang;
  global $conf;
  global $ID;
  global $INFO;
  global $auth;

  print p_locale_xhtml('updateprofile');

  if (empty($_POST['fullname'])) $_POST['fullname'] = $INFO['userinfo']['name'];
  if (empty($_POST['email'])) $_POST['email'] = $INFO['userinfo']['mail'];
?>
  <div class="centeralign">
  <form id="dw__register" method="post" action="<?php echo wl($ID)?>" accept-charset="<?php echo $lang['encoding']?>">
  <fieldset style="width: 80%;">
    <input type="hidden" name="do" value="profile" />
    <input type="hidden" name="save" value="1" />

    <legend><?php echo $lang['profile']?></legend>
    <label class="block">
      <?php echo $lang['user']?>
      <input type="text" name="fullname" disabled="disabled" class="edit" size="50" value="<?php echo formText($_SERVER['REMOTE_USER'])?>" />
    </label><br />
    <label class="block">
      <?php echo $lang['fullname']?>
      <input type="text" name="fullname" <?php if(!$auth->canDo('modName')) echo 'disabled="disabled"'?> class="edit" size="50" value="<?php echo formText($_POST['fullname'])?>" />
    </label><br />
    <label class="block">
      <?php echo $lang['email']?>
      <input type="text" name="email" <?php if(!$auth->canDo('modName')) echo 'disabled="disabled"'?> class="edit" size="50" value="<?php echo formText($_POST['email'])?>" />
    </label><br /><br />

    <?php if($auth->canDo('modPass')) { ?>
    <label class="block">
      <?php echo $lang['newpass']?>
      <input type="password" name="newpass" class="edit" size="50" />
    </label><br />
    <label class="block">
      <?php echo $lang['passchk']?>
      <input type="password" name="passchk" class="edit" size="50" />
    </label><br />
    <?php } ?>

    <?php if ($conf['profileconfirm']) { ?>
      <br />
      <label class="block">
      <?php echo $lang['oldpass']?>
      <input type="password" name="oldpass" class="edit" size="50" />
    </label><br />
    <?php } ?>
    <?php //bad and dirty event insert hook
    $evdata = array();
    trigger_event('HTML_PROFILEFORM_INJECTION', $evdata);
    ?>
    <input type="submit" class="button" value="<?php echo $lang['btn_save']?>" />
    <input type="reset" class="button" value="<?php echo $lang['btn_reset']?>" />
  </fieldset>
  </form>
  </div>
<?php
}

/**
 * This displays the edit form (lots of logic included)
 *
 * @fixme    this is a huge lump of code and should be modularized
 * @triggers HTML_PAGE_FROMTEMPLATE
 * @triggers HTML_EDITFORM_INJECTION
 * @author   Andreas Gohr <andi@splitbrain.org>
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
    if($INFO['exists']){
      if($RANGE){
        list($PRE,$text,$SUF) = rawWikiSlices($RANGE,$ID,$REV);
      }else{
        $text = rawWiki($ID,$REV);
      }
    }else{
      //try to load a pagetemplate
      $data = array($ID);
      $text = trigger_event('HTML_PAGE_FROMTEMPLATE',$data,'pageTemplate',true);
    }
  }else{
    $pr = true; //preview mode
  }

  $wr = $INFO['writable'];
  if($wr){
    if ($REV) print p_locale_xhtml('editrev');
    print p_locale_xhtml($include);
    $ro=false;
  }else{
    // check pseudo action 'source'
    if(!actionOK('source')){
      msg('Command disabled: source',-1);
      return;
    }
    print p_locale_xhtml('read');
    $ro='readonly="readonly"';
  }
  if(!$DATE) $DATE = $INFO['lastmod'];


?>
  <div style="width:99%;">

   <div class="toolbar">
      <div id="draft__status"><?php if(!empty($INFO['draft'])) echo $lang['draftdate'].' '.date($conf['dformat']);?></div>
      <div id="tool__bar"><?php if(!$ro){?><a href="<?php echo DOKU_BASE?>lib/exe/mediamanager.php?ns=<?php echo $INFO['namespace']?>"
      target="_blank"><?php echo $lang['mediaselect'] ?></a><?php }?></div>

      <?php if($wr){?>
      <script type="text/javascript" charset="utf-8"><!--//--><![CDATA[//><!--
        <?php /* sets changed to true when previewed */?>
        textChanged = <?php ($pr) ? print 'true' : print 'false' ?>;
      //--><!]]></script>
      <span id="spell__action"></span>
      <div id="spell__suggest"></div>
      <?php } ?>
   </div>
   <div id="spell__result"></div>


   <form id="dw__editform" method="post" action="<?php echo script()?>" accept-charset="<?php echo $lang['encoding']?>"><div class="no">
      <input type="hidden" name="id"   value="<?php echo $ID?>" />
      <input type="hidden" name="rev"  value="<?php echo $REV?>" />
      <input type="hidden" name="date" value="<?php echo $DATE?>" />
      <input type="hidden" name="prefix" value="<?php echo formText($PRE)?>" />
      <input type="hidden" name="suffix" value="<?php echo formText($SUF)?>" />
    </div>

    <textarea name="wikitext" id="wiki__text" <?php echo $ro?> cols="80" rows="10" class="edit" tabindex="1"><?php echo "\n".formText($text)?></textarea>

    <?php //bad and dirty event insert hook
    $evdata = array('writable' => $wr);
    trigger_event('HTML_EDITFORM_INJECTION', $evdata);
    ?>

    <div id="wiki__editbar">
      <div id="size__ctl"></div>
      <?php if($wr){?>
         <div class="editButtons">
            <input class="button" id="edbtn__save" type="submit" name="do[save]" value="<?php echo $lang['btn_save']?>" accesskey="s" title="<?php echo $lang['btn_save']?> [ALT+S]" tabindex="4" />
            <input class="button" id="edbtn__preview" type="submit" name="do[preview]" value="<?php echo $lang['btn_preview']?>" accesskey="p" title="<?php echo $lang['btn_preview']?> [ALT+P]" tabindex="5" />
            <input class="button" type="submit" name="do[draftdel]" value="<?php echo $lang['btn_cancel']?>" tabindex="6" />
         </div>
      <?php } ?>
      <?php if($wr){ ?>
        <div class="summary">
           <label for="edit__summary" class="nowrap"><?php echo $lang['summary']?>:</label>
           <input type="text" class="edit" name="summary" id="edit__summary" size="50" value="<?php echo formText($SUM)?>" tabindex="2" />
           <?php html_minoredit()?>
        </div>
      <?php }?>
    </div>
  </form>
  </div>
<?php
}

/**
 * Adds a checkbox for minor edits for logged in users
 *
 * @author Andrea Gohr <andi@splitbrain.org>
 */
function html_minoredit(){
  global $conf;
  global $lang;
  // minor edits are for logged in users only
  if(!$conf['useacl'] || !$_SERVER['REMOTE_USER']){
    return;
  }

  $p = array();
  $p['name']     = 'minor';
  $p['type']     = 'checkbox';
  $p['id']       = 'minoredit';
  $p['tabindex'] = 3;
  $p['value']    = '1';
  if(!empty($_REQUEST['minor'])) $p['checked']='checked';
  $att = buildAttributes($p);

  print '<span class="nowrap">';
  print "<input $att />";
  print '<label for="minoredit">';
  print $lang['minoredit'];
  print '</label>';
  print '</span>';
}

/**
 * prints some debug info
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_debug(){
  global $conf;
  global $lang;
  global $auth;
  global $INFO;

  //remove sensitive data
  $cnf = $conf;
  $cnf['auth']='***';
  $cnf['notify']='***';
  $cnf['ftp']='***';
  $nfo = $INFO;
  $nfo['userinfo'] = '***';
  $ses = $_SESSION;
  $ses[$conf['title']]['auth'] = '***';

  print '<html><body>';

  print '<p>When reporting bugs please send all the following ';
  print 'output as a mail to andi@splitbrain.org ';
  print 'The best way to do this is to save this page in your browser</p>';

  print '<b>$INFO:</b><pre>';
  print_r($nfo);
  print '</pre>';

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

  if($auth){
    print '<b>Auth backend capabilities:</b><pre>';
    print_r($auth->cando);
    print '</pre>';
  }

  print '<b>$_SESSION:</b><pre>';
  print_r($ses);
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

function html_admin(){
  global $ID;
  global $INFO;
  global $lang;
  global $conf;

  print p_locale_xhtml('admin');

  // build menu of admin functions from the plugins that handle them
  $pluginlist = plugin_list('admin');
  $menu = array();
  foreach ($pluginlist as $p) {
    if($obj =& plugin_load('admin',$p) === NULL) continue;

    // check permissions
    if($obj->forAdminOnly() && !$INFO['isadmin']) continue;

    $menu[] = array('plugin' => $p,
                    'prompt' => $obj->getMenuText($conf['lang']),
                    'sort' => $obj->getMenuSort()
                   );
  }

  usort($menu, 'p_sort_modes');

  // output the menu
  ptln('<ul>');

  foreach ($menu as $item) {
    if (!$item['prompt']) continue;
    ptln('  <li><div class="li"><a href="'.wl($ID, 'do=admin&amp;page='.$item['plugin']).'">'.$item['prompt'].'</a></div></li>');
  }

  ptln('</ul>');
}

/**
 * Form to request a new password for an existing account
 *
 * @author Benoit Chesneau <benoit@bchesneau.info>
 */
function html_resendpwd() {
  global $lang;
  global $conf;
  global $ID;

  print p_locale_xhtml('resendpwd');
?>
  <div class="centeralign">
  <form id="dw__resendpwd" action="<?php echo wl($ID)?>" accept-charset="<?php echo $lang['encoding']?>" method="post">
    <fieldset>
      <br />
      <legend><?php echo $lang['resendpwd']?></legend>
      <input type="hidden" name="do" value="resendpwd" />
      <input type="hidden" name="save" value="1" />
      <label class="block">
        <span><?php echo $lang['user']?></span>
        <input type="text" name="login" value="<?php echo formText($_POST['login'])?>" class="edit" /><br /><br />
      </label><br />
      <input type="submit" value="<?php echo $lang['btn_resendpwd']?>" class="button" />
    </fieldset>
  </form>
  </div>
<?php
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
