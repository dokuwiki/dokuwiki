<?php

namespace dokuwiki;

class Logger
{
    const LOG_ERROR = 'error';
    const LOG_DEPRECATED = 'deprecated';
    const LOG_DEBUG = 'debug';

    /** @var Logger[] */
    static protected $instances;

    /** @var string what kind of log is this */
    protected $facility;

    protected $isLogging = true;

    /**
     * Logger constructor.
     *
     * @param string $facility The type of log
     */
    protected function __construct($facility)
    {
        global $conf;
        $this->facility = $facility;

        // Should logging be disabled for this facility?
        $dontlog = explode(',', $conf['dontlog']);
        $dontlog = array_map('trim', $dontlog);
        if (in_array($facility, $dontlog)) $this->isLogging = false;
    }

    /**
     * Return a Logger instance for the given facility
     *
     * @param string $facility The type of log
     * @return Logger
     */
    static public function getInstance($facility = self::LOG_ERROR)
    {
        if (empty(self::$instances[$facility])) {
            self::$instances[$facility] = new Logger($facility);
        }
        return self::$instances[$facility];
    }

    /**
     * Convenience method to directly log to the error log
     *
     * @param string $message The log message
     * @param mixed $details Any details that should be added to the log entry
     * @param string $file A source filename if this is related to a source position
     * @param int $line A line number for the above file
     * @return bool has a log been written?
     */
    static public function error($message, $details = null, $file = '', $line = 0)
    {
        return self::getInstance(self::LOG_ERROR)->log(
            $message, $details, $file, $line
        );
    }

    /**
     * Convenience method to directly log to the debug log
     *
     * @param string $message The log message
     * @param mixed $details Any details that should be added to the log entry
     * @param string $file A source filename if this is related to a source position
     * @param int $line A line number for the above file
     * @return bool has a log been written?
     */
    static public function debug($message, $details = null, $file = '', $line = 0)
    {
        return self::getInstance(self::LOG_DEBUG)->log(
            $message, $details, $file, $line
        );
    }

    /**
     * Convenience method to directly log to the deprecation log
     *
     * @param string $message The log message
     * @param mixed $details Any details that should be added to the log entry
     * @param string $file A source filename if this is related to a source position
     * @param int $line A line number for the above file
     * @return bool has a log been written?
     */
    static public function deprecated($message, $details = null, $file = '', $line = 0)
    {
        return self::getInstance(self::LOG_DEPRECATED)->log(
            $message, $details, $file, $line
        );
    }

    /**
     * Log a message to the facility log
     *
     * @param string $message The log message
     * @param mixed $details Any details that should be added to the log entry
     * @param string $file A source filename if this is related to a source position
     * @param int $line A line number for the above file
     * @return bool has a log been written?
     */
    public function log($message, $details = null, $file = '', $line = 0)
    {
        if(!$this->isLogging) return false;

        // details are logged indented
        if ($details) {
            if (!is_string($details)) {
                $details = json_encode($details, JSON_PRETTY_PRINT);
            }
            $details = explode("\n", $details);
            $loglines = array_map(function ($line) {
                return '  ' . $line;
            }, $details);
        } elseif ($details) {
            $loglines = [$details];
        } else {
            $loglines = [];
        }

        // datetime, fileline, message
        $logline = gmdate('Y-m-d H:i:s') . "\t";
        if ($file) {
            $logline .= $file;
            if ($line) $logline .= "($line)";
        }
        $logline .= "\t" . $message;

        array_unshift($loglines, $logline);
        return $this->writeLogLines($loglines);
    }

    /**
     * Construct the log file for the given day
     *
     * @param false|string|int $date Date to access, false for today
     * @return string
     */
    public function getLogfile($date = false)
    {
        global $conf;

        if ($date !== null) $date = strtotime($date);
        if (!$date) $date = time();

        return $conf['logdir'] . '/' . $this->facility . '/' . date('Y-m-d', $date) . '.log';
    }

    /**
     * Write the given lines to today's facility log
     *
     * @param string[] $lines the raw lines to append to the log
     * @return bool true if the log was written
     */
    protected function writeLogLines($lines)
    {
        $logfile = $this->getLogfile();
        return io_saveFile($logfile, join("\n", $lines) . "\n", true);
    }
}
