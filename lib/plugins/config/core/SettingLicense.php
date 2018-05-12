<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_license
 */
class SettingLicense extends SettingMultichoice {

    protected $choices = array('');      // none choosen

    /** @inheritdoc */
    public function initialize($default, $local, $protected) {
        global $license;

        foreach($license as $key => $data) {
            $this->choices[] = $key;
            $this->lang[$this->key . '_o_' . $key] = $data['name']; // stored in setting
        }

        parent::initialize($default, $local, $protected);
    }
}
