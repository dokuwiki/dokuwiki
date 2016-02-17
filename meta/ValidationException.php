<?php

namespace plugin\struct\meta;

/**
 * Class ValidationException
 *
 * Used to signal validation exceptions
 *
 * @package plugin\struct\meta
 */
class ValidationException extends StructException {
    protected $trans_prefix = 'Validation Exception';
}
