<?php
/**
 * DokuWiki template functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * Returns the path to the given template, uses
 * default one if the custom version doesn't exist.
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
 * Everything that doesn't use the main template file isn't
 * handled by this function. ACL stuff is not done here either.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_content($prependTOC=true) {
    global $ACT;
    global $INFO;
    $INFO['prependTOC'] = $prependTOC;

    ob_start();
    trigger_event('TPL_ACT_RENDER',$ACT,'tpl_content_core');
    $html_output = ob_get_clean();
    trigger_event('TPL_CONTENT_DISPLAY',$html_output,'ptln');

    return !empty($html_output);
}

function tpl_content_core(){
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
        case 'locked':
            html_locked();
        case 'edit':
        case 'recover':
            html_edit();
            break;
        case 'preview':
            html_edit();
            html_show($TEXT);
            break;
        case 'draft':
            html_draft();
            break;
        case 'search':
            html_search();
            break;
        case 'revisions':
            $first = isset($_REQUEST['first']) ? intval($_REQUEST['first']) : 0;
            html_revisions($first);
            break;
        case 'diff':
            html_diff();
            break;
        case 'recent':
            if (is_array($_REQUEST['first'])) {
                $_REQUEST['first'] = array_keys($_REQUEST['first']);
                $_REQUEST['first'] = $_REQUEST['first'][0];
            }
            $first = is_numeric($_REQUEST['first']) ? intval($_REQUEST['first']) : 0;
            $show_changes = $_REQUEST['show_changes'];
            html_recent($first, $show_changes);
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
        case 'subscribe':
            tpl_subscribe();
            break;
        case 'media':
            tpl_media();
            break;
        default:
            $evt = new Doku_Event('TPL_ACT_UNKNOWN',$ACT);
            if ($evt->advise_before())
                msg("Failed to handle command: ".hsc($ACT),-1);
            $evt->advise_after();
            unset($evt);
            return false;
    }
    return true;
}

/**
 * Places the TOC where the function is called
 *
 * If you use this you most probably want to call tpl_content with
 * a false argument
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_toc($return=false){
    global $TOC;
    global $ACT;
    global $ID;
    global $REV;
    global $INFO;
    global $conf;
    $toc = array();

    if(is_array($TOC)){
        // if a TOC was prepared in global scope, always use it
        $toc = $TOC;
    }elseif(($ACT == 'show' || substr($ACT,0,6) == 'export') && !$REV && $INFO['exists']){
        // get TOC from metadata, render if neccessary
        $meta = p_get_metadata($ID, false, METADATA_RENDER_USING_CACHE);
        if(isset($meta['internal']['toc'])){
            $tocok = $meta['internal']['toc'];
        }else{
            $tocok = true;
        }
        $toc   = $meta['description']['tableofcontents'];
        if(!$tocok || !is_array($toc) || !$conf['tocminheads'] || count($toc) < $conf['tocminheads']){
            $toc = array();
        }
    }elseif($ACT == 'admin'){
        // try to load admin plugin TOC FIXME: duplicates code from tpl_admin
        $plugin = null;
        if (!empty($_REQUEST['page'])) {
            $pluginlist = plugin_list('admin');
            if (in_array($_REQUEST['page'], $pluginlist)) {
                // attempt to load the plugin
                $plugin =& plugin_load('admin',$_REQUEST['page']);
            }
        }
        if ( ($plugin !== null) &&
                (!$plugin->forAdminOnly() || $INFO['isadmin']) ){
            $toc = $plugin->getTOC();
            $TOC = $toc; // avoid later rebuild
        }
    }

    trigger_event('TPL_TOC_RENDER', $toc, null, false);
    $html = html_TOC($toc);
    if($return) return $html;
    echo $html;
}

/**
 * Handle the admin page contents
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_admin(){
    global $INFO;
    global $TOC;

    $plugin = null;
    if (!empty($_REQUEST['page'])) {
        $pluginlist = plugin_list('admin');

        if (in_array($_REQUEST['page'], $pluginlist)) {

            // attempt to load the plugin
            $plugin =& plugin_load('admin',$_REQUEST['page']);
        }
    }

    if ($plugin !== null){
        if(!is_array($TOC)) $TOC = $plugin->getTOC(); //if TOC wasn't requested yet
        if($INFO['prependTOC']) tpl_toc();
        $plugin->html();
    }else{
        html_admin();
    }
    return true;
}

/**
 * Print the correct HTML meta headers
 *
 * This has to go into the head section of your template.
 *
 * @triggers TPL_METAHEADER_OUTPUT
 * @param  boolean $alt Should feeds and alternative format links be added?
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_metaheaders($alt=true){
    global $ID;
    global $REV;
    global $INFO;
    global $JSINFO;
    global $ACT;
    global $QUERY;
    global $lang;
    global $conf;
    $it=2;

    // prepare the head array
    $head = array();

    // prepare seed for js and css
    $tseed = 0;
    $depends = getConfigFiles('main');
    foreach($depends as $f) {
        $time = @filemtime($f);
        if($time > $tseed) $tseed = $time;
    }

    // the usual stuff
    $head['meta'][] = array( 'name'=>'generator', 'content'=>'DokuWiki');
    $head['link'][] = array( 'rel'=>'search', 'type'=>'application/opensearchdescription+xml',
            'href'=>DOKU_BASE.'lib/exe/opensearch.php', 'title'=>$conf['title'] );
    $head['link'][] = array( 'rel'=>'start', 'href'=>DOKU_BASE );
    if(actionOK('index')){
        $head['link'][] = array( 'rel'=>'contents', 'href'=> wl($ID,'do=index',false,'&'),
                'title'=>$lang['btn_index'] );
    }

    if($alt){
        $head['link'][] = array( 'rel'=>'alternate', 'type'=>'application/rss+xml',
                'title'=>'Recent Changes', 'href'=>DOKU_BASE.'feed.php');
        $head['link'][] = array( 'rel'=>'alternate', 'type'=>'application/rss+xml',
                'title'=>'Current Namespace',
                'href'=>DOKU_BASE.'feed.php?mode=list&ns='.$INFO['namespace']);
        if(($ACT == 'show' || $ACT == 'search') && $INFO['writable']){
            $head['link'][] = array( 'rel'=>'edit',
                    'title'=>$lang['btn_edit'],
                    'href'=> wl($ID,'do=edit',false,'&'));
        }

        if($ACT == 'search'){
            $head['link'][] = array( 'rel'=>'alternate', 'type'=>'application/rss+xml',
                    'title'=>'Search Result',
                    'href'=>DOKU_BASE.'feed.php?mode=search&q='.$QUERY);
        }

        if(actionOK('export_xhtml')){
            $head['link'][] = array( 'rel'=>'alternate', 'type'=>'text/html', 'title'=>'Plain HTML',
                    'href'=>exportlink($ID, 'xhtml', '', false, '&'));
        }

        if(actionOK('export_raw')){
            $head['link'][] = array( 'rel'=>'alternate', 'type'=>'text/plain', 'title'=>'Wiki Markup',
                    'href'=>exportlink($ID, 'raw', '', false, '&'));
        }
    }

    // setup robot tags apropriate for different modes
    if( ($ACT=='show' || $ACT=='export_xhtml') && !$REV){
        if($INFO['exists']){
            //delay indexing:
            if((time() - $INFO['lastmod']) >= $conf['indexdelay']){
                $head['meta'][] = array( 'name'=>'robots', 'content'=>'index,follow');
            }else{
                $head['meta'][] = array( 'name'=>'robots', 'content'=>'noindex,nofollow');
            }
            $head['link'][] = array( 'rel'=>'canonical', 'href'=>wl($ID,'',true,'&') );
        }else{
            $head['meta'][] = array( 'name'=>'robots', 'content'=>'noindex,follow');
        }
    }elseif(defined('DOKU_MEDIADETAIL')){
        $head['meta'][] = array( 'name'=>'robots', 'content'=>'index,follow');
    }else{
        $head['meta'][] = array( 'name'=>'robots', 'content'=>'noindex,nofollow');
    }

    // set metadata
    if($ACT == 'show' || $ACT=='export_xhtml'){
        // date of modification
        if($REV){
            $head['meta'][] = array( 'name'=>'date', 'content'=>date('Y-m-d\TH:i:sO',$REV));
        }else{
            $head['meta'][] = array( 'name'=>'date', 'content'=>date('Y-m-d\TH:i:sO',$INFO['lastmod']));
        }

        // keywords (explicit or implicit)
        if(!empty($INFO['meta']['subject'])){
            $head['meta'][] = array( 'name'=>'keywords', 'content'=>join(',',$INFO['meta']['subject']));
        }else{
            $head['meta'][] = array( 'name'=>'keywords', 'content'=>str_replace(':',',',$ID));
        }
    }

    // load stylesheets
    $head['link'][] = array('rel'=>'stylesheet', 'media'=>'screen', 'type'=>'text/css',
            'href'=>DOKU_BASE.'lib/exe/css.php?t='.$conf['template'].'&tseed='.$tseed);
    $head['link'][] = array('rel'=>'stylesheet', 'media'=>'all', 'type'=>'text/css',
            'href'=>DOKU_BASE.'lib/exe/css.php?s=all&t='.$conf['template'].'&tseed='.$tseed);
    $head['link'][] = array('rel'=>'stylesheet', 'media'=>'print', 'type'=>'text/css',
            'href'=>DOKU_BASE.'lib/exe/css.php?s=print&t='.$conf['template'].'&tseed='.$tseed);

    // make $INFO and other vars available to JavaScripts
    $json = new JSON();
    $script = "var NS='".$INFO['namespace']."';";
    if($conf['useacl'] && $_SERVER['REMOTE_USER']){
        $script .= "var SIG='".toolbar_signature()."';";
    }
    $script .= 'var JSINFO = '.$json->encode($JSINFO).';';
    $head['script'][] = array( 'type'=>'text/javascript', '_data'=> $script);

    // load external javascript
    $head['script'][] = array( 'type'=>'text/javascript', 'charset'=>'utf-8', '_data'=>'',
            'src'=>DOKU_BASE.'lib/exe/js.php'.'?tseed='.$tseed);

    // trigger event here
    trigger_event('TPL_METAHEADER_OUTPUT',$head,'_tpl_metaheaders_action',true);
    return true;
}

/**
 * prints the array build by tpl_metaheaders
 *
 * $data is an array of different header tags. Each tag can have multiple
 * instances. Attributes are given as key value pairs. Values will be HTML
 * encoded automatically so they should be provided as is in the $data array.
 *
 * For tags having a body attribute specify the the body data in the special
 * attribute '_data'. This field will NOT BE ESCAPED automatically.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function _tpl_metaheaders_action($data){
    foreach($data as $tag => $inst){
        foreach($inst as $attr){
            echo '<',$tag,' ',buildAttributes($attr);
            if(isset($attr['_data']) || $tag == 'script'){
                if($tag == 'script' && $attr['_data'])
                    $attr['_data'] = "<!--//--><![CDATA[//><!--\n".
                        $attr['_data'].
                        "\n//--><!]]>";

                echo '>',$attr['_data'],'</',$tag,'>';
            }else{
                echo '/>';
            }
            echo "\n";
        }
    }
}

/**
 * Print a link
 *
 * Just builds a link.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_link($url,$name,$more='',$return=false){
    $out = '<a href="'.$url.'" ';
    if ($more) $out .= ' '.$more;
    $out .= ">$name</a>";
    if ($return) return $out;
    print $out;
    return true;
}

/**
 * Prints a link to a WikiPage
 *
 * Wrapper around html_wikilink
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_pagelink($id,$name=null){
    print html_wikilink($id,$name);
    return true;
}

/**
 * get the parent page
 *
 * Tries to find out which page is parent.
 * returns false if none is available
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_getparent($id){
    global $conf;
    $parent = getNS($id).':';
    resolve_pageid('',$parent,$exists);
    if($parent == $id) {
        $pos = strrpos (getNS($id),':');
        $parent = substr($parent,0,$pos).':';
        resolve_pageid('',$parent,$exists);
        if($parent == $id) return false;
    }
    return $parent;
}

/**
 * Print one of the buttons
 *
 * @author Adrian Lang <mail@adrianlang.de>
 * @see    tpl_get_action
 */
