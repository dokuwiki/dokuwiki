<?php
/**
 * Information and debugging functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) die('meh.');
if(!defined('DOKU_MESSAGEURL')) define('DOKU_MESSAGEURL','http://update.dokuwiki.org/check/');

/**
 * Check for new messages from upstream
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function checkUpdateMessages(){
    global $conf;
    global $INFO;
    global $updateVersion;
    if(!$conf['updatecheck']) return;
    if($conf['useacl'] && !$INFO['ismanager']) return;

    $cf = $conf['cachedir'].'/messages.txt';
    $lm = @filemtime($cf);

    // check if new messages needs to be fetched
    if($lm < time()-(60*60*24) || $lm < @filemtime(DOKU_INC.DOKU_SCRIPT)){
        @touch($cf);
        dbglog("checkUpdateMessages(): downloading messages.txt");
        $http = new DokuHTTPClient();
        $http->timeout = 12;
        $data = $http->get(DOKU_MESSAGEURL.$updateVersion);
        io_saveFile($cf,$data);
    }else{
        dbglog("checkUpdateMessages(): messages.txt up to date");
        $data = io_readFile($cf);
    }

    // show messages through the usual message mechanism
    $msgs = explode("\n%\n",$data);
    foreach($msgs as $msg){
        if($msg) msg($msg,2);
    }
}


/**
 * Return DokuWiki's version (split up in date and type)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getVersionData(){
    $version = array();
    //import version string
    if(@file_exists(DOKU_INC.'VERSION')){
        //official release
        $version['date'] = trim(io_readfile(DOKU_INC.'VERSION'));
        $version['type'] = 'Release';
    }elseif(is_dir(DOKU_INC.'.git')){
        $version['type'] = 'Git';
        $version['date'] = 'unknown';

        $inventory = DOKU_INC.'.git/logs/HEAD';
        if(is_file($inventory)){
            $sz   = filesize($inventory);
            $seek = max(0,$sz-2000); // read from back of the file
            $fh   = fopen($inventory,'rb');
            fseek($fh,$seek);
            $chunk = fread($fh,2000);
            fclose($fh);
            $chunk = trim($chunk);
            $chunk = @array_pop(explode("\n",$chunk));   //last log line
            $chunk = @array_shift(explode("\t",$chunk)); //strip commit msg
            $chunk = explode(" ",$chunk);
            array_pop($chunk); //strip timezone
            $date = date('Y-m-d',array_pop($chunk));
            if($date) $version['date'] = $date;
        }
    }else{
        global $updateVersion;
        $version['date'] = 'update version '.$updateVersion;
        $version['type'] = 'snapshot?';
    }
    return $version;
}

/**
 * Return DokuWiki's version (as a string)
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function getVersion(){
    $version = getVersionData();
    return $version['type'].' '.$version['date'];
}

/**
 * print a message
 *
 * If HTTP headers were not sent yet the message is added
 * to the global message array else it's printed directly
 * using html_msgarea()
 *
 *
 * Levels can be:
 *
 * -1 error
 *  0 info
 *  1 success
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    html_msgarea
 */

define('MSG_PUBLIC', 0);
define('MSG_USERS_ONLY', 1);
define('MSG_MANAGERS_ONLY',2);
define('MSG_ADMINS_ONLY',4);

function msg($message,$lvl=0,$line='',$file='',$allow=MSG_PUBLIC){
    global $MSG, $MSG_shown;
    $errors[-1] = 'error';
    $errors[0]  = 'info';
    $errors[1]  = 'success';
    $errors[2]  = 'notify';

    if($line || $file) $message.=' ['.utf8_basename($file).':'.$line.']';

    if(!isset($MSG)) $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message, 'allow' => $allow);
    if(isset($MSG_shown) || headers_sent()){
        if(function_exists('html_msgarea')){
            html_msgarea();
        }else{
            print "ERROR($lvl) $message";
        }
        unset($GLOBALS['MSG']);
    }
}
/**
 * Determine whether the current user is allowed to view the message
 * in the $msg data structure
 *
 * @param  $msg   array    dokuwiki msg structure
 *                         msg   => string, the message
 *                         lvl   => int, level of the message (see msg() function)
 *                         allow => int, flag used to determine who is allowed to see the message
 *                                       see MSG_* constants
 */
