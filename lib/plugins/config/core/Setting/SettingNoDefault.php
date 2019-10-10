<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_no_default
 *
 * A do-nothing class used to detect settings with no default value.
 * Used internaly to hide undefined settings, and generate the undefined settings list.
 */
class SettingNoDefault extends SettingUndefined {
    protected $errorMessage = '_msg_setting_no_default';
}
