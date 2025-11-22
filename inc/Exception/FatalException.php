<?php

namespace easywiki\Exception;

/**
 * Fatal Errors are converted into this Exception in out Shutdown handler
 */
class FatalException extends \ErrorException
{
}
