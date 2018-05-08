<?php
/**
 * Popularity Feedback Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class admin_plugin_popularity extends DokuWiki_Admin_Plugin
{

    /** @var helper_plugin_popularity */
    protected $helper;
    protected $sentStatus = null;

    /**
     * admin_plugin_popularity constructor.
     */
    public function __construct()
    {
        $this->helper = $this->loadHelper('popularity', false);
    }

    /**
     * return prompt for admin menu
     * @param $language
     * @return string
     */
    public function getMenuText($language)
    {
        return $this->getLang('name');
    }

    /**
     * return sort order for position in admin menu
     */
    public function getMenuSort()
    {
        return 2000;
    }

    /**
     * Accessible for managers
     */
    public function forAdminOnly()
    {
        return false;
    }


    /**
     * handle user request
     */
    public function handle()
    {
        global $INPUT;

        //Send the data
        if ($INPUT->has('data')) {
            $this->sentStatus = $this->helper->sendData($INPUT->str('data'));
            if ($this->sentStatus === '') {
                //Update the last time we sent the data
                touch($this->helper->popularityLastSubmitFile);
            }
            //Deal with the autosubmit option
            $this->enableAutosubmit($INPUT->has('autosubmit'));
        }
    }

    /**
     * Enable or disable autosubmit
     * @param bool $enable If TRUE, it will enable autosubmit. Else, it will disable it.
     */
    protected function enableAutosubmit($enable)
    {
        if ($enable) {
            io_saveFile($this->helper->autosubmitFile, ' ');
        } else {
            @unlink($this->helper->autosubmitFile);
        }
    }

    /**
     * Output HTML form
     */
    public function html()
    {
        global $INPUT;

        if (! $INPUT->has('data')) {
            echo $this->locale_xhtml('intro');

            //If there was an error the last time we tried to autosubmit, warn the user
            if ($this->helper->isAutoSubmitEnabled()) {
                if (file_exists($this->helper->autosubmitErrorFile)) {
                    echo $this->getLang('autosubmitError');
                    echo io_readFile($this->helper->autosubmitErrorFile);
                }
            }

            flush();
            echo $this->buildForm('server');

            //Print the last time the data was sent
            $lastSent = $this->helper->lastSentTime();
            if ($lastSent !== 0) {
                echo $this->getLang('lastSent') . ' ' . datetime_h($lastSent);
            }
        } else {
            //If we just submitted the form
            if ($this->sentStatus === '') {
                //If we successfully sent the data
                echo $this->locale_xhtml('submitted');
            } else {
                //If we failed to submit the data, try directly with the browser
                echo $this->getLang('submissionFailed') . $this->sentStatus . '<br />';
                echo $this->getLang('submitDirectly');
                echo $this->buildForm('browser', $INPUT->str('data'));
            }
        }
    }


    /**
     * Build the form which presents the data to be sent
     * @param string $submissionMode How is the data supposed to be sent? (may be: 'browser' or 'server')
     * @param string $data   The popularity data, if it has already been computed. NULL otherwise.
     * @return string The form, as an html string
     */
    protected function buildForm($submissionMode, $data = null)
    {
        $url = ($submissionMode === 'browser' ? $this->helper->submitUrl : script());
        if (is_null($data)) {
            $data = $this->helper->gatherAsString();
        }

        $form = '<form method="post" action="'. $url  .'" accept-charset="utf-8">'
            .'<fieldset style="width: 60%;">'
            .'<textarea class="edit" rows="10" cols="80" readonly="readonly" name="data">'
            .$data
            .'</textarea><br />';

        //If we submit via the server, we give the opportunity to suscribe to the autosubmission option
        if ($submissionMode !== 'browser') {
            $form .= '<label for="autosubmit">'
                .'<input type="checkbox" name="autosubmit" id="autosubmit" '
                .($this->helper->isAutosubmitEnabled() ? 'checked' : '' )
                .'/> ' . $this->getLang('autosubmit') .'<br />'
                .'</label>'
                .'<input type="hidden" name="do" value="admin" />'
                .'<input type="hidden" name="page" value="popularity" />';
        }
        $form .= '<button type="submit">'.$this->getLang('submit').'</button>'
            .'</fieldset>'
            .'</form>';
        return $form;
    }
}
