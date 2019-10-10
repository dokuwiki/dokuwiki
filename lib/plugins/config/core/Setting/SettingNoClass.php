<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_no_class
 * A do-nothing class used to detect settings with a missing setting class.
 * Used internaly to hide undefined settings, and generate the undefined settings list.
 */
class SettingNoClass extends SettingUndefined {
    protected $errorMessage = '_msg_setting_no_class';
}
