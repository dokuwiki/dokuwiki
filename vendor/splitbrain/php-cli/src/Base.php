<?php

namespace splitbrain\phpcli;

/**
 * Class CLIBase
 *
 * All base functionality is implemented here.
 *
 * Your commandline should not inherit from this class, but from one of the *CLI* classes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @license MIT
 */
abstract class Base
{
    /** @var string the executed script itself */
    protected $bin;
    /** @var  Options the option parser */
    protected $options;
    /** @var  Colors */
    public $colors;

    /** @var array PSR-3 compatible loglevels and their prefix, color, output channel, enabled status */
    protected $loglevel = array(
        'debug' => array(
            'icon' => '',
            'color' => Colors::C_RESET,
            'channel' => STDOUT,
            'enabled' => true
        ),
        'info' => array(
            'icon' => 'ℹ ',
            'color' => Colors::C_CYAN,
            'channel' => STDOUT,
            'enabled' => true
        ),
        'notice' => array(
            'icon' => '☛ ',
            'color' => Colors::C_CYAN,
            'channel' => STDOUT,
            'enabled' => true
        ),
        'success' => array(
            'icon' => '✓ ',
            'color' => Colors::C_GREEN,
            'channel' => STDOUT,
            'enabled' => true
        ),
        'warning' => array(
            'icon' => '⚠ ',
            'color' => Colors::C_BROWN,
            'channel' => STDERR,
            'enabled' => true
        ),
        'error' => array(
            'icon' => '✗ ',
            'color' => Colors::C_RED,
            'channel' => STDERR,
            'enabled' => true
        ),
        'critical' => array(
            'icon' => '☠ ',
            'color' => Colors::C_LIGHTRED,
            'channel' => STDERR,
            'enabled' => true
        ),
        'alert' => array(
            'icon' => '✖ ',
            'color' => Colors::C_LIGHTRED,
            'channel' => STDERR,
            'enabled' => true
        ),
        'emergency' => array(
            'icon' => '✘ ',
            'color' => Colors::C_LIGHTRED,
            'channel' => STDERR,
            'enabled' => true
        ),
    );

    /** @var string default log level */
    protected $logdefault = 'info';

    /**
     * constructor
     *
     * Initialize the arguments, set up helper classes and set up the CLI environment
     *
     * @param bool $autocatch should exceptions be catched and handled automatically?
     */
    public function __construct($autocatch = true)
    {
        if ($autocatch) {
            set_exception_handler(array($this, 'fatal'));
        }

        $this->colors = new Colors();
        $this->options = new Options($this->colors);
    }

    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     *
     * @throws Exception
     */
    abstract protected function setup(Options $options);

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param Options $options
     * @return void
     *
     * @throws Exception
     */
    abstract protected function main(Options $options);

    /**
     * Execute the CLI program
     *
     * Executes the setup() routine, adds default options, initiate the options parsing and argument checking
     * and finally executes main() - Each part is split into their own protected function below, so behaviour
     * can easily be overwritten
     *
     * @throws Exception
     */
    public function run()
    {
        if ('cli' != php_sapi_name()) {
            throw new Exception('This has to be run from the command line');
        }

        $this->setup($this->options);
        $this->registerDefaultOptions();
        $this->parseOptions();
        $this->handleDefaultOptions();
        $this->setupLogging();
        $this->checkArguments();
        $this->execute();
    }

    // region run handlers - for easier overriding

    /**
     * Add the default help, color and log options
     */
    protected function registerDefaultOptions()
    {
        $this->options->registerOption(
            'help',
            'Display this help screen and exit immediately.',
            'h'
        );
        $this->options->registerOption(
            'no-colors',
            'Do not use any colors in output. Useful when piping output to other tools or files.'
        );
        $this->options->registerOption(
            'loglevel',
            'Minimum level of messages to display. Default is ' . $this->colors->wrap($this->logdefault, Colors::C_CYAN) . '. ' .
            'Valid levels are: debug, info, notice, success, warning, error, critical, alert, emergency.',
            null,
            'level'
        );
    }

    /**
     * Handle the default options
     */
    protected function handleDefaultOptions()
    {
        if ($this->options->getOpt('no-colors')) {
            $this->colors->disable();
        }
        if ($this->options->getOpt('help')) {
            echo $this->options->help();
            exit(0);
        }
    }

    /**
     * Handle the logging options
     */
    protected function setupLogging()
    {
        $level = $this->options->getOpt('loglevel', $this->logdefault);
        $this->setLogLevel($level);
    }

    /**
     * Wrapper around the option parsing
     */
    protected function parseOptions()
    {
        $this->options->parseOptions();
    }

    /**
     * Wrapper around the argument checking
     */
    protected function checkArguments()
    {
        $this->options->checkArguments();
    }

    /**
     * Wrapper around main
     */
    protected function execute()
    {
        $this->main($this->options);
    }

    // endregion

    // region logging

    /**
     * Set the current log level
     *
     * @param string $level
     */
    public function setLogLevel($level)
    {
        if (!isset($this->loglevel[$level])) $this->fatal('Unknown log level');
        $enable = false;
        foreach (array_keys($this->loglevel) as $l) {
            if ($l == $level) $enable = true;
            $this->loglevel[$l]['enabled'] = $enable;
        }
    }

    /**
     * Check if a message with the given level should be logged
     *
     * @param string $level
     * @return bool
     */
    public function isLogLevelEnabled($level)
    {
        if (!isset($this->loglevel[$level])) $this->fatal('Unknown log level');
        return $this->loglevel[$level]['enabled'];
    }

    /**
     * Exits the program on a fatal error
     *
     * @param \Exception|string $error either an exception or an error message
     * @param array $context
     */
    public function fatal($error, array $context = array())
    {
        $code = 0;
        if (is_object($error) && is_a($error, 'Exception')) {
            /** @var Exception $error */
            $this->logMessage('debug', get_class($error) . ' caught in ' . $error->getFile() . ':' . $error->getLine());
            $this->logMessage('debug', $error->getTraceAsString());
            $code = $error->getCode();
            $error = $error->getMessage();

        }
        if (!$code) {
            $code = Exception::E_ANY;
        }

        $this->logMessage('critical', $error, $context);
        exit($code);
    }

    /**
     * Normal, positive outcome (This is not a PSR-3 level)
     *
     * @param string $string
     * @param array $context
     */
    public function success($string, array $context = array())
    {
        $this->logMessage('success', $string, $context);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    protected function logMessage($level, $message, array $context = array())
    {
        // unknown level is always an error
        if (!isset($this->loglevel[$level])) $level = 'error';

        $info = $this->loglevel[$level];
        if (!$this->isLogLevelEnabled($level)) return; // no logging for this level

        $message = $this->interpolate($message, $context);

        // when colors are wanted, we also add the icon
        if ($this->colors->isEnabled()) {
            $message = $info['icon'] . $message;
        }

        $this->colors->ptln($message, $info['color'], $info['channel']);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param $message
     * @param array $context
     * @return string
     */
    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr((string)$message, $replace);
    }

    // endregion
}
