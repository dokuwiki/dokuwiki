<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\types\AbstractBaseType;

if(!defined('DOKU_INC')) die();

class action_plugin_struct_config extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
    }

    /**
     * Reconfigure config for a given type
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     */
    public function handle_ajax(Doku_Event $event, $param) {
        if($event->data != 'plugin_struct_config') return;
        $event->preventDefault();
        $event->stopPropagation();
        global $INPUT;

        $conf = json_decode($INPUT->str('conf'), true);
        $typeclasses = Column::allTypes();
        $class = $typeclasses[$INPUT->str('type', 'Text')];
        /** @var AbstractBaseType $type */
        $type = new $class($conf);

        header('Content-Type: text/plain'); // we need the encoded string, not decoded by jQuery
        echo json_encode($type->getConfig());
    }

}
