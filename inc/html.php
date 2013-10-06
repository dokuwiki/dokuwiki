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
 * @param string  $id      id of the target page
 * @param string  $name    the name of the link, i.e. the text that is displayed
 * @param string|array  $search  search string(s) that shall be highlighted in the target page
 * @return string the HTML code of the link
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

    if (!isset($data['name']) || $data['name'] === '') return '';

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
 * Highlights searchqueries in HTML code
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function html_hilight($html,$phrases){
    $phrases = (array) $phrases;
    $phrases = array_map('preg_quote_cb', $phrases);
    $phrases = array_map('ft_snippet_re_preprocess', $phrases);
    $phrases = array_filter($phrases);
    $regex = join('|',$phrases);

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
    global $QUERY;
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
    print '<div id="dw__loading">'.NL;
    print '<script type="text/javascript">/*<![CDATA[*/'.NL;
    print 'showLoadBar();'.NL;
    print '/*!]]>*/</script>'.NL;
    print '</div>'.NL;
    flush();

    //do quick pagesearch
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
        print '<dl class="search_results">';
        $num = 1;
        foreach($data as $id => $cnt){
            print '<dt>';
            print html_wikilink(':'.$id,useHeading('navigation')?null:$id,$regex);
            if($cnt !== 0){
                print ': '.$cnt.' '.$lang['hits'].'';
            }
            print '</dt>';
            if($cnt !== 0){
                if($num < FT_SNIPPET_NUMBER){ // create snippets for the first number of matches only
                    print '<dd>'.ft_snippet($id,$regex).'</dd>';
                }
                $num++;
            }
            flush();
        }
        print '</dl>';
    }else{
        print '<div class="nothing">'.$lang['nothingfound'].'</div>';
    }

    //hide progressbar
    print '<script type="text/javascript">/*<![CDATA[*/'.NL;
    print 'hideLoadBar("dw__loading");'.NL;
    print '/*!]]>*/</script>'.NL;
    flush();
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

    $params = array('id' => 'page__revisions', 'class' => 'changes');
    if ($media_id) $params['action'] = media_managerURL(array('image' => $media_id), '&');

    $form = new Doku_Form($params);
    $form->addElement(form_makeOpenTag('ul'));

    if (!$media_id) $exists = $INFO['exists'];
    else $exists = @file_exists(mediaFN($id));

    $display_name = (!$media_id && useHeading('navigation')) ? hsc(p_get_first_heading($id)) : $id;
    if (!$display_name) $display_name = $id;

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
        $form->addElement($display_name);
        $form->addElement(form_makeCloseTag('a'));

        if ($media_id) $form->addElement(form_makeOpenTag('div'));

        if (!$media_id) {
            $form->addElement(form_makeOpenTag('span', array('class' => 'sum')));
            $form->addElement(' – ');
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
            $form->addElement($display_name);
            $form->addElement(form_makeCloseTag('a'));
        }else{
            $form->addElement('<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />');
            $form->addElement($display_name);
        }

        if ($media_id) $form->addElement(form_makeOpenTag('div'));

        if ($info['sum']) {
            $form->addElement(form_makeOpenTag('span', array('class' => 'sum')));
            if (!$media_id) $form->addElement(' – ');
            $form->addElement('<bdi>'.htmlspecialchars($info['sum']).'</bdi>');
            $form->addElement(form_makeCloseTag('span'));
        }

        $form->addElement(form_makeOpenTag('span', array('class' => 'user')));
        if($info['user']){
            $form->addElement('<bdi>'.editorinfo($info['user']).'</bdi>');
            if(auth_ismanager()){
                $form->addElement(' <bdo dir="ltr">('.$info['ip'].')</bdo>');
            }
        }else{
            $form->addElement('<bdo dir="ltr">'.$info['ip'].'</bdo>');
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

    $form = new Doku_Form(array('id' => 'dw__recent', 'method' => 'GET', 'class' => 'changes'));
    $form->addHidden('sectok', null);
    $form->addHidden('do', 'recent');
    $form->addHidden('id', $ID);

    if ($conf['mediarevisions']) {
        $form->addElement('<div class="changeType">');
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
        $form->addElement('</div>');
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
            $form->addElement('<img src="'.$icon.'" alt="'.$recent['id'].'" class="icon" />');
        }

        $form->addElement(form_makeOpenTag('span', array('class' => 'date')));
        $form->addElement($date);
        $form->addElement(form_makeCloseTag('span'));

        $diff = false;
        $href = '';

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
        $form->addElement(' – '.htmlspecialchars($recent['sum']));
        $form->addElement(form_makeCloseTag('span'));

        $form->addElement(form_makeOpenTag('span', array('class' => 'user')));
        if($recent['user']){
            $form->addElement('<bdi>'.editorinfo($recent['user']).'</bdi>');
            if(auth_ismanager()){
                $form->addElement(' <bdo dir="ltr">('.$recent['ip'].')</bdo>');
            }
        }else{
            $form->addElement('<bdo dir="ltr">'.$recent['ip'].'</bdo>');
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
 * Index item formatter
 *
 * User function for html_buildlist()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_list_index($item){
    global $ID, $conf;

    // prevent searchbots needlessly following links
    $nofollow = ($ID != $conf['start'] || $conf['sitemap']) ? ' rel="nofollow"' : '';

    $ret = '';
    $base = ':'.$item['id'];
    $base = substr($base,strrpos($base,':')+1);
    if($item['type']=='d'){
        // FS#2766, no need for search bots to follow namespace links in the index
        $ret .= '<a href="'.wl($ID,'idx='.rawurlencode($item['id'])).'" title="' . $item['id'] . '" class="idx_dir"' . $nofollow . '><strong>';
        $ret .= $base;
        $ret .= '</strong></a>';
    }else{
        // default is noNSorNS($id), but we want noNS($id) when useheading is off FS#2605
        $ret .= html_wikilink(':'.$item['id'], useHeading('navigation') ? null : noNS($item['id']));
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

function html_insert_softbreaks($diffhtml) {
    // search the diff html string for both:
    // - html tags, so these can be ignored
    // - long strings of characters without breaking characters
    return preg_replace_callback('/<[^>]*>|[^<> ]{12,}/','html_softbreak_callback',$diffhtml);
}

function html_softbreak_callback($match){
    // if match is an html tag, return it intact
    if ($match[0]{0} == '<') return $match[0];

    // its a long string without a breaking character,
    // make certain characters into breaking characters by inserting a
    // breaking character (zero length space, U+200B / #8203) in front them.
    $regex = <<< REGEX
(?(?=                                 # start a conditional expression with a positive look ahead ...
&\#?\\w{1,6};)                        # ... for html entities - we don't want to split them (ok to catch some invalid combinations)
&\#?\\w{1,6};                         # yes pattern - a quicker match for the html entity, since we know we have one
|
[?/,&\#;:]                            # no pattern - any other group of 'special' characters to insert a breaking character after
)+                                    # end conditional expression
REGEX;

    return preg_replace('<'.$regex.'>xu','\0&#8203;',$match[0]);
}

/**
 * Prints the global message array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_msgarea(){
    global $MSG, $MSG_shown;
    /** @var array $MSG */
    // store if the global $MSG has already been shown and thus HTML output has been started
    $MSG_shown = true;

    if(!isset($MSG)) return;

    $shown = array();
    foreach($MSG as $msg){
        $hash = md5($msg['msg']);
        if(isset($shown[$hash])) continue; // skip double messages
        if(info_msg_allowed($msg)){
            print '<div class="'.$msg['lvl'].'">';
            print $msg['msg'];
            print '</div>';
        }
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
    global $INPUT;

    $base_attrs = array('size'=>50,'required'=>'required');
    $email_attrs = $base_attrs + array('type'=>'email','class'=>'edit');

    print p_locale_xhtml('register');
    print '<div class="centeralign">'.NL;
    $form = new Doku_Form(array('id' => 'dw__register'));
    $form->startFieldset($lang['btn_register']);
    $form->addHidden('do', 'register');
    $form->addHidden('save', '1');
    $form->addElement(form_makeTextField('login', $INPUT->post->str('login'), $lang['user'], '', 'block', $base_attrs));
    if (!$conf['autopasswd']) {
        $form->addElement(form_makePasswordField('pass', $lang['pass'], '', 'block', $base_attrs));
        $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', $base_attrs));
    }
    $form->addElement(form_makeTextField('fullname', $INPUT->post->str('fullname'), $lang['fullname'], '', 'block', $base_attrs));
    $form->addElement(form_makeField('email','email', $INPUT->post->str('email'), $lang['email'], '', 'block', $email_attrs));
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
    global $INPUT;
    global $INFO;
    /** @var auth_basic $auth */
    global $auth;

    print p_locale_xhtml('updateprofile');
    print '<div class="centeralign">'.NL;

    $fullname = $INPUT->post->str('fullname', $INFO['userinfo']['name'], true);
    $email = $INPUT->post->str('email', $INFO['userinfo']['mail'], true);
    $form = new Doku_Form(array('id' => 'dw__register'));
    $form->startFieldset($lang['profile']);
    $form->addHidden('do', 'profile');
    $form->addHidden('save', '1');
    $form->addElement(form_makeTextField('login', $_SERVER['REMOTE_USER'], $lang['user'], '', 'block', array('size'=>'50', 'disabled'=>'disabled')));
    $attr = array('size'=>'50');
    if (!$auth->canDo('modName')) $attr['disabled'] = 'disabled';
    $form->addElement(form_makeTextField('fullname', $fullname, $lang['fullname'], '', 'block', $attr));
    $attr = array('size'=>'50', 'class'=>'edit');
    if (!$auth->canDo('modMail')) $attr['disabled'] = 'disabled';
    $form->addElement(form_makeField('email','email', $email, $lang['email'], '', 'block', $attr));
    $form->addElement(form_makeTag('br'));
    if ($auth->canDo('modPass')) {
        $form->addElement(form_makePasswordField('newpass', $lang['newpass'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));
    }
    if ($conf['profileconfirm']) {
        $form->addElement(form_makeTag('br'));
        $form->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50', 'required' => 'required')));
    }
    $form->addElement(form_makeButton('submit', '', $lang['btn_save']));
    $form->addElement(form_makeButton('reset', '', $lang['btn_reset']));

    $form->endFieldset();
    html_form('updateprofile', $form);

    if ($auth->canDo('delUser') && actionOK('profile_delete')) {
        $form_profiledelete = new Doku_Form(array('id' => 'dw__profiledelete'));
        $form_profiledelete->startFieldset($lang['profdeleteuser']);
        $form_profiledelete->addHidden('do', 'profile_delete');
        $form_profiledelete->addHidden('delete', '1');
        $form_profiledelete->addElement(form_makeCheckboxField('confirm_delete', '1', $lang['profconfdelete'],'dw__confirmdelete','', array('required' => 'required')));
        if ($conf['profileconfirm']) {
            $form_profiledelete->addElement(form_makeTag('br'));
            $form_profiledelete->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50', 'required' => 'required')));
        }
        $form_profiledelete->addElement(form_makeButton('submit', '', $lang['btn_deleteuser']));
        $form_profiledelete->endFieldset();

        html_form('profiledelete', $form_profiledelete);
    }

    print '</div>'.NL;
}

/**
 * prints some debug info
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function html_debug(){
    global $conf;
    global $lang;
    /** @var auth_basic $auth */
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
        foreach ($auth->getCapabilities() as $cando){
            print '   '.str_pad($cando,16) . ' => ' . (int)$auth->canDo($cando) . NL;
        }
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
 * Form to request a new password for an existing account
 *
 * @author Benoit Chesneau <benoit@bchesneau.info>
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function html_resendpwd() {
    global $lang;
    global $conf;
    global $INPUT;

    $token = preg_replace('/[^a-f0-9]+/','',$INPUT->str('pwauth'));

    if(!$conf['autopasswd'] && $token){
        print p_locale_xhtml('resetpwd');
        print '<div class="centeralign">'.NL;
        $form = new Doku_Form(array('id' => 'dw__resendpwd'));
        $form->startFieldset($lang['btn_resendpwd']);
        $form->addHidden('token', $token);
        $form->addHidden('do', 'resendpwd');

        $form->addElement(form_makePasswordField('pass', $lang['pass'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));

        $form->addElement(form_makeButton('submit', '', $lang['btn_resendpwd']));
        $form->endFieldset();
        html_form('resendpwd', $form);
        print '</div>'.NL;
    }else{
        print p_locale_xhtml('resendpwd');
        print '<div class="centeralign">'.NL;
        $form = new Doku_Form(array('id' => 'dw__resendpwd'));
        $form->startFieldset($lang['resendpwd']);
        $form->addHidden('do', 'resendpwd');
        $form->addHidden('save', '1');
        $form->addElement(form_makeTag('br'));
        $form->addElement(form_makeTextField('login', $INPUT->post->str('login'), $lang['user'], '', 'block'));
        $form->addElement(form_makeTag('br'));
        $form->addElement(form_makeTag('br'));
        $form->addElement(form_makeButton('submit', '', $lang['btn_resendpwd']));
        $form->endFieldset();
        html_form('resendpwd', $form);
        print '</div>'.NL;
    }
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
    $out .= '<div id="dw__toc">'.DOKU_LF;
    $out .= '<h3 class="toggle">';
    $out .= $lang['toc'];
    $out .= '</h3>'.DOKU_LF;
    $out .= '<div>'.DOKU_LF;
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

    return '<a href="'.$link.'">'.hsc($item['title']).'</a>';
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
 * @return array the toc item
 */
function html_mktocitem($link, $text, $level, $hash='#'){
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
 * @param string     $name The name of the form
 * @param Doku_Form  $form The form
 */
function html_form($name, &$form) {
    // Safety check in case the caller forgets.
    $form->endFieldset();
    trigger_event('HTML_'.strtoupper($name).'FORM_OUTPUT', $form, 'html_form_output', false);
}

/**
 * Form print function.
 * Just calls printForm() on the data object.
 * @param Doku_Form $data The form
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
 * @return string         - the XHTML markup
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

/**
 * Prints HTML code for the given tab structure
 *
 * @param array  $tabs        tab structure
 * @param string $current_tab the current tab id
 */
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

