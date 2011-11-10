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
        $http = new DokuHTTPClient();
        $http->timeout = 8;
        $data = $http->get(DOKU_MESSAGEURL.$updateVersion);
        io_saveFile($cf,$data);
    }else{
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
        $version['date'] = 'unknown';
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
 * Run a few sanity checks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function check(){
    global $conf;
    global $INFO;

    if ($INFO['isadmin'] || $INFO['ismanager']){
        msg('DokuWiki version: '.getVersion(),1);
    }

    if(version_compare(phpversion(),'5.1.2','<')){
        msg('Your PHP version is too old ('.phpversion().' vs. 5.1.2+ needed)',-1);
    }else{
        msg('PHP version '.phpversion(),1);
    }

    $mem = (int) php_to_byte(ini_get('memory_limit'));
    if($mem){
        if($mem < 16777216){
            msg('PHP is limited to less than 16MB RAM ('.$mem.' bytes). Increase memory_limit in php.ini',-1);
        }elseif($mem < 20971520){
            msg('PHP is limited to less than 20MB RAM ('.$mem.' bytes), you might encounter problems with bigger pages. Increase memory_limit in php.ini',-1);
        }elseif($mem < 33554432){
            msg('PHP is limited to less than 32MB RAM ('.$mem.' bytes), but that should be enough in most cases. If not, increase memory_limit in php.ini',0);
        }else{
            msg('More than 32MB RAM ('.$mem.' bytes) available.',1);
        }
    }

    if(is_writable($conf['changelog'])){
        msg('Changelog is writable',1);
    }else{
        if (@file_exists($conf['changelog'])) {
            msg('Changelog is not writable',-1);
        }
    }

    if (isset($conf['changelog_old']) && @file_exists($conf['changelog_old'])) {
        msg('Old changelog exists', 0);
    }

    if (@file_exists($conf['changelog'].'_failed')) {
        msg('Importing old changelog failed', -1);
    } else if (@file_exists($conf['changelog'].'_importing')) {
        msg('Importing old changelog now.', 0);
    } else if (@file_exists($conf['changelog'].'_import_ok')) {
        msg('Old changelog imported', 1);
        if (!plugin_isdisabled('importoldchangelog')) {
            msg('Importoldchangelog plugin not disabled after import', -1);
        }
    }

    if(is_writable($conf['datadir'])){
        msg('Datadir is writable',1);
    }else{
        msg('Datadir is not writable',-1);
    }

    if(is_writable($conf['olddir'])){
        msg('Attic is writable',1);
    }else{
        msg('Attic is not writable',-1);
    }

    if(is_writable($conf['mediadir'])){
        msg('Mediadir is writable',1);
    }else{
        msg('Mediadir is not writable',-1);
    }

    if(is_writable($conf['cachedir'])){
        msg('Cachedir is writable',1);
    }else{
        msg('Cachedir is not writable',-1);
    }

    if(is_writable($conf['lockdir'])){
        msg('Lockdir is writable',1);
    }else{
        msg('Lockdir is not writable',-1);
    }

    if(is_writable(DOKU_CONF)){
        msg('conf directory is writable',1);
    }else{
        msg('conf directory is not writable',-1);
    }

    if($conf['authtype'] == 'plain'){
        global $config_cascade;
        if(is_writable($config_cascade['plainauth.users']['default'])){
            msg('conf/users.auth.php is writable',1);
        }else{
            msg('conf/users.auth.php is not writable',0);
        }
    }

    if(function_exists('mb_strpos')){
        if(defined('UTF8_NOMBSTRING')){
            msg('mb_string extension is available but will not be used',0);
        }else{
            msg('mb_string extension is available and will be used',1);
            if(ini_get('mbstring.func_overload') != 0){
                msg('mb_string function overloading is enabled, this will cause problems and should be disabled',-1);
            }
        }
    }else{
        msg('mb_string extension not available - PHP only replacements will be used',0);
    }

    if($conf['allowdebug']){
        msg('Debugging support is enabled. If you don\'t need it you should set $conf[\'allowdebug\'] = 0',-1);
    }else{
        msg('Debugging support is disabled',1);
    }

    if($INFO['userinfo']['name']){
        msg('You are currently logged in as '.$_SERVER['REMOTE_USER'].' ('.$INFO['userinfo']['name'].')',0);
        msg('You are part of the groups '.join($INFO['userinfo']['grps'],', '),0);
    }else{
        msg('You are currently not logged in',0);
    }

    msg('Your current permission for this page is '.$INFO['perm'],0);

    if(is_writable($INFO['filepath'])){
        msg('The current page is writable by the webserver',0);
    }else{
        msg('The current page is not writable by the webserver',0);
    }

    if($INFO['writable']){
        msg('The current page is writable by you',0);
    }else{
        msg('The current page is not writable by you',0);
    }

    $check = wl('','',true).'data/_dummy';
    $http = new DokuHTTPClient();
    $http->timeout = 6;
    $res = $http->get($check);
    if(strpos($res,'data directory') !== false){
        msg('It seems like the data directory is accessible from the web.
                Make sure this directory is properly protected
                (See <a href="http://www.dokuwiki.org/security">security</a>)',-1);
    }elseif($http->status == 404 || $http->status == 403){
        msg('The data directory seems to be properly protected',1);
    }else{
        msg('Failed to check if the data directory is accessible from the web.
                Make sure this directory is properly protected
                (See <a href="http://www.dokuwiki.org/security">security</a>)',-1);
    }

    // Check for corrupted search index
    $lengths = idx_listIndexLengths();
    $index_corrupted = false;
    foreach ($lengths as $length) {
        if (count(idx_getIndex('w', $length)) != count(idx_getIndex('i', $length))) {
            $index_corrupted = true;
            break;
        }
    }

    foreach (idx_getIndex('metadata', '') as $index) {
        if (count(idx_getIndex($index.'_w', '')) != count(idx_getIndex($index.'_i', ''))) {
            $index_corrupted = true;
            break;
        }
    }

    if ($index_corrupted)
        msg('The search index is corrupted. It might produce wrong results and most
                probably needs to be rebuilt. See
                <a href="http://www.dokuwiki.org/faq:searchindex">faq:searchindex</a>
                for ways to rebuild the search index.', -1);
    elseif (!empty($lengths))
        msg('The search index seems to be working', 1);
    else
        msg('The search index is empty. See
                <a href="http://www.dokuwiki.org/faq:searchindex">faq:searchindex</a>
                for help on how to fix the search index. If the default indexer
                isn\'t used or the wiki is actually empty this is normal.');
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
function msg($message,$lvl=0,$line='',$file=''){
    global $MSG, $MSG_shown;
    $errors[-1] = 'error';
    $errors[0]  = 'info';
    $errors[1]  = 'success';
    $errors[2]  = 'notify';

    if($line || $file) $message.=' ['.basename($file).':'.$line.']';

    if(!isset($MSG)) $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
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
