<?php

namespace dokuwiki\plugin\struct\test\mock;

class Assignments extends \dokuwiki\plugin\struct\meta\Assignments {
    public function matchPagePattern($pattern, $page, $pns = null) {
        return parent::matchPagePattern($pattern, $page, $pns);
    }
}
