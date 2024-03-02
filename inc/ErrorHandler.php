<?php

namespace dokuwiki;

use dokuwiki\Exception\FatalException;

/**
 * Manage the global handling of errors and exceptions
 *
 * Developer may use this to log and display exceptions themselves
 */
class ErrorHandler
{
    /**
     * Standard error codes used in PHP errors
     * @see https://www.php.net/manual/en/errorfunc.constants.php
     */
    protected const ERRORCODES = [
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR',
        32 => 'E_CORE_WARNING',
        64 => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024 => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
    ];

    /**
     * Register the default error handling
     */
    public static function register()
    {
        if (!defined('DOKU_UNITTEST')) {
            set_exception_handler([ErrorHandler::class, 'fatalException']);
            register_shutdown_function([ErrorHandler::class, 'fatalShutdown']);
            set_error_handler(
                [ErrorHandler::class, 'errorHandler'],
                E_WARNING | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR
            );
        }
    }

    /**
     * Default Exception handler to show a nice user message before dieing
     *
     * The exception is logged to the error log
     *
     * @param \Throwable $e
     */
    public static function fatalException($e)
    {
        $plugin = self::guessPlugin($e);
        $title = hsc(get_class($e) . ': ' . $e->getMessage());
        $msg = 'An unforeseen error has occured. This is most likely a bug somewhere.';
        if ($plugin) $msg .= ' It might be a problem in the ' . $plugin . ' plugin.';
        $logged = self::logException($e)
            ? 'More info has been written to the DokuWiki error log.'
            : $e->getFile() . ':' . $e->getLine();

        echo <<<EOT
<!DOCTYPE html>
<html>
<head><title>$title</title></head>
<body style="font-family: Arial, sans-serif">
    <div style="width:60%; margin: auto; background-color: #fcc;
                border: 1px solid #faa; padding: 0.5em 1em;">
        <h1 style="font-size: 120%">$title</h1>
        <p>$msg</p>
        <p>$logged</p>
    </div>
</body>
</html>
EOT;
    }

    /**
     * Convenience method to display an error message for the given Exception
     *
     * @param \Throwable $e
     * @param string $intro
     */
    public static function showExceptionMsg($e, $intro = 'Error!')
    {
        $msg = hsc($intro) . '<br />' . hsc(get_class($e) . ': ' . $e->getMessage());
        if (self::logException($e)) $msg .= '<br />More info is available in the error log.';
        msg($msg, -1);
    }

    /**
     * Last resort to handle fatal errors that still can't be caught
     */
    public static function fatalShutdown()
    {
        $error = error_get_last();
        // Check if it's a core/fatal error, otherwise it's a normal shutdown
        if (
            $error !== null &&
            in_array(
                $error['type'],
                [
                    E_ERROR,
                    E_CORE_ERROR,
                    E_COMPILE_ERROR,
                ]
            )
        ) {
            self::fatalException(
                new FatalException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * Log the given exception to the error log
     *
     * @param \Throwable $e
     * @return bool false if the logging failed
     */
    public static function logException($e)
    {
        if ($e instanceof \ErrorException) {
            $prefix = self::ERRORCODES[$e->getSeverity()];
        } else {
            $prefix = get_class($e);
        }

        return Logger::getInstance()->log(
            $prefix . ': ' . $e->getMessage(),
            $e->getTraceAsString(),
            $e->getFile(),
            $e->getLine()
        );
    }

    /**
     * Error handler to log non-exception errors
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        global $conf;

        // ignore supressed warnings
        if (!(error_reporting() & $errno)) return false;

        $ex = new \ErrorException(
            $errstr,
            0,
            $errno,
            $errfile,
            $errline
        );
        self::logException($ex);

        if ($ex->getSeverity() === E_WARNING && $conf['hidewarnings']) {
            return true;
        }

        return false;
    }

    /**
     * Checks the the stacktrace for plugin files
     *
     * @param \Throwable $e
     * @return false|string
     */
    protected static function guessPlugin($e)
    {
        if (preg_match('/lib\/plugins\/(\w+)\//', str_replace('\\', '/', $e->getFile()), $match)) {
            return $match[1];
        }

        foreach ($e->getTrace() as $line) {
            if (
                isset($line['class']) &&
                preg_match('/\w+?_plugin_(\w+)/', $line['class'], $match)
            ) {
                return $match[1];
            }

            if (
                isset($line['file']) &&
                preg_match('/lib\/plugins\/(\w+)\//', str_replace('\\', '/', $line['file']), $match)
            ) {
                return $match[1];
            }
        }

        return false;
    }
}
