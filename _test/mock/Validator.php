<?php

namespace dokuwiki\plugin\struct\test\mock;

use \dokuwiki\plugin\struct\meta;
use dokuwiki\plugin\struct\types\AbstractBaseType;

class Validator extends meta\Validator {
    public function validateField(AbstractBaseType $type, $label, &$data) {
        return parent::validateField($type, $label, $data);
    }

}