function tpl_button($type,$return=false){
    $data = tpl_get_action($type);
    if ($data === false) {
        return false;
    } elseif (!is_array($data)) {
        $out = sprintf($data, 'button');
    } else {
        extract($data);
        if ($id === '#dokuwiki__top') {
            $out = html_topbtn();
        } else {
            $out = html_btn($type, $id, $accesskey, $params, $method);
        }
    }
    if ($return) return $out;
    echo $out;
    return true;
}

/**
 * Like the action buttons but links
 *
 * @author Adrian Lang <mail@adrianlang.de>
 * @see    tpl_get_action
 */
function tpl_actionlink($type,$pre='',$suf='',$inner='',$return=false){
    global $lang;
    $data = tpl_get_action($type);
    if ($data === false) {
        return false;
    } elseif (!is_array($data)) {
        $out = sprintf($data, 'link');
    } else {
        extract($data);
        if (strpos($id, '#') === 0) {
            $linktarget = $id;
        } else {
            $linktarget = wl($id, $params);
        }
        $caption = $lang['btn_' . $type];
        $akey = $addTitle = '';
        if($accesskey){
            $akey = 'accesskey="'.$accesskey.'" ';
            $addTitle = ' ['.strtoupper($accesskey).']';
        }
        $out = tpl_link($linktarget, $pre.(($inner)?$inner:$caption).$suf,
                        'class="action ' . $type . '" ' .
                        $akey . 'rel="nofollow" ' .
                        'title="' . hsc($caption).$addTitle . '"', 1);
    }
    if ($return) return $out;
    echo $out;
    return true;
}

