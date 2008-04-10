<?php
/**
* Brutally chopped and modified from http://pear.php.net/package/Console_Getopts
*/
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Andrei Zmievski <andrei@php.net>                             |
// | Modified: Harry Fuecks hfuecks  gmail.com                   |
// +----------------------------------------------------------------------+
//

if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');

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
 */
 class Doku_Cli_Opts {

    /**
    * <?php ?>
    * @see http://www.sitepoint.com/article/php-command-line-1/3
    * @param string executing file name - this MUST be passed the __FILE__ constant
    * @param string short options
    * @param array (optional) long options
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

    function getopt2($args, $short_options, $long_options = null) {
        return Doku_Cli_Opts::doGetopt(
            2, $args, $short_options, $long_options
            );
    }

    function getopt($args, $short_options, $long_options = null) {
        return Doku_Cli_Opts::doGetopt(
            1, $args, $short_options, $long_options
            );
    }

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
            } else {
                $error = Doku_Cli_Opts::_parseShortOption(substr($arg, 1), $short_options, $opts, $args);
                if (Doku_Cli_Opts::isError($error))
                    return $error;
            }
        }

        return array($opts, $non_opts);
    }

    function _parseShortOption($arg, $short_options, &$opts, &$args) {
        for ($i = 0; $i < strlen($arg); $i++) {
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
                    } else if (list(, $opt_arg) = each($args))
                        /* Else use the next argument. */;
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

    function _parseLongOption($arg, $long_options, &$opts, &$args) {
        @list($opt, $opt_arg) = explode('=', $arg);
        $opt_len = strlen($opt);

        for ($i = 0; $i < count($long_options); $i++) {
            $long_opt  = $long_options[$i];
            $opt_start = substr($long_opt, 0, $opt_len);

            /* Option doesn't match. Go on to the next one. */
            if ($opt_start != $opt)
                continue;

            $opt_rest  = substr($long_opt, $opt_len);

            /* Check that the options uniquely matches one of the allowed
               options. */
            if ($opt_rest != '' && $opt{0} != '=' &&
                $i + 1 < count($long_options) &&
                $opt == substr($long_options[$i+1], 0, $opt_len)) {
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

    function raiseError($code, $msg) {
        return new Doku_Cli_Opts_Error($code, $msg);
    }

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
