<?php

namespace dokuwiki;

class ErrorHandler
{

    /**
     * Register the default error handling
     */
    public static function register()
    {
        set_error_handler([ErrorHandler::class, 'errorConverter']);
        if (!defined('DOKU_UNITTEST')) {
            set_exception_handler([ErrorHandler::class, 'fatalException']);
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
            ? 'More info has been written to the DokuWiki _error.log'
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
        $msg = $intro . get_class($e) . ': ' . $e->getMessage();
        self::logException($e);
        msg(hsc($msg), -1);
    }

    /**
     * Default error handler to convert old school warnings, notices, etc to exceptions
     *
     * You should not need to call this directly!
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     * @throws \ErrorException
     */
    public static function errorConverter($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Log the given exception to the error log
     *
     * @param \Throwable $e
     * @return bool false if the logging failed
     */
    public static function logException($e)
    {
        global $conf;

        $log = join("\t", [gmdate('c'), get_class($e), $e->getFile() . ':' . $e->getLine(), $e->getMessage()]) . "\n";
        return io_saveFile($conf['cachedir'] . '/_error.log', $log, true);
    }

    /**
     * Checks the the stacktrace for plugin files
     *
     * @param \Throwable $e
     * @return false|string
     */
    protected static function guessPlugin($e)
    {
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
