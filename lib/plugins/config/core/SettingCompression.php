<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_compression
 */
class SettingCompression extends SettingMultichoice {

    protected $_choices = array('0');      // 0 = no compression, always supported

    /**
     * Receives current values for the setting $key
     *
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default, $local, $protected) {

        // populate _choices with the compression methods supported by this php installation
        if(function_exists('gzopen')) $this->_choices[] = 'gz';
        if(function_exists('bzopen')) $this->_choices[] = 'bz2';

        parent::initialize($default, $local, $protected);
    }
}
