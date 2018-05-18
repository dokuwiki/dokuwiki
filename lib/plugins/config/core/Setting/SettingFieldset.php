<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * A do-nothing class used to detect the 'fieldset' type.
 *
 * Used to start a new settings "display-group".
 */
class SettingFieldset extends Setting {

    /** @inheritdoc */
    public function shouldHaveDefault() {
        return false;
    }

}
