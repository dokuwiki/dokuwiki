<?php
/**
 * DokuWiki Plugin struct (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use plugin\struct\meta\Schema;

if(!defined('DOKU_INC')) die();

class admin_plugin_struct extends DokuWiki_Admin_Plugin {

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
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $INPUT;

        ptln('<h1>'.$this->getLang('menu').'</h1>');


        $table = Schema::cleanTableName($INPUT->str('schema'));
        if($table) {
            $schema = new Schema($table);
            echo $schema->adminEditor();
        }

    }



}

// vim:ts=4:sw=4:et:
