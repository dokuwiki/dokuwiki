<?php
/**
 * Popularity Feedback Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

class helper_plugin_popularity extends Dokuwiki_Plugin {
    /**
     * The url where the data should be sent
     */
    var $submitUrl = 'http://update.dokuwiki.org/popularity.php';

    /**
     * Name of the file which determine if the the autosubmit is enabled,
     * and when it was submited for the last time
     */
    var $autosubmitFile;

    /**
     * File where the last error which happened when we tried to autosubmit, will be log
     */
    var $autosubmitErrorFile;

    /**
     * Name of the file which determine when the popularity data was manually
     * submitted for the last time
     * (If this file doesn't exist, the data has never been sent)
     */
    var $popularityLastSubmitFile;


    function __construct(){
        global $conf;
        $this->autosubmitFile = $conf['cachedir'].'/autosubmit.txt';
        $this->autosubmitErrorFile = $conf['cachedir'].'/autosubmitError.txt';
        $this->popularityLastSubmitFile = $conf['cachedir'].'/lastSubmitTime.txt';
    }

    /**
     * Return methods of this helper
     *
     * @return array with methods description
     */
    function getMethods(){
        $result = array();
        $result[] = array(
                'name'   => 'isAutoSubmitEnabled',
                'desc'   => 'Check if autosubmit is enabled',
                'params' => array(),
                'return' => array('result' => 'bool')
                );
        $result[] = array(
                'name'   => 'sendData',
                'desc'   => 'Send the popularity data',
                'params' => array('data' => 'string'),
                'return' => array()
                );
        $result[] = array(
                'name' => 'gatherAsString',
                'desc' => 'Gather the popularity data',
                'params' => array(),
                'return' => array('data' => 'string')
                );
        $result[] = array(
                'name'   => 'lastSentTime',
                'desc'   => 'Compute the last time popularity data was sent',
                'params' => array(),
                'return' => array('data' => 'int')
                );
        return $result;

    }

    /**
     * Check if autosubmit is enabled
     *
     * @return boolean TRUE if we should send data once a month, FALSE otherwise
     */
    function isAutoSubmitEnabled(){
        return file_exists($this->autosubmitFile);
    }

    /**
     * Send the data, to the submit url
     *
     * @param string $data The popularity data
     * @return string An empty string if everything worked fine, a string describing the error otherwise
     */
    function sendData($data){
        $error = '';
        $httpClient = new DokuHTTPClient();
        $status = $httpClient->sendRequest($this->submitUrl, array('data' => $data), 'POST');
        if ( ! $status ){
            $error = $httpClient->error;
        }
        return $error;
    }

    /**
     * Compute the last time the data was sent. If it has never been sent, we return 0.
     *
     * @return int
     */
    function lastSentTime(){
        $manualSubmission = @filemtime($this->popularityLastSubmitFile);
        $autoSubmission   = @filemtime($this->autosubmitFile);

        return max((int) $manualSubmission, (int) $autoSubmission);
    }

    /**
     * Gather all information
     *
     * @return string The popularity data as a string
     */
    function gatherAsString(){
        $data = $this->_gather();
        $string = '';
        foreach($data as $key => $val){
            if(is_array($val)) foreach($val as $v){
                $string .=  hsc($key)."\t".hsc($v)."\n";
            }else{
                $string .= hsc($key)."\t".hsc($val)."\n";
            }
        }
        return $string;
    }

    /**
     * Gather all information
     *
     * @return array The popularity data as an array
     */
    function _gather(){
        global $conf;
        /** @var $auth DokuWiki_Auth_Plugin */
        global $auth;
        $data = array();
        $phptime = ini_get('max_execution_time');
        @set_time_limit(0);
        $pluginInfo = $this->getInfo();

        // version
        $data['anon_id'] = md5(auth_cookiesalt());
        $data['version'] = getVersion();
        $data['popversion'] = $pluginInfo['date'];
        $data['language'] = $conf['lang'];
        $data['now']      = time();
        $data['popauto']  = (int) $this->isAutoSubmitEnabled();

        // some config values
        $data['conf_useacl']   = $conf['useacl'];
        $data['conf_authtype'] = $conf['authtype'];
        $data['conf_template'] = $conf['template'];

        // number and size of pages
        $list = array();
        search($list,$conf['datadir'],array($this,'_search_count'),array('all'=>false),'');
        $data['page_count']    = $list['file_count'];
        $data['page_size']     = $list['file_size'];
        $data['page_biggest']  = $list['file_max'];
        $data['page_smallest'] = $list['file_min'];
        $data['page_nscount']  = $list['dir_count'];
        $data['page_nsnest']   = $list['dir_nest'];
        if($list['file_count']) $data['page_avg'] = $list['file_size'] / $list['file_count'];
        $data['page_oldest']   = $list['file_oldest'];
        unset($list);

        // number and size of media
        $list = array();
        search($list,$conf['mediadir'],array($this,'_search_count'),array('all'=>true));
        $data['media_count']    = $list['file_count'];
        $data['media_size']     = $list['file_size'];
        $data['media_biggest']  = $list['file_max'];
        $data['media_smallest'] = $list['file_min'];
        $data['media_nscount']  = $list['dir_count'];
        $data['media_nsnest']   = $list['dir_nest'];
        if($list['file_count']) $data['media_avg'] = $list['file_size'] / $list['file_count'];
        unset($list);

        // number and size of cache
        $list = array();
        search($list,$conf['cachedir'],array($this,'_search_count'),array('all'=>true));
        $data['cache_count']    = $list['file_count'];
        $data['cache_size']     = $list['file_size'];
        $data['cache_biggest']  = $list['file_max'];
        $data['cache_smallest'] = $list['file_min'];
        if($list['file_count']) $data['cache_avg'] = $list['file_size'] / $list['file_count'];
        unset($list);

        // number and size of index
        $list = array();
        search($list,$conf['indexdir'],array($this,'_search_count'),array('all'=>true));
        $data['index_count']    = $list['file_count'];
        $data['index_size']     = $list['file_size'];
        $data['index_biggest']  = $list['file_max'];
        $data['index_smallest'] = $list['file_min'];
        if($list['file_count']) $data['index_avg'] = $list['file_size'] / $list['file_count'];
        unset($list);

        // number and size of meta
        $list = array();
        search($list,$conf['metadir'],array($this,'_search_count'),array('all'=>true));
        $data['meta_count']    = $list['file_count'];
        $data['meta_size']     = $list['file_size'];
        $data['meta_biggest']  = $list['file_max'];
        $data['meta_smallest'] = $list['file_min'];
        if($list['file_count']) $data['meta_avg'] = $list['file_size'] / $list['file_count'];
        unset($list);

        // number and size of attic
        $list = array();
        search($list,$conf['olddir'],array($this,'_search_count'),array('all'=>true));
        $data['attic_count']    = $list['file_count'];
        $data['attic_size']     = $list['file_size'];
        $data['attic_biggest']  = $list['file_max'];
        $data['attic_smallest'] = $list['file_min'];
        if($list['file_count']) $data['attic_avg'] = $list['file_size'] / $list['file_count'];
        $data['attic_oldest']   = $list['file_oldest'];
        unset($list);

        // user count
        if($auth && $auth->canDo('getUserCount')){
            $data['user_count'] = $auth->getUserCount();
        }

        // calculate edits per day
        $list = @file($conf['metadir'].'/_dokuwiki.changes');
        $count = count($list);
        if($count > 2){
            $first = (int) substr(array_shift($list),0,10);
            $last  = (int) substr(array_pop($list),0,10);
            $dur = ($last - $first)/(60*60*24); // number of days in the changelog
            $data['edits_per_day'] = $count/$dur;
        }
        unset($list);

        // plugins
        $data['plugin'] = plugin_list();

        // pcre info
        if(defined('PCRE_VERSION')) $data['pcre_version'] = PCRE_VERSION;
        $data['pcre_backtrack'] = ini_get('pcre.backtrack_limit');
        $data['pcre_recursion'] = ini_get('pcre.recursion_limit');

        // php info
        $data['os'] = PHP_OS;
        $data['webserver'] = $_SERVER['SERVER_SOFTWARE'];
        $data['php_version'] = phpversion();
        $data['php_sapi'] = php_sapi_name();
        $data['php_memory'] = $this->_to_byte(ini_get('memory_limit'));
        $data['php_exectime'] = $phptime;
        $data['php_extension'] = get_loaded_extensions();

        // plugin usage data
        $this->_add_plugin_usage_data($data);

        return $data;
    }

    protected function _add_plugin_usage_data(&$data){
        $pluginsData = array();
        trigger_event('PLUGIN_POPULARITY_DATA_SETUP', $pluginsData);
        foreach($pluginsData as $plugin => $d){
           if ( is_array($d) ) {
               foreach($d as $key => $value){
                   $data['plugin_' . $plugin . '_' . $key] = $value;
               }
           } else {
               $data['plugin_' . $plugin] = $d;
           }
        }
    }

    /**
     * Callback to search and count the content of directories in DokuWiki
     *
     * @param array &$data  Reference to the result data structure
     * @param string $base  Base usually $conf['datadir']
     * @param string $file  current file or directory relative to $base
     * @param string $type  Type either 'd' for directory or 'f' for file
     * @param int    $lvl   Current recursion depht
     * @param array  $opts  option array as given to search()
     * @return bool
     */
    function _search_count(&$data,$base,$file,$type,$lvl,$opts){
        // traverse
        if($type == 'd'){
            if($data['dir_nest'] < $lvl) $data['dir_nest'] = $lvl;
            $data['dir_count']++;
            return true;
        }

        //only search txt files if 'all' option not set
        if($opts['all'] || substr($file,-4) == '.txt'){
            $size = filesize($base.'/'.$file);
            $date = filemtime($base.'/'.$file);
            $data['file_count']++;
            $data['file_size'] += $size;
            if(!isset($data['file_min']) || $data['file_min'] > $size) $data['file_min'] = $size;
            if($data['file_max'] < $size) $data['file_max'] = $size;
            if(!isset($data['file_oldest']) || $data['file_oldest'] > $date) $data['file_oldest'] = $date;
        }

        return false;
    }

    /**
     * Convert php.ini shorthands to byte
     *
     * @author <gilthans dot NO dot SPAM at gmail dot com>
     * @link   http://php.net/manual/en/ini.core.php#79564
     *
     * @param string $v
     * @return int|string
     */
    function _to_byte($v){
        $l = substr($v, -1);
        $ret = substr($v, 0, -1);
        switch(strtoupper($l)){
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'P':
                $ret *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'T':
                $ret *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'G':
                $ret *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
            break;
        }
        return $ret;
    }
}
