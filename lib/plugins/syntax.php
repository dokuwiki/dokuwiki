<?php

use dokuwiki\Debug\DebugHelper;

DebugHelper::dbgDeprecatedFunction(
    'Autoloading',
    1,
    'require(' . basename(__FILE__) . ')'
);
