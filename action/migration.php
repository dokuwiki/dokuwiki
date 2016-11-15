<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki

if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_struct_migration
 *
 * Handle migrations that need more than just SQL
 */
class action_plugin_struct_migration extends DokuWiki_Action_Plugin {
    /**
     * @inheritDoc
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PLUGIN_SQLITE_DATABASE_UPGRADE', 'BEFORE', $this, 'handle_migrations');
    }

    /**
     * Call our custom migrations when defined
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_migrations(Doku_Event $event, $param) {
        $to = $event->data['to'];

        if(is_callable(array($this, "migration$to"))) {
            $event->preventDefault();
            $event->result = call_user_func(array($this, "migration$to"), $event->data['sqlite']);
        }
    }

    /**
     * Executes Migration 12
     *
     * Add a latest column to all existing multi tables
     *
     * @param helper_plugin_sqlite $sqlite
     * @return bool
     */
    protected function migration12(helper_plugin_sqlite $sqlite) {
        /** @noinspection SqlResolve */
        $sql = "SELECT name FROM sqlite_master WHERE type = 'table' AND name LIKE 'multi_%'";
        $res = $sqlite->query($sql);
        $tables = $sqlite->res2arr($res);
        $sqlite->res_close($res);

        foreach($tables as $row) {
            $sql = 'ALTER TABLE ? ADD COLUMN latest INT DEFAULT 1';
            $sqlite->query($sql, $row['name']);
        }

        return true;
    }

}
