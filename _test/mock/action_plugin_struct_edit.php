<?php

namespace dokuwiki\plugin\struct\test\mock;

class action_plugin_struct_edit extends \action_plugin_struct_edit {

    /**
     * Create the form to edit schemadata
     *
     * @param string $tablename
     * @return string The HTML for this schema's form
     */
    public function createForm($tablename) {
        return parent::createForm($tablename);
    }

    public static function getVAR() {
        return self::$VAR;
    }

}
