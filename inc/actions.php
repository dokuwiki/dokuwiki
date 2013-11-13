<?php
/**
 * DokuWiki Actions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * Call the needed action handlers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @triggers ACTION_ACT_PREPROCESS
 * @triggers ACTION_HEADERS_SEND
 */
function act_dispatch(){
    global $ACT;
    global $ID;
    global $INFO;
    global $QUERY;
    global $INPUT;
    global $lang;
    global $conf;

    $preact = $ACT;

    // give plugins an opportunity to process the action
    $evt = new Doku_Event('ACTION_ACT_PREPROCESS',$ACT);
    if ($evt->advise_before()) {

        //sanitize $ACT
        $ACT = act_validate($ACT);

        //check if searchword was given - else just show
        $s = cleanID($QUERY);
        if($ACT == 'search' && empty($s)){
            $ACT = 'show';
        }

        //login stuff
        if(in_array($ACT,array('login','logout'))){
            $ACT = act_auth($ACT);
        }

        //check if user is asking to (un)subscribe a page
        if($ACT == 'subscribe') {
            try {
                $ACT = act_subscription($ACT);
            } catch (Exception $e) {
                msg($e->getMessage(), -1);
            }
        }

        //display some info
        if($ACT == 'check'){
            check();
            $ACT = 'show';
        }

        //check permissions
        $ACT = act_permcheck($ACT);

        //sitemap
        if ($ACT == 'sitemap'){
            act_sitemap($ACT);
        }

        //recent changes
        if ($ACT == 'recent'){
            $show_changes = $INPUT->str('show_changes');
            if (!empty($show_changes)) {
                set_doku_pref('show_changes', $show_changes);
            }
        }

        //diff
        if ($ACT == 'diff'){
            $difftype = $INPUT->str('difftype');
            if (!empty($difftype)) {
                set_doku_pref('difftype', $difftype);
            }
        }

        //register
        if($ACT == 'register' && $INPUT->post->bool('save') && register()){
            $ACT = 'login';
        }

        if ($ACT == 'resendpwd' && act_resendpwd()) {
            $ACT = 'login';
        }

        // user profile changes
        if (in_array($ACT, array('profile','profile_delete'))) {
            if(!$_SERVER['REMOTE_USER']) {
                $ACT = 'login';
            } else {
                switch ($ACT) {
                    case 'profile' :
                        if(updateprofile()) {
                            msg($lang['profchanged'],1);
                            $ACT = 'show';
                        }
                        break;
                    case 'profile_delete' :
                        if(auth_deleteprofile()){
                            msg($lang['profdeleted'],1);
                            $ACT = 'show';
                        } else {
                            $ACT = 'profile';
                        }
                        break;
                }
            }
        }

        //revert
        if($ACT == 'revert'){
            if(checkSecurityToken()){
                $ACT = act_revert($ACT);
            }else{
                $ACT = 'show';
            }
        }

        //save
        if($ACT == 'save'){
            if(checkSecurityToken()){
                $ACT = act_save($ACT);
            }else{
                $ACT = 'preview';
            }
        }

        //cancel conflicting edit
        if($ACT == 'cancel')
            $ACT = 'show';

        //draft deletion
        if($ACT == 'draftdel')
            $ACT = act_draftdel($ACT);

        //draft saving on preview
        if($ACT == 'preview')
            $ACT = act_draftsave($ACT);

        //edit
        if(in_array($ACT, array('edit', 'preview', 'recover'))) {
            $ACT = act_edit($ACT);
        }else{
            unlock($ID); //try to unlock
        }

        //handle export
        if(substr($ACT,0,7) == 'export_')
            $ACT = act_export($ACT);

        //handle admin tasks
        if($ACT == 'admin'){
            // retrieve admin plugin name from $_REQUEST['page']
            if (($page = $INPUT->str('page', '', true)) != '') {
                $pluginlist = plugin_list('admin');
                if (in_array($page, $pluginlist)) {
                    // attempt to load the plugin

                    if (($plugin = plugin_load('admin',$page)) !== null){
                        /** @var DokuWiki_Admin_Plugin $plugin */
                        if($plugin->forAdminOnly() && !$INFO['isadmin']){
                            // a manager tried to load a plugin that's for admins only
                            $INPUT->remove('page');
                            msg('For admins only',-1);
                        }else{
                            $plugin->handle();
                        }
                    }
                }
            }
        }

        // check permissions again - the action may have changed
        $ACT = act_permcheck($ACT);
    }  // end event ACTION_ACT_PREPROCESS default action
    $evt->advise_after();
    // Make sure plugs can handle 'denied'
    if($conf['send404'] && $ACT == 'denied') {
        http_status(403);
    }
    unset($evt);

    // when action 'show', the intial not 'show' and POST, do a redirect
    if($ACT == 'show' && $preact != 'show' && strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
        act_redirect($ID,$preact);
    }

    global $INFO;
    global $conf;
    global $license;

    //call template FIXME: all needed vars available?
    $headers[] = 'Content-Type: text/html; charset=utf-8';
    trigger_event('ACTION_HEADERS_SEND',$headers,'act_sendheaders');

    include(template('main.php'));
    // output for the commands is now handled in inc/templates.php
    // in function tpl_content()
}

/**
 * Send the given headers using header()
 *
 * @param array $headers The headers that shall be sent
 */
function act_sendheaders($headers) {
    foreach ($headers as $hdr) header($hdr);
}

/**
 * Sanitize the action command
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_clean($act){
    // check if the action was given as array key
    if(is_array($act)){
        list($act) = array_keys($act);
    }

    //remove all bad chars
    $act = strtolower($act);
    $act = preg_replace('/[^1-9a-z_]+/','',$act);

    if($act == 'export_html') $act = 'export_xhtml';
    if($act == 'export_htmlbody') $act = 'export_xhtmlbody';

    if($act === '') $act = 'show';
    return $act;
}

/**
 * Sanitize and validate action commands.
 *
 * Add all allowed commands here.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_validate($act) {
    global $conf;
    global $INFO;

    $act = act_clean($act);

    // check if action is disabled
    if(!actionOK($act)){
        msg('Command disabled: '.htmlspecialchars($act),-1);
        return 'show';
    }

    //disable all acl related commands if ACL is disabled
    if(!$conf['useacl'] && in_array($act,array('login','logout','register','admin',
                    'subscribe','unsubscribe','profile','revert',
                    'resendpwd','profile_delete'))){
        msg('Command unavailable: '.htmlspecialchars($act),-1);
        return 'show';
    }

    //is there really a draft?
    if($act == 'draft' && !file_exists($INFO['draft'])) return 'edit';

    if(!in_array($act,array('login','logout','register','save','cancel','edit','draft',
                    'preview','search','show','check','index','revisions',
                    'diff','recent','backlink','admin','subscribe','revert',
                    'unsubscribe','profile','profile_delete','resendpwd','recover',
                    'draftdel','sitemap','media')) && substr($act,0,7) != 'export_' ) {
        msg('Command unknown: '.htmlspecialchars($act),-1);
        return 'show';
    }
    return $act;
}

/**
 * Run permissionchecks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_permcheck($act){
    global $INFO;
    global $conf;

    if(in_array($act,array('save','preview','edit','recover'))){
        if($INFO['exists']){
            if($act == 'edit'){
                //the edit function will check again and do a source show
                //when no AUTH_EDIT available
                $permneed = AUTH_READ;
            }else{
                $permneed = AUTH_EDIT;
            }
        }else{
            $permneed = AUTH_CREATE;
        }
    }elseif(in_array($act,array('login','search','recent','profile','profile_delete','index', 'sitemap'))){
        $permneed = AUTH_NONE;
    }elseif($act == 'revert'){
        $permneed = AUTH_ADMIN;
        if($INFO['ismanager']) $permneed = AUTH_EDIT;
    }elseif($act == 'register'){
        $permneed = AUTH_NONE;
    }elseif($act == 'resendpwd'){
        $permneed = AUTH_NONE;
    }elseif($act == 'admin'){
        if($INFO['ismanager']){
            // if the manager has the needed permissions for a certain admin
            // action is checked later
            $permneed = AUTH_READ;
        }else{
            $permneed = AUTH_ADMIN;
        }
    }else{
        $permneed = AUTH_READ;
    }
    if($INFO['perm'] >= $permneed) return $act;

    return 'denied';
}

/**
 * Handle 'draftdel'
 *
 * Deletes the draft for the current page and user
 */
