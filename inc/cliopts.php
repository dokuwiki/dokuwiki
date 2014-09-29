<?php
/**
 * Brutally chopped and modified from http://pear.php.net/package/Console_Getopts
 *
 * PHP Version 5
 *
 * Copyright (c) 1997-2004 The PHP Group
 *
 * LICENSE: This source file is subject to the New BSD license that is
 * available through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/bsd-license.php. If you did not receive
 * a copy of the New BSD License and are unable to obtain it through the web,
 * please send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category Console
 * @package  Console_Getopt
 * @author   Andrei Zmievski <andrei@php.net>
 * @modified Harry Fuecks hfuecks  gmail.com
 * @modified Tanguy Ortolo <tanguy+dokuwiki@ortolo.eu>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version  CVS: $Id$
 * @link     http://pear.php.net/package/Console_Getopt
 *
 */

//------------------------------------------------------------------------------
/**
 * Sets up CLI environment based on SAPI and PHP version
 * Helps resolve some issues between the CGI and CLI SAPIs
 * as well is inconsistencies between PHP 4.3+ and older versions
 */
if (version_compare(phpversion(), '4.3.0', '<') || php_sapi_name() == 'cgi') {
    // Handle output buffering
    @ob_end_flush();
    ob_implicit_flush(true);

    // PHP ini settings
    set_time_limit(0);
    ini_set('track_errors', true);
    ini_set('html_errors', false);
    ini_set('magic_quotes_runtime', false);

    // Define stream constants
    define('STDIN', fopen('php://stdin', 'r'));
    define('STDOUT', fopen('php://stdout', 'w'));
    define('STDERR', fopen('php://stderr', 'w'));

    // Close the streams on script termination
    register_shutdown_function(
        create_function('',
        'fclose(STDIN); fclose(STDOUT); fclose(STDERR); return true;')
        );
}

//------------------------------------------------------------------------------
/**
* Error codes
*/
define('DOKU_CLI_OPTS_UNKNOWN_OPT',1); //Unrecognized option
define('DOKU_CLI_OPTS_OPT_ARG_REQUIRED',2); //Option requires argument
define('DOKU_CLI_OPTS_OPT_ARG_DENIED',3); //Option not allowed argument
define('DOKU_CLI_OPTS_OPT_ABIGUOUS',4);//Option abiguous
define('DOKU_CLI_OPTS_ARG_READ',5);//Could not read argv

//------------------------------------------------------------------------------
/**
 * Command-line options parsing class.
 *
 * @author Andrei Zmievski <andrei@php.net>
 *
 * @deprecated 2014-05-16
 */
class Doku_Cli_Opts {

    /**
     * <?php ?>
     * @see http://www.sitepoint.com/article/php-command-line-1/3
     * @param string $bin_file      executing file name - this MUST be passed the __FILE__ constant
     * @param string $short_options short options
     * @param array  $long_options  (optional) long options
     * @return Doku_Cli_Opts_Container or Doku_Cli_Opts_Error
     */
    function & getOptions($bin_file, $short_options, $long_options = null) {
        $args = Doku_Cli_Opts::readPHPArgv();

        if ( Doku_Cli_Opts::isError($args) ) {
            return $args;
        }

        // Compatibility between "php extensions.php" and "./extensions.php"
        if ( realpath($_SERVER['argv'][0]) == $bin_file ) {
            $options = Doku_Cli_Opts::getOpt($args,$short_options,$long_options);
        } else {
            $options = Doku_Cli_Opts::getOpt2($args,$short_options,$long_options);
        }

        if ( Doku_Cli_Opts::isError($options) ) {
            return $options;
        }

        $container = new Doku_Cli_Opts_Container($options);
        return $container;
    }

