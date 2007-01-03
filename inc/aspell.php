<?php
/**
 * Aspell interface
 *
 * This library gives full access to aspell's pipe interface. Optionally it
 * provides some of the functions from the pspell PHP extension by wrapping
 * them to calls to the aspell binary.
 *
 * It can be simply dropped into code written for the pspell extension like
 * the following
 *
 * if(!function_exists('pspell_suggest')){
 *   define('PSPELL_COMP',1);
 *   require_once ("pspell_comp.php");
 * }
 *
 * Define the path to the aspell binary like this if needed:
 *
 * define('ASPELL_BIN','/path/to/aspell');
 *
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @todo     Not all pspell functions are supported
 *
 */

// path to your aspell binary
if(!defined('ASPELL_BIN')) define('ASPELL_BIN','aspell');


// different spelling modes supported by aspell
if(!defined('PSPELL_FAST'))         define(PSPELL_FAST,1);         # Fast mode (least number of suggestions)
if(!defined('PSPELL_NORMAL'))       define(PSPELL_NORMAL,2);       # Normal mode (more suggestions)
if(!defined('PSPELL_BAD_SPELLERS')) define(PSPELL_BAD_SPELLERS,3); # Slow mode (a lot of suggestions)
if(!defined('ASPELL_ULTRA'))        define(ASPELL_ULTRA,4);        # Ultra fast mode (not available in Pspell!)



/**
 * You can define PSPELL_COMP to use this class as drop in replacement
 * for the pspell extension
 */
if(defined('PSPELL_COMP')){
    // spelling is not supported by aspell and ignored
    function pspell_config_create($language, $spelling=null, $jargon=null, $encoding='iso8859-1'){
        return new Aspell($language, $jargon, $encoding);
    }

    function pspell_config_mode(&$config, $mode){
        return $config->setMode($mode);
    }

    function pspell_new_config(&$config){
        return $config;
    }

    function pspell_check(&$dict,$word){
        return $dict->check($word);
    }

    function pspell_suggest(&$dict, $word){
        return $dict->suggest($word);
    }
}

/**
 * Class to interface aspell
 *
 * Needs PHP >= 4.3.0
 */
class Aspell{
    var $language = null;
    var $jargon   = null;
    var $personal = null;
    var $encoding = 'iso8859-1';
    var $mode     = PSPELL_NORMAL;
    var $version  = 0;

    var $args='';

    /**
     * Constructor. Works like pspell_config_create()
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function Aspell($language, $jargon=null, $encoding='iso8859-1'){
        $this->language = $language;
        $this->jargon   = $jargon;
        $this->encoding = $encoding;
    }

    /**
     * Set the spelling mode like pspell_config_mode()
     *
     * Mode can be PSPELL_FAST, PSPELL_NORMAL, PSPELL_BAD_SPELLER or ASPELL_ULTRA
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function setMode($mode){
        if(!in_array($mode,array(PSPELL_FAST,PSPELL_NORMAL,PSPELL_BAD_SPELLER,ASPELL_ULTRA))){
            $mode = PSPELL_NORMAL;
        }

        $this->mode = $mode;
        return $mode;
    }

    /**
     * Prepares the needed arguments for the call to the aspell binary
     *
     * No need to call this directly
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function _prepareArgs(){
        $this->args = '';

        if($this->language != null){
            $this->args .= ' --lang='.escapeshellarg($this->language);
        }else{
            return false; // no lang no spell
        }

        if($this->jargon != null){
            $this->args .= ' --jargon='.escapeshellarg($this->jargon);
        }

        if($this->personal != null){
            $this->args .= ' --personal='.escapeshellarg($this->personal);
        }

        if($this->encoding != null){
            $this->args .= ' --encoding='.escapeshellarg($this->encoding);
        }

        switch ($this->mode){
            case PSPELL_FAST:
                $this->args .= ' --sug-mode=fast';
                break;
            case PSPELL_BAD_SPELLERS:
                $this->args .= ' --sug-mode=bad-spellers';
                break;
            case ASPELL_ULTRA:
                $this->args .= ' --sug-mode=ultra';
                break;
            default:
                $this->args .= ' --sug-mode=normal';
        }

        return true;
    }


    /**
     * Pipes a text to aspell
     *
     * This opens a bidirectional pipe to the aspell binary, writes
     * the given text to STDIN and returns STDOUT and STDERR
     *
     * You can give an array of special commands to be executed first
     * as $specials parameter. Data lines are escaped automatically
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     * @link     http://aspell.sf.net/man-html/Through-A-Pipe.html
     */
    function runAspell($text,&$out,&$err,$specials=null){
        if(empty($text)) return true;
        $terse = true;

        // prepare arguments
        $this->_prepareArgs();
        $command = ASPELL_BIN.' -a'.$this->args;
        $stdin   = '';

        // prepare specials
        if(is_array($specials)){
            foreach($specials as $s){
                if ($s == '!') $terse = false;
                $stdin .= "$s\n";
            }
        }

        // prepare text
        $stdin .= "^".str_replace("\n", "\n^",$text);

        // run aspell through the pipe
        $rc = $this->execPipe($command,$stdin,$out,$err);
        if(is_null($rc)){
            $err = "Could not run Aspell '".ASPELL_BIN."'";
            return false;
        }

        // Aspell has a bug that can't be autodetected because both versions
        // might produce the same output but under different conditions. So
        // we check Aspells version number here to divide broken and working
        // versions of Aspell.
        $tmp = array();
        preg_match('/^\@.*Aspell (\d+)\.(\d+).(\d+)/',$out,$tmp);
        $this->version = $tmp[1]*100 + $tmp[2]*10 + $tmp[3];

        if ($this->version <= 603)  // version 0.60.3
            $r = $terse ? "\n*\n\$1" : "\n\$1"; // replacement for broken Aspell
        else
            $r = $terse ? "\n*\n" : "\n";    // replacement for good Aspell

        // lines starting with a '?' are no realy misspelled words and some
        // Aspell versions doesn't produce usable output anyway so we filter
        // them out here.
        $out = preg_replace('/\n\? [^\n\&\*]*([\n]?)/',$r, $out);

        if ($err){
            //something went wrong
            $err = "Aspell returned an error(".ASPELL_BIN." exitcode: $rc ):\n".$err;
            return false;
        }
        return true;
    }