function act_draftdel($act){
    global $INFO;
    @unlink($INFO['draft']);
    $INFO['draft'] = null;
    return 'show';
}

/**
 * Saves a draft on preview
 *
 * @todo this currently duplicates code from ajax.php :-/
 */
function act_draftsave($act){
    global $INFO;
    global $ID;
    global $INPUT;
    global $conf;
    if($conf['usedraft'] && $INPUT->post->has('wikitext')) {
        $draft = array('id'     => $ID,
                'prefix' => substr($INPUT->post->str('prefix'), 0, -1),
                'text'   => $INPUT->post->str('wikitext'),
                'suffix' => $INPUT->post->str('suffix'),
                'date'   => $INPUT->post->int('date'),
                'client' => $INFO['client'],
                );
        $cname = getCacheName($draft['client'].$ID,'.draft');
        if(io_saveFile($cname,serialize($draft))){
            $INFO['draft'] = $cname;
        }
    }
    return $act;
}

/**
 * Handle 'save'
 *
 * Checks for spam and conflicts and saves the page.
 * Does a redirect to show the page afterwards or
 * returns a new action.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_save($act){
    global $ID;
    global $DATE;
    global $PRE;
    global $TEXT;
    global $SUF;
    global $SUM;
    global $lang;
    global $INFO;
    global $INPUT;

    //spam check
    if(checkwordblock()) {
        msg($lang['wordblock'], -1);
        return 'edit';
    }
    //conflict check
    if($DATE != 0 && $INFO['meta']['date']['modified'] > $DATE )
        return 'conflict';

    //save it
    saveWikiText($ID,con($PRE,$TEXT,$SUF,1),$SUM,$INPUT->bool('minor')); //use pretty mode for con
    //unlock it
    unlock($ID);

    //delete draft
    act_draftdel($act);
    session_write_close();

    // when done, show page
    return 'show';
}

/**
 * Revert to a certain revision
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_revert($act){
    global $ID;
    global $REV;
    global $lang;
    // FIXME $INFO['writable'] currently refers to the attic version
    // global $INFO;
    // if (!$INFO['writable']) {
    //     return 'show';
    // }

    // when no revision is given, delete current one
    // FIXME this feature is not exposed in the GUI currently
    $text = '';
    $sum  = $lang['deleted'];
    if($REV){
        $text = rawWiki($ID,$REV);
        if(!$text) return 'show'; //something went wrong
        $sum = sprintf($lang['restored'], dformat($REV));
    }

    // spam check

    if (checkwordblock($text)) {
        msg($lang['wordblock'], -1);
        return 'edit';
    }

    saveWikiText($ID,$text,$sum,false);
    msg($sum,1);

    //delete any draft
    act_draftdel($act);
    session_write_close();

    // when done, show current page
    $_SERVER['REQUEST_METHOD'] = 'post'; //should force a redirect
    $REV = '';
    return 'show';
}

/**
 * Do a redirect after receiving post data
 *
 * Tries to add the section id as hash mark after section editing
 */
function act_redirect($id,$preact){
    global $PRE;
    global $TEXT;

    $opts = array(
            'id'       => $id,
            'preact'   => $preact
            );
    //get section name when coming from section edit
    if($PRE && preg_match('/^\s*==+([^=\n]+)/',$TEXT,$match)){
        $check = false; //Byref
        $opts['fragment'] = sectionID($match[0], $check);
    }

    trigger_event('ACTION_SHOW_REDIRECT',$opts,'act_redirect_execute');
}

/**
 * Execute the redirect
 *
 * @param array $opts id and fragment for the redirect
 */
function act_redirect_execute($opts){
    $go = wl($opts['id'],'',true);
    if(isset($opts['fragment'])) $go .= '#'.$opts['fragment'];

    //show it
    send_redirect($go);
}

