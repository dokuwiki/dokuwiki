<?php
/**
 * Template Functions
 *
 * This file provides template specific custom functions that are
 * not provided by the DokuWiki core.
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

/**
 * Create link/button to discussion page and back
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_discussion($discussNS='discussion',$link=0,$wrapper=0,$reverse=0) {
    global $ID;

    if ($reverse) {
        $discussPage   = $ID.':'.$discussNS;
        $isDiscussPage = substr($ID,-strlen($discussNS),strlen($discussNS))==$discussNS;
        $backID        = substr($ID,0,-strlen($discussNS));
    } else {
        $discussPage   = $discussNS.':'.$ID;
        $isDiscussPage = substr($ID,0,strlen($discussNS))==$discussNS;
        $backID        = strstr($ID,':');
    }

    if ($wrapper) echo "<$wrapper>";

    if($isDiscussPage) {
        if ($link)
            tpl_pagelink($backID,tpl_getLang('back_to_article'));
        else
            echo html_btn('back2article',$backID,'',array(),0,0,tpl_getLang('back_to_article'));
    } else {
        if ($link)
            tpl_pagelink($discussPage,tpl_getLang('discussion'));
        else
            echo html_btn('discussion',$discussPage,'',array(),0,0,tpl_getLang('discussion'));
    }

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Create link/button to user page
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_userpage($userNS='user',$link=0,$wrapper=false) {
    if (!$_SERVER['REMOTE_USER']) return;

    global $conf;
    $userPage = $userNS.':'.$_SERVER['REMOTE_USER'].':'.$conf['start'];

    if ($wrapper) echo "<$wrapper>";

    if ($link)
        tpl_pagelink($userPage,tpl_getLang('userpage'));
    else
        echo html_btn('userpage',$userPage,'',array(),0,0,tpl_getLang('userpage'));

    if ($wrapper) echo "</$wrapper>";
}
