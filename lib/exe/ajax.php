<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && !empty($HTTP_RAW_POST_DATA)){
    parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
//close session
session_write_close();

header('Content-Type: text/html; charset=utf-8');


//call the requested function
if(isset($_POST['call'])){
    $call = $_POST['call'];
}else if(isset($_GET['call'])){
    $call = $_GET['call'];
}else{
    exit;
}
$callfn = 'ajax_'.$call;

if(function_exists($callfn)){
    $callfn();
}else{
    $evt = new Doku_Event('AJAX_CALL_UNKNOWN', $call);
    if ($evt->advise_before()) {
        print "AJAX call '".htmlspecialchars($call)."' unknown!\n";
        exit;
    }
    $evt->advise_after();
    unset($evt);
}

/**
 * Searches for matching pagenames
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_qsearch(){
    global $conf;
    global $lang;

    $query = $_POST['q'];
    if(empty($query)) $query = $_GET['q'];
    if(empty($query)) return;

    $data = ft_pageLookup($query, true, useHeading('navigation'));

    if(!count($data)) return;

    print '<strong>'.$lang['quickhits'].'</strong>';
    print '<ul>';
    foreach($data as $id => $title){
        if (useHeading('navigation')) {
            $name = $title;
        } else {
            $ns = getNS($id);
            if($ns){
                $name = noNS($id).' ('.$ns.')';
            }else{
                $name = $id;
            }
        }
        echo '<li>' . html_wikilink(':'.$id,$name) . '</li>';
    }
    print '</ul>';
}

/**
 * Support OpenSearch suggestions
 *
 * @link   http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.0
 * @author Mike Frysinger <vapier@gentoo.org>
 */
function ajax_suggestions() {
    global $conf;
    global $lang;

    $query = cleanID($_POST['q']);
    if(empty($query)) $query = cleanID($_GET['q']);
    if(empty($query)) return;

    $data = array();
    $data = ft_pageLookup($query);
    if(!count($data)) return;
    $data = array_keys($data);

    // limit results to 15 hits
    $data = array_slice($data, 0, 15);
    $data = array_map('trim',$data);
    $data = array_map('noNS',$data);
    $data = array_unique($data);
    sort($data);

    /* now construct a json */
    $suggestions = array(
                        $query,  // the original query
                        $data,   // some suggestions
                        array(), // no description
                        array()  // no urls
                   );
    $json = new JSON();

    header('Content-Type: application/x-suggestions+json');
    print $json->encode($suggestions);
}

/**
 * Refresh a page lock and save draft
 *
 * Andreas Gohr <andi@splitbrain.org>
 */
function ajax_lock(){
    global $conf;
    global $lang;
    global $ID;
    global $INFO;

    $ID = cleanID($_POST['id']);
    if(empty($ID)) return;

    $INFO = pageinfo();

    if (!$INFO['writable']) {
        echo 'Permission denied';
        return;
    }

    if(!checklock($ID)){
        lock($ID);
        echo 1;
    }

    if($conf['usedraft'] && $_POST['wikitext']){
        $client = $_SERVER['REMOTE_USER'];
        if(!$client) $client = clientIP(true);

        $draft = array('id'     => $ID,
                'prefix' => substr($_POST['prefix'], 0, -1),
                'text'   => $_POST['wikitext'],
                'suffix' => $_POST['suffix'],
                'date'   => (int) $_POST['date'],
                'client' => $client,
                );
        $cname = getCacheName($draft['client'].$ID,'.draft');
        if(io_saveFile($cname,serialize($draft))){
            echo $lang['draftdate'].' '.dformat();
        }
    }

}

/**
 * Delete a draft
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_draftdel(){
    $id = cleanID($_REQUEST['id']);
    if(empty($id)) return;

    $client = $_SERVER['REMOTE_USER'];
    if(!$client) $client = clientIP(true);

    $cname = getCacheName($client.$id,'.draft');
    @unlink($cname);
}

/**
 * Return subnamespaces for the Mediamanager
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_medians(){
    global $conf;

    // wanted namespace
    $ns  = cleanID($_POST['ns']);
    $dir  = utf8_encodeFN(str_replace(':','/',$ns));

    $lvl = count(explode(':',$ns));

    $data = array();
    search($data,$conf['mediadir'],'search_index',array('nofiles' => true),$dir);
    foreach(array_keys($data) as $item){
        $data[$item]['level'] = $lvl+1;
    }
    echo html_buildlist($data, 'idx', 'media_nstree_item', 'media_nstree_li');
}

/**
 * Return list of files for the Mediamanager
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_medialist(){
    global $conf;
    global $NS;

    $NS = $_POST['ns'];
    if ($_POST['do'] == 'media') {
        tpl_mediaFileList();
    } else {
        tpl_mediaContent(true);
    }
}

/**
 * Return the content of the right column
 * (image details) for the Mediamanager
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function ajax_mediadetails(){
    global $DEL, $NS, $IMG, $AUTH, $JUMPTO, $REV, $lang, $fullscreen, $conf;
    $fullscreen = true;
    require_once(DOKU_INC.'lib/exe/mediamanager.php');

    if ($_REQUEST['image']) $image = cleanID($_REQUEST['image']);
    if (isset($IMG)) $image = $IMG;
    if (isset($JUMPTO)) $image = $JUMPTO;
    if (isset($REV) && !$JUMPTO) $rev = $REV;

    html_msgarea();
    tpl_mediaFileDetails($image, $rev);
}

/**
 * Returns image diff representation for mediamanager
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function ajax_mediadiff(){
    global $NS;

    if ($_REQUEST['image']) $image = cleanID($_REQUEST['image']);
    $NS = $_POST['ns'];
    $auth = auth_quickaclcheck("$ns:*");
    media_diff($image, $NS, $auth, true);
}

function ajax_mediaupload(){
    global $NS, $MSG;

    if ($_FILES['qqfile']['tmp_name']) {
        $id = ((empty($_POST['mediaid'])) ? $_FILES['qqfile']['name'] : $_POST['mediaid']);
    } elseif (isset($_GET['qqfile'])) {
        $id = $_GET['qqfile'];
    }

    $id = cleanID($id, false, true);

    $NS = $_REQUEST['ns'];
    $ns = $NS.':'.getNS($id);

    $AUTH = auth_quickaclcheck("$ns:*");
    if($AUTH >= AUTH_UPLOAD) { io_createNamespace("$ns:xxx", 'media'); }

    if ($_FILES['qqfile']['error']) unset($_FILES['qqfile']);

    if ($_FILES['qqfile']['tmp_name']) $res = media_upload($NS, $AUTH, $_FILES['qqfile']);
    if (isset($_GET['qqfile'])) $res = media_upload_xhr($NS, $AUTH);

    if ($res) $result = array('success' => true,
        'link' => media_managerURL(array('ns' => $ns, 'image' => $NS.':'.$id), '&'),
        'id' => $NS.':'.$id, 'ns' => $NS);

    if (!$result) {
        $error = '';
        if (isset($MSG)) {
            foreach($MSG as $msg) $error .= $msg['msg'];
        }
        $result = array('error' => $msg['msg'], 'ns' => $NS);
    }
    $json = new JSON;
    echo htmlspecialchars($json->encode($result), ENT_NOQUOTES);
}

function dir_delete($path) {
    if (!is_string($path) || $path == "") return false;

    if (is_dir($path) && !is_link($path)) {
        if (!$dh = @opendir($path)) return false;

        while ($f = readdir($dh)) {
            if ($f == '..' || $f == '.') continue;
            dir_delete("$path/$f");
        }

        closedir($dh);
        return @rmdir($path);
    } else {
        return @unlink($path);
    }

    return false;
}

/**
 * Return sub index for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_index(){
    global $conf;

    // wanted namespace
    $ns  = cleanID($_POST['idx']);
    $dir  = utf8_encodeFN(str_replace(':','/',$ns));

    $lvl = count(explode(':',$ns));

    $data = array();
    search($data,$conf['datadir'],'search_index',array('ns' => $ns),$dir);
    foreach(array_keys($data) as $item){
        $data[$item]['level'] = $lvl+1;
    }
    echo html_buildlist($data, 'idx', 'html_list_index', 'html_li_index');
}

/**
 * List matching namespaces and pages for the link wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function ajax_linkwiz(){
    global $conf;
    global $lang;

    $q  = ltrim(trim($_POST['q']),':');
    $id = noNS($q);
    $ns = getNS($q);

    $ns = cleanID($ns);
    $id = cleanID($id);

    $nsd  = utf8_encodeFN(str_replace(':','/',$ns));
    $idd  = utf8_encodeFN(str_replace(':','/',$id));

    $data = array();
    if($q && !$ns){

        // use index to lookup matching pages
        $pages = array();
        $pages = ft_pageLookup($id,true);

        // result contains matches in pages and namespaces
        // we now extract the matching namespaces to show
        // them seperately
        $dirs  = array();

        foreach($pages as $pid => $title){
            if(strpos(noNS($pid),$id) === false){
                // match was in the namespace
                $dirs[getNS($pid)] = 1; // assoc array avoids dupes
            }else{
                // it is a matching page, add it to the result
                $data[] = array(
                        'id'    => $pid,
                        'title' => $title,
                        'type'  => 'f',
                        );
            }
            unset($pages[$pid]);
        }
        foreach($dirs as $dir => $junk){
            $data[] = array(
                    'id'   => $dir,
                    'type' => 'd',
                    );
        }

    }else{

        $opts = array(
                'depth' => 1,
                'listfiles' => true,
                'listdirs'  => true,
                'pagesonly' => true,
                'firsthead' => true,
                'sneakyacl' => $conf['sneaky_index'],
                );
        if($id) $opts['filematch'] = '^.*\/'.$id;
        if($id) $opts['dirmatch']  = '^.*\/'.$id;
        search($data,$conf['datadir'],'search_universal',$opts,$nsd);

        // add back to upper
        if($ns){
            array_unshift($data,array(
                        'id'   => getNS($ns),
                        'type' => 'u',
                        ));
        }
    }

    // fixme sort results in a useful way ?

    if(!count($data)){
        echo $lang['nothingfound'];
        exit;
    }

    // output the found data
    $even = 1;
    foreach($data as $item){
        $even *= -1; //zebra

        if(($item['type'] == 'd' || $item['type'] == 'u') && $item['id']) $item['id'] .= ':';
        $link = wl($item['id']);

        echo '<div class="'.(($even > 0)?'even':'odd').' type_'.$item['type'].'">';

        if($item['type'] == 'u'){
            $name = $lang['upperns'];
        }else{
            $name = htmlspecialchars($item['id']);
        }

        echo '<a href="'.$link.'" title="'.htmlspecialchars($item['id']).'" class="wikilink1">'.$name.'</a>';

        if($item['title']){
            echo '<span>'.htmlspecialchars($item['title']).'</span>';
        }
        echo '</div>';
    }

}

//Setup VIM: ex: et ts=2 :
