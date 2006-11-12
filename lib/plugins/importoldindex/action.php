<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_importoldindex extends DokuWiki_Action_Plugin {

    function getInfo(){
        return array(
            'author' => 'Tom N Harris',
            'email'  => 'tnharris@whoopdedo.org',
            'date'   => '2006-11-09',
            'name'   => 'Import Old Index',
            'desc'   => 'Moves old index files to a new location, sorted by string length.',
            'url'    => 'http://whoopdedo.org/doku/wiki'
            );
    }

    function register(&$controller) {
        $controller->register_hook('TEMPORARY_INDEX_UPGRADE_EVENT', 'BEFORE', $this, 'run_import');
    }

    function run_import(&$event, $args) {
        global $conf;

        touch($conf['indexdir'].'/index_importing'); // changelog importing lock
        // load old index
        $word_idx = file($conf['cachedir'].'/word.idx');
        $idx = file($conf['cachedir'].'/index.idx');
        $words = array();
        for ($lno=0;$lno<count($word_idx);$lno++){
            $wlen = strlen($word_idx[$lno])-1;
            //if($wlen<3) continue;
            if(!isset($words[$wlen])) $words[$wlen] = array();
            $words[$wlen][] = $lno;
        }

        foreach (array_keys($words) as $wlen) {
            $new_words = array();
            $new_idx = array();
            foreach ($words[$wlen] as $lno) {
                $new_words[] = $word_idx[$lno];
                $new_idx[] = $idx[$lno];
            }
            io_saveFile($conf['indexdir']."/w$wlen.idx", implode('', $new_words));
            io_saveFile($conf['indexdir']."/i$wlen.idx", implode('', $new_idx));
        }

        @copy($conf['cachedir'].'/page.idx', $conf['indexdir'].'/page.idx');
        if($conf['fperm']) chmod($conf['indexdir'].'/page.idx', $conf['fperm']);
        unlink($conf['indexdir'].'/index_importing'); // changelog importing unlock
        plugin_disable('importoldindex'); // only needs to run once
    }

}

