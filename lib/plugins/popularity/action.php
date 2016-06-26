<?php
/**
 * Popularity Feedback Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

require_once(DOKU_PLUGIN.'action.php');
require_once(DOKU_PLUGIN.'popularity/admin.php');

class action_plugin_popularity extends Dokuwiki_Action_Plugin {

    /**
     * @var helper_plugin_popularity
     */
    var $helper;

    function __construct(){
        $this->helper = $this->loadHelper('popularity', false);
    }

    /**
     * Register its handlers with the dokuwiki's event controller
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('INDEXER_TASKS_RUN', 'AFTER',  $this, '_autosubmit', array());
    }

    function _autosubmit(Doku_Event &$event, $param){
        //Do we have to send the data now
        if ( !$this->helper->isAutosubmitEnabled() || $this->_isTooEarlyToSubmit() ){
            return;
        }

        //Actually send it
        $status = $this->helper->sendData( $this->helper->gatherAsString() );

        if ( $status !== '' ){
            //If an error occured, log it
            io_saveFile( $this->helper->autosubmitErrorFile, $status );
        } else {
            //If the data has been sent successfully, previous log of errors are useless
            @unlink($this->helper->autosubmitErrorFile);
            //Update the last time we sent data
            touch ( $this->helper->autosubmitFile );
        }

        $event->stopPropagation();
        $event->preventDefault();
    }

    /**
     * Check if it's time to send autosubmit data
     * (we should have check if autosubmit is enabled first)
     */
    function _isTooEarlyToSubmit(){
        $lastSubmit = $this->helper->lastSentTime();
        return $lastSubmit + 24*60*60*30 > time();
    }
}
