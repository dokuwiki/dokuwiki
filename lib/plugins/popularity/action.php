<?php
/**
 * Popularity Feedback Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

class action_plugin_popularity extends DokuWiki_Action_Plugin
{

    /**
     * @var helper_plugin_popularity
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = $this->loadHelper('popularity', false);
    }

    /** @inheritdoc */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('INDEXER_TASKS_RUN', 'AFTER', $this, 'autosubmit', array());
    }

    /**
     * Event handler
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function autosubmit(Doku_Event &$event, $param)
    {
        //Do we have to send the data now
        if (!$this->helper->isAutosubmitEnabled() || $this->isTooEarlyToSubmit()) {
            return;
        }

        //Actually send it
        $status = $this->helper->sendData($this->helper->gatherAsString());

        if ($status !== '') {
            //If an error occured, log it
            io_saveFile($this->helper->autosubmitErrorFile, $status);
        } else {
            //If the data has been sent successfully, previous log of errors are useless
            @unlink($this->helper->autosubmitErrorFile);
            //Update the last time we sent data
            touch($this->helper->autosubmitFile);
        }

        $event->stopPropagation();
        $event->preventDefault();
    }

    /**
     * Check if it's time to send autosubmit data
     * (we should have check if autosubmit is enabled first)
     */
    protected function isTooEarlyToSubmit()
    {
        $lastSubmit = $this->helper->lastSentTime();
        return $lastSubmit + 24*60*60*30 > time();
    }
}
