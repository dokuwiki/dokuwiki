<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_sepchar
 */
class SettingSepchar extends SettingMultichoice {

    /**
     * @param string $key
     * @param array|null $param array with metadata of setting
     */
    public function __construct($key, $param = null) {
        $str = '_-.';
        for($i = 0; $i < strlen($str); $i++) $this->_choices[] = $str{$i};

        // call foundation class constructor
        parent::__construct($key, $param);
    }
}
