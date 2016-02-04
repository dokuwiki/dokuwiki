<?php
/**
 * DokuWiki Plugin struct (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\Form\Form;
use plugin\struct\meta\Assignments;
use plugin\struct\meta\Schema;
use plugin\struct\meta\SchemaEditor;

if(!defined('DOKU_INC')) die();

class admin_plugin_struct_assignments extends DokuWiki_Admin_Plugin {

    /** @var helper_plugin_sqlite */
    protected $sqlite;

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
        return true;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        global $INPUT;

        $assignments = new Assignments();
        if($INPUT->str('action') && $INPUT->arr('assignment') && checkSecurityToken()) {
            $assignment = $INPUT->arr('assignment');
            $ok = false;
            if ($INPUT->str('action') === 'delete') {
                $ok = $assignments->remove($assignment['assign'], $assignment['tbl']);
            } else if($INPUT->str('action') === 'add') {
                $ok = $assignments->add($assignment['assign'], $assignment['tbl']);
            }
            if(empty($sql) || empty($assignment['assign']) || empty($assignment['tbl']) || !$ok) {
                msg('something went wrong while saving', -1);
            }
        }
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $ID;

        echo $this->locale_xhtml('assignments_intro');

        $res = $this->sqlite->query('SELECT tbl FROM schemas GROUP BY tbl');
        $schemas = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        $ass = new Assignments();
        $assignments = $ass->getAll();



        echo '<form action="'.wl($ID).'">';
        echo '<input type="hidden" name="do" value="admin" />';
        echo '<input type="hidden" name="page" value="struct_assignments" />';
        echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />';
        echo '<table class="inline">';

        // header
        echo '<tr>';
        echo '<th>Page/Namespace</th>'; // FIXME localize
        echo '<th>Schema</th>'; // FIXME localize
        echo '<th></th>';
        echo '</tr>';

        // existing assignments
        foreach ($assignments as $assignment) {
            $schema = $assignment['tbl'];
            $assignee = $assignment['assign'];

            $link = wl($ID, array(
                'do' => 'admin',
                'page' => 'struct_assignments',
                'action' => 'delete',
                'sectok' => getSecurityToken(),
                'assignment[tbl]' => $schema,
                'assignment[assign]' => $assignee,
            ));

            echo '<tr>';
            echo '<td>'.hsc($assignee).'</td>';
            echo '<td>'.hsc($schema).'</td>';
            echo '<td><a href="'.$link.'">Delete</a></td>'; //FIXME localize
            echo '</tr>';
        }

        // new assignment form
        echo '<tr>';
        echo '<td><input type="text" name="assignment[assign]" /></td>';
        echo '<td>';
        echo '<select name="assignment[tbl]">';
        foreach ($schemas as $schema){
            echo '<option value="'. hsc($schema['tbl']) .'">'. hsc($schema['tbl']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td><button type="submit" name="action" value="add">Add</button></td>'; // FIXME localize
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
