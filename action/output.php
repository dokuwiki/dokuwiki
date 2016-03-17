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
 * Class action_plugin_struct_output
 *
 * This action component handles the automatic output of all schema data that has been assigned
 * to the current page by appending the appropriate instruction to the handler calls.
 *
 * The real output creation is done within the syntax component
 * @see syntax_plugin_struct_output
 */
class action_plugin_struct_output extends DokuWiki_Action_Plugin {

    protected $lastread = '';

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_HANDLER_DONE', 'AFTER', $this, 'handle_output');
        $controller->register_hook('IO_WIKIPAGE_READ', 'AFTER', $this, 'handle_read');
    }

    /**
     * This is kind of a hack. We want to be sure our instruction is only added when the
     * instructions of the main page are created. There is no clear way to figure that out
     * though. Thus we only act on when the appropriate wiki page was read from disk
     * immediately before our call.
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_read(Doku_Event $event, $param) {
        $this->lastread = cleanID($event->data[1] . ':' . $event->data[2]);
    }

    /**
     * Appends the instruction to render our syntax output component to each page
     * after the first found headline or the very begining if no headline was found
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_output(Doku_Event $event, $param) {
        global $ID;
        if($this->lastread != $ID) return; // avoid nested calls
        $this->lastread = '';
        if(!page_exists($ID)) return;

        $ins = -1;
        $pos = 0;
        foreach($event->data->calls as $num => $call) {
            // try to find the first header
            if($call[0] == 'header') {
                $pos = $call[2];
                $ins = $num;
                break;
            }

            // abort when after we looked at the first 150 bytes
            if($call[3] > 150) {
                break;
            }
        }

        // insert our own call after the found position
        array_splice(
            $event->data->calls,
            $ins+1,
            0,
            array(
                array(
                    'plugin',
                    array(
                        'struct_output', array('pos' => $pos), DOKU_LEXER_SPECIAL, ''
                    ),
                    $pos
                )
            )
        );
    }

}

// vim:ts=4:sw=4:et:
