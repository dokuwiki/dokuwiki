<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_sepchar
 */
class SettingSepchar extends SettingMultichoice {

    /** @inheritdoc */
    public function __construct($key, $param = null) {
        $str = '_-.';
        for($i = 0; $i < strlen($str); $i++) $this->choices[] = $str[$i];

        // call foundation class constructor
        parent::__construct($key, $param);
    }
}
