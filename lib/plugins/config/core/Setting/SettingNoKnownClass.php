<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * A do-nothing class used to detect settings with a missing setting class.
 * Used internaly to hide undefined settings, and generate the undefined settings list.
 */
class SettingNoKnownClass extends SettingUndefined {
    protected $errorMessage = '_msg_setting_no_known_class';
}