/**
 * Check the actions and get data for buttons and links
 *
 * Available actions are
 *
 *  edit        - edit/create/show/draft
 *  history     - old revisions
 *  recent      - recent changes
 *  login       - login/logout - if ACL enabled
 *  profile     - user profile (if logged in)
 *  index       - The index
 *  admin       - admin page - if enough rights
 *  top         - back to top
 *  back        - back to parent - if available
 *  backlink    - links to the list of backlinks
 *  subscribe/subscription- subscribe/unsubscribe
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @author Adrian Lang <mail@adrianlang.de>
 */
function tpl_get_action($type) {
    global $ID;
    global $INFO;
    global $REV;
    global $ACT;
    global $conf;
    global $auth;

    // check disabled actions and fix the badly named ones
    if($type == 'history') $type='revisions';
    if(!actionOK($type)) return false;

    $accesskey = null;
    $id        = $ID;
    $method    = 'get';
    $params    = array('do' => $type);
    switch($type){
        case 'edit':
            // most complicated type - we need to decide on current action
            if($ACT == 'show' || $ACT == 'search'){
                $method = 'post';
                if($INFO['writable']){
                    $accesskey = 'e';
                    if(!empty($INFO['draft'])) {
                        $type = 'draft';
                        $params['do'] = 'draft';
                    } else {
                        $params['rev'] = $REV;
                        if(!$INFO['exists']){
                            $type   = 'create';
                        }
                    }
                }else{
                    if(!actionOK('source')) return false; //pseudo action
                    $params['rev'] = $REV;
                    $type = 'source';
                    $accesskey = 'v';
                }
            }else{
                $params = '';
                $type = 'show';
                $accesskey = 'v';
            }
            break;
        case 'revisions':
            $type = 'revs';
            $accesskey = 'o';
            break;
        case 'recent':
            $accesskey = 'r';
            break;
        case 'index':
            $accesskey = 'x';
            break;
        case 'top':
            $accesskey = 'x';
            $params = '';
            $id = '#dokuwiki__top';
            break;
        case 'back':
            $parent = tpl_getparent($ID);
            if (!$parent) {
                return false;
            }
            $id = $parent;
            $params = '';
            $accesskey = 'b';
            break;
        case 'login':
            $params['sectok'] = getSecurityToken();
            if(isset($_SERVER['REMOTE_USER'])){
                if (!actionOK('logout')) {
                    return false;
                }
                $params['do'] = 'logout';
                $type = 'logout';
            }
            break;
        case 'register':
            if($_SERVER['REMOTE_USER']){
                return false;
            }
            break;
        case 'resendpwd':
            if($_SERVER['REMOTE_USER']){
                return false;
            }
            break;
        case 'admin':
            if(!$INFO['ismanager']){
                return false;
            }
            break;
        case 'revert':
            if(!$INFO['ismanager'] || !$REV || !$INFO['writable']) {
                return false;
            }
            $params['rev'] = $REV;
            $params['sectok'] = getSecurityToken();
            break;
        case 'subscription':
            $type = 'subscribe';
            $params['do'] = 'subscribe';
        case 'subscribe':
            if(!$_SERVER['REMOTE_USER']){
                return false;
            }
            break;
        case 'backlink':
            break;
        case 'profile':
            if(!isset($_SERVER['REMOTE_USER'])){
                return false;
            }
            break;
        case 'media':
            break;
        default:
            return '[unknown %s type]';
            break;
    }
    return compact('accesskey', 'type', 'id', 'method', 'params');
}

