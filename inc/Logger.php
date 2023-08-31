<?php

namespace dokuwiki;

use dokuwiki\Extension\Event;

/**
 * Log messages to a daily log file
 */
class Logger
{
    public const LOG_ERROR = 'error';
    public const LOG_DEPRECATED = 'deprecated';
    public const LOG_DEBUG = 'debug';

    /** @var Logger[] */
    protected static $instances;

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
    public static function getInstance($facility = self::LOG_ERROR)
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
    public static function error($message, $details = null, $file = '', $line = 0)
    {
        return self::getInstance(self::LOG_ERROR)->log(
            $message,
            $details,
            $file,
            $line
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
    public static function debug($message, $details = null, $file = '', $line = 0)
    {
        return self::getInstance(self::LOG_DEBUG)->log(
            $message,
            $details,
            $file,
            $line
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
    public static function deprecated($message, $details = null, $file = '', $line = 0)
    {
        return self::getInstance(self::LOG_DEPRECATED)->log(
            $message,
            $details,
            $file,
            $line
        );
    }

    /**
     * Log a message to the facility log
     *
     * @param string $message The log message
     * @param mixed $details Any details that should be added to the log entry
     * @param string $file A source filename if this is related to a source position
     * @param int $line A line number for the above file
     * @triggers LOGGER_DATA_FORMAT can be used to change the logged data or intercept it
     * @return bool has a log been written?
     */
    public function log($message, $details = null, $file = '', $line = 0)
    {
        global $EVENT_HANDLER;
        if (!$this->isLogging) return false;

        $datetime = time();
        $data = [
            'facility' => $this->facility,
            'datetime' => $datetime,
            'message' => $message,
            'details' => $details,
            'file' => $file,
            'line' => $line,
            'loglines' => [],
            'logfile' => $this->getLogfile($datetime),
        ];

        if ($EVENT_HANDLER !== null) {
            $event = new Event('LOGGER_DATA_FORMAT', $data);
            if ($event->advise_before()) {
                $data['loglines'] = $this->formatLogLines($data);
            }
            $event->advise_after();
        } else {
            // The event system is not yet available, to ensure the log isn't lost even on
            // fatal errors, the default action is executed
            $data['loglines'] = $this->formatLogLines($data);
        }

        // only log when any data available
        if (count($data['loglines'])) {
            return $this->writeLogLines($data['loglines'], $data['logfile']);
        } else {
            return false;
        }
    }

    /**
     * Is this logging instace actually logging?
     *
     * @return bool
     */
    public function isLogging()
    {
        return $this->isLogging;
    }

    /**
     * Formats the given data as loglines
     *
     * @param array $data Event data from LOGGER_DATA_FORMAT
     * @return string[] the lines to log
     */
    protected function formatLogLines($data)
    {
        extract($data);

        // details are logged indented
        if ($details) {
            if (!is_string($details)) {
                $details = json_encode($details, JSON_PRETTY_PRINT);
            }
            $details = explode("\n", $details);
            $loglines = array_map(static fn($line) => '  ' . $line, $details);
        } elseif ($details) {
            $loglines = [$details];
        } else {
            $loglines = [];
        }

        // datetime, fileline, message
        $logline = gmdate('Y-m-d H:i:s', $datetime) . "\t";
        if ($file) {
            $logline .= $file;
            if ($line) $logline .= "($line)";
        }
        $logline .= "\t" . $message;
        array_unshift($loglines, $logline);

        return $loglines;
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

        if ($date !== null && !is_numeric($date)) {
            $date = strtotime($date);
        }
        if (!$date) $date = time();

        return $conf['logdir'] . '/' . $this->facility . '/' . date('Y-m-d', $date) . '.log';
    }

    /**
     * Write the given lines to today's facility log
     *
     * @param string[] $lines the raw lines to append to the log
     * @param string $logfile where to write to
     * @return bool true if the log was written
     */
    protected function writeLogLines($lines, $logfile)
    {
        if (defined('DOKU_UNITTEST')) {
            fwrite(STDERR, "\n[" . $this->facility . '] ' . implode("\n", $lines) . "\n");
        }
        return io_saveFile($logfile, implode("\n", $lines) . "\n", true);
    }
}
