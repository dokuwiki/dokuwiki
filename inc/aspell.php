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
    var $encoding = 'iso8859-1';
    var $mode     = PSPELL_NORMAL;
    
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
        $this->_prepareArgs();
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
        $this->_prepareArgs();
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

        if($this->language){
            $this->args .= ' --lang='.escapeshellarg($this->language);
        }else{
            return false; // no lang no spell
        }

        if($this->jargon){
            $this->args .= ' --jargon='.escapeshellarg($this->jargon);
        }

        if($this->encoding){
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
     * You have full access to aspell's pipe mode here - this means you need
     * quote your lines yourself read the aspell manual for more info
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     * @link     http://aspell.sf.net/man-html/Through-A-Pipe.html
     */
    function runAspell($text,&$out,&$err){
        if(empty($text)) return true;

        //prepare file descriptors
        $descspec = array(
               0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
               1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
               2 => array('pipe', 'w')    // stderr is a file to write to
        );

        $process = proc_open(ASPELL_BIN.' -a'.$this->args, $descspec, $pipes);
        if (is_resource($process)) {
            //write to stdin
            fwrite($pipes[0],$text);
            fclose($pipes[0]);

            //read stdout
            while (!feof($pipes[1])) {
                $out .= fread($pipes[1], 8192);
            }
            fclose($pipes[1]);

            //read stderr
            while (!feof($pipes[2])) {
                $err .= fread($pipes[2], 8192);
            }
            fclose($pipes[2]);

            if(proc_close($process) != 0){
                //something went wrong
                trigger_error("aspell returned an error: $err", E_USER_WARNING);
                return null;
            }
            return true;
        }
        //opening failed
        trigger_error("Could not run aspell '".ASPELL_BIN."'", E_USER_WARNING);
        return false;
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