function info_msg_allowed($msg){
    global $INFO, $auth;

    // is the message public? - everyone and anyone can see it
    if (empty($msg['allow']) || ($msg['allow'] == MSG_PUBLIC)) return true;

    // restricted msg, but no authentication
    if (empty($auth)) return false;

    switch ($msg['allow']){
        case MSG_USERS_ONLY:
            return !empty($INFO['userinfo']);

        case MSG_MANAGERS_ONLY:
            return $INFO['ismanager'];

        case MSG_ADMINS_ONLY:
            return $INFO['isadmin'];

        default:
            trigger_error('invalid msg allow restriction.  msg="'.$msg['msg'].'" allow='.$msg['allow'].'"', E_USER_WARNING);
            return $INFO['isadmin'];
    }

    return false;
}

/**
 * print debug messages
 *
 * little function to print the content of a var
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function dbg($msg,$hidden=false){
    if($hidden){
        echo "<!--\n";
        print_r($msg);
        echo "\n-->";
    }else{
        echo '<pre class="dbg">';
        echo hsc(print_r($msg,true));
        echo '</pre>';
    }
}

/**
 * Print info to a log file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function dbglog($msg,$header=''){
    global $conf;
    // The debug log isn't automatically cleaned thus only write it when
    // debugging has been enabled by the user.
    if($conf['allowdebug'] !== 1) return;
    if(is_object($msg) || is_array($msg)){
        $msg = print_r($msg,true);
    }

    if($header) $msg = "$header\n$msg";

    $file = $conf['cachedir'].'/debug.log';
    $fh = fopen($file,'a');
    if($fh){
        fwrite($fh,date('H:i:s ').$_SERVER['REMOTE_ADDR'].': '.$msg."\n");
        fclose($fh);
    }
}

/**
 * Print a reversed, prettyprinted backtrace
 *
 * @author Gary Owen <gary_owen@bigfoot.com>
 */
function dbg_backtrace(){
    // Get backtrace
    $backtrace = debug_backtrace();

    // Unset call to debug_print_backtrace
    array_shift($backtrace);

    // Iterate backtrace
    $calls = array();
    $depth = count($backtrace) - 1;
    foreach ($backtrace as $i => $call) {
        $location = $call['file'] . ':' . $call['line'];
        $function = (isset($call['class'])) ?
            $call['class'] . $call['type'] . $call['function'] : $call['function'];

        $params = array();
        if (isset($call['args'])){
            foreach($call['args'] as $arg){
                if(is_object($arg)){
                    $params[] = '[Object '.get_class($arg).']';
                }elseif(is_array($arg)){
                    $params[] = '[Array]';
                }elseif(is_null($arg)){
                    $param[] = '[NULL]';
                }else{
                    $params[] = (string) '"'.$arg.'"';
                }
            }
        }
        $params = implode(', ',$params);

        $calls[$depth - $i] = sprintf('%s(%s) called at %s',
                $function,
                str_replace("\n", '\n', $params),
                $location);
    }
    ksort($calls);

    return implode("\n", $calls);
}

/**
 * Remove all data from an array where the key seems to point to sensitive data
 *
 * This is used to remove passwords, mail addresses and similar data from the
 * debug output
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function debug_guard(&$data){
    foreach($data as $key => $value){
        if(preg_match('/(notify|pass|auth|secret|ftp|userinfo|token|buid|mail|proxy)/i',$key)){
            $data[$key] = '***';
            continue;
        }
        if(is_array($value)) debug_guard($data[$key]);
    }
}
