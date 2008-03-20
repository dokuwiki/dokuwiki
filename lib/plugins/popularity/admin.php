<?php
/**
 * Popularity Feedback Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
require_once(DOKU_INC.'inc/infoutils.php');
require_once(DOKU_INC.'inc/pluginutils.php');
require_once(DOKU_INC.'inc/search.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_popularity extends DokuWiki_Admin_Plugin {
    var $version = '2008-02-20';


    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => $this->version,
            'name'   => 'Popularity Feedback Plugin',
            'desc'   => 'Send anonymous data about your wiki to the developers.',
            'url'    => 'http://wiki.splitbrain.org/wiki:popularity',
        );
    }

    /**
     * return prompt for admin menu
     */
    function getMenuText($language) {
        return $this->getLang('name');
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
        return 2000;
    }

    /**
     * Accessible for managers
     */
    function forAdminOnly() {
        return false;
    }


    /**
     * handle user request
     */
    function handle() {
    }

    /**
     * Output HTML form
     */
    function html() {
        echo $this->locale_xhtml('intro');

        flush();
        $data = $this->_gather();
        echo '<form method="post" action="http://update.dokuwiki.org/popularity.php" accept-charset="utf-8">';
        echo '<fieldset style="width: 60%;">';
        echo '<textarea class="edit" rows="10" cols="80" readonly="readonly" name="data">';
        foreach($data as $key => $val){
            if(is_array($val)) foreach($val as $v){
                echo hsc($key)."\t".hsc($v)."\n";
            }else{
                echo hsc($key)."\t".hsc($val)."\n";
            }
        }
        echo '</textarea><br />';
        echo '<input type="submit" class="button" value="'.$this->getLang('submit').'"/>';
        echo '</fieldset>';
        echo '</form>';

//        dbg($data);
    }


    /**
     * Gather all information
     */
    function _gather(){
        global $conf;
        global $auth;
        $data = array();
        $phptime = ini_get('max_execution_time');
        @set_time_limit(0);

        // version
        $data['anon_id'] = md5(auth_cookiesalt());
        $data['version'] = getVersion();
        $data['popversion'] = $this->version;
        $data['language'] = $conf['lang'];
        $data['now']      = time();

        // some config values
        $data['conf_useacl']   = $conf['useacl'];
        $data['conf_authtype'] = $conf['authtype'];
        $data['conf_template'] = $conf['template'];

        // number and size of pages
        $list = array();
        search($list,$conf['datadir'],array($this,'_search_count'),'','');
        $data['page_count']    = $list['file_count'];
        $data['page_size']     = $list['file_size'];
        $data['page_biggest']  = $list['file_max'];
        $data['page_smallest'] = $list['file_min'];
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

        return $data;
    }


    function _search_count(&$data,$base,$file,$type,$lvl,$opts){
        // traverse
        if($type == 'd'){
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
     * @link   http://de3.php.net/manual/en/ini.core.php#79564
     */
    function _to_byte($v){
        $l = substr($v, -1);
        $ret = substr($v, 0, -1);
        switch(strtoupper($l)){
            case 'P':
                $ret *= 1024;
            case 'T':
                $ret *= 1024;
            case 'G':
                $ret *= 1024;
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
            break;
        }
        return $ret;
    }
}
