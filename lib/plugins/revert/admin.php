<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
require_once(DOKU_INC.'inc/changelog.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_revert extends DokuWiki_Admin_Plugin {
        var $cmd;

    /**
     * Constructor
     */
    function admin_plugin_revert(){
        $this->setupLocale();
    }

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => '2005-09-04',
            'name'   => 'Revert Manager',
            'desc'   => 'Allows you to mass revert recent edits',
            'url'    => 'http://wiki.splitbrain.org/plugin:revert',
        );
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 40;
    }

    /**
     * handle user request
     */
    function handle() {
    }

    /**
     * output appropriate html
     */
    function html() {
        print $this->plugin_locale_xhtml('intro');

        if(is_array($_REQUEST['revert'])) $this->_revert($_REQUEST['revert']);


        echo '<form method="post">';
        echo '<input type="text" name="filter" class="edit" />';
        echo '<input type="submit" class="button" />';
        echo '</form>';

        $this->_list($_REQUEST['filter']);
    }

    function _revert($revert){
        global $conf;
        echo '<hr /><div>';
        foreach($revert as $id){
            global $REV;
            $old = getRevisions($id, 0, 1);
            $REV = $old[0];
            if($REV){
                saveWikiText($id,rawWiki($id,$REV),'old revision restored',false);
                echo "$id reverted to $REV<br />";
            }else{
                saveWikiText($id,'','',false);
                echo "$id removed<br />";
            }
            @set_time_limit(10);
            flush();
        }
        echo '</div><hr />';
    }

    function _list($filter){
        global $conf;
        echo '<form method="post">';

        $recents = getRecents(0,800);
        print '<ul>';

        foreach($recents as $recent){
            if($filter){
                if(strpos(rawWiki($recent['id']),$filter) === false) continue;
            }


            $date = date($conf['dformat'],$recent['date']);

            print ($recent['type']==='e') ? '<li class="minor">' : '<li>';
            print '<div class="li">';

            print '<input type="checkbox" name="revert[]" value="'.hsc($recent['id']).'" checked=checked />';


            print $date.' ';

            print '<a href="'.wl($recent['id'],"do=diff").'">';
            $p = array();
            $p['src']    = DOKU_BASE.'lib/images/diff.png';
            $p['width']  = 15;
            $p['height'] = 11;
            $p['title']  = $lang['diff'];
            $p['alt']    = $lang['diff'];
            $att = buildAttributes($p);
            print "<img $att />";
            print '</a> ';

            print '<a href="'.wl($recent['id'],"do=revisions").'">';
            $p = array();
            $p['src']    = DOKU_BASE.'lib/images/history.png';
            $p['width']  = 12;
            $p['height'] = 14;
            $p['title']  = $lang['btn_revs'];
            $p['alt']    = $lang['btn_revs'];
            $att = buildAttributes($p);
            print "<img $att />";
            print '</a> ';

            print html_wikilink(':'.$recent['id'],$conf['useheading']?NULL:$recent['id']);
            print ' &ndash; '.htmlspecialchars($recent['sum']);

            print ' <span class="user">';
                print $recent['user'].' '.$recent['ip'];
            print '</span>';

            print '</div>';
            print '</li>';

            @set_time_limit(10);
            flush();
        }
        print '</ul>';

        echo '<input type="submit">';
        echo '</form>';
    }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
