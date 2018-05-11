<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_license
 */
class SettingLicense extends SettingMultichoice {

    protected $_choices = array('');      // none choosen

    /**
     * Receives current values for the setting $key
     *
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default, $local, $protected) {
        global $license;

        foreach($license as $key => $data) {
            $this->_choices[] = $key;
            $this->lang[$this->_key . '_o_' . $key] = $data['name']; // stored in setting
        }

        parent::initialize($default, $local, $protected);
    }
}
