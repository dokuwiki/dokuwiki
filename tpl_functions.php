<?php

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LF')) define('DOKU_LF',"\n");

/**
 * Create link/button to discussion page and back
 */
function _tpl_discussion($discussNS='discussion:',$link=0) {
    global $ID;
    global $lang;
    if(substr($ID,0,strlen($discussNS))==$discussNS) {
        $backID = substr(strstr($ID,':'),1);
        if ($link)
            tpl_pagelink(':'.$backID,$lang['btn_back']);
        else
            echo html_btn('back',$backID,'',array());
    } else {
        if ($link)
            tpl_pagelink($discussNS.$ID,tpl_getLang('btn_discussion'));
        else
            echo html_btn('discussion',$discussNS.$ID,'',array());
    }
}
