<?php
/**
 * HTML output functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');
if(!defined('NL')) define('NL',"\n");

/**
 * Convenience function to quickly build a wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_wikilink($id,$name=null,$search=''){
    static $xhtml_renderer = null;
    if(is_null($xhtml_renderer)){
        $xhtml_renderer = p_get_renderer('xhtml');
    }

    return $xhtml_renderer->internallink($id,$name,$search,true,'navigation');
}

/**
 * Helps building long attribute lists
 *
 * @deprecated Use buildAttributes instead
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_attbuild($attributes){
    $ret = '';
    foreach ( $attributes as $key => $value ) {
        $ret .= $key.'="'.formText($value).'" ';
    }
    return trim($ret);
}

/**
 * The loginform
 *
 * @author   Andreas Gohr <andi@splitbrain.org>
 */
function html_login(){
    global $lang;
    global $conf;
    global $ID;

    print p_locale_xhtml('login');
    print '<div class="centeralign">'.NL;
    $form = new Doku_Form(array('id' => 'dw__login'));
    $form->startFieldset($lang['btn_login']);
    $form->addHidden('id', $ID);
    $form->addHidden('do', 'login');
    $form->addElement(form_makeTextField('u', ((!$_REQUEST['http_credentials']) ? $_REQUEST['u'] : ''), $lang['user'], 'focus__this', 'block'));
    $form->addElement(form_makePasswordField('p', $lang['pass'], '', 'block'));
    if($conf['rememberme']) {
        $form->addElement(form_makeCheckboxField('r', '1', $lang['remember'], 'remember__me', 'simple'));
    }
    $form->addElement(form_makeButton('submit', '', $lang['btn_login']));
    $form->endFieldset();

    if(actionOK('register')){
        $form->addElement('<p>'.$lang['reghere'].': '.tpl_actionlink('register','','','',true).'</p>');
    }

    if (actionOK('resendpwd')) {
        $form->addElement('<p>'.$lang['pwdforget'].': '.tpl_actionlink('resendpwd','','','',true).'</p>');
    }

    html_form('login', $form);
    print '</div>'.NL;
}

