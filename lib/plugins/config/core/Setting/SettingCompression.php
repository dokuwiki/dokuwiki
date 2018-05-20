<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_compression
 */
class SettingCompression extends SettingMultichoice {

    protected $choices = array('0');      // 0 = no compression, always supported

    /** @inheritdoc */
    public function initialize($default = null, $local = null, $protected = null) {

        // populate _choices with the compression methods supported by this php installation
        if(function_exists('gzopen')) $this->choices[] = 'gz';
        if(function_exists('bzopen')) $this->choices[] = 'bz2';

        parent::initialize($default, $local, $protected);
    }
}
