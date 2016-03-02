<?php

namespace plugin\struct\test\mock;

class Assignments extends \plugin\struct\meta\Assignments {
    public function matchPagePattern($pattern, $page, $pns = null) {
        return parent::matchPagePattern($pattern, $page, $pns);
    }
}