/**
 * inserts section edit buttons if wanted or removes the markers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_secedit($text,$show=true){
    global $INFO;

    $regexp = '#<!-- EDIT(\d+) ([A-Z_]+) (?:"([^"]*)" )?\[(\d+-\d*)\] -->#';

    if(!$INFO['writable'] || !$show || $INFO['rev']){
        return preg_replace($regexp,'',$text);
    }

    return preg_replace_callback($regexp,
                'html_secedit_button', $text);
}

/**
 * prepares section edit button data for event triggering
 * used as a callback in html_secedit
 *
 * @triggers HTML_SECEDIT_BUTTON
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_secedit_button($matches){
    $data = array('secid'  => $matches[1],
                  'target' => strtolower($matches[2]),
                  'range'  => $matches[count($matches) - 1]);
    if (count($matches) === 5) {
        $data['name'] = $matches[3];
    }

    return trigger_event('HTML_SECEDIT_BUTTON', $data,
                         'html_secedit_get_button');
}

/**
 * prints a section editing button
 * used as default action form HTML_SECEDIT_BUTTON
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function html_secedit_get_button($data) {
    global $ID;
    global $INFO;

    if (!isset($data['name']) || $data['name'] === '') return;

    $name = $data['name'];
    unset($data['name']);

    $secid = $data['secid'];
    unset($data['secid']);

    return "<div class='secedit editbutton_" . $data['target'] .
                       " editbutton_" . $secid . "'>" .
           html_btn('secedit', $ID, '',
                    array_merge(array('do'  => 'edit',
                                      'rev' => $INFO['lastmod'],
                                      'summary' => '['.$name.'] '), $data),
                    'post', $name) . '</div>';
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
function html_btn($name,$id,$akey,$params,$method='get',$tooltip='',$label=false){
    global $conf;
    global $lang;

    if (!$label)
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

    $ret .= '<form class="button btn_'.$name.'" method="'.$method.'" action="'.$script.'"><div class="no">';

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

    $ret .= '<input type="submit" value="'.hsc($label).'" class="button" ';
    if($akey){
        $tip .= ' ['.strtoupper($akey).']';
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
function html_show($txt=null){
    global $ID;
    global $REV;
    global $HIGH;
    global $INFO;
    //disable section editing for old revisions or in preview
    if($txt || $REV){
        $secedit = false;
    }else{
        $secedit = true;
    }

    if (!is_null($txt)){
        //PreviewHeader
        echo '<br id="scroll__here" />';
        echo p_locale_xhtml('preview');
        echo '<div class="preview">';
        $html = html_secedit(p_render('xhtml',p_get_instructions($txt),$info),$secedit);
        if($INFO['prependTOC']) $html = tpl_toc(true).$html;
        echo $html;
        echo '<div class="clearer"></div>';
        echo '</div>';

    }else{
        if ($REV) print p_locale_xhtml('showrev');
        $html = p_wiki_xhtml($ID,$REV,true);
        $html = html_secedit($html,$secedit);
        if($INFO['prependTOC']) $html = tpl_toc(true).$html;
        $html = html_hilight($html,$HIGH);
        echo $html;
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

    print p_locale_xhtml('draft');
    $form = new Doku_Form(array('id' => 'dw__editform'));
    $form->addHidden('id', $ID);
    $form->addHidden('date', $draft['date']);
    $form->addElement(form_makeWikiText($text, array('readonly'=>'readonly')));
    $form->addElement(form_makeOpenTag('div', array('id'=>'draft__status')));
    $form->addElement($lang['draftdate'].' '. dformat(filemtime($INFO['draft'])));
    $form->addElement(form_makeCloseTag('div'));
    $form->addElement(form_makeButton('submit', 'recover', $lang['btn_recover'], array('tabindex'=>'1')));
    $form->addElement(form_makeButton('submit', 'draftdel', $lang['btn_draftdel'], array('tabindex'=>'2')));
    $form->addElement(form_makeButton('submit', 'show', $lang['btn_cancel'], array('tabindex'=>'3')));
    html_form('draft', $form);
}

/**
 * Highlights searchqueries in HTML code
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function html_hilight($html,$phrases){
    $phrases = array_filter((array) $phrases);
    $regex = join('|',array_map('ft_snippet_re_preprocess', array_map('preg_quote_cb',$phrases)));

    if ($regex === '') return $html;
    if (!utf8_check($regex)) return $html;
    $html = @preg_replace_callback("/((<[^>]*)|$regex)/ui",'html_hilight_callback',$html);
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
    global $conf;
    global $QUERY;
    global $ID;
    global $lang;

    $intro = p_locale_xhtml('searchpage');
    // allow use of placeholder in search intro
    $intro = str_replace(
                array('@QUERY@','@SEARCH@'),
                array(hsc(rawurlencode($QUERY)),hsc($QUERY)),
                $intro);
    echo $intro;
    flush();

    //show progressbar
    print '<div class="centeralign" id="dw__loading">'.NL;
    print '<script type="text/javascript" charset="utf-8"><!--//--><![CDATA[//><!--'.NL;
    print 'showLoadBar();'.NL;
    print '//--><!]]></script>'.NL;
    print '<br /></div>'.NL;
    flush();

    //do quick pagesearch
    $data = array();

    $data = ft_pageLookup($QUERY,true,useHeading('navigation'));
    if(count($data)){
        print '<div class="search_quickresult">';
        print '<h3>'.$lang['quickhits'].':</h3>';
        print '<ul class="search_quickhits">';
        foreach($data as $id => $title){
            print '<li> ';
            if (useHeading('navigation')) {
                $name = $title;
            }else{
                $ns = getNS($id);
                if($ns){
                    $name = shorten(noNS($id), ' ('.$ns.')',30);
                }else{
                    $name = $id;
                }
            }
            print html_wikilink(':'.$id,$name);
            print '</li> ';
        }
        print '</ul> ';
        //clear float (see http://www.complexspiral.com/publications/containing-floats/)
        print '<div class="clearer"></div>';
        print '</div>';
    }
    flush();

    //do fulltext search
    $data = ft_pageSearch($QUERY,$regex);
    if(count($data)){
        $num = 1;
        foreach($data as $id => $cnt){
            print '<div class="search_result">';
            print html_wikilink(':'.$id,useHeading('navigation')?null:$id,$regex);
            if($cnt !== 0){
                print ': <span class="search_cnt">'.$cnt.' '.$lang['hits'].'</span><br />';
                if($num < FT_SNIPPET_NUMBER){ // create snippets for the first number of matches only
                    print '<div class="search_snippet">'.ft_snippet($id,$regex).'</div>';
                }
                $num++;
            }
            print '</div>';
            flush();
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
    $expire = dformat($locktime + $conf['locktime']);
    $min    = round(($conf['locktime'] - (time() - $locktime) )/60);

    print p_locale_xhtml('locked');
    print '<ul>';
    print '<li><div class="li"><strong>'.$lang['lockedby'].':</strong> '.editorinfo($INFO['locked']).'</div></li>';
    print '<li><div class="li"><strong>'.$lang['lockexpire'].':</strong> '.$expire.' ('.$min.' min)</div></li>';
    print '</ul>';
}

/**
 * list old revisions
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function html_revisions($first=0, $media_id = false){
    global $ID;
    global $INFO;
    global $conf;
    global $lang;
    $id = $ID;
    /* we need to get one additionally log entry to be able to
     * decide if this is the last page or is there another one.
     * see html_recent()
     */
    if (!$media_id) $revisions = getRevisions($ID, $first, $conf['recent']+1);
    else {
        $revisions = getRevisions($media_id, $first, $conf['recent']+1, 8192, true);
        $id = $media_id;
    }

    if(count($revisions)==0 && $first!=0){
        $first=0;
        if (!$media_id) $revisions = getRevisions($ID, $first, $conf['recent']+1);
        else $revisions = getRevisions($media_id, $first, $conf['recent']+1, 8192, true);
    }
    $hasNext = false;
    if (count($revisions)>$conf['recent']) {
        $hasNext = true;
        array_pop($revisions); // remove extra log entry
    }

    if (!$media_id) $date = dformat($INFO['lastmod']);
    else $date = dformat(@filemtime(mediaFN($id)));

    if (!$media_id) print p_locale_xhtml('revisions');

    $params = array('id' => 'page__revisions');
    if ($media_id) $params['action'] = media_managerURL(array('image' => $media_id), '&');

    $form = new Doku_Form($params);
    $form->addElement(form_makeOpenTag('ul'));

    if (!$media_id) $exists = $INFO['exists'];
    else $exists = @file_exists(mediaFN($id));

    if($exists && $first==0){
        if (!$media_id && isset($INFO['meta']) && isset($INFO['meta']['last_change']) && $INFO['meta']['last_change']['type']===DOKU_CHANGE_TYPE_MINOR_EDIT)
            $form->addElement(form_makeOpenTag('li', array('class' => 'minor')));
        else
            $form->addElement(form_makeOpenTag('li'));
        $form->addElement(form_makeOpenTag('div', array('class' => 'li')));
        $form->addElement(form_makeTag('input', array(
                        'type' => 'checkbox',
                        'name' => 'rev2[]',
                        'value' => 'current')));

        $form->addElement(form_makeOpenTag('span', array('class' => 'date')));
        $form->addElement($date);
        $form->addElement(form_makeCloseTag('span'));

        $form->addElement('<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />');

        if (!$media_id) $href = wl($id);
        else $href = media_managerURL(array('image' => $id, 'tab_details' => 'view'), '&');
        $form->addElement(form_makeOpenTag('a', array(
                        'class' => 'wikilink1',
                        'href'  => $href)));
        $form->addElement($id);
        $form->addElement(form_makeCloseTag('a'));

        if ($media_id) $form->addElement(form_makeOpenTag('div'));

        if (!$media_id) {
            $form->addElement(form_makeOpenTag('span', array('class' => 'sum')));
            $form->addElement(' &ndash; ');
            $form->addElement(htmlspecialchars($INFO['sum']));
            $form->addElement(form_makeCloseTag('span'));
        }

        $form->addElement(form_makeOpenTag('span', array('class' => 'user')));
        if (!$media_id) $editor = $INFO['editor'];
        else {
            $revinfo = getRevisionInfo($id, @filemtime(fullpath(mediaFN($id))), 1024, true);
            if($revinfo['user']){
                $editor = $revinfo['user'];
            }else{
                $editor = $revinfo['ip'];
            }
        }
        $form->addElement((empty($editor))?('('.$lang['external_edit'].')'):editorinfo($editor));
        $form->addElement(form_makeCloseTag('span'));

        $form->addElement('('.$lang['current'].')');

        if ($media_id) $form->addElement(form_makeCloseTag('div'));

        $form->addElement(form_makeCloseTag('div'));
        $form->addElement(form_makeCloseTag('li'));
    }

    foreach($revisions as $rev){
        $date = dformat($rev);
        if (!$media_id) {
            $info = getRevisionInfo($id,$rev,true);
            $exists = page_exists($id,$rev);
        }  else {
            $info = getRevisionInfo($id,$rev,true,true);
            $exists = @file_exists(mediaFN($id,$rev));
        }

        if ($info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT)
            $form->addElement(form_makeOpenTag('li', array('class' => 'minor')));
        else
            $form->addElement(form_makeOpenTag('li'));
        $form->addElement(form_makeOpenTag('div', array('class' => 'li')));
        if($exists){
            $form->addElement(form_makeTag('input', array(
                            'type' => 'checkbox',
                            'name' => 'rev2[]',
                            'value' => $rev)));
        }else{
            $form->addElement('<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />');
        }

        $form->addElement(form_makeOpenTag('span', array('class' => 'date')));
        $form->addElement($date);
        $form->addElement(form_makeCloseTag('span'));

        if($exists){
            if (!$media_id) $href = wl($id,"rev=$rev,do=diff", false, '&');
            else $href = media_managerURL(array('image' => $id, 'rev' => $rev, 'mediado' => 'diff'), '&');
            $form->addElement(form_makeOpenTag('a', array('href' => $href, 'class' => 'diff_link')));
            $form->addElement(form_makeTag('img', array(
                            'src'    => DOKU_BASE.'lib/images/diff.png',
                            'width'  => 15,
                            'height' => 11,
                            'title'  => $lang['diff'],
                            'alt'    => $lang['diff'])));
            $form->addElement(form_makeCloseTag('a'));
            if (!$media_id) $href = wl($id,"rev=$rev",false,'&');
            else $href = media_managerURL(array('image' => $id, 'tab_details' => 'view', 'rev' => $rev), '&');
            $form->addElement(form_makeOpenTag('a', array('href' => $href, 'class' => 'wikilink1')));
            $form->addElement($id);
            $form->addElement(form_makeCloseTag('a'));
        }else{
            $form->addElement('<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />');
            $form->addElement($id);
        }

        if ($media_id) $form->addElement(form_makeOpenTag('div'));

        if ($info['sum']) {
            $form->addElement(form_makeOpenTag('span', array('class' => 'sum')));
            if (!$media_id) $form->addElement(' &ndash; ');
            $form->addElement(htmlspecialchars($info['sum']));
            $form->addElement(form_makeCloseTag('span'));
        }

        $form->addElement(form_makeOpenTag('span', array('class' => 'user')));
        if($info['user']){
            $form->addElement(editorinfo($info['user']));
            if(auth_ismanager()){
                $form->addElement(' ('.$info['ip'].')');
            }
        }else{
            $form->addElement($info['ip']);
        }
        $form->addElement(form_makeCloseTag('span'));

        if ($media_id) $form->addElement(form_makeCloseTag('div'));

        $form->addElement(form_makeCloseTag('div'));
        $form->addElement(form_makeCloseTag('li'));
    }
    $form->addElement(form_makeCloseTag('ul'));
    if (!$media_id) {
        $form->addElement(form_makeButton('submit', 'diff', $lang['diff2']));
    } else {
        $form->addHidden('mediado', 'diff');
        $form->addElement(form_makeButton('submit', '', $lang['diff2']));
    }
    html_form('revisions', $form);

    print '<div class="pagenav">';
    $last = $first + $conf['recent'];
    if ($first > 0) {
        $first -= $conf['recent'];
        if ($first < 0) $first = 0;
        print '<div class="pagenav-prev">';
        if ($media_id) {
            print html_btn('newer',$media_id,"p",media_managerURL(array('first' => $first), '&amp;', false, true));
        } else {
            print html_btn('newer',$id,"p",array('do' => 'revisions', 'first' => $first));
        }
        print '</div>';
    }
    if ($hasNext) {
        print '<div class="pagenav-next">';
        if ($media_id) {
            print html_btn('older',$media_id,"n",media_managerURL(array('first' => $last), '&amp;', false, true));
        } else {
            print html_btn('older',$id,"n",array('do' => 'revisions', 'first' => $last));
        }
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
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function html_recent($first=0, $show_changes='both'){
    global $conf;
    global $lang;
    global $ID;
    /* we need to get one additionally log entry to be able to
     * decide if this is the last page or is there another one.
     * This is the cheapest solution to get this information.
     */
    $flags = 0;
    if ($show_changes == 'mediafiles' && $conf['mediarevisions']) {
        $flags = RECENTS_MEDIA_CHANGES;
    } elseif ($show_changes == 'pages') {
        $flags = 0;
    } elseif ($conf['mediarevisions']) {
        $show_changes = 'both';
        $flags = RECENTS_MEDIA_PAGES_MIXED;
    }

    $recents = getRecents($first,$conf['recent'] + 1,getNS($ID),$flags);
    if(count($recents) == 0 && $first != 0){
        $first=0;
        $recents = getRecents($first,$conf['recent'] + 1,getNS($ID),$flags);
    }
    $hasNext = false;
    if (count($recents)>$conf['recent']) {
        $hasNext = true;
        array_pop($recents); // remove extra log entry
    }

    print p_locale_xhtml('recent');

    if (getNS($ID) != '')
        print '<div class="level1"><p>' . sprintf($lang['recent_global'], getNS($ID), wl('', 'do=recent')) . '</p></div>';

    $form = new Doku_Form(array('id' => 'dw__recent', 'method' => 'GET'));
    $form->addHidden('sectok', null);
    $form->addHidden('do', 'recent');
    $form->addHidden('id', $ID);

    if ($conf['mediarevisions']) {
        $form->addElement(form_makeListboxField(
                    'show_changes',
                    array(
                        'pages'      => $lang['pages_changes'],
                        'mediafiles' => $lang['media_changes'],
                        'both'       => $lang['both_changes']),
                    $show_changes,
                    $lang['changes_type'],
                    '','',
                    array('class'=>'quickselect')));

        $form->addElement(form_makeButton('submit', 'recent', $lang['btn_apply']));
    }

    $form->addElement(form_makeOpenTag('ul'));

    foreach($recents as $recent){
        $date = dformat($recent['date']);
        if ($recent['type']===DOKU_CHANGE_TYPE_MINOR_EDIT)
            $form->addElement(form_makeOpenTag('li', array('class' => 'minor')));
        else
            $form->addElement(form_makeOpenTag('li'));

        $form->addElement(form_makeOpenTag('div', array('class' => 'li')));

        if ($recent['media']) {
            $form->addElement(media_printicon($recent['id']));
        } else {
            $icon = DOKU_BASE.'lib/images/fileicons/file.png';
            $form->addElement('<img src="'.$icon.'" alt="'.$filename.'" class="icon" />');
        }

        $form->addElement(form_makeOpenTag('span', array('class' => 'date')));
        $form->addElement($date);
        $form->addElement(form_makeCloseTag('span'));

        if ($recent['media']) {
            $diff = (count(getRevisions($recent['id'], 0, 1, 8192, true)) && @file_exists(mediaFN($recent['id'])));
            if ($diff) {
                $href = media_managerURL(array('tab_details' => 'history',
                    'mediado' => 'diff', 'image' => $recent['id'], 'ns' => getNS($recent['id'])), '&');
            }
        } else {
            $href = wl($recent['id'],"do=diff", false, '&');
        }

        if ($recent['media'] && !$diff) {
            $form->addElement('<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />');
        } else {
            $form->addElement(form_makeOpenTag('a', array('class' => 'diff_link', 'href' => $href)));
            $form->addElement(form_makeTag('img', array(
                            'src'   => DOKU_BASE.'lib/images/diff.png',
                            'width' => 15,
                            'height'=> 11,
                            'title' => $lang['diff'],
                            'alt'   => $lang['diff']
                            )));
            $form->addElement(form_makeCloseTag('a'));
        }

        if ($recent['media']) {
            $href = media_managerURL(array('tab_details' => 'history',
                'image' => $recent['id'], 'ns' => getNS($recent['id'])), '&');
        } else {
            $href = wl($recent['id'],"do=revisions",false,'&');
        }
        $form->addElement(form_makeOpenTag('a', array('class' => 'revisions_link', 'href' => $href)));
        $form->addElement(form_makeTag('img', array(
                        'src'   => DOKU_BASE.'lib/images/history.png',
                        'width' => 12,
                        'height'=> 14,
                        'title' => $lang['btn_revs'],
                        'alt'   => $lang['btn_revs']
                        )));
        $form->addElement(form_makeCloseTag('a'));

        if ($recent['media']) {
            $href = media_managerURL(array('tab_details' => 'view', 'image' => $recent['id'], 'ns' => getNS($recent['id'])), '&');
            $class = (file_exists(mediaFN($recent['id']))) ? 'wikilink1' : $class = 'wikilink2';
            $form->addElement(form_makeOpenTag('a', array('class' => $class, 'href' => $href)));
            $form->addElement($recent['id']);
            $form->addElement(form_makeCloseTag('a'));
        } else {
            $form->addElement(html_wikilink(':'.$recent['id'],useHeading('navigation')?null:$recent['id']));
        }
        $form->addElement(form_makeOpenTag('span', array('class' => 'sum')));
        $form->addElement(' &ndash; '.htmlspecialchars($recent['sum']));
        $form->addElement(form_makeCloseTag('span'));

        $form->addElement(form_makeOpenTag('span', array('class' => 'user')));
        if($recent['user']){
            $form->addElement(editorinfo($recent['user']));
            if(auth_ismanager()){
                $form->addElement(' ('.$recent['ip'].')');
            }
        }else{
            $form->addElement($recent['ip']);
        }
        $form->addElement(form_makeCloseTag('span'));

        $form->addElement(form_makeCloseTag('div'));
        $form->addElement(form_makeCloseTag('li'));
    }
    $form->addElement(form_makeCloseTag('ul'));

    $form->addElement(form_makeOpenTag('div', array('class' => 'pagenav')));
    $last = $first + $conf['recent'];
    if ($first > 0) {
        $first -= $conf['recent'];
        if ($first < 0) $first = 0;
        $form->addElement(form_makeOpenTag('div', array('class' => 'pagenav-prev')));
        $form->addElement(form_makeTag('input', array(
                    'type'  => 'submit',
                    'name'  => 'first['.$first.']',
                    'value' => $lang['btn_newer'],
                    'accesskey' => 'n',
                    'title' => $lang['btn_newer'].' [N]',
                    'class' => 'button show'
                    )));
        $form->addElement(form_makeCloseTag('div'));
    }
    if ($hasNext) {
        $form->addElement(form_makeOpenTag('div', array('class' => 'pagenav-next')));
        $form->addElement(form_makeTag('input', array(
                        'type'  => 'submit',
                        'name'  => 'first['.$last.']',
                        'value' => $lang['btn_older'],
                        'accesskey' => 'p',
                        'title' => $lang['btn_older'].' [P]',
                        'class' => 'button show'
                        )));
        $form->addElement(form_makeCloseTag('div'));
    }
    $form->addElement(form_makeCloseTag('div'));
    html_form('recent', $form);
}

/**
 * Display page index
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_index($ns){
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

    echo p_locale_xhtml('index');
    echo '<div id="index__tree">';

    $data = array();
    search($data,$conf['datadir'],'search_index',array('ns' => $ns));
    echo html_buildlist($data,'idx','html_list_index','html_li_index');

    echo '</div>';
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
        $ret .= '<a href="'.wl($ID,'idx='.rawurlencode($item['id'])).'" class="idx_dir"><strong>';
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
 * This user function is used in html_buildlist to build the
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
function html_buildlist($data,$class,$func,$lifunc='html_li_default',$forcewrapper=false){
    if (count($data) === 0) {
        return '';
    }

    $start_level = $data[0]['level'];
    $level = $start_level;
    $ret   = '';
    $open  = 0;

    foreach ($data as $item){

        if( $item['level'] > $level ){
            //open new list
            for($i=0; $i<($item['level'] - $level); $i++){
                if ($i) $ret .= "<li class=\"clear\">";
                $ret .= "\n<ul class=\"$class\">\n";
                $open++;
            }
            $level = $item['level'];

        }elseif( $item['level'] < $level ){
            //close last item
            $ret .= "</li>\n";
            while( $level > $item['level'] && $open > 0 ){
                //close higher lists
                $ret .= "</ul>\n</li>\n";
                $level--;
                $open--;
            }
        } elseif ($ret !== '') {
            //close previous item
            $ret .= "</li>\n";
        }

        //print item
        $ret .= call_user_func($lifunc,$item);
        $ret .= '<div class="li">';

        $ret .= call_user_func($func,$item);
        $ret .= '</div>';
    }

    //close remaining items and lists
    $ret .= "</li>\n";
    while($open-- > 0) {
        $ret .= "</ul></li>\n";
    }

    if ($forcewrapper || $start_level < 2) {
        // Trigger building a wrapper ul if the first level is
        // 0 (we have a root object) or 1 (just the root content)
        $ret = "\n<ul class=\"$class\">\n".$ret."</ul>\n";
    }

    return $ret;
}

/**
 * display backlinks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 */
function html_backlinks(){
    global $ID;
    global $conf;
    global $lang;

    print p_locale_xhtml('backlinks');

    $data = ft_backlinks($ID);

    if(!empty($data)) {
        print '<ul class="idx">';
        foreach($data as $blink){
            print '<li><div class="li">';
            print html_wikilink(':'.$blink,useHeading('navigation')?null:$blink);
            print '</div></li>';
        }
        print '</ul>';
    } else {
        print '<div class="level1"><p>' . $lang['nothingfound'] . '</p></div>';
    }
}

function html_diff_head($l_rev, $r_rev, $id = null, $media = false) {
    global $lang;
    if ($id === null) {
        global $ID;
        $id = $ID;
    }
    $media_or_wikiFN = $media ? 'mediaFN' : 'wikiFN';
    $ml_or_wl = $media ? 'ml' : 'wl';
    $l_minor = $r_minor = '';

    if(!$l_rev){
        $l_head = '&mdash;';
    }else{
        $l_info   = getRevisionInfo($id,$l_rev,true, $media);
        if($l_info['user']){
            $l_user = editorinfo($l_info['user']);
            if(auth_ismanager()) $l_user .= ' ('.$l_info['ip'].')';
        } else {
            $l_user = $l_info['ip'];
        }
        $l_user  = '<span class="user">'.$l_user.'</span>';
        $l_sum   = ($l_info['sum']) ? '<span class="sum">'.hsc($l_info['sum']).'</span>' : '';
        if ($l_info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) $l_minor = 'class="minor"';

        $l_head_title = ($media) ? dformat($l_rev) : $id.' ['.dformat($l_rev).']';
        $l_head = '<a class="wikilink1" href="'.$ml_or_wl($id,"rev=$l_rev").'">'.
        $l_head_title.'</a>'.
        '<br />'.$l_user.' '.$l_sum;
    }

    if($r_rev){
        $r_info   = getRevisionInfo($id,$r_rev,true, $media);
        if($r_info['user']){
            $r_user = editorinfo($r_info['user']);
            if(auth_ismanager()) $r_user .= ' ('.$r_info['ip'].')';
        } else {
            $r_user = $r_info['ip'];
        }
        $r_user = '<span class="user">'.$r_user.'</span>';
        $r_sum  = ($r_info['sum']) ? '<span class="sum">'.hsc($r_info['sum']).'</span>' : '';
        if ($r_info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) $r_minor = 'class="minor"';

        $r_head_title = ($media) ? dformat($r_rev) : $id.' ['.dformat($r_rev).']';
        $r_head = '<a class="wikilink1" href="'.$ml_or_wl($id,"rev=$r_rev").'">'.
        $r_head_title.'</a>'.
        '<br />'.$r_user.' '.$r_sum;
    }elseif($_rev = @filemtime($media_or_wikiFN($id))){
        $_info   = getRevisionInfo($id,$_rev,true, $media);
        if($_info['user']){
            $_user = editorinfo($_info['user']);
            if(auth_ismanager()) $_user .= ' ('.$_info['ip'].')';
        } else {
            $_user = $_info['ip'];
        }
        $_user = '<span class="user">'.$_user.'</span>';
        $_sum  = ($_info['sum']) ? '<span class="sum">'.hsc($_info['sum']).'</span>' : '';
        if ($_info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) $r_minor = 'class="minor"';

        $r_head_title = ($media) ? dformat($_rev) : $id.' ['.dformat($_rev).']';
        $r_head  = '<a class="wikilink1" href="'.$ml_or_wl($id).'">'.
        $r_head_title.'</a> '.
        '('.$lang['current'].')'.
        '<br />'.$_user.' '.$_sum;
    }else{
        $r_head = '&mdash; ('.$lang['current'].')';
    }

    return array($l_head, $r_head, $l_minor, $r_minor);
}

/**
 * show diff
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $text - compare with this text with most current version
 * @param  bool   $intr - display the intro text
 */
function html_diff($text='',$intro=true,$type=null){
    global $ID;
    global $REV;
    global $lang;
    global $conf;

    if(!$type) $type = $_REQUEST['difftype'];
    if($type != 'inline') $type = 'sidebyside';

    // we're trying to be clever here, revisions to compare can be either
    // given as rev and rev2 parameters, with rev2 being optional. Or in an
    // array in rev2.
    $rev1 = $REV;

    if(is_array($_REQUEST['rev2'])){
        $rev1 = (int) $_REQUEST['rev2'][0];
        $rev2 = (int) $_REQUEST['rev2'][1];

        if(!$rev1){
            $rev1 = $rev2;
            unset($rev2);
        }
    }else{
        $rev2 = (int) $_REQUEST['rev2'];
    }

    $r_minor = '';
    $l_minor = '';

    if($text){                      // compare text to the most current revision
        $l_rev   = '';
        $l_text  = rawWiki($ID,'');
        $l_head  = '<a class="wikilink1" href="'.wl($ID).'">'.
            $ID.' '.dformat((int) @filemtime(wikiFN($ID))).'</a> '.
            $lang['current'];

        $r_rev   = '';
        $r_text  = cleanText($text);
        $r_head  = $lang['yours'];
    }else{
        if($rev1 && $rev2){            // two specific revisions wanted
            // make sure order is correct (older on the left)
            if($rev1 < $rev2){
                $l_rev = $rev1;
                $r_rev = $rev2;
            }else{
                $l_rev = $rev2;
                $r_rev = $rev1;
            }
        }elseif($rev1){                // single revision given, compare to current
            $r_rev = '';
            $l_rev = $rev1;
        }else{                        // no revision was given, compare previous to current
            $r_rev = '';
            $revs = getRevisions($ID, 0, 1);
            $l_rev = $revs[0];
            $REV = $l_rev; // store revision back in $REV
        }

        // when both revisions are empty then the page was created just now
        if(!$l_rev && !$r_rev){
            $l_text = '';
        }else{
            $l_text = rawWiki($ID,$l_rev);
        }
        $r_text = rawWiki($ID,$r_rev);

        list($l_head, $r_head, $l_minor, $r_minor) = html_diff_head($l_rev, $r_rev);
    }

    $df = new Diff(explode("\n",htmlspecialchars($l_text)),
        explode("\n",htmlspecialchars($r_text)));

    if($type == 'inline'){
        $tdf = new InlineDiffFormatter();
    } else {
        $tdf = new TableDiffFormatter();
    }



    if($intro) print p_locale_xhtml('diff');

    if (!$text) {
        ptln('<div class="diffoptions">');

        $form = new Doku_Form(array('action'=>wl()));
        $form->addHidden('id',$ID);
        $form->addHidden('rev2[0]',$l_rev);
        $form->addHidden('rev2[1]',$r_rev);
        $form->addHidden('do','diff');
        $form->addElement(form_makeListboxField(
                            'difftype',
                            array(
                                'sidebyside' => $lang['diff_side'],
                                'inline'     => $lang['diff_inline']),
                            $type,
                            $lang['diff_type'],
                            '','',
                            array('class'=>'quickselect')));
        $form->addElement(form_makeButton('submit', 'diff','Go'));
        $form->printForm();


        $diffurl = wl($ID, array(
                        'do'       => 'diff',
                        'rev2[0]'  => $l_rev,
                        'rev2[1]'  => $r_rev,
                        'difftype' => $type,
                      ));
        ptln('<p><a class="wikilink1" href="'.$diffurl.'">'.$lang['difflink'].'</a></p>');
        ptln('</div>');
    }
    ?>
    <table class="diff diff_<?php echo $type?>">
    <tr>
    <th colspan="2" <?php echo $l_minor?>>
    <?php echo $l_head?>
    </th>
    <th colspan="2" <?php echo $r_minor?>>
    <?php echo $r_head?>
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
    $form = new Doku_Form(array('id' => 'dw__editform'));
    $form->addHidden('id', $ID);
    $form->addHidden('wikitext', $text);
    $form->addHidden('summary', $summary);
    $form->addElement(form_makeButton('submit', 'save', $lang['btn_save'], array('accesskey'=>'s')));
    $form->addElement(form_makeButton('submit', 'cancel', $lang['btn_cancel']));
    html_form('conflict', $form);
    print '<br /><br /><br /><br />'.NL;
}

/**
 * Prints the global message array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_msgarea(){
    global $MSG, $MSG_shown;
    // store if the global $MSG has already been shown and thus HTML output has been started
    $MSG_shown = true;

    if(!isset($MSG)) return;

    $shown = array();
    foreach($MSG as $msg){
        $hash = md5($msg['msg']);
        if(isset($shown[$hash])) continue; // skip double messages
        print '<div class="'.$msg['lvl'].'">';
        print $msg['msg'];
        print '</div>';
        $shown[$hash] = 1;
    }

    unset($GLOBALS['MSG']);
}

/**
 * Prints the registration form
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_register(){
    global $lang;
    global $conf;
    global $ID;

    print p_locale_xhtml('register');
    print '<div class="centeralign">'.NL;
    $form = new Doku_Form(array('id' => 'dw__register'));
    $form->startFieldset($lang['btn_register']);
    $form->addHidden('do', 'register');
    $form->addHidden('save', '1');
    $form->addElement(form_makeTextField('login', $_POST['login'], $lang['user'], '', 'block', array('size'=>'50')));
    if (!$conf['autopasswd']) {
        $form->addElement(form_makePasswordField('pass', $lang['pass'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));
    }
    $form->addElement(form_makeTextField('fullname', $_POST['fullname'], $lang['fullname'], '', 'block', array('size'=>'50')));
    $form->addElement(form_makeTextField('email', $_POST['email'], $lang['email'], '', 'block', array('size'=>'50')));
    $form->addElement(form_makeButton('submit', '', $lang['btn_register']));
    $form->endFieldset();
    html_form('register', $form);

    print '</div>'.NL;
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
    print '<div class="centeralign">'.NL;
    $form = new Doku_Form(array('id' => 'dw__register'));
    $form->startFieldset($lang['profile']);
    $form->addHidden('do', 'profile');
    $form->addHidden('save', '1');
    $form->addElement(form_makeTextField('fullname', $_SERVER['REMOTE_USER'], $lang['user'], '', 'block', array('size'=>'50', 'disabled'=>'disabled')));
    $attr = array('size'=>'50');
    if (!$auth->canDo('modName')) $attr['disabled'] = 'disabled';
    $form->addElement(form_makeTextField('fullname', $_POST['fullname'], $lang['fullname'], '', 'block', $attr));
    $attr = array('size'=>'50');
    if (!$auth->canDo('modMail')) $attr['disabled'] = 'disabled';
    $form->addElement(form_makeTextField('email', $_POST['email'], $lang['email'], '', 'block', $attr));
    $form->addElement(form_makeTag('br'));
    if ($auth->canDo('modPass')) {
        $form->addElement(form_makePasswordField('newpass', $lang['newpass'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));
    }
    if ($conf['profileconfirm']) {
        $form->addElement(form_makeTag('br'));
        $form->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50')));
    }
    $form->addElement(form_makeButton('submit', '', $lang['btn_save']));
    $form->addElement(form_makeButton('reset', '', $lang['btn_reset']));
    $form->endFieldset();
    html_form('updateprofile', $form);
    print '</div>'.NL;
}

/**
 * Preprocess edit form data
 *
 * @author   Andreas Gohr <andi@splitbrain.org>
 *
 * @triggers HTML_EDITFORM_OUTPUT
 */
function html_edit(){
    global $ID;
    global $REV;
    global $DATE;
    global $PRE;
    global $SUF;
    global $INFO;
    global $SUM;
    global $lang;
    global $conf;
    global $TEXT;
    global $RANGE;

    if (isset($_REQUEST['changecheck'])) {
        $check = $_REQUEST['changecheck'];
    } elseif(!$INFO['exists']){
        // $TEXT has been loaded from page template
        $check = md5('');
    } else {
        $check = md5($TEXT);
    }
    $mod = md5($TEXT) !== $check;

    $wr = $INFO['writable'] && !$INFO['locked'];
    $include = 'edit';
    if($wr){
        if ($REV) $include = 'editrev';
    }else{
        // check pseudo action 'source'
        if(!actionOK('source')){
            msg('Command disabled: source',-1);
            return;
        }
        $include = 'read';
    }

    global $license;

    $form = new Doku_Form(array('id' => 'dw__editform'));
    $form->addHidden('id', $ID);
    $form->addHidden('rev', $REV);
    $form->addHidden('date', $DATE);
    $form->addHidden('prefix', $PRE . '.');
    $form->addHidden('suffix', $SUF);
    $form->addHidden('changecheck', $check);

    $data = array('form' => $form,
                  'wr'   => $wr,
                  'media_manager' => true,
                  'target' => (isset($_REQUEST['target']) && $wr &&
                               $RANGE !== '') ? $_REQUEST['target'] : 'section',
                  'intro_locale' => $include);

    if ($data['target'] !== 'section') {
        // Only emit event if page is writable, section edit data is valid and
        // edit target is not section.
        trigger_event('HTML_EDIT_FORMSELECTION', $data, 'html_edit_form', true);
    } else {
        html_edit_form($data);
    }
    if (isset($data['intro_locale'])) {
        echo p_locale_xhtml($data['intro_locale']);
    }

    $form->addHidden('target', $data['target']);
    $form->addElement(form_makeOpenTag('div', array('id'=>'wiki__editbar')));
    $form->addElement(form_makeOpenTag('div', array('id'=>'size__ctl')));
    $form->addElement(form_makeCloseTag('div'));
    if ($wr) {
        $form->addElement(form_makeOpenTag('div', array('class'=>'editButtons')));
        $form->addElement(form_makeButton('submit', 'save', $lang['btn_save'], array('id'=>'edbtn__save', 'accesskey'=>'s', 'tabindex'=>'4')));
        $form->addElement(form_makeButton('submit', 'preview', $lang['btn_preview'], array('id'=>'edbtn__preview', 'accesskey'=>'p', 'tabindex'=>'5')));
        $form->addElement(form_makeButton('submit', 'draftdel', $lang['btn_cancel'], array('tabindex'=>'6')));
        $form->addElement(form_makeCloseTag('div'));
        $form->addElement(form_makeOpenTag('div', array('class'=>'summary')));
        $form->addElement(form_makeTextField('summary', $SUM, $lang['summary'], 'edit__summary', 'nowrap', array('size'=>'50', 'tabindex'=>'2')));
        $elem = html_minoredit();
        if ($elem) $form->addElement($elem);
        $form->addElement(form_makeCloseTag('div'));
    }
    $form->addElement(form_makeCloseTag('div'));
    if($wr && $conf['license']){
        $form->addElement(form_makeOpenTag('div', array('class'=>'license')));
        $out  = $lang['licenseok'];
        $out .= ' <a href="'.$license[$conf['license']]['url'].'" rel="license" class="urlextern"';
        if($conf['target']['extern']) $out .= ' target="'.$conf['target']['extern'].'"';
        $out .= '>'.$license[$conf['license']]['name'].'</a>';
        $form->addElement($out);
        $form->addElement(form_makeCloseTag('div'));
    }

    if ($wr) {
        // sets changed to true when previewed
        echo '<script type="text/javascript" charset="utf-8"><!--//--><![CDATA[//><!--'. NL;
        echo 'textChanged = ' . ($mod ? 'true' : 'false');
        echo '//--><!]]></script>' . NL;
    } ?>
    <div style="width:99%;">

    <div class="toolbar">
    <div id="draft__status"><?php if(!empty($INFO['draft'])) echo $lang['draftdate'].' '.dformat();?></div>
    <div id="tool__bar"><?php if ($wr && $data['media_manager']){?><a href="<?php echo DOKU_BASE?>lib/exe/mediamanager.php?ns=<?php echo $INFO['namespace']?>"
        target="_blank"><?php echo $lang['mediaselect'] ?></a><?php }?></div>

    </div>
    <?php

    html_form('edit', $form);
    print '</div>'.NL;
}

/**
 * Display the default edit form
 *
 * Is the default action for HTML_EDIT_FORMSELECTION.
 */
function html_edit_form($param) {
    global $TEXT;

    if ($param['target'] !== 'section') {
        msg('No editor for edit target ' . $param['target'] . ' found.', -1);
    }

    $attr = array('tabindex'=>'1');
    if (!$param['wr']) $attr['readonly'] = 'readonly';

    $param['form']->addElement(form_makeWikiText($TEXT, $attr));
}

/**
 * Adds a checkbox for minor edits for logged in users
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_minoredit(){
    global $conf;
    global $lang;
    // minor edits are for logged in users only
    if(!$conf['useacl'] || !$_SERVER['REMOTE_USER']){
        return false;
    }

    $p = array();
    $p['tabindex'] = 3;
    if(!empty($_REQUEST['minor'])) $p['checked']='checked';
    return form_makeCheckboxField('minor', '1', $lang['minoredit'], 'minoredit', 'nowrap', $p);
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
    debug_guard($cnf);
    $nfo = $INFO;
    debug_guard($nfo);
    $ses = $_SESSION;
    debug_guard($ses);

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

/**
 * List available Administration Tasks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Håkan Sandell <hakan.sandell@home.se>
 */
function html_admin(){
    global $ID;
    global $INFO;
    global $lang;
    global $conf;
    global $auth;

    // build menu of admin functions from the plugins that handle them
    $pluginlist = plugin_list('admin');
    $menu = array();
    foreach ($pluginlist as $p) {
        if($obj =& plugin_load('admin',$p) === null) continue;

        // check permissions
        if($obj->forAdminOnly() && !$INFO['isadmin']) continue;

        $menu[$p] = array('plugin' => $p,
                'prompt' => $obj->getMenuText($conf['lang']),
                'sort' => $obj->getMenuSort()
                );
    }

    // data security check
    // @todo: could be checked and only displayed if $conf['savedir'] is under the web root
    echo '<a style="border:none; float:right;"
            href="http://www.dokuwiki.org/security#web_access_security">
            <img src="data/security.png" alt="Your data directory seems to be protected properly."
             onerror="this.parentNode.style.display=\'none\'" /></a>';

    print p_locale_xhtml('admin');

    // Admin Tasks
    if($INFO['isadmin']){
        ptln('<ul class="admin_tasks">');

        if($menu['usermanager'] && $auth && $auth->canDo('getUsers')){
            ptln('  <li class="admin_usermanager"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'usermanager')).'">'.
                    $menu['usermanager']['prompt'].'</a></div></li>');
        }
        unset($menu['usermanager']);

        if($menu['acl']){
            ptln('  <li class="admin_acl"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'acl')).'">'.
                    $menu['acl']['prompt'].'</a></div></li>');
        }
        unset($menu['acl']);

        if($menu['plugin']){
            ptln('  <li class="admin_plugin"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'plugin')).'">'.
                    $menu['plugin']['prompt'].'</a></div></li>');
        }
        unset($menu['plugin']);

        if($menu['config']){
            ptln('  <li class="admin_config"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'config')).'">'.
                    $menu['config']['prompt'].'</a></div></li>');
        }
        unset($menu['config']);
    }
    ptln('</ul>');

    // Manager Tasks
    ptln('<ul class="admin_tasks">');

    if($menu['revert']){
        ptln('  <li class="admin_revert"><div class="li">'.
                '<a href="'.wl($ID, array('do' => 'admin','page' => 'revert')).'">'.
                $menu['revert']['prompt'].'</a></div></li>');
    }
    unset($menu['revert']);

    if($menu['popularity']){
        ptln('  <li class="admin_popularity"><div class="li">'.
                '<a href="'.wl($ID, array('do' => 'admin','page' => 'popularity')).'">'.
                $menu['popularity']['prompt'].'</a></div></li>');
    }
    unset($menu['popularity']);

    // print DokuWiki version:
    ptln('</ul>');
    echo '<div id="admin__version">';
    echo getVersion();
    echo '</div>';

    // print the rest as sorted list
    if(count($menu)){
        usort($menu, 'p_sort_modes');
        // output the menu
        ptln('<div class="clearer"></div>');
        print p_locale_xhtml('adminplugins');
        ptln('<ul>');
        foreach ($menu as $item) {
            if (!$item['prompt']) continue;
            ptln('  <li><div class="li"><a href="'.wl($ID, 'do=admin&amp;page='.$item['plugin']).'">'.$item['prompt'].'</a></div></li>');
        }
        ptln('</ul>');
    }
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
    print '<div class="centeralign">'.NL;
    $form = new Doku_Form(array('id' => 'dw__resendpwd'));
    $form->startFieldset($lang['resendpwd']);
    $form->addHidden('do', 'resendpwd');
    $form->addHidden('save', '1');
    $form->addElement(form_makeTag('br'));
    $form->addElement(form_makeTextField('login', $_POST['login'], $lang['user'], '', 'block'));
    $form->addElement(form_makeTag('br'));
    $form->addElement(form_makeTag('br'));
    $form->addElement(form_makeButton('submit', '', $lang['btn_resendpwd']));
    $form->endFieldset();
    html_form('resendpwd', $form);
    print '</div>'.NL;
}

/**
 * Return the TOC rendered to XHTML
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_TOC($toc){
    if(!count($toc)) return '';
    global $lang;
    $out  = '<!-- TOC START -->'.DOKU_LF;
    $out .= '<div class="toc">'.DOKU_LF;
    $out .= '<div class="tocheader toctoggle" id="toc__header">';
    $out .= $lang['toc'];
    $out .= '</div>'.DOKU_LF;
    $out .= '<div id="toc__inside">'.DOKU_LF;
    $out .= html_buildlist($toc,'toc','html_list_toc','html_li_default',true);
    $out .= '</div>'.DOKU_LF.'</div>'.DOKU_LF;
    $out .= '<!-- TOC END -->'.DOKU_LF;
    return $out;
}

/**
 * Callback for html_buildlist
 */
function html_list_toc($item){
    if(isset($item['hid'])){
        $link = '#'.$item['hid'];
    }else{
        $link = $item['link'];
    }

    return '<span class="li"><a href="'.$link.'" class="toc">'.
        hsc($item['title']).'</a></span>';
}

/**
 * Helper function to build TOC items
 *
 * Returns an array ready to be added to a TOC array
 *
 * @param string $link  - where to link (if $hash set to '#' it's a local anchor)
 * @param string $text  - what to display in the TOC
 * @param int    $level - nesting level
 * @param string $hash  - is prepended to the given $link, set blank if you want full links
 */
function html_mktocitem($link, $text, $level, $hash='#'){
    global $conf;
    return  array( 'link'  => $hash.$link,
            'title' => $text,
            'type'  => 'ul',
            'level' => $level);
}

/**
 * Output a Doku_Form object.
 * Triggers an event with the form name: HTML_{$name}FORM_OUTPUT
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function html_form($name, &$form) {
    // Safety check in case the caller forgets.
    $form->endFieldset();
    trigger_event('HTML_'.strtoupper($name).'FORM_OUTPUT', $form, 'html_form_output', false);
}

/**
 * Form print function.
 * Just calls printForm() on the data object.
 */
function html_form_output($data) {
    $data->printForm();
}

/**
 * Embed a flash object in HTML
 *
 * This will create the needed HTML to embed a flash movie in a cross browser
 * compatble way using valid XHTML
 *
 * The parameters $params, $flashvars and $atts need to be associative arrays.
 * No escaping needs to be done for them. The alternative content *has* to be
 * escaped because it is used as is. If no alternative content is given
 * $lang['noflash'] is used.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://latrine.dgx.cz/how-to-correctly-insert-a-flash-into-xhtml
 *
 * @param string $swf      - the SWF movie to embed
 * @param int $width       - width of the flash movie in pixels
 * @param int $height      - height of the flash movie in pixels
 * @param array $params    - additional parameters (<param>)
 * @param array $flashvars - parameters to be passed in the flashvar parameter
 * @param array $atts      - additional attributes for the <object> tag
 * @param string $alt      - alternative content (is NOT automatically escaped!)
 * @returns string         - the XHTML markup
 */
function html_flashobject($swf,$width,$height,$params=null,$flashvars=null,$atts=null,$alt=''){
    global $lang;

    $out = '';

    // prepare the object attributes
    if(is_null($atts)) $atts = array();
    $atts['width']  = (int) $width;
    $atts['height'] = (int) $height;
    if(!$atts['width'])  $atts['width']  = 425;
    if(!$atts['height']) $atts['height'] = 350;

    // add object attributes for standard compliant browsers
    $std = $atts;
    $std['type'] = 'application/x-shockwave-flash';
    $std['data'] = $swf;

    // add object attributes for IE
    $ie  = $atts;
    $ie['classid'] = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';

    // open object (with conditional comments)
    $out .= '<!--[if !IE]> -->'.NL;
    $out .= '<object '.buildAttributes($std).'>'.NL;
    $out .= '<!-- <![endif]-->'.NL;
    $out .= '<!--[if IE]>'.NL;
    $out .= '<object '.buildAttributes($ie).'>'.NL;
    $out .= '    <param name="movie" value="'.hsc($swf).'" />'.NL;
    $out .= '<!--><!-- -->'.NL;

    // print params
    if(is_array($params)) foreach($params as $key => $val){
        $out .= '  <param name="'.hsc($key).'" value="'.hsc($val).'" />'.NL;
    }

    // add flashvars
    if(is_array($flashvars)){
        $out .= '  <param name="FlashVars" value="'.buildURLparams($flashvars).'" />'.NL;
    }

    // alternative content
    if($alt){
        $out .= $alt.NL;
    }else{
        $out .= $lang['noflash'].NL;
    }

    // finish
    $out .= '</object>'.NL;
    $out .= '<!-- <![endif]-->'.NL;

    return $out;
}

function html_tabs($tabs, $current_tab = null) {
    echo '<ul class="tabs">'.NL;

    foreach($tabs as $id => $tab) {
        html_tab($tab['href'], $tab['caption'], $id === $current_tab);
    }

    echo '</ul>'.NL;
}
/**
 * Prints a single tab
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @author Adrian Lang <mail@adrianlang.de>
 *
 * @param string $href - tab href
 * @param string $caption - tab caption
 * @param boolean $selected - is tab selected
 */

function html_tab($href, $caption, $selected=false) {
    $tab = '<li>';
    if ($selected) {
        $tab .= '<strong>';
    } else {
        $tab .= '<a href="' . hsc($href) . '">';
    }
    $tab .= hsc($caption)
         .  '</' . ($selected ? 'strong' : 'a') . '>'
         .  '</li>'.NL;
    echo $tab;
}