/**
 * Wrapper around tpl_button() and tpl_actionlink()
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function tpl_action($type,$link=0,$wrapper=false,$return=false,$pre='',$suf='',$inner='') {
    $out = '';
    if ($link) $out .= tpl_actionlink($type,$pre,$suf,$inner,1);
    else $out .= tpl_button($type,1);
    if ($out && $wrapper) $out = "<$wrapper>$out</$wrapper>";

    if ($return) return $out;
    print $out;
    return $out ? true : false;
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
    global $QUERY;

    // don't print the search form if search action has been disabled
    if (!actionOk('search')) return false;

    print '<form action="'.wl().'" accept-charset="utf-8" class="search" id="dw__search" method="get"><div class="no">';
    print '<input type="hidden" name="do" value="search" />';
    print '<input type="text" ';
    if($ACT == 'search') print 'value="'.htmlspecialchars($QUERY).'" ';
    if(!$autocomplete) print 'autocomplete="off" ';
    print 'id="qsearch__in" accesskey="f" name="id" class="edit" title="[F]" />';
    print '<input type="submit" value="'.$lang['btn_search'].'" class="button" title="'.$lang['btn_search'].'" />';
    if($ajax) print '<div id="qsearch__out" class="ajax_qsearch JSpopup"></div>';
    print '</div></form>';
    return true;
}

/**
 * Print the breadcrumbs trace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_breadcrumbs($sep='&bull;'){
    global $lang;
    global $conf;

    //check if enabled
    if(!$conf['breadcrumbs']) return false;

    $crumbs = breadcrumbs(); //setup crumb trace

    //reverse crumborder in right-to-left mode, add RLM character to fix heb/eng display mixups
    if($lang['direction'] == 'rtl') {
        $crumbs = array_reverse($crumbs,true);
        $crumbs_sep = ' &#8207;<span class="bcsep">'.$sep.'</span>&#8207; ';
    } else {
        $crumbs_sep = ' <span class="bcsep">'.$sep.'</span> ';
    }

    //render crumbs, highlight the last one
    print '<span class="bchead">'.$lang['breadcrumb'].':</span>';
    $last = count($crumbs);
    $i = 0;
    foreach ($crumbs as $id => $name){
        $i++;
        echo $crumbs_sep;
        if ($i == $last) print '<span class="curid">';
        tpl_link(wl($id),hsc($name),'class="breadcrumbs" title="'.$id.'"');
        if ($i == $last) print '</span>';
    }
    return true;
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
 * @author <fredrik@averpil.com>
 * @todo   May behave strangely in RTL languages
 */
function tpl_youarehere($sep=' &raquo; '){
    global $conf;
    global $ID;
    global $lang;

    // check if enabled
    if(!$conf['youarehere']) return false;

    $parts = explode(':', $ID);
    $count = count($parts);

    echo '<span class="bchead">'.$lang['youarehere'].': </span>';

    // always print the startpage
    tpl_pagelink(':'.$conf['start']);

    // print intermediate namespace links
    $part = '';
    for($i=0; $i<$count - 1; $i++){
        $part .= $parts[$i].':';
        $page = $part;
        if ($page == $conf['start']) continue; // Skip startpage

        // output
        echo $sep;
        tpl_pagelink($page);
    }

    // print current page, skipping start page, skipping for namespace index
    resolve_pageid('',$page,$exists);
    if(isset($page) && $page==$part.$parts[$i]) return;
    $page = $part.$parts[$i];
    if($page == $conf['start']) return;
    echo $sep;
    tpl_pagelink($page);
    return true;
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
    if(isset($_SERVER['REMOTE_USER'])){
        print $lang['loggedinas'].': '.hsc($INFO['userinfo']['name']).' ('.hsc($_SERVER['REMOTE_USER']).')';
        return true;
    }
    return false;
}

