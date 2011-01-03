<?php
/**
 * Template Functions
 *
 * This file provides template specific custom functions that are
 * not provided by the DokuWiki core.
 * It is common practice to start each function with an underscore
 * to make sure it won't interfere with future core functions.
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

/**
 * Create link/button to discussion page and back
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_discussion($discussionPage,$title,$backTitle,$link=0,$wrapper=0) {
    global $ID;

    $discussPage    = str_replace('@ID@',$ID,$discussionPage);
    $discussPageRaw = str_replace('@ID@','',$discussionPage);
    $isDiscussPage  = strpos($ID,$discussPageRaw)!==false;
    $backID         = str_replace($discussPageRaw,'',$ID);

    if ($wrapper) echo "<$wrapper>";

    if ($isDiscussPage) {
        if ($link)
            tpl_pagelink($backID,$backTitle);
        else
            echo html_btn('back2article',$backID,'',array(),'get',0,$backTitle);
    } else {
        if ($link)
            tpl_pagelink($discussPage,$title);
        else
            echo html_btn('discussion',$discussPage,'',array(),'get',0,$title);
    }

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Create link/button to user page
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_userpage($userPage,$title,$link=0,$wrapper=0) {
    if (!$_SERVER['REMOTE_USER']) return;

    global $conf;
    $userPage = str_replace('@USER@',$_SERVER['REMOTE_USER'],$userPage);

    if ($wrapper) echo "<$wrapper>";

    if ($link)
        tpl_pagelink($userPage,$title);
    else
        echo html_btn('userpage',$userPage,'',array(),'get',0,$title);

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Wrapper around custom template actions
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_action($type,$link=0,$wrapper=0) {
    switch ($type) {
        case 'discussion':
            if (tpl_getConf('discussionPage')) {
                _tpl_discussion(tpl_getConf('discussionPage'),tpl_getLang('discussion'),tpl_getLang('back_to_article'),$link,$wrapper);
            }
            break;
        case 'userpage':
            if (tpl_getConf('userPage')) {
                _tpl_userpage(tpl_getConf('userPage'),tpl_getLang('userpage'),$link,$wrapper);
            }
            break;
    }
}

/**
 * Use favicon.ico from data/media root directory if it exists, otherwise use
 * the one in the template's image directory.
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_getFavicon() {
    if (file_exists(mediaFN('favicon.ico')))
        return ml('favicon.ico');
    return DOKU_TPL.'images/favicon.ico';
}
