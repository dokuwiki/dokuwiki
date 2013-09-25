<?php

/**
 * Handler for action media
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Media extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "media";
    }

    /**
     * The Doku_Action interface to specify the required permission level
     * to handle action media
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * The Doku_Action interface to display html for action media
     * Prints full-screen media manager
     * was tpl_media() by
     * @author Kate Arzamastseva <pshns@ukr.net>
     * 
     * @global string $NS
     * @global string $IMG
     * @global string $JUMPTO
     * @global string $REV
     * @global array $lang
     * @global boolean $fullscreen
     * @global array $INPUT
     */
    public function html() {
        global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT;
        $fullscreen = true;
        require_once DOKU_INC.'lib/exe/mediamanager.php';

        $rev   = '';
        $image = cleanID($INPUT->str('image'));
        if(isset($IMG)) $image = $IMG;
        if(isset($JUMPTO)) $image = $JUMPTO;
        if(isset($REV) && !$JUMPTO) $rev = $REV;

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
}
