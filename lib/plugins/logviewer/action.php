<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin logviewer (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class action_plugin_logviewer extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('INDEXER_TASKS_RUN', 'AFTER', $this, 'pruneLogs');
    }


    /**
     * Event handler for INDEXER_TASKS_RUN
     *
     * @see https://www.dokuwiki.org/devel:events:INDEXER_TASKS_RUN
     * @param Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function pruneLogs(Event $event, $param)
    {
        global $conf;

        $prune = $conf['logdir'] . '/pruned';
        if (@filemtime($prune) > time() - 24 * 60 * 60) {
            return; // already pruned today
        }

        $logdirs = glob($conf['logdir'] . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
        foreach ($logdirs as $dir) {
            $dates = glob($dir . '/*.log'); // glob returns sorted results
            if (count($dates) > $conf['logretain']) {
                $dates = array_slice($dates, 0, -1 * $conf['logretain']);
                foreach ($dates as $date) {
                    io_rmdir($date, true);
                }
            }
        }

        io_saveFile($prune, '');
    }
}
