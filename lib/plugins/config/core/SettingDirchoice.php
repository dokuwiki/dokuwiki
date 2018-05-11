<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_dirchoice
 */
class SettingDirchoice extends SettingMultichoice {

    protected $_dir = '';

    /**
     * Receives current values for the setting $key
     *
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default, $local, $protected) {

        // populate $this->_choices with a list of directories
        $list = array();

        if($dh = @opendir($this->_dir)) {
            while(false !== ($entry = readdir($dh))) {
                if($entry == '.' || $entry == '..') continue;
                if($this->_pattern && !preg_match($this->_pattern, $entry)) continue;

                $file = (is_link($this->_dir . $entry)) ? readlink($this->_dir . $entry) : $this->_dir . $entry;
                if(is_dir($file)) $list[] = $entry;
            }
            closedir($dh);
        }
        sort($list);
        $this->_choices = $list;

        parent::initialize($default, $local, $protected);
    }
}