/**
 * Print some info about the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_pageinfo($ret=false){
    global $conf;
    global $lang;
    global $INFO;
    global $ID;

    // return if we are not allowed to view the page
    if (!auth_quickaclcheck($ID)) { return false; }

    // prepare date and path
    $fn = $INFO['filepath'];
    if(!$conf['fullpath']){
        if($INFO['rev']){
            $fn = str_replace(fullpath($conf['olddir']).'/','',$fn);
        }else{
            $fn = str_replace(fullpath($conf['datadir']).'/','',$fn);
        }
    }
    $fn = utf8_decodeFN($fn);
    $date = dformat($INFO['lastmod']);

    // print it
    if($INFO['exists']){
        $out = '';
        $out .= $fn;
        $out .= ' &middot; ';
        $out .= $lang['lastmod'];
        $out .= ': ';
        $out .= $date;
        if($INFO['editor']){
            $out .= ' '.$lang['by'].' ';
            $out .= editorinfo($INFO['editor']);
        }else{
            $out .= ' ('.$lang['external_edit'].')';
        }
        if($INFO['locked']){
            $out .= ' &middot; ';
            $out .= $lang['lockedby'];
            $out .= ': ';
            $out .= editorinfo($INFO['locked']);
        }
        if($ret){
            return $out;
        }else{
            echo $out;
            return true;
        }
    }
    return false;
}

/**
 * Prints or returns the name of the given page (current one if none given).
 *
 * If useheading is enabled this will use the first headline else
 * the given ID is used.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_pagetitle($id=null, $ret=false){
    global $conf;
    if(is_null($id)){
        global $ID;
        $id = $ID;
    }

    $name = $id;
    if (useHeading('navigation')) {
        $title = p_get_first_heading($id);
        if ($title) $name = $title;
    }

    if ($ret) {
        return hsc($name);
    } else {
        print hsc($name);
        return true;
    }
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
function tpl_img_getTag($tags,$alt='',$src=null){
    // Init Exif Reader
    global $SRC;

    if(is_null($src)) $src = $SRC;

    static $meta = null;
    if(is_null($meta)) $meta = new JpegMeta($src);
    if($meta === false) return $alt;
    $info = $meta->getField($tags);
    if($info == false) return $alt;
    return $info;
}

/**
 * Prints the image with a link to the full sized version
 *
 * Only allowed in: detail.php
 *
 * @param $maxwidth  int - maximal width of the image
 * @param $maxheight int - maximal height of the image
 * @param $link bool     - link to the orginal size?
 * @param $params array  - additional image attributes
 */
function tpl_img($maxwidth=0,$maxheight=0,$link=true,$params=null){
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
    $url=ml($IMG,array('cache'=>$_REQUEST['cache']),true,'&');
    $src=ml($IMG,array('cache'=>$_REQUEST['cache'],'w'=>$w,'h'=>$h),true,'&');

    //prepare attributes
    $alt=tpl_img_getTag('Simple.Title');
    if(is_null($params)){
        $p = array();
    }else{
        $p = $params;
    }
    if($w) $p['width']  = $w;
    if($h) $p['height'] = $h;
    $p['class']  = 'img_detail';
    if($alt){
        $p['alt']   = $alt;
        $p['title'] = $alt;
    }else{
        $p['alt'] = '';
    }
    $p['src'] = $src;

    $data = array('url'=>($link?$url:null), 'params'=>$p);
    return trigger_event('TPL_IMG_DISPLAY',$data,'_tpl_img_action',true);
}

/**
 * Default action for TPL_IMG_DISPLAY
 */
function _tpl_img_action($data, $param=NULL) {
    $p = buildAttributes($data['params']);

    if($data['url']) print '<a href="'.hsc($data['url']).'">';
    print '<img '.$p.'/>';
    if($data['url']) print '</a>';
    return true;
}

/**
 * This function inserts a small gif which in reality is the indexer function.
 *
 * Should be called somewhere at the very end of the main.php
 * template
 */
function tpl_indexerWebBug(){
    global $ID;
    global $INFO;
    if(!$INFO['exists']) return false;

    $p = array();
    $p['src']    = DOKU_BASE.'lib/exe/indexer.php?id='.rawurlencode($ID).
        '&'.time();
    $p['width']  = 2; //no more 1x1 px image because we live in times of ad blockers...
    $p['height'] = 1;
    $p['alt']    = '';
    $att = buildAttributes($p);
    print "<img $att />";
    return true;
}

// configuration methods
/**
 * tpl_getConf($id)
 *
 * use this function to access template configuration variables
 */
function tpl_getConf($id){
    global $conf;
    static $tpl_configloaded = false;

    $tpl = $conf['template'];

    if (!$tpl_configloaded){
        $tconf = tpl_loadConfig();
        if ($tconf !== false){
            foreach ($tconf as $key => $value){
                if (isset($conf['tpl'][$tpl][$key])) continue;
                $conf['tpl'][$tpl][$key] = $value;
            }
            $tpl_configloaded = true;
        }
    }

    return $conf['tpl'][$tpl][$id];
}

