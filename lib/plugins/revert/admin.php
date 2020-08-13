<?php

use dokuwiki\ChangeLog\PageChangeLog;

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_revert extends DokuWiki_Admin_Plugin
{
    protected $cmd;
    // some vars which might need tuning later
    protected $max_lines = 800; // lines to read from changelog
    protected $max_revs  = 20;  // numer of old revisions to check


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setupLocale();
    }

    /**
     * access for managers
     */
    public function forAdminOnly()
    {
        return false;
    }

    /**
     * return sort order for position in admin menu
     */
    public function getMenuSort()
    {
        return 40;
    }

    /**
     * handle user request
     */
    public function handle()
    {
    }

    /**
     * output appropriate html
     */
    public function html()
    {
        global $INPUT;

        echo $this->locale_xhtml('intro');

        $this->printSearchForm();

        if (is_array($INPUT->param('revert')) && checkSecurityToken()) {
            $this->revertEdits($INPUT->arr('revert'), $INPUT->str('filter'));
        } elseif ($INPUT->has('filter')) {
            $this->listEdits($INPUT->str('filter'));
        }
    }

    /**
     * Display the form for searching spam pages
     */
    protected function printSearchForm()
    {
        global $lang, $INPUT;
        echo '<form action="" method="post"><div class="no">';
        echo '<label>'.$this->getLang('filter').': </label>';
        echo '<input type="text" name="filter" class="edit" value="'.hsc($INPUT->str('filter')).'" /> ';
        echo '<button type="submit">'.$lang['btn_search'].'</button> ';
        echo '<span>'.$this->getLang('note1').'</span>';
        echo '</div></form><br /><br />';
    }

    /**
     * Start the reversion process
     */
    protected function revertEdits($revert, $filter)
    {
        echo '<hr /><br />';
        echo '<p>'.$this->getLang('revstart').'</p>';

        echo '<ul>';
        foreach ($revert as $id) {
            global $REV;

            // find the last non-spammy revision
            $data = '';
            $pagelog = new PageChangeLog($id);
            $old  = $pagelog->getRevisions(0, $this->max_revs);
            if (count($old)) {
                foreach ($old as $REV) {
                    $data = rawWiki($id, $REV);
                    if (strpos($data, $filter) === false) break;
                }
            }

            if ($data) {
                saveWikiText($id, $data, 'old revision restored', false);
                printf('<li><div class="li">'.$this->getLang('reverted').'</div></li>', $id, $REV);
            } else {
                saveWikiText($id, '', '', false);
                printf('<li><div class="li">'.$this->getLang('removed').'</div></li>', $id);
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
    protected function listEdits($filter)
    {
        global $conf;
        global $lang;
        echo '<hr /><br />';
        echo '<form action="" method="post"><div class="no">';
        echo '<input type="hidden" name="filter" value="'.hsc($filter).'" />';
        formSecurityToken();

        $recents = getRecents(0, $this->max_lines);
        echo '<ul>';

        $cnt = 0;
        foreach ($recents as $recent) {
            if ($filter) {
                if (strpos(rawWiki($recent['id']), $filter) === false) continue;
            }

            $cnt++;
            $date = dformat($recent['date']);

            echo ($recent['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) ? '<li class="minor">' : '<li>';
            echo '<div class="li">';
            echo '<input type="checkbox" name="revert[]" value="'.hsc($recent['id']).
                '" checked="checked" id="revert__'.$cnt.'" />';
            echo ' <label for="revert__'.$cnt.'">'.$date.'</label> ';

            echo '<a href="'.wl($recent['id'], "do=diff").'">';
            $p = array();
            $p['src']    = DOKU_BASE.'lib/images/diff.png';
            $p['width']  = 15;
            $p['height'] = 11;
            $p['title']  = $lang['diff'];
            $p['alt']    = $lang['diff'];
            $att = buildAttributes($p);
            echo "<img $att />";
            echo '</a> ';

            echo '<a href="'.wl($recent['id'], "do=revisions").'">';
            $p = array();
            $p['src']    = DOKU_BASE.'lib/images/history.png';
            $p['width']  = 12;
            $p['height'] = 14;
            $p['title']  = $lang['btn_revs'];
            $p['alt']    = $lang['btn_revs'];
            $att = buildAttributes($p);
            echo "<img $att />";
            echo '</a> ';

            echo html_wikilink(':'.$recent['id'], (useHeading('navigation'))?null:$recent['id']);
            echo ' – '.htmlspecialchars($recent['sum']);

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
        echo '<button type="submit">'.$this->getLang('revert').'</button> ';
        printf($this->getLang('note2'), hsc($filter));
        echo '</p>';

        echo '</div></form>';
    }
}
//Setup VIM: ex: et ts=4 :
