<?php
/**
 * DokuWiki Plugin struct (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\Schema;

if(!defined('DOKU_INC')) die();

class admin_plugin_struct_assignments extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 501;
    }

    /**
     * Return the text that is displayed at the main admin menu
     *
     * @param string $language language code
     * @return string menu string
     */
    public function getMenuText($language) {
        return $this->getLang('menu_assignments');
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        global $INPUT;
        global $ID;

        $assignments = Assignments::getInstance();
        if($INPUT->str('action') && $INPUT->arr('assignment') && checkSecurityToken()) {
            $assignment = $INPUT->arr('assignment');
            if(!blank($assignment['assign']) && !blank($assignment['tbl'])) {
                if($INPUT->str('action') === 'delete') {
                    $ok = $assignments->removePattern($assignment['assign'], $assignment['tbl']);
                    if(!$ok) msg('failed to remove pattern', -1);
                } else if($INPUT->str('action') === 'add') {
                    if($assignment['assign']{0} == '/') {
                        if(@preg_match($assignment['assign'], null) === false) {
                            msg('Invalid regular expression. Pattern not saved', -1);
                        } else {
                            $ok = $assignments->addPattern($assignment['assign'], $assignment['tbl']);
                            if(!$ok) msg('failed to add pattern', -1);
                        }
                    } else {
                        $ok = $assignments->addPattern($assignment['assign'], $assignment['tbl']);
                        if(!$ok) msg('failed to add pattern', -1);
                    }
                }
            }



            send_redirect(wl($ID, array('do' => 'admin', 'page' => 'struct_assignments'), true, '&'));
        }
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $ID;

        echo $this->locale_xhtml('assignments_intro');

        $ass = Assignments::getInstance();
        $assignments = $ass->getAllPatterns();

        echo '<form action="' . wl($ID) . '" action="post">';
        echo '<input type="hidden" name="do" value="admin" />';
        echo '<input type="hidden" name="page" value="struct_assignments" />';
        echo '<input type="hidden" name="sectok" value="' . getSecurityToken() . '" />';
        echo '<table class="inline">';

        // header
        echo '<tr>';
        echo '<th>'.$this->getLang('assign_assign').'</th>';
        echo '<th>'.$this->getLang('assign_tbl').'</th>';
        echo '<th></th>';
        echo '</tr>';

        // existing assignments
        foreach($assignments as $assignment) {
            $schema = $assignment['tbl'];
            $assignee = $assignment['pattern'];

            $link = wl(
                $ID, array(
                'do' => 'admin',
                'page' => 'struct_assignments',
                'action' => 'delete',
                'sectok' => getSecurityToken(),
                'assignment[tbl]' => $schema,
                'assignment[assign]' => $assignee,
            )
            );

            echo '<tr>';
            echo '<td>' . hsc($assignee) . '</td>';
            echo '<td>' . hsc($schema) . '</td>';
            echo '<td><a class="deleteSchema" href="' . $link . '">'.$this->getLang('assign_del').'</a></td>';
            echo '</tr>';
        }

        // new assignment form
        echo '<tr>';
        echo '<td><input type="text" name="assignment[assign]" /></td>';
        echo '<td>';
        echo '<select name="assignment[tbl]">';
        foreach(Schema::getAll('page') as $table) {
            echo '<option value="' . hsc($table) . '">' . hsc($table) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td><button type="submit" name="action" value="add">'.$this->getLang('assign_add').'</button></td>';
        echo '</tr>';

        echo '</table>';
    }

    /**
     * Copies the TOC from the Schema Editor
     *
     * @return array
     */
    public function getTOC() {
        /** @var admin_plugin_struct_schemas $plugin */
        $plugin = plugin_load('admin', 'struct_schemas');
        return $plugin->getTOC();
    }

}

// vim:ts=4:sw=4:et:
