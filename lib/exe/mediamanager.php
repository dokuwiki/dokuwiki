<?php

use dokuwiki\Extension\Event;

    if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
    define('DOKU_MEDIAMANAGER',1);

    // for multi uploader:
    @ini_set('session.use_only_cookies',0);

    require_once(DOKU_INC.'inc/init.php');

    global $INPUT;
    global $lang;
    global $conf;
    // handle passed message
    if($INPUT->str('msg1')) msg(hsc($INPUT->str('msg1')),1);
    if($INPUT->str('err')) msg(hsc($INPUT->str('err')),-1);

    global $DEL;
    // get namespace to display (either direct or from deletion order)
    if($INPUT->str('delete')){
        $DEL = cleanID($INPUT->str('delete'));
        $IMG = $DEL;
        $NS  = getNS($DEL);
    }elseif($INPUT->str('edit')){
        $IMG = cleanID($INPUT->str('edit'));
        $NS  = getNS($IMG);
    }elseif($INPUT->str('img')){
        $IMG = cleanID($INPUT->str('img'));
        $NS  = getNS($IMG);
    }else{
        $NS = cleanID($INPUT->str('ns'));
        $IMG = null;
    }

    global $INFO, $JSINFO;
    $INFO = !empty($INFO) ? array_merge($INFO, mediainfo()) : mediainfo();
    $JSINFO['id']        = '';
    $JSINFO['namespace'] = '';
    $AUTH = $INFO['perm'];    // shortcut for historical reasons

    $tmp = array();
    Event::createAndTrigger('MEDIAMANAGER_STARTED', $tmp);
    session_write_close();  //close session

    // do not display the manager if user does not have read access
    if($AUTH < AUTH_READ && !$fullscreen) {
        http_status(403);
        die($lang['accessdenied']);
    }

    // handle flash upload
    if(isset($_FILES['Filedata'])){
        $_FILES['upload'] =& $_FILES['Filedata'];
        $JUMPTO = media_upload($NS,$AUTH);
        if($JUMPTO == false){
            http_status(400);
            echo 'Upload failed';
        }
        echo 'ok';
        exit;
    }

    // give info on PHP caught upload errors
    if(!empty($_FILES['upload']['error'])){
        switch($_FILES['upload']['error']){
            case 1:
            case 2:
                msg(sprintf($lang['uploadsize'],
                    filesize_h(php_to_byte(ini_get('upload_max_filesize')))),-1);
                break;
            default:
                msg($lang['uploadfail'].' ('.$_FILES['upload']['error'].')',-1);
        }
        unset($_FILES['upload']);
    }

    // handle upload
    if(!empty($_FILES['upload']['tmp_name'])){
        $JUMPTO = media_upload($NS,$AUTH);
        if($JUMPTO) $NS = getNS($JUMPTO);
    }

    // handle meta saving
    if($IMG && @array_key_exists('save', $INPUT->arr('do'))){
        $JUMPTO = media_metasave($IMG,$AUTH,$INPUT->arr('meta'));
    }

    if($IMG && ($INPUT->str('mediado') == 'save' || @array_key_exists('save', $INPUT->arr('mediado')))) {
        $JUMPTO = media_metasave($IMG,$AUTH,$INPUT->arr('meta'));
    }

    if ($INPUT->int('rev') && $conf['mediarevisions']) $REV = $INPUT->int('rev');

    if($INPUT->str('mediado') == 'restore' && $conf['mediarevisions']){
        $JUMPTO = media_restore($INPUT->str('image'), $REV, $AUTH);
    }

    // handle deletion
    if($DEL) {
        $res = 0;
        if(checkSecurityToken()) {
            $res = media_delete($DEL,$AUTH);
        }
        if ($res & DOKU_MEDIA_DELETED) {
            $msg = sprintf($lang['deletesucc'], noNS($DEL));
            if ($res & DOKU_MEDIA_EMPTY_NS && !$fullscreen) {
                // current namespace was removed. redirecting to root ns passing msg along
                send_redirect(DOKU_URL.'lib/exe/mediamanager.php?msg1='.
                        rawurlencode($msg).'&edid='.$INPUT->str('edid'));
            }
            msg($msg,1);
        } elseif ($res & DOKU_MEDIA_INUSE) {
            if(!$conf['refshow']) {
                msg(sprintf($lang['mediainuse'],noNS($DEL)),0);
            }
        } else {
            msg(sprintf($lang['deletefail'],noNS($DEL)),-1);
        }
    }
    // finished - start output

    if (!$fullscreen) {
        header('Content-Type: text/html; charset=utf-8');
        include(template('mediamanager.php'));
    }

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
