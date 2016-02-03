<?php
/**
 * DokuWiki Plugin struct (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\Form\Form;
use plugin\struct\meta\Schema;
use plugin\struct\meta\SchemaEditor;

if(!defined('DOKU_INC')) die();

class admin_plugin_struct_schemas extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 500;
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

        $table = Schema::cleanTableName($INPUT->str('table'));
        if($table && $INPUT->bool('save') && checkSecurityToken()) {
            $builder = new \plugin\struct\meta\SchemaBuilder($table, $INPUT->arr('schema'));
            if(!$builder->build()) {
                msg('something went wrong while saving', -1);
            }
        }

    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $INPUT;

        echo $this->locale_xhtml('intro');

        $table = Schema::cleanTableName($INPUT->str('table'));
        if($table) {
            echo '<h2>'.sprintf($this->getLang('edithl'), hsc($table)).'</h2>';

            $editor = new SchemaEditor(new Schema($table));
            echo $editor->getEditor();
        } else {
            $this->html_newschema();
        }
    }

    /**
     * Form to add a new schema
     */
    protected function html_newschema() {
        $form = new Form();
        $form->addFieldsetOpen($this->getLang('create'));
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'struct');
        $form->addTextInput('table', $this->getLang('schemaname'));
        $form->addButton('', $this->getLang('save'));
        $form->addHTML('<p>'.$this->getLang('createhint').'</p>'); // FIXME is that true? we probably could
        $form->addFieldsetClose();
        echo $form->toHTML();
    }

    /**
     * Adds all available schemas to the Table of Contents
     *
     * @return array
     */
    public function getTOC() {
        global $ID;

        /** @var helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $db = $helper->getDB();

        parent::getTOC();
        if(!$db) return parent::getTOC();

        $res = $db->query("SELECT DISTINCT tbl FROM schemas ORDER BY tbl");
        $tables = $db->res2arr($res);
        $db->res_close($res);

        $toc = array();
        $link = wl($ID, array(
            'do' => 'admin',
            'page' => 'struct'
        ));
        $toc[] = html_mktocitem($link, $this->getLang('menu'), 0, '');

        foreach($tables as $row) {
            $link = wl($ID, array(
                'do' => 'admin',
                'page' => 'struct',
                'table' => $row['tbl']
            ));

            $toc[] = html_mktocitem($link, hsc($row['tbl']), 1, '');
        }
        return $toc;
    }

}

// vim:ts=4:sw=4:et:
