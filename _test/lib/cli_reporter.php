<?php // -*- fill-column: 80; tab-width: 4; c-basic-offset: 4 -*-

if (! defined('ST_FAILDETAIL_SEPARATOR')) {
    define('ST_FAILDETAIL_SEPARATOR', "->");
}

if (! defined('ST_FAILS_RETURN_CODE')) {
    define('ST_FAILS_RETURN_CODE', 1);
}

if (version_compare(phpversion(), '4.3.0', '<') ||
    php_sapi_name() == 'cgi') {
    define('STDOUT', fopen('php://stdout', 'w'));
    define('STDERR', fopen('php://stderr', 'w'));
    register_shutdown_function(
        create_function('', 'fclose(STDOUT); fclose(STDERR); return true;'));
}

/**
 * Minimal command line test displayer. Writes fail details to STDERR. Returns 0
 * to the shell if all tests pass, ST_FAILS_RETURN_CODE if any test fails.
 */
class CLIReporter extends SimpleReporter {

    var $faildetail_separator = ST_FAILDETAIL_SEPARATOR;
    var $_failinfo;

    function CLIReporter($faildetail_separator = NULL) {
        $this->SimpleReporter();
        if (! is_null($faildetail_separator)) {
            $this->setFailDetailSeparator($faildetail_separator);
        }
    }

    function setFailDetailSeparator($separator) {
        $this->faildetail_separator = $separator;
    }

    /**
     * Return a formatted faildetail for printing.
     */
    function &_paintTestFailDetail(&$message) {
        $buffer = '';
        $faildetail = $this->getTestList();
        array_shift($faildetail);
        $buffer .= implode($this->faildetail_separator, $faildetail);
        $buffer .= $this->faildetail_separator . "$message\n";
        return $buffer;
    }

    /**
     * Paint fail faildetail to STDERR.
     */
    function paintFail($message) {
        parent::paintFail($message);
        fwrite(STDERR, 'FAIL' . $this->faildetail_separator .
               $this->_paintTestFailDetail($message));
        if($this->_failinfo){
            fwrite(STDERR, '  additional info was: '.$this->_failinfo."\n");
            $this->_failinfo = '';
        }
    }

    /**
     * reset failinfo
     */
    function paintPass($message) {
        parent::paintPass($message);
        $this->_failinfo = '';
    }

    /**
     * Paint exception faildetail to STDERR.
     */
    function paintException($message) {
        parent::paintException($message);
        fwrite(STDERR, 'EXCEPTION' . $this->faildetail_separator .
               $this->_paintTestFailDetail($message));
    }

    /**
     * Handle failinfo message
     */
    function paintSignal($type,$message) {
        parent::paintSignal($type,$message);
        if($type = 'failinfo') $this->_failinfo = $message;
    }



    /**
     * Paint a footer with test case name, timestamp, counts of fails and
     * exceptions.
     */
    function paintFooter($test_name) {
        $buffer = $this->getTestCaseProgress() . '/' .
            $this->getTestCaseCount() . ' test cases complete: ';

        if (0 < ($this->getFailCount() + $this->getExceptionCount())) {
            $buffer .= $this->getPassCount() . " passes";
            if (0 < $this->getFailCount()) {
                $buffer .= ", " . $this->getFailCount() . " fails";
            }
            if (0 < $this->getExceptionCount()) {
                $buffer .= ", " . $this->getExceptionCount() . " exceptions";
            }
            $buffer .= ".\n";
            fwrite(STDOUT, $buffer);
            exit(ST_FAILS_RETURN_CODE);
        } else {
            fwrite(STDOUT, $buffer . $this->getPassCount() . " passes.\n");
        }
    }
}
