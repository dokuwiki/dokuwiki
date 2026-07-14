<?php

namespace dokuwiki\plugin\extension;

use Throwable;

/**
 * Implements translatable exception messages
 */
class Exception extends \Exception
{
    /**
     * @param string $message The error message or language string
     * @param array $context array of sprintf variables to be replaced in the message
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message = "", $context = [], Throwable $previous = null)
    {
        // try to translate the message
        $helper = plugin_load('helper', 'extension');
        $newmessage = $helper->getLang($message);
        if ($newmessage === '') {
            $newmessage = $message;
        }

        if ($context) {
            $newmessage = vsprintf($newmessage, $context);
        }

        parent::__construct($newmessage, 0, $previous);
    }
}
