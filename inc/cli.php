<?php

/**
 * Class DokuCLI
 *
 * All DokuWiki commandline scripts should inherit from this class and implement the abstract methods.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
abstract class DokuCLI {
    /** @var string the executed script itself */
    protected $bin;
    /** @var  DokuCLI_Options the option parser */
    protected $options;
    /** @var  DokuCLI_Colors */
    public $colors;

    /**
     * constructor
     *
     * Initialize the arguments, set up helper classes and set up the CLI environment
     */
    public function __construct() {
        set_exception_handler(array($this, 'fatal'));

        $this->options = new DokuCLI_Options();
        $this->colors  = new DokuCLI_Colors();

        dbg_deprecated('use \splitbrain\phpcli\CLI instead');
        $this->error('DokuCLI is deprecated, use \splitbrain\phpcli\CLI instead.');
    }

    /**
     * Register options and arguments on the given $options object
     *
     * @param DokuCLI_Options $options
     * @return void
     */
    abstract protected function setup(DokuCLI_Options $options);

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param DokuCLI_Options $options
     * @return void
     */
    abstract protected function main(DokuCLI_Options $options);

    /**
     * Execute the CLI program
     *
     * Executes the setup() routine, adds default options, initiate the options parsing and argument checking
     * and finally executes main()
     */
    public function run() {
        if('cli' != php_sapi_name()) throw new DokuCLI_Exception('This has to be run from the command line');

        // setup
        $this->setup($this->options);
        $this->options->registerOption(
            'no-colors',
            'Do not use any colors in output. Useful when piping output to other tools or files.'
        );
        $this->options->registerOption(
            'help',
            'Display this help screen and exit immediately.',
            'h'
        );

        // parse
        $this->options->parseOptions();

        // handle defaults
        if($this->options->getOpt('no-colors')) {
            $this->colors->disable();
        }
        if($this->options->getOpt('help')) {
            echo $this->options->help();
            exit(0);
        }

        // check arguments
        $this->options->checkArguments();

        // execute
        $this->main($this->options);

        exit(0);
    }

    /**
     * Exits the program on a fatal error
     *
     * @param Exception|string $error either an exception or an error message
     */
    public function fatal($error) {
        $code = 0;
        if(is_object($error) && is_a($error, 'Exception')) {
            /** @var Exception $error */
            $code  = $error->getCode();
            $error = $error->getMessage();
        }
        if(!$code) $code = DokuCLI_Exception::E_ANY;

        $this->error($error);
        exit($code);
    }

    /**
     * Print an error message
     *
     * @param string $string
     */
    public function error($string) {
        $this->colors->ptln("E: $string", 'red', STDERR);
    }

    /**
     * Print a success message
     *
     * @param string $string
     */
    public function success($string) {
        $this->colors->ptln("S: $string", 'green', STDERR);
    }

    /**
     * Print an info message
     *
     * @param string $string
     */
    public function info($string) {
        $this->colors->ptln("I: $string", 'cyan', STDERR);
    }

}

/**
 * Class DokuCLI_Colors
 *
 * Handles color output on (Linux) terminals
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class DokuCLI_Colors {
    /** @var array known color names */
    protected $colors = array(
        'reset'       => "\33[0m",
        'black'       => "\33[0;30m",
        'darkgray'    => "\33[1;30m",
        'blue'        => "\33[0;34m",
        'lightblue'   => "\33[1;34m",
        'green'       => "\33[0;32m",
        'lightgreen'  => "\33[1;32m",
        'cyan'        => "\33[0;36m",
        'lightcyan'   => "\33[1;36m",
        'red'         => "\33[0;31m",
        'lightred'    => "\33[1;31m",
        'purple'      => "\33[0;35m",
        'lightpurple' => "\33[1;35m",
        'brown'       => "\33[0;33m",
        'yellow'      => "\33[1;33m",
        'lightgray'   => "\33[0;37m",
        'white'       => "\33[1;37m",
    );

    /** @var bool should colors be used? */
    protected $enabled = true;

    /**
     * Constructor
     *
     * Tries to disable colors for non-terminals
     */
    public function __construct() {
        if(function_exists('posix_isatty') && !posix_isatty(STDOUT)) {
            $this->enabled = false;
            return;
        }
        if(!getenv('TERM')) {
            $this->enabled = false;
            return;
        }
    }

    /**
     * enable color output
     */
    public function enable() {
        $this->enabled = true;
    }

    /**
     * disable color output
     */
    public function disable() {
        $this->enabled = false;
    }

    /**
     * Convenience function to print a line in a given color
     *
     * @param string   $line
     * @param string   $color
     * @param resource $channel
     */
    public function ptln($line, $color, $channel = STDOUT) {
        $this->set($color);
        fwrite($channel, rtrim($line)."\n");
        $this->reset();
    }

    /**
     * Set the given color for consecutive output
     *
     * @param string $color one of the supported color names
     * @throws DokuCLI_Exception
     */
    public function set($color) {
        if(!$this->enabled) return;
        if(!isset($this->colors[$color])) throw new DokuCLI_Exception("No such color $color");
        echo $this->colors[$color];
    }

    /**
     * reset the terminal color
     */
    public function reset() {
        $this->set('reset');
    }
}

