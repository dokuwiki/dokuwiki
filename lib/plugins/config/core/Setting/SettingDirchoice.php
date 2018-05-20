<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_dirchoice
 */
class SettingDirchoice extends SettingMultichoice {

    protected $dir = '';

    /** @inheritdoc */
    public function initialize($default = null, $local = null, $protected = null) {

        // populate $this->_choices with a list of directories
        $list = array();

        if($dh = @opendir($this->dir)) {
            while(false !== ($entry = readdir($dh))) {
                if($entry == '.' || $entry == '..') continue;
                if($this->pattern && !preg_match($this->pattern, $entry)) continue;

                $file = (is_link($this->dir . $entry)) ? readlink($this->dir . $entry) : $this->dir . $entry;
                if(is_dir($file)) $list[] = $entry;
            }
            closedir($dh);
        }
        sort($list);
        $this->choices = $list;

        parent::initialize($default, $local, $protected);
    }
}
