<?php

/**
 * Handler for action backlink
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Backlink extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "backlink";
    }

    /**
     * Specifies the required permissions for displaying backlinks.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_READ;
    }
}

/**
 * Renderer for action backlink
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Backlink extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "backlink";
    }

    /**
     * display backlinks
     * Was html_backlinks() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Michael Klier <chi@chimeric.de>
     * 
     * @global string $ID
     * @global array $lang
     */
    public function xhtml() {
        global $ID;
        global $lang;

        print p_locale_xhtml('backlinks');

        $data = ft_backlinks($ID);

        if(!empty($data)) {
            print '<ul class="idx">';
            foreach($data as $blink){
                print '<li><div class="li">';
                print html_wikilink(':'.$blink,useHeading('navigation')?null:$blink);
                print '</div></li>';
            }
            print '</ul>';
        } else {
            print '<div class="level1"><p>' . $lang['nothingfound'] . '</p></div>';
        }
    }
}
