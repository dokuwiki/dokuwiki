<?php
    if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
    define('DOKU_MEDIAMANAGER',1);
    require_once(DOKU_INC.'inc/init.php');
    require_once(DOKU_INC.'inc/lang/en/lang.php');
    require_once(DOKU_INC.'inc/lang/'.$conf['lang'].'/lang.php');
    require_once(DOKU_INC.'inc/media.php');
    require_once(DOKU_INC.'inc/common.php');
    require_once(DOKU_INC.'inc/search.php');
    require_once(DOKU_INC.'inc/template.php');
    require_once(DOKU_INC.'inc/auth.php');
    session_write_close();  //close session

    // handle passed message
    if($_REQUEST['msg1']) msg(hsc($_REQUEST['msg1']),1);


    // get namespace to display (either direct or from deletion order)
    if($_REQUEST['delete']){
        $DEL = cleanID($_REQUEST['delete']);
        $IMG = $DEL;
        $NS  = getNS($DEL);
    }elseif($_REQUEST['edit']){
        $IMG = cleanID($_REQUEST['edit']);
        $NS  = getNS($IMG);
    }elseif($_REQUEST['img']){
        $IMG = cleanID($_REQUEST['img']);
        $NS  = getNS($IMG);
    }else{
        $NS = $_REQUEST['ns'];
        $NS = cleanID($NS);
    }

    // check auth
    $AUTH = auth_quickaclcheck("$NS:*");

    // create the given namespace (just for beautification)
    if($AUTH >= AUTH_UPLOAD) { io_createNamespace("$NS:xxx", 'media'); }

    // handle upload
    if($_FILES['upload']['tmp_name']){
        $JUMPTO = media_upload($NS,$AUTH);
        if($JUMPTO) $NS = getNS($JUMPTO);
    }

    // handle meta saving
    if($IMG && $_REQUEST['do']['save']){
        $JUMPTO = media_metasave($IMG,$AUTH,$_REQUEST['meta']);
    }

    // handle deletion
    if($DEL) {
        $INUSE = media_delete($DEL,$AUTH);
    }

    // finished - start output
    header('Content-Type: text/html; charset=utf-8');
    include(template('mediamanager.php'));
