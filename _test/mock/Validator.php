<?php

namespace plugin\struct\test\mock;

use \plugin\struct\meta;
use plugin\struct\types\AbstractBaseType;

class Validator extends meta\Validator {
    public function validateField(AbstractBaseType $type, $label, &$data) {
        return parent::validateField($type, $label, $data);
    }

}
