<?php
/**
 * DokuWiki Plugin styler (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_styler extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
       $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action');

    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_action(Doku_Event &$event, $param) {
        $event->data = act_clean($event->data);
        if($event->data === 'styler_plugin_preview') {
            msg('handle') ;
            $event->data = 'show';
            $this->preview();
        } elseif ($event->data === 'styler_plugin_save') {
            $event->data = 'show';
        }
    }

    protected function preview(){
        global $conf;
        $ini     = $conf['cachedir'].'/preview.ini';
        io_saveFile($ini, $this->makeini());
    }

    protected function makeini() {
        global $INPUT;

        $ini = "[replacements]\n";
        foreach($INPUT->arr('tpl') as $key => $val) {
            $ini .= $key .' = "'.addslashes($val).'"'."\n";
        }

        return $ini;
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_ajax(Doku_Event &$event, $param) {
        if($event->data != 'plugin_styler') return;
        $event->preventDefault();
        $event->stopPropagation();

        /** @var admin_plugin_styler $hlp */
        $hlp = plugin_load('admin', 'styler');
        $hlp->html();
    }

}

// vim:ts=4:sw=4:et:
