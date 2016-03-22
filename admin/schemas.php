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
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        global $INPUT;

        // form submit
        $table = Schema::cleanTableName($INPUT->str('table'));
        if($table && $INPUT->bool('save') && checkSecurityToken()) {
            $builder = new \plugin\struct\meta\SchemaBuilder($table, $INPUT->arr('schema'));
            if(!$builder->build()) {
                msg('something went wrong while saving', -1);
            }
        }
        // export
        if($table && $INPUT->bool('export')) {
            $builder = new \plugin\struct\meta\Schema($table);
            header('Content-Type: application/json');
            header("Content-Disposition: attachment; filename=$table.struct.json");
            echo $builder->toJSON();
            exit;
        }
        // import
        if($table && $INPUT->bool('import')) {
            if(isset($_FILES['schemafile']['tmp_name'])) {
                $json = io_readFile($_FILES['schemafile']['tmp_name'], false);
                if(!$json) {
                    msg('Something went wrong with the upload', -1);
                } else {
                    $builder = new \plugin\struct\meta\SchemaImporter($table, $json);
                    if(!$builder->build()) {
                        msg('something went wrong while saving', -1);
                    }
                }
            }
        }
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $INPUT;

        $table = Schema::cleanTableName($INPUT->str('table'));
        if($table) {
            echo $this->locale_xhtml('editor_edit');
            echo '<h2>' . sprintf($this->getLang('edithl'), hsc($table)) . '</h2>';

            echo '<ul class="tabs" id="plugin__struct_tabs">';
            /** @noinspection HtmlUnknownAnchorTarget */
            echo '<li class="active"><a href="#plugin__struct_editor">'.$this->getLang('tab_edit').'</a></li>';
            /** @noinspection HtmlUnknownAnchorTarget */
            echo '<li><a href="#plugin__struct_json">'.$this->getLang('tab_export').'</a></li>';
            echo '</ul>';
            echo '<div class="panelHeader"></div>';

            $editor = new SchemaEditor(new Schema($table));
            echo $editor->getEditor();
            echo $this->html_json();

        } else {
            echo $this->locale_xhtml('editor_intro');
            echo $this->html_newschema();
        }
    }

    /**
     * Form for handling import/export from/to JSON
     * @return string
     */
    protected function html_json() {
        global $INPUT;
        $table = Schema::cleanTableName($INPUT->str('table'));

        $form = new Form(array('enctype' => 'multipart/form-data', 'id' => 'plugin__struct_json'));
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'struct_schemas');
        $form->setHiddenField('table', $table);

        $form->addFieldsetOpen($this->getLang('export'));
        $form->addButton('export', $this->getLang('btn_export'));
        $form->addFieldsetClose();

        $form->addFieldsetOpen($this->getLang('import'));
        $form->addElement(new \dokuwiki\Form\InputElement('file', 'schemafile'));
        $form->addButton('import', $this->getLang('btn_import'));
        $form->addHTML('<p>'.$this->getLang('import_warning').'</p>');
        $form->addFieldsetClose();
        return $form->toHTML();
    }

    /**
     * Form to add a new schema
     *
     * @return string
     */
    protected function html_newschema() {
        $form = new Form();
        $form->addFieldsetOpen($this->getLang('create'));
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'struct_schemas');
        $form->addTextInput('table', $this->getLang('schemaname'));
        $form->addButton('', $this->getLang('save'));
        $form->addHTML('<p>' . $this->getLang('createhint') . '</p>'); // FIXME is that true? we probably could
        $form->addFieldsetClose();
        return $form->toHTML();
    }

    /**
     * Adds all available schemas to the Table of Contents
     *
     * @return array
     */
    public function getTOC() {
        global $ID;

        $toc = array();
        $link = wl(
            $ID, array(
            'do' => 'admin',
            'page' => 'struct_assignments'
        )
        );
        $toc[] = html_mktocitem($link, $this->getLang('menu_assignments'), 0, '');
        $link = wl(
            $ID, array(
            'do' => 'admin',
            'page' => 'struct_schemas'
        )
        );
        $toc[] = html_mktocitem($link, $this->getLang('menu'), 0, '');

        $tables = Schema::getAll();
        foreach($tables as $table) {
            $link = wl(
                $ID, array(
                'do' => 'admin',
                'page' => 'struct_schemas',
                'table' => $table
            )
            );

            $toc[] = html_mktocitem($link, hsc($table), 1, '');
        }
        return $toc;
    }

}

// vim:ts=4:sw=4:et:
