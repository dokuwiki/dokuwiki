<?php

use easywiki\Debug\DebugHelper;

DebugHelper::dbgDeprecatedFunction(
    'Autoloading',
    1,
    'require(' . basename(__FILE__) . ')'
);
