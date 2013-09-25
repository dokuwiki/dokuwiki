<?php

/**
 * Handler for action backlink
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Backlink extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "backlink";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action backlink.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * Doku_Action interface, to display backlinks
     * Was html_backlinks() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Michael Klier <chi@chimeric.de>
     * 
     * @global string $ID
     * @global string $lang
     */
    public function html() {
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
