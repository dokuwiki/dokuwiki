<?php
/**
 * DokuWiki Plugin safefnrecode (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

class action_plugin_safefnrecode extends DokuWiki_Action_Plugin
{

    /** @inheritdoc */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('INDEXER_TASKS_RUN', 'BEFORE', $this, 'handleIndexerTasksRun');
    }

    /**
     * Handle indexer event
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handleIndexerTasksRun(Doku_Event $event, $param)
    {
        global $conf;
        if ($conf['fnencode'] != 'safe') return;

        if (!file_exists($conf['datadir'].'_safefn.recoded')) {
            $this->recode($conf['datadir']);
            touch($conf['datadir'].'_safefn.recoded');
        }

        if (!file_exists($conf['olddir'].'_safefn.recoded')) {
            $this->recode($conf['olddir']);
            touch($conf['olddir'].'_safefn.recoded');
        }

        if (!file_exists($conf['metadir'].'_safefn.recoded')) {
            $this->recode($conf['metadir']);
            touch($conf['metadir'].'_safefn.recoded');
        }

        if (!file_exists($conf['mediadir'].'_safefn.recoded')) {
            $this->recode($conf['mediadir']);
            touch($conf['mediadir'].'_safefn.recoded');
        }
    }

    /**
     * Recursive function to rename all safe encoded files to use the new
     * square bracket post indicator
     */
    private function recode($dir)
    {
        $dh = opendir($dir);
        if (!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') continue;           # cur and upper dir
            if (is_dir("$dir/$file")) $this->recode("$dir/$file"); #recurse
            if (strpos($file, '%') === false) continue;             # no encoding used
            $new = preg_replace('/(%[^\]]*?)\./', '\1]', $file);    # new post indicator
            if (preg_match('/%[^\]]+$/', $new)) $new .= ']';        # fix end FS#2122
            rename("$dir/$file", "$dir/$new");                     # rename it
        }
        closedir($dh);
    }
}