/**
 * Handle 'login', 'logout'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_auth($act){
    global $ID;
    global $INFO;

    //already logged in?
    if(isset($_SERVER['REMOTE_USER']) && $act=='login'){
        return 'show';
    }

    //handle logout
    if($act=='logout'){
        $lockedby = checklock($ID); //page still locked?
        if($lockedby == $_SERVER['REMOTE_USER'])
            unlock($ID); //try to unlock

        // do the logout stuff
        auth_logoff();

        // rebuild info array
        $INFO = pageinfo();

        act_redirect($ID,'login');
    }

    return $act;
}

/**
 * Handle 'edit', 'preview', 'recover'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_edit($act){
    global $ID;
    global $INFO;

    global $TEXT;
    global $RANGE;
    global $PRE;
    global $SUF;
    global $REV;
    global $SUM;
    global $lang;
    global $DATE;

    if (!isset($TEXT)) {
        if ($INFO['exists']) {
            if ($RANGE) {
                list($PRE,$TEXT,$SUF) = rawWikiSlices($RANGE,$ID,$REV);
            } else {
                $TEXT = rawWiki($ID,$REV);
            }
        } else {
            $TEXT = pageTemplate($ID);
        }
    }

    //set summary default
    if(!$SUM){
        if($REV){
            $SUM = sprintf($lang['restored'], dformat($REV));
        }elseif(!$INFO['exists']){
            $SUM = $lang['created'];
        }
    }

    // Use the date of the newest revision, not of the revision we edit
    // This is used for conflict detection
    if(!$DATE) $DATE = @filemtime(wikiFN($ID));

    //check if locked by anyone - if not lock for my self
    //do not lock when the user can't edit anyway
    if ($INFO['writable']) {
        $lockedby = checklock($ID);
        if($lockedby) return 'locked';

        lock($ID);
    }

    return $act;
}

/**
 * Export a wiki page for various formats
 *
 * Triggers ACTION_EXPORT_POSTPROCESS
 *
 *  Event data:
 *    data['id']      -- page id
 *    data['mode']    -- requested export mode
 *    data['headers'] -- export headers
 *    data['output']  -- export output
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 */
function act_export($act){
    global $ID;
    global $REV;
    global $conf;
    global $lang;

    $pre = '';
    $post = '';
    $output = '';
    $headers = array();

    // search engines: never cache exported docs! (Google only currently)
    $headers['X-Robots-Tag'] = 'noindex';

    $mode = substr($act,7);
    switch($mode) {
        case 'raw':
            $headers['Content-Type'] = 'text/plain; charset=utf-8';
            $headers['Content-Disposition'] = 'attachment; filename='.noNS($ID).'.txt';
            $output = rawWiki($ID,$REV);
            break;
        case 'xhtml':
            $pre .= '<!DOCTYPE html>' . DOKU_LF;
            $pre .= '<html lang="'.$conf['lang'].'" dir="'.$lang['direction'].'">' . DOKU_LF;
            $pre .= '<head>' . DOKU_LF;
            $pre .= '  <meta charset="utf-8" />' . DOKU_LF;
            $pre .= '  <title>'.$ID.'</title>' . DOKU_LF;

            // get metaheaders
            ob_start();
            tpl_metaheaders();
            $pre .= ob_get_clean();

            $pre .= '</head>' . DOKU_LF;
            $pre .= '<body>' . DOKU_LF;
            $pre .= '<div class="dokuwiki export">' . DOKU_LF;

            // get toc
            $pre .= tpl_toc(true);

            $headers['Content-Type'] = 'text/html; charset=utf-8';
            $output = p_wiki_xhtml($ID,$REV,false);

            $post .= '</div>' . DOKU_LF;
            $post .= '</body>' . DOKU_LF;
            $post .= '</html>' . DOKU_LF;
            break;
        case 'xhtmlbody':
            $headers['Content-Type'] = 'text/html; charset=utf-8';
            $output = p_wiki_xhtml($ID,$REV,false);
            break;
        default:
            $output = p_cached_output(wikiFN($ID,$REV), $mode);
            $headers = p_get_metadata($ID,"format $mode");
            break;
    }

    // prepare event data
    $data = array();
    $data['id'] = $ID;
    $data['mode'] = $mode;
    $data['headers'] = $headers;
    $data['output'] =& $output;

    trigger_event('ACTION_EXPORT_POSTPROCESS', $data);

    if(!empty($data['output'])){
        if(is_array($data['headers'])) foreach($data['headers'] as $key => $val){
            header("$key: $val");
        }
        print $pre.$data['output'].$post;
        exit;
    }
    return 'show';
}

/**
 * Handle sitemap delivery
 *
 * @author Michael Hamann <michael@content-space.de>
 */