/**
 * tpl_loadConfig()
 * reads all template configuration variables
 * this function is automatically called by tpl_getConf()
 */
function tpl_loadConfig(){

    $file = DOKU_TPLINC.'/conf/default.php';
    $conf = array();

    if (!@file_exists($file)) return false;

    // load default config file
    include($file);

    return $conf;
}

// language methods
/**
 * tpl_getLang($id)
 *
 * use this function to access template language variables
 */
function tpl_getLang($id){
    static $lang = array();

    if (count($lang) === 0){
        $path = DOKU_TPLINC.'lang/';

        $lang = array();

        global $conf;            // definitely don't invoke "global $lang"
        // don't include once
        @include($path.'en/lang.php');
        if ($conf['lang'] != 'en') @include($path.$conf['lang'].'/lang.php');
    }

    return $lang[$id];
}

/**
 * prints the "main content" in the mediamanger popup
 *
 * Depending on the user's actions this may be a list of
 * files in a namespace, the meta editing dialog or
 * a message of referencing pages
 *
 * Only allowed in mediamanager.php
 *
 * @triggers MEDIAMANAGER_CONTENT_OUTPUT
 * @param bool $fromajax - set true when calling this function via ajax
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_mediaContent($fromajax=false){
    global $IMG;
    global $AUTH;
    global $INUSE;
    global $NS;
    global $JUMPTO;

    if(is_array($_REQUEST['do'])){
        $do = array_shift(array_keys($_REQUEST['do']));
    }else{
        $do = $_REQUEST['do'];
    }
    if(in_array($do,array('save','cancel'))) $do = '';

    if(!$do){
        if($_REQUEST['edit']){
            $do = 'metaform';
        }elseif(is_array($INUSE)){
            $do = 'filesinuse';
        }else{
            $do = 'filelist';
        }
    }

    // output the content pane, wrapped in an event.
    if(!$fromajax) ptln('<div id="media__content">');
    $data = array( 'do' => $do);
    $evt = new Doku_Event('MEDIAMANAGER_CONTENT_OUTPUT', $data);
    if ($evt->advise_before()) {
        $do = $data['do'];
        if($do == 'filesinuse'){
            media_filesinuse($INUSE,$IMG);
        }elseif($do == 'filelist'){
            media_filelist($NS,$AUTH,$JUMPTO);
        }elseif($do == 'searchlist'){
            media_searchlist($_REQUEST['q'],$NS,$AUTH);
        }else{
            msg('Unknown action '.hsc($do),-1);
        }
    }
    $evt->advise_after();
    unset($evt);
    if(!$fromajax) ptln('</div>');

}

/**
 * Prints the central column in full-screen media manager
 * Depending on the opened tab this may be a list of
 * files in a namespace, upload form or search form
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function tpl_mediaFileList(){
    global $AUTH;
    global $NS;
    global $JUMPTO;
    global $lang;

    $opened_tab = $_REQUEST['tab_files'];
    if (!$opened_tab || !in_array($opened_tab, array('files', 'upload', 'search'))) $opened_tab = 'files';
    if ($_REQUEST['mediado'] == 'update') $opened_tab = 'upload';

    echo '<h2 class="a11y">' . $lang['mediaselect'] . '</h2>'.NL;

    media_tabs_files($opened_tab);

    echo '<div class="panelHeader">'.NL;
    echo '<h3>';
    $tabTitle = ($NS) ? $NS : '['.$lang['mediaroot'].']';
    printf($lang['media_' . $opened_tab], '<strong>'.$tabTitle.'</strong>');
    echo '</h3>'.NL;
    if ($opened_tab === 'search' || $opened_tab === 'files') {
        media_tab_files_options();
    }
    echo '</div>'.NL;

    echo '<div class="panelContent">'.NL;
    if ($opened_tab == 'files') {
        media_tab_files($NS,$AUTH,$JUMPTO);
    } elseif ($opened_tab == 'upload') {
        media_tab_upload($NS,$AUTH,$JUMPTO);
    } elseif ($opened_tab == 'search') {
        media_tab_search($NS,$AUTH);
    }
    echo '</div>'.NL;
}

/**
 * Prints the third column in full-screen media manager
 * Depending on the opened tab this may be details of the
 * selected file, the meta editing dialog or
 * list of file revisions
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function tpl_mediaFileDetails($image, $rev){
    global $AUTH, $NS, $conf, $DEL, $lang;

    $removed = (!file_exists(mediaFN($image)) && file_exists(mediaMetaFN($image, '.changes')) && $conf['mediarevisions']);
    if (!$image || (!file_exists(mediaFN($image)) && !$removed) || $DEL) return '';
    if ($rev && !file_exists(mediaFN($image, $rev))) $rev = false;
    if (isset($NS) && getNS($image) != $NS) return '';
    $do = $_REQUEST['mediado'];

    $opened_tab = $_REQUEST['tab_details'];

    $tab_array = array('view');
    list($ext, $mime) = mimetype($image);
    if ($mime == 'image/jpeg') {
        $tab_array[] = 'edit';
    }
    if ($conf['mediarevisions']) {
        $tab_array[] = 'history';
    }

    if (!$opened_tab || !in_array($opened_tab, $tab_array)) $opened_tab = 'view';
    if ($_REQUEST['edit']) $opened_tab = 'edit';
    if ($do == 'restore') $opened_tab = 'view';

    media_tabs_details($image, $opened_tab);

    echo '<div class="panelHeader"><h3>';
    list($ext,$mime,$dl) = mimetype($image,false);
    $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);
    $class = 'select mediafile mf_'.$class;
    $tabTitle = '<strong class="'.$class.'">'.$image.'</strong>';
    if ($opened_tab === 'view' && $rev) {
        printf($lang['media_viewold'], $tabTitle, dformat($rev));
    } else {
        printf($lang['media_' . $opened_tab], $tabTitle);
    }
    echo '</h3></div>'.NL;

    echo '<div class="panelContent">'.NL;

    if ($opened_tab == 'view') {
        media_tab_view($image, $NS, $AUTH, $rev);

    } elseif ($opened_tab == 'edit' && !$removed) {
        media_tab_edit($image, $NS, $AUTH);

    } elseif ($opened_tab == 'history' && $conf['mediarevisions']) {
        media_tab_history($image,$NS,$AUTH);
    }

    echo '</div>'.NL;
}

/**
 * prints the namespace tree in the mediamanger popup
 *
 * Only allowed in mediamanager.php
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_mediaTree(){
    global $NS;
    ptln('<div id="media__tree">');
    media_nstree($NS);
    ptln('</div>');
}


/**
 * Print a dropdown menu with all DokuWiki actions
 *
 * Note: this will not use any pretty URLs
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_actiondropdown($empty='',$button='&gt;'){
    global $ID;
    global $INFO;
    global $REV;
    global $ACT;
    global $conf;
    global $lang;
    global $auth;

    echo '<form action="' . DOKU_SCRIPT . '" method="post" accept-charset="utf-8">';
    echo '<input type="hidden" name="id" value="'.$ID.'" />';
    if($REV) echo '<input type="hidden" name="rev" value="'.$REV.'" />';
    echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />';

    echo '<select name="do" class="edit quickselect">';
    echo '<option value="">'.$empty.'</option>';

    echo '<optgroup label=" &mdash; ">';
        $act = tpl_get_action('edit');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('revisions');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('revert');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('backlink');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';
    echo '</optgroup>';

    echo '<optgroup label=" &mdash; ">';
        $act = tpl_get_action('recent');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('index');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';
    echo '</optgroup>';

    echo '<optgroup label=" &mdash; ">';
        $act = tpl_get_action('login');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('profile');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('subscribe');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';

        $act = tpl_get_action('admin');
        if($act) echo '<option value="'.$act['params']['do'].'">'.$lang['btn_'.$act['type']].'</option>';
    echo '</optgroup>';

    echo '</select>';
    echo '<input type="submit" value="'.$button.'" />';
    echo '</form>';
}

/**
 * Print a informational line about the used license
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $img    - print image? (|button|badge)
 * @param  bool   $return - when true don't print, but return HTML
 */