    /**
     * Parses the command-line options.
     *
     * The first parameter to this function should be the list of command-line
     * arguments without the leading reference to the running program.
     *
     * The second parameter is a string of allowed short options. Each of the
     * option letters can be followed by a colon ':' to specify that the option
     * requires an argument, or a double colon '::' to specify that the option
     * takes an optional argument.
     *
     * The third argument is an optional array of allowed long options. The
     * leading '--' should not be included in the option name. Options that
     * require an argument should be followed by '=', and options that take an
     * option argument should be followed by '=='.
     *
     * The return value is an array of two elements: the list of parsed
     * options and the list of non-option command-line arguments. Each entry in
     * the list of parsed options is a pair of elements - the first one
     * specifies the option, and the second one specifies the option argument,
     * if there was one.
     *
     * Long and short options can be mixed.
     *
     * Most of the semantics of this function are based on GNU getopt_long().
     *
     * @param array  $args          an array of command-line arguments
     * @param string $short_options specifies the list of allowed short options
     * @param array  $long_options  specifies the list of allowed long options
     *
     * @return array two-element array containing the list of parsed options and
     * the non-option arguments
     * @access public
     */
    function getopt2($args, $short_options, $long_options = null) {
        return Doku_Cli_Opts::doGetopt(
            2, $args, $short_options, $long_options
            );
    }

    /**
     * This function expects $args to start with the script name (POSIX-style).
     * Preserved for backwards compatibility.
     *
     * @param array  $args          an array of command-line arguments
     * @param string $short_options specifies the list of allowed short options
     * @param array  $long_options  specifies the list of allowed long options
     *
     * @see getopt2()
     * @return array two-element array containing the list of parsed options and
     * the non-option arguments
     */
    function getopt($args, $short_options, $long_options = null) {
        return Doku_Cli_Opts::doGetopt(
            1, $args, $short_options, $long_options
            );
    }

    /**
     * The actual implementation of the argument parsing code.
     *
     * @param int    $version       Version to use
     * @param array  $args          an array of command-line arguments
     * @param string $short_options specifies the list of allowed short options
     * @param array  $long_options  specifies the list of allowed long options
     *
     * @return array
     */
    function doGetopt($version, $args, $short_options, $long_options = null) {

        // in case you pass directly readPHPArgv() as the first arg
        if (Doku_Cli_Opts::isError($args)) {
            return $args;
        }
        if (empty($args)) {
            return array(array(), array());
        }
        $opts     = array();
        $non_opts = array();

        settype($args, 'array');

        if ($long_options && is_array($long_options)) {
            sort($long_options);
        }

        /*
         * Preserve backwards compatibility with callers that relied on
         * erroneous POSIX fix.
         */
        if ($version < 2) {
            if (isset($args[0]{0}) && $args[0]{0} != '-') {
                array_shift($args);
            }
        }

        reset($args);
        while (list($i, $arg) = each($args)) {

            /* The special element '--' means explicit end of
               options. Treat the rest of the arguments as non-options
               and end the loop. */
            if ($arg == '--') {
                $non_opts = array_merge($non_opts, array_slice($args, $i + 1));
                break;
            }

            if ($arg{0} != '-' || (strlen($arg) > 1 && $arg{1} == '-' && !$long_options)) {
                $non_opts = array_merge($non_opts, array_slice($args, $i));
                break;
            } elseif (strlen($arg) > 1 && $arg{1} == '-') {
                $error = Doku_Cli_Opts::_parseLongOption(substr($arg, 2), $long_options, $opts, $args);
                if (Doku_Cli_Opts::isError($error))
                    return $error;
            } elseif ($arg == '-') {
                // - is stdin
                $non_opts = array_merge($non_opts, array_slice($args, $i));
                break;
            } else {
                $error = Doku_Cli_Opts::_parseShortOption(substr($arg, 1), $short_options, $opts, $args);
                if (Doku_Cli_Opts::isError($error))
                    return $error;
            }
        }

        return array($opts, $non_opts);
    }

