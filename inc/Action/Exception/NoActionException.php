<?php

namespace easywiki\Action\Exception;

/**
 * Class NoActionException
 *
 * Thrown in the ActionRouter when a wanted action can not be found. Triggers
 * the unknown action event
 *
 * @package easywiki\Action\Exception
 */
class NoActionException extends \Exception
{
}
