<?php

namespace dokuwiki\plugin\struct\test\mock;

use dokuwiki\plugin\struct\types\AbstractBaseType;

class action_plugin_struct_entry extends \action_plugin_struct_entry {

    /**
     * Validate the given data
     *
     * Catches the Validation exceptions and transforms them into proper messages.
     *
     * Blank values are not validated and always pass
     *
     * @param AbstractBaseType $type
     * @param string $label
     * @param array|string|int $data
     * @return bool true if the data validates, otherwise false
     */
    public function validate(AbstractBaseType $type, $label, $data) {
        return parent::validate($type, $label, $data);
    }

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
