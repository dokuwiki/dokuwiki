<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
require_once(DOKU_INC.'inc/changelog.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_revert extends DokuWiki_Admin_Plugin {
    var $cmd;
    // some vars which might need tuning later
    var $max_lines = 800; // lines to read from changelog
    var $max_revs  = 20;  // numer of old revisions to check


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
            'date'   => '2008-05-94',
            'name'   => 'Revert Manager',
            'desc'   => 'Allows you to mass revert recent edits',
            'url'    => 'http://wiki.splitbrain.org/plugin:revert',
        );
    }

    /**
     * access for managers
     */
    function forAdminOnly(){
        return false;
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

        echo $this->plugin_locale_xhtml('intro');

        $this->_searchform();

        if(is_array($_REQUEST['revert']) && checkSecurityToken()){
            $this->_revert($_REQUEST['revert'],$_REQUEST['filter']);
        }elseif(isset($_REQUEST['filter'])){
            $this->_list($_REQUEST['filter']);
        }
    }

    /**
     * Display the form for searching spam pages
     */
    function _searchform(){
        global $lang;
        echo '<form action="" method="post"><div class="no">';
        echo '<label>'.$this->getLang('filter').': </label>';
        echo '<input type="text" name="filter" class="edit" value="'.hsc($_REQUEST['filter']).'" />';
        echo '<input type="submit" class="button" value="'.$lang['btn_search'].'" />';
        echo ' <span>'.$this->getLang('note1').'</span>';
        echo '</div></form><br /><br />';
    }

    /**
     * Start the reversion process
     */
    function _revert($revert,$filter){
        global $conf;

        echo '<hr /><br />';
        echo '<p>'.$this->getLang('revstart').'</p>';

        echo '<ul>';
        foreach($revert as $id){
            global $REV;

            // find the last non-spammy revision
            $data = '';
            $old  = getRevisions($id, 0, $this->max_revs);
            if(count($old)){
                foreach($old as $REV){
                    $data = rawWiki($id,$REV);
                    if(strpos($data,$filter) === false) break;
                }
            }

            if($data){
                saveWikiText($id,$data,'old revision restored',false);
                printf('<li><div class="li">'.$this->getLang('reverted').'</div></li>',$id,$REV);
            }else{
                saveWikiText($id,'','',false);
                printf('<li><div class="li">'.$this->getLang('removed').'</div></li>',$id);
            }
            @set_time_limit(10);
            flush();
        }
        echo '</ul>';

        echo '<p>'.$this->getLang('revstop').'</p>';
    }

    /**
     * List recent edits matching the given filter
     */
    function _list($filter){
        global $conf;
        echo '<hr /><br />';
        echo '<form action="" method="post"><div class="no">';
        echo '<input type="hidden" name="filter" value="'.hsc($filter).'" />';
        formSecurityToken();

        $recents = getRecents(0,$this->max_lines);
        echo '<ul>';


        $cnt = 0;
        foreach($recents as $recent){
            if($filter){
                if(strpos(rawWiki($recent['id']),$filter) === false) continue;
            }

            $cnt++;
            $date = strftime($conf['dformat'],$recent['date']);

            echo ($recent['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) ? '<li class="minor">' : '<li>';
            echo '<div class="li">';
            echo '<input type="checkbox" name="revert[]" value="'.hsc($recent['id']).'" checked="checked" id="revert__'.$cnt.'" />';
            echo '<label for="revert__'.$cnt.'">'.$date.'</label> ';

            echo '<a href="'.wl($recent['id'],"do=diff").'">';
            $p = array();
            $p['src']    = DOKU_BASE.'lib/images/diff.png';
            $p['width']  = 15;
            $p['height'] = 11;
            $p['title']  = $lang['diff'];
            $p['alt']    = $lang['diff'];
            $att = buildAttributes($p);
            echo "<img $att />";
            echo '</a> ';

            echo '<a href="'.wl($recent['id'],"do=revisions").'">';
            $p = array();
            $p['src']    = DOKU_BASE.'lib/images/history.png';
            $p['width']  = 12;
            $p['height'] = 14;
            $p['title']  = $lang['btn_revs'];
            $p['alt']    = $lang['btn_revs'];
            $att = buildAttributes($p);
            echo "<img $att />";
            echo '</a> ';

            echo html_wikilink(':'.$recent['id'],$conf['useheading']?NULL:$recent['id']);
            echo ' &ndash; '.htmlspecialchars($recent['sum']);

            echo ' <span class="user">';
                echo $recent['user'].' '.$recent['ip'];
            echo '</span>';

            echo '</div>';
            echo '</li>';

            @set_time_limit(10);
            flush();
        }
        echo '</ul>';

        echo '<p>';
        echo '<input type="submit" class="button" value="'.$this->getLang('revert').'" /> ';
        printf($this->getLang('note2'),hsc($filter));
        echo '</p>';

        echo '</div></form>';
    }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
