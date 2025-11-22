<?php

namespace easywiki\Action\Exception;

/**
 * Class ActionAbort
 *
 * Strictly speaking not an Exception but an expected execution path. Used to
 * signal when one action is done and another should take over.
 *
 * If you want to signal the same but under some error condition use ActionException
 * or one of it's decendants.
 *
 * The message will NOT be shown to the enduser
 *
 * @package easywiki\Action\Exception
 */
class ActionAbort extends ActionException
{
}