    /**
     * Runs the given command with the given input on STDIN
     *
     * STDOUT and STDERR are written to the given vars, the command's
     * exit code is returned. If the pip couldn't be opened null is returned
     *
     * @author <richard at 2006 dot atterer dot net>
     * @link http://www.php.net/manual/en/function.proc-open.php#64116
     */
    function execPipe($command,$stdin,&$stdout,&$stderr){
        $descriptorSpec = array(0 => array("pipe", "r"),
                                1 => array('pipe', 'w'),
                                2 => array('pipe', 'w'));
        $process = proc_open($command, $descriptorSpec, $pipes);
        if(!$process) return null;

        $txOff = 0;
        $txLen = strlen($stdin);
        $stdoutDone = false;
        $stderrDone = false;

        stream_set_blocking($pipes[0], 0); // Make stdin/stdout/stderr non-blocking
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);

        if ($txLen == 0) fclose($pipes[0]);
        while (true) {
            $rx = array(); // The program's stdout/stderr
            if (!$stdoutDone) $rx[] = $pipes[1];
            if (!$stderrDone) $rx[] = $pipes[2];
            $tx = array(); // The program's stdin
            if ($txOff < $txLen) $tx[] = $pipes[0];
            stream_select($rx, $tx, $ex = NULL, NULL, NULL); // Block til r/w possible

            if (!empty($tx)) {
                $txRet = fwrite($pipes[0], substr($stdin, $txOff, 8192));
                if ($txRet !== false) $txOff += $txRet;
                if ($txOff >= $txLen) fclose($pipes[0]);
            }

            foreach ($rx as $r) {
                if ($r == $pipes[1]) {
                    $stdout .= fread($pipes[1], 8192);
                    if (feof($pipes[1])) {
                        fclose($pipes[1]);
                        $stdoutDone = true;
                    }
                } else if ($r == $pipes[2]) {
                    $stderr .= fread($pipes[2], 8192);
                    if (feof($pipes[2])) {
                        fclose($pipes[2]);
                        $stderrDone = true;
                    }
                }
            }
            if (!is_resource($process)) break;
            if ($txOff >= $txLen && $stdoutDone && $stderrDone) break;
        }
        return proc_close($process);
    }




    /**
     * Checks a single word for correctness
     *
     * @returns  array of suggestions or true on correct spelling
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function suggest($word){
        if($this->runAspell("^$word",$out,$err)){
            //parse output
            $lines = split("\n",$out);
            foreach ($lines as $line){
                $line = trim($line);
                if(empty($line))    continue;       // empty line
                if($line[0] == '@') continue;       // comment
                if($line[0] == '*') return true;    // no mistakes made
                if($line[0] == '#') return array(); // mistake but no suggestions
                if($line[0] == '&'){
                    $line = preg_replace('/&.*?: /','',$line);
                    return split(', ',$line);
                }
            }
        }
        return array();
    }

    /**
     * Check if a word is mispelled like pspell_check
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function check($word){
        if(is_array($this->suggest($word))){
            return false;
        }else{
            return true;
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