/**
 * Class DokuCLI_Options
 *
 * Parses command line options passed to the CLI script. Allows CLI scripts to easily register all accepted options and
 * commands and even generates a help text from this setup.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class DokuCLI_Options {
    /** @var  array keeps the list of options to parse */
    protected $setup;

    /** @var  array store parsed options */
    protected $options = array();

    /** @var string current parsed command if any */
    protected $command = '';

    /** @var  array passed non-option arguments */
    public $args = array();

    /** @var  string the executed script */
    protected $bin;

    /**
     * Constructor
     */
    public function __construct() {
        $this->setup = array(
            '' => array(
                'opts' => array(),
                'args' => array(),
                'help' => ''
            )
        ); // default command

        $this->args = $this->readPHPArgv();
        $this->bin  = basename(array_shift($this->args));

        $this->options = array();
    }

    /**
     * Sets the help text for the tool itself
     *
     * @param string $help
     */
    public function setHelp($help) {
        $this->setup['']['help'] = $help;
    }

    /**
     * Register the names of arguments for help generation and number checking
     *
     * This has to be called in the order arguments are expected
     *
     * @param string $arg      argument name (just for help)
     * @param string $help     help text
     * @param bool   $required is this a required argument
     * @param string $command  if theses apply to a sub command only
     * @throws DokuCLI_Exception
     */
    public function registerArgument($arg, $help, $required = true, $command = '') {
        if(!isset($this->setup[$command])) throw new DokuCLI_Exception("Command $command not registered");

        $this->setup[$command]['args'][] = array(
            'name'     => $arg,
            'help'     => $help,
            'required' => $required
        );
    }

    /**
     * This registers a sub command
     *
     * Sub commands have their own options and use their own function (not main()).
     *
     * @param string $command
     * @param string $help
     * @throws DokuCLI_Exception
     */
    public function registerCommand($command, $help) {
        if(isset($this->setup[$command])) throw new DokuCLI_Exception("Command $command already registered");

        $this->setup[$command] = array(
            'opts' => array(),
            'args' => array(),
            'help' => $help
        );

    }

    /**
     * Register an option for option parsing and help generation
     *
     * @param string      $long     multi character option (specified with --)
     * @param string      $help     help text for this option
     * @param string|null $short    one character option (specified with -)
     * @param bool|string $needsarg does this option require an argument? give it a name here
     * @param string      $command  what command does this option apply to
     * @throws DokuCLI_Exception
     */
    public function registerOption($long, $help, $short = null, $needsarg = false, $command = '') {
        if(!isset($this->setup[$command])) throw new DokuCLI_Exception("Command $command not registered");

        $this->setup[$command]['opts'][$long] = array(
            'needsarg' => $needsarg,
            'help'     => $help,
            'short'    => $short
        );

        if($short) {
            if(strlen($short) > 1) throw new DokuCLI_Exception("Short options should be exactly one ASCII character");

            $this->setup[$command]['short'][$short] = $long;
        }
    }

    /**
     * Checks the actual number of arguments against the required number
     *
     * Throws an exception if arguments are missing. Called from parseOptions()
     *
     * @throws DokuCLI_Exception
     */
    public function checkArguments() {
        $argc = count($this->args);

        $req = 0;
        foreach($this->setup[$this->command]['args'] as $arg) {
            if(!$arg['required']) break; // last required arguments seen
            $req++;
        }

        if($req > $argc) throw new DokuCLI_Exception("Not enough arguments", DokuCLI_Exception::E_OPT_ARG_REQUIRED);
    }

    /**
     * Parses the given arguments for known options and command
     *
     * The given $args array should NOT contain the executed file as first item anymore! The $args
     * array is stripped from any options and possible command. All found otions can be accessed via the
     * getOpt() function
     *
     * Note that command options will overwrite any global options with the same name
     *
     * @throws DokuCLI_Exception
     */
    public function parseOptions() {
        $non_opts = array();

        $argc = count($this->args);
        for($i = 0; $i < $argc; $i++) {
            $arg = $this->args[$i];

            // The special element '--' means explicit end of options. Treat the rest of the arguments as non-options
            // and end the loop.
            if($arg == '--') {
                $non_opts = array_merge($non_opts, array_slice($this->args, $i + 1));
                break;
            }

            // '-' is stdin - a normal argument
            if($arg == '-') {
                $non_opts = array_merge($non_opts, array_slice($this->args, $i));
                break;
            }

            // first non-option
            if($arg{0} != '-') {
                $non_opts = array_merge($non_opts, array_slice($this->args, $i));
                break;
            }

            // long option
            if(strlen($arg) > 1 && $arg{1} == '-') {
                list($opt, $val) = explode('=', substr($arg, 2), 2);

                if(!isset($this->setup[$this->command]['opts'][$opt])) {
                    throw new DokuCLI_Exception("No such option $arg", DokuCLI_Exception::E_UNKNOWN_OPT);
                }

                // argument required?
                if($this->setup[$this->command]['opts'][$opt]['needsarg']) {
                    if(is_null($val) && $i + 1 < $argc && !preg_match('/^--?[\w]/', $this->args[$i + 1])) {
                        $val = $this->args[++$i];
                    }
                    if(is_null($val)) {
                        throw new DokuCLI_Exception("Option $arg requires an argument", DokuCLI_Exception::E_OPT_ARG_REQUIRED);
                    }
                    $this->options[$opt] = $val;
                } else {
                    $this->options[$opt] = true;
                }

                continue;
            }

            // short option
            $opt = substr($arg, 1);
            if(!isset($this->setup[$this->command]['short'][$opt])) {
                throw new DokuCLI_Exception("No such option $arg", DokuCLI_Exception::E_UNKNOWN_OPT);
            } else {
                $opt = $this->setup[$this->command]['short'][$opt]; // store it under long name
            }

            // argument required?
            if($this->setup[$this->command]['opts'][$opt]['needsarg']) {
                $val = null;
                if($i + 1 < $argc && !preg_match('/^--?[\w]/', $this->args[$i + 1])) {
                    $val = $this->args[++$i];
                }
                if(is_null($val)) {
                    throw new DokuCLI_Exception("Option $arg requires an argument", DokuCLI_Exception::E_OPT_ARG_REQUIRED);
                }
                $this->options[$opt] = $val;
            } else {
                $this->options[$opt] = true;
            }
        }

        // parsing is now done, update args array
        $this->args = $non_opts;

        // if not done yet, check if first argument is a command and reexecute argument parsing if it is
        if(!$this->command && $this->args && isset($this->setup[$this->args[0]])) {
            // it is a command!
            $this->command = array_shift($this->args);
            $this->parseOptions(); // second pass
        }
    }

    /**
     * Get the value of the given option
     *
     * Please note that all options are accessed by their long option names regardless of how they were
     * specified on commandline.
     *
     * Can only be used after parseOptions() has been run
     *
     * @param string $option
     * @param bool|string $default what to return if the option was not set
     * @return bool|string
     */
    public function getOpt($option, $default = false) {
        if(isset($this->options[$option])) return $this->options[$option];
        return $default;
    }

    /**
     * Return the found command if any
     *
     * @return string
     */
    public function getCmd() {
        return $this->command;
    }

    /**
     * Builds a help screen from the available options. You may want to call it from -h or on error
     *
     * @return string
     */
    public function help() {
        $text = '';

        $hascommands = (count($this->setup) > 1);
        foreach($this->setup as $command => $config) {
            $hasopts = (bool) $this->setup[$command]['opts'];
            $hasargs = (bool) $this->setup[$command]['args'];

            if(!$command) {
                $text .= 'USAGE: '.$this->bin;
            } else {
                $text .= "\n$command";
            }

            if($hasopts) $text .= ' <OPTIONS>';

            foreach($this->setup[$command]['args'] as $arg) {
                if($arg['required']) {
                    $text .= ' <'.$arg['name'].'>';
                } else {
                    $text .= ' [<'.$arg['name'].'>]';
                }
            }
            $text .= "\n";

            if($this->setup[$command]['help']) {
                $text .= "\n";
                $text .= $this->tableFormat(
                    array(2, 72),
                    array('', $this->setup[$command]['help']."\n")
                );
            }

            if($hasopts) {
                $text .= "\n  OPTIONS\n\n";
                foreach($this->setup[$command]['opts'] as $long => $opt) {

                    $name = '';
                    if($opt['short']) {
                        $name .= '-'.$opt['short'];
                        if($opt['needsarg']) $name .= ' <'.$opt['needsarg'].'>';
                        $name .= ', ';
                    }
                    $name .= "--$long";
                    if($opt['needsarg']) $name .= ' <'.$opt['needsarg'].'>';

                    $text .= $this->tableFormat(
                        array(2, 20, 52),
                        array('', $name, $opt['help'])
                    );
                    $text .= "\n";
                }
            }

            if($hasargs) {
                $text .= "\n";
                foreach($this->setup[$command]['args'] as $arg) {
                    $name = '<'.$arg['name'].'>';

                    $text .= $this->tableFormat(
                        array(2, 20, 52),
                        array('', $name, $arg['help'])
                    );
                }
            }

            if($command == '' && $hascommands) {
                $text .= "\nThis tool accepts a command as first parameter as outlined below:\n";
            }
        }

        return $text;
    }

    /**
     * Safely read the $argv PHP array across different PHP configurations.
     * Will take care on register_globals and register_argc_argv ini directives
     *
     * @throws DokuCLI_Exception
     * @return array the $argv PHP array or PEAR error if not registered
     */
    private function readPHPArgv() {
        global $argv;
        if(!is_array($argv)) {
            if(!@is_array($_SERVER['argv'])) {
                if(!@is_array($GLOBALS['HTTP_SERVER_VARS']['argv'])) {
                    throw new DokuCLI_Exception(
                        "Could not read cmd args (register_argc_argv=Off?)",
                        DOKU_CLI_OPTS_ARG_READ
                    );
                }
                return $GLOBALS['HTTP_SERVER_VARS']['argv'];
            }
            return $_SERVER['argv'];
        }
        return $argv;
    }

    /**
     * Displays text in multiple word wrapped columns
     *
     * @param int[]    $widths list of column widths (in characters)
     * @param string[] $texts  list of texts for each column
     * @return string
     */
    private function tableFormat($widths, $texts) {
        $wrapped = array();
        $maxlen  = 0;

        foreach($widths as $col => $width) {
            $wrapped[$col] = explode("\n", wordwrap($texts[$col], $width - 1, "\n", true)); // -1 char border
            $len           = count($wrapped[$col]);
            if($len > $maxlen) $maxlen = $len;

        }

        $out = '';
        for($i = 0; $i < $maxlen; $i++) {
            foreach($widths as $col => $width) {
                if(isset($wrapped[$col][$i])) {
                    $val = $wrapped[$col][$i];
                } else {
                    $val = '';
                }
                $out .= sprintf('%-'.$width.'s', $val);
            }
            $out .= "\n";
        }
        return $out;
    }
}

/**
 * Class DokuCLI_Exception
 *
 * The code is used as exit code for the CLI tool. This should probably be extended. Many cases just fall back to the
 * E_ANY code.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class DokuCLI_Exception extends Exception {
    const E_ANY = -1; // no error code specified
    const E_UNKNOWN_OPT = 1; //Unrecognized option
    const E_OPT_ARG_REQUIRED = 2; //Option requires argument
    const E_OPT_ARG_DENIED = 3; //Option not allowed argument
    const E_OPT_ABIGUOUS = 4; //Option abiguous
    const E_ARG_READ = 5; //Could not read argv

    /**
     * @param string    $message     The Exception message to throw.
     * @param int       $code        The Exception code
     * @param Exception $previous    The previous exception used for the exception chaining.
     */
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        if(!$code) $code = DokuCLI_Exception::E_ANY;
        parent::__construct($message, $code, $previous);
    }
}
