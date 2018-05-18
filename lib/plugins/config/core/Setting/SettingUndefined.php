<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * A do-nothing class used to detect settings with no metadata entry.
 * Used internaly to hide undefined settings, and generate the undefined settings list.
 */
class SettingUndefined extends SettingHidden {

    /** @inheritdoc */
    public function shouldHaveDefault() {
        return false;
    }

}