function tpl_license($img='badge',$imgonly=false,$return=false){
    global $license;
    global $conf;
    global $lang;
    if(!$conf['license']) return '';
    if(!is_array($license[$conf['license']])) return '';
    $lic = $license[$conf['license']];

    $out  = '<div class="license">';
    if($img){
        $src = license_img($img);
        if($src){
            $out .= '<a href="'.$lic['url'].'" rel="license"';
            if($conf['target']['extern']) $out .= ' target="'.$conf['target']['extern'].'"';
            $out .= '><img src="'.DOKU_BASE.$src.'" class="medialeft lic'.$img.'" alt="'.$lic['name'].'" /></a> ';
        }
    }
    if(!$imgonly) {
        $out .= $lang['license'];
        $out .= ' <a href="'.$lic['url'].'" rel="license" class="urlextern"';
        if($conf['target']['extern']) $out .= ' target="'.$conf['target']['extern'].'"';
        $out .= '>'.$lic['name'].'</a>';
    }
    $out .= '</div>';

    if($return) return $out;
    echo $out;
}


/**
 * Includes the rendered XHTML of a given page
 *
 * This function is useful to populate sidebars or similar features in a
 * template
 */
function tpl_include_page($pageid,$print=true){
    global $ID;
    $oldid = $ID;
    $html = p_wiki_xhtml($pageid,'',false);
    $ID = $oldid;

    if(!$print) return $html;
    echo $html;
}