    /**
     * Parse short option
     *
     * @param string     $arg           Argument
     * @param string     $short_options Available short options
     * @param string[][] &$opts
     * @param string[]   &$args
     *
     * @access private
     * @return void|Doku_Cli_Opts_Error
     */
    function _parseShortOption($arg, $short_options, &$opts, &$args) {
        $len = strlen($arg);
        for ($i = 0; $i < $len; $i++) {
            $opt = $arg{$i};
            $opt_arg = null;

            /* Try to find the short option in the specifier string. */
            if (($spec = strstr($short_options, $opt)) === false || $arg{$i} == ':')
            {
                return Doku_Cli_Opts::raiseError(
                    DOKU_CLI_OPTS_UNKNOWN_OPT,
                    "Unrecognized option -- $opt"
                    );
            }

            if (strlen($spec) > 1 && $spec{1} == ':') {
                if (strlen($spec) > 2 && $spec{2} == ':') {
                    if ($i + 1 < strlen($arg)) {
                        /* Option takes an optional argument. Use the remainder of
                           the arg string if there is anything left. */
                        $opts[] = array($opt, substr($arg, $i + 1));
                        break;
                    }
                } else {
                    /* Option requires an argument. Use the remainder of the arg
                       string if there is anything left. */
                    if ($i + 1 < strlen($arg)) {
                        $opts[] = array($opt,  substr($arg, $i + 1));
                        break;
                    } else if (list(, $opt_arg) = each($args)) {
                        /* Else use the next argument. */;
                        if (Doku_Cli_Opts::_isShortOpt($opt_arg) || Doku_Cli_Opts::_isLongOpt($opt_arg))
                            return Doku_Cli_Opts::raiseError(
                                DOKU_CLI_OPTS_OPT_ARG_REQUIRED,
                                "option requires an argument --$opt"
                                );
                    }
                    else
                        return Doku_Cli_Opts::raiseError(
                            DOKU_CLI_OPTS_OPT_ARG_REQUIRED,
                            "Option requires an argument -- $opt"
                            );
                }
            }

            $opts[] = array($opt, $opt_arg);
        }
    }

    /**
     * Checks if an argument is a short option
     *
     * @param string $arg Argument to check
     *
     * @access private
     * @return bool
     */
    function _isShortOpt($arg){
        return strlen($arg) == 2 && $arg[0] == '-'
               && preg_match('/[a-zA-Z]/', $arg[1]);
    }

    /**
     * Checks if an argument is a long option
     *
     * @param string $arg Argument to check
     *
     * @access private
     * @return bool
     */
    function _isLongOpt($arg){
        return strlen($arg) > 2 && $arg[0] == '-' && $arg[1] == '-' &&
               preg_match('/[a-zA-Z]+$/', substr($arg, 2));
    }

