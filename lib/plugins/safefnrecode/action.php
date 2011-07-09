<?php
/**
 * DokuWiki Plugin safefnrecode (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN.'action.php';

class action_plugin_safefnrecode extends DokuWiki_Action_Plugin {

    public function register(Doku_Event_Handler &$controller) {

       $controller->register_hook('INDEXER_TASKS_RUN', 'BEFORE', $this, 'handle_indexer_tasks_run');

    }

    public function handle_indexer_tasks_run(Doku_Event &$event, $param) {
        global $conf;
        if($conf['fnencode'] != 'safe') return;

        if(!file_exists($conf['datadir'].'_safefn.recoded')){
            $this->recode($conf['datadir']);
            touch($conf['datadir'].'_safefn.recoded');
        }

        if(!file_exists($conf['olddir'].'_safefn.recoded')){
            $this->recode($conf['olddir']);
            touch($conf['olddir'].'_safefn.recoded');
        }

        if(!file_exists($conf['metadir'].'_safefn.recoded')){
            $this->recode($conf['metadir']);
            touch($conf['metadir'].'_safefn.recoded');
        }

        if(!file_exists($conf['mediadir'].'_safefn.recoded')){
            $this->recode($conf['mediadir']);
            touch($conf['mediadir'].'_safefn.recoded');
        }

    }

    /**
     * Recursive function to rename all safe encoded files to use the new
     * square bracket post indicator
     */
    private function recode($dir){
        $dh = opendir($dir);
        if(!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if($file == '.' || $file == '..') continue;           # cur and upper dir
            if(is_dir("$dir/$file")) $this->recode("$dir/$file"); #recurse
            if(strpos($file,'%') === false) continue;             # no encoding used
            $new = preg_replace('/(%[^\]]*?)\./','\1]',$file);    # new post indicator
            if(preg_match('/%[^\]]+$/',$new)) $new .= ']';        # fix end FS#2122
            rename("$dir/$file","$dir/$new");                     # rename it
        }
        closedir($dh);
    }

}

// vim:ts=4:sw=4:et:
