<?php

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LF')) define('DOKU_LF',"\n");

/* @todo: fix label of buttons */

/**
 * Create link/button to discussion page and back
 */
function _tpl_discussion($discussNS='discussion:',$link=0) {
    global $ID;
    if(substr($ID,0,strlen($discussNS))==$discussNS) {
        $backID = substr(strstr($ID,':'),1);
        if ($link)
            tpl_link(wl($backID),tpl_getLang('btn_back2article'));
        else
            echo html_btn('back2article',$backID,'',array());
    } else {
        if ($link)
            tpl_link(wl($discussNS.$ID),tpl_getLang('btn_discussion'));
        else
            echo html_btn('discussion',$discussNS.$ID,'',array());
    }
}

/**
 * Create link/button to user page
 */
function _tpl_userpage($userNS='user:',$link=0) {
    global $conf;
    if ($link)
        tpl_link(wl($userNS.$_SERVER['REMOTE_USER'].':'.$conf['start']),tpl_getLang('btn_userpage'));
    else
        echo html_btn('userpage',$userNS.$_SERVER['REMOTE_USER'].':'.$conf['start'],'',array());
}