    /**
     * Parse long option
     *
     * @param string     $arg          Argument
     * @param string[]   $long_options Available long options
     * @param string[][] &$opts
     * @param string[]   &$args
     *
     * @access private
     * @return void|Doku_Cli_Opts_Error
     */
    function _parseLongOption($arg, $long_options, &$opts, &$args) {
        @list($opt, $opt_arg) = explode('=', $arg, 2);
        $opt_len = strlen($opt);
        $opt_cnt = count($long_options);

        for ($i = 0; $i < $opt_cnt; $i++) {
            $long_opt  = $long_options[$i];
            $opt_start = substr($long_opt, 0, $opt_len);

            $long_opt_name = str_replace('=', '', $long_opt);

            /* Option doesn't match. Go on to the next one. */
            if ($opt_start != $opt)
                continue;

            $opt_rest = substr($long_opt, $opt_len);

            /* Check that the options uniquely matches one of the allowed
               options. */
            if ($i + 1 < count($long_options)) {
                $next_option_rest = substr($long_options[$i + 1], $opt_len);
            } else {
                $next_option_rest = '';
            }

            if ($opt_rest != '' && $opt{0} != '=' &&
                $i + 1 < $opt_cnt &&
                $opt == substr($long_options[$i+1], 0, $opt_len) &&
                $next_option_rest != '' &&
                $next_option_rest{0} != '=') {
                return Doku_Cli_Opts::raiseError(
                    DOKU_CLI_OPTS_OPT_ABIGUOUS,
                    "Option --$opt is ambiguous"
                    );
            }

            if (substr($long_opt, -1) == '=') {
                if (substr($long_opt, -2) != '==') {
                    /* Long option requires an argument.
                       Take the next argument if one wasn't specified. */;
                    if (!strlen($opt_arg) && !(list(, $opt_arg) = each($args))) {
                        return Doku_Cli_Opts::raiseError(
                            DOKU_CLI_OPTS_OPT_ARG_REQUIRED,
                            "Option --$opt requires an argument"
                            );
                    }

                    if (Doku_Cli_Opts::_isShortOpt($opt_arg)
                        || Doku_Cli_Opts::_isLongOpt($opt_arg))
                        return Doku_Cli_Opts::raiseError(
                            DOKU_CLI_OPTS_OPT_ARG_REQUIRED,
                            "Option --$opt requires an argument"
                            );
                }
            } else if ($opt_arg) {
                return Doku_Cli_Opts::raiseError(
                    DOKU_CLI_OPTS_OPT_ARG_DENIED,
                    "Option --$opt doesn't allow an argument"
                    );
            }

            $opts[] = array('--' . $opt, $opt_arg);
            return;
        }

        return Doku_Cli_Opts::raiseError(
            DOKU_CLI_OPTS_UNKNOWN_OPT,
            "Unrecognized option --$opt"
            );
    }

    /**
     * Safely read the $argv PHP array across different PHP configurations.
     * Will take care on register_globals and register_argc_argv ini directives
     *
     * @access public
     * @return array|Doku_Cli_Opts_Error the $argv PHP array or PEAR error if not registered
     */
    function readPHPArgv() {
        global $argv;
        if (!is_array($argv)) {
            if (!@is_array($_SERVER['argv'])) {
                if (!@is_array($GLOBALS['HTTP_SERVER_VARS']['argv'])) {
                    return Doku_Cli_Opts::raiseError(
                        DOKU_CLI_OPTS_ARG_READ,
                        "Could not read cmd args (register_argc_argv=Off?)"
                        );
                }
                return $GLOBALS['HTTP_SERVER_VARS']['argv'];
            }
            return $_SERVER['argv'];
        }
        return $argv;
    }

    /**
     * @param $code
     * @param $msg
     * @return Doku_Cli_Opts_Error
     */
    function raiseError($code, $msg) {
        return new Doku_Cli_Opts_Error($code, $msg);
    }

    /**
     * @param $obj
     * @return bool
     */
    function isError($obj) {
        return is_a($obj, 'Doku_Cli_Opts_Error');
    }

}

//------------------------------------------------------------------------------
class Doku_Cli_Opts_Error {

    var $code;
    var $msg;

    function Doku_Cli_Opts_Error($code, $msg) {
        $this->code = $code;
        $this->msg = $msg;
    }

    function getMessage() {
        return $this->msg;
    }

    function isError() {
        return true;
    }

}

//------------------------------------------------------------------------------
class Doku_Cli_Opts_Container {

    var $options = array();
    var $args = array();

    function Doku_Cli_Opts_Container($options) {
        foreach ( $options[0] as $option ) {
            if ( false !== ( strpos($option[0], '--') ) ) {
                $opt_name = substr($option[0], 2);
            } else {
                $opt_name = $option[0];
            }
            $this->options[$opt_name] = $option[1];
        }

        $this->args = $options[1];
    }

    function has($option) {
        return array_key_exists($option, $this->options);
    }

    function get($option) {
        if ( isset($this->options[$option]) ) {
            return ( $this->options[$option] ) ;
        }
    }

    function arg($index) {
        if ( isset($this->args[$index]) ) {
            return $this->args[$index];
        }
    }

    function numArgs() {
        return count($this->args);
    }

    function hasArgs() {
        return count($this->args) !== 0;
    }

    function isError() {
        return false;
    }

}