function act_sitemap($act) {
    global $conf;

    if ($conf['sitemap'] < 1 || !is_numeric($conf['sitemap'])) {
        http_status(404);
        print "Sitemap generation is disabled.";
        exit;
    }

    $sitemap = Sitemapper::getFilePath();
    if (Sitemapper::sitemapIsCompressed()) {
        $mime = 'application/x-gzip';
    }else{
        $mime = 'application/xml; charset=utf-8';
    }

    // Check if sitemap file exists, otherwise create it
    if (!is_readable($sitemap)) {
        Sitemapper::generate();
    }

    if (is_readable($sitemap)) {
        // Send headers
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename='.utf8_basename($sitemap));

        http_conditionalRequest(filemtime($sitemap));

        // Send file
        //use x-sendfile header to pass the delivery to compatible webservers
        if (http_sendfile($sitemap)) exit;

        readfile($sitemap);
        exit;
    }

    http_status(500);
    print "Could not read the sitemap file - bad permissions?";
    exit;
}

/**
 * Handle page 'subscribe'
 *
 * Throws exception on error.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function act_subscription($act){
    global $lang;
    global $INFO;
    global $ID;
    global $INPUT;

    // subcriptions work for logged in users only
    if(!$_SERVER['REMOTE_USER']) return 'show';

    // get and preprocess data.
    $params = array();
    foreach(array('target', 'style', 'action') as $param) {
        if ($INPUT->has("sub_$param")) {
            $params[$param] = $INPUT->str("sub_$param");
        }
    }

    // any action given? if not just return and show the subscription page
    if(!$params['action'] || !checkSecurityToken()) return $act;

    // Handle POST data, may throw exception.
    trigger_event('ACTION_HANDLE_SUBSCRIBE', $params, 'subscription_handle_post');

    $target = $params['target'];
    $style  = $params['style'];
    $action = $params['action'];

    // Perform action.
    $sub = new Subscription();
    if($action == 'unsubscribe'){
        $ok = $sub->remove($target, $_SERVER['REMOTE_USER'], $style);
    }else{
        $ok = $sub->add($target, $_SERVER['REMOTE_USER'], $style);
    }

    if($ok) {
        msg(sprintf($lang["subscr_{$action}_success"], hsc($INFO['userinfo']['name']),
                    prettyprint_id($target)), 1);
        act_redirect($ID, $act);
    } else {
        throw new Exception(sprintf($lang["subscr_{$action}_error"],
                                    hsc($INFO['userinfo']['name']),
                                    prettyprint_id($target)));
    }

    // Assure that we have valid data if act_redirect somehow fails.
    $INFO['subscribed'] = $sub->user_subscription();
    return 'show';
}

/**
 * Validate POST data
 *
 * Validates POST data for a subscribe or unsubscribe request. This is the
 * default action for the event ACTION_HANDLE_SUBSCRIBE.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_handle_post(&$params) {
    global $INFO;
    global $lang;

    // Get and validate parameters.
    if (!isset($params['target'])) {
        throw new Exception('no subscription target given');
    }
    $target = $params['target'];
    $valid_styles = array('every', 'digest');
    if (substr($target, -1, 1) === ':') {
        // Allow “list” subscribe style since the target is a namespace.
        $valid_styles[] = 'list';
    }
    $style  = valid_input_set('style', $valid_styles, $params,
                              'invalid subscription style given');
    $action = valid_input_set('action', array('subscribe', 'unsubscribe'),
                              $params, 'invalid subscription action given');

    // Check other conditions.
    if ($action === 'subscribe') {
        if ($INFO['userinfo']['mail'] === '') {
            throw new Exception($lang['subscr_subscribe_noaddress']);
        }
    } elseif ($action === 'unsubscribe') {
        $is = false;
        foreach($INFO['subscribed'] as $subscr) {
            if ($subscr['target'] === $target) {
                $is = true;
            }
        }
        if ($is === false) {
            throw new Exception(sprintf($lang['subscr_not_subscribed'],
                                        $_SERVER['REMOTE_USER'],
                                        prettyprint_id($target)));
        }
        // subscription_set deletes a subscription if style = null.
        $style = null;
    }

    $params = compact('target', 'style', 'action');
}

//Setup VIM: ex: et ts=2 :
