<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_license
 */
class SettingLicense extends SettingMultichoice {

    protected $choices = array('');      // none choosen

    /** @inheritdoc */
    public function initialize($default = null, $local = null, $protected = null) {
        global $license;

        foreach($license as $key => $data) {
            $this->choices[] = $key;
            $this->lang[$this->key . '_o_' . $key] = $data['name']; // stored in setting
        }

        parent::initialize($default, $local, $protected);
    }
}
