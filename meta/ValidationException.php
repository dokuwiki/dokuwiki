<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class ValidationException
 *
 * Used to signal validation exceptions
 *
 * @package dokuwiki\plugin\struct\meta
 */
class ValidationException extends StructException {
    protected $trans_prefix = 'Validation Exception ';
}
