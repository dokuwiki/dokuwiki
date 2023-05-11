<?php

namespace dokuwiki\Search\Exception;

class IndexLockException extends SearchException
{
    /** @inheritdoc */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        if($message == '') $message = 'Index not locked for writing';
        parent::__construct($message, $code, $previous);
    }

}