/**
 * Display the subscribe form
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function tpl_subscribe() {
    global $INFO;
    global $ID;
    global $lang;
    global $conf;
    $stime_days = $conf['subscribe_time']/60/60/24;

    echo p_locale_xhtml('subscr_form');
    echo '<h2>' . $lang['subscr_m_current_header'] . '</h2>';
    echo '<div class="level2">';
    if ($INFO['subscribed'] === false) {
        echo '<p>' . $lang['subscr_m_not_subscribed'] . '</p>';
    } else {
        echo '<ul>';
        foreach($INFO['subscribed'] as $sub) {
            echo '<li><div class="li">';
            if ($sub['target'] !== $ID) {
                echo '<code class="ns">'.hsc(prettyprint_id($sub['target'])).'</code>';
            } else {
                echo '<code class="page">'.hsc(prettyprint_id($sub['target'])).'</code>';
            }
            $sstl = sprintf($lang['subscr_style_'.$sub['style']], $stime_days);
            if(!$sstl) $sstl = hsc($sub['style']);
            echo ' ('.$sstl.') ';

            echo '<a href="' . wl($ID,
                                  array('do'=>'subscribe',
                                        'sub_target'=>$sub['target'],
                                        'sub_style'=>$sub['style'],
                                        'sub_action'=>'unsubscribe',
                                        'sectok' => getSecurityToken())) .
                 '" class="unsubscribe">'.$lang['subscr_m_unsubscribe'] .
                 '</a></div></li>';
        }
        echo '</ul>';
    }
    echo '</div>';

    // Add new subscription form
    echo '<h2>' . $lang['subscr_m_new_header'] . '</h2>';
    echo '<div class="level2">';
    $ns = getNS($ID).':';
    $targets = array(
            $ID => '<code class="page">'.prettyprint_id($ID).'</code>',
            $ns => '<code class="ns">'.prettyprint_id($ns).'</code>',
            );
    $styles = array(
            'every'  => $lang['subscr_style_every'],
            'digest' => sprintf($lang['subscr_style_digest'], $stime_days),
            'list' => sprintf($lang['subscr_style_list'], $stime_days),
            );

    $form = new Doku_Form(array('id' => 'subscribe__form'));
    $form->startFieldset($lang['subscr_m_subscribe']);
    $form->addRadioSet('sub_target', $targets);
    $form->startFieldset($lang['subscr_m_receive']);
    $form->addRadioSet('sub_style', $styles);
    $form->addHidden('sub_action', 'subscribe');
    $form->addHidden('do', 'subscribe');
    $form->addHidden('id', $ID);
    $form->endFieldset();
    $form->addElement(form_makeButton('submit', 'subscribe', $lang['subscr_m_subscribe']));
    html_form('SUBSCRIBE', $form);
    echo '</div>';
}

/**
 * Tries to send already created content right to the browser
 *
 * Wraps around ob_flush() and flush()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tpl_flush(){
    ob_flush();
    flush();
}


/**
 * Returns icon from data/media root directory if it exists, otherwise
 * the one in the template's image directory.
 *
 * @param  bool $abs        - if to use absolute URL
 * @param  string $fileName - file name of icon
 * @author Anika Henke <anika@selfthinker.org>
 */
function tpl_getFavicon($abs=false, $fileName='favicon.ico') {
    if (file_exists(mediaFN($fileName))) {
        return ml($fileName, '', true, '', $abs);
    }

    if($abs) {
        return DOKU_URL.substr(DOKU_TPL.'images/'.$fileName, strlen(DOKU_REL));
    }
    return DOKU_TPL.'images/'.$fileName;
}

/**
 * Returns <link> tag for various icon types (favicon|mobile|generic)
 *
 * @param  array $types - list of icon types to display (favicon|mobile|generic)
 * @author Anika Henke <anika@selfthinker.org>
 */
function tpl_favicon($types=array('favicon')) {

    $return = '';

    foreach ($types as $type) {
        switch($type) {
            case 'favicon':
                $return .= '<link rel="shortcut icon" href="'.tpl_getFavicon().'" />'.NL;
                break;
            case 'mobile':
                $return .= '<link rel="apple-touch-icon" href="'.tpl_getFavicon(false, 'apple-touch-icon.png').'" />'.NL;
                break;
            case 'generic':
                // ideal world solution, which doesn't work in any browser yet
                $return .= '<link rel="icon" href="'.tpl_getFavicon(false, 'icon.svg').'" type="image/svg+xml" />'.NL;
                break;
        }
    }

    return $return;
}

/**
 * Prints full-screen media manager
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function tpl_media() {
    global $DEL, $NS, $IMG, $AUTH, $JUMPTO, $REV, $lang, $fullscreen, $conf;
    $fullscreen = true;
    require_once DOKU_INC.'lib/exe/mediamanager.php';

    if ($_REQUEST['image']) $image = cleanID($_REQUEST['image']);
    if (isset($IMG)) $image = $IMG;
    if (isset($JUMPTO)) $image = $JUMPTO;
    if (isset($REV) && !$JUMPTO) $rev = $REV;

    echo '<div id="mediamanager__page">'.NL;
    echo '<h1>'.$lang['btn_media'].'</h1>'.NL;
    html_msgarea();

    echo '<div class="panel namespaces">'.NL;
    echo '<h2>'.$lang['namespaces'].'</h2>'.NL;
    echo '<div class="panelHeader">';
    echo $lang['media_namespaces'];
    echo '</div>'.NL;

    echo '<div class="panelContent" id="media__tree">'.NL;
    media_nstree($NS);
    echo '</div>'.NL;
    echo '</div>'.NL;

    echo '<div class="panel filelist">'.NL;
    tpl_mediaFileList();
    echo '</div>'.NL;

    echo '<div class="panel file">'.NL;
    echo '<h2 class="a11y">'.$lang['media_file'].'</h2>'.NL;
    tpl_mediaFileDetails($image, $rev);
    echo '</div>'.NL;

    echo '</div>'.NL;
}

//Setup VIM: ex: et ts=4 :

