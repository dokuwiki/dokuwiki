<?php

use dokuwiki\Logger;

/**
 * DokuWiki Plugin logviewer (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
class admin_plugin_logviewer extends DokuWiki_Admin_Plugin
{

    protected $facilities;
    protected $facility;
    protected $date;

    /** @inheritDoc */
    public function forAdminOnly()
    {
        return true;
    }

    /** @inheritDoc */
    public function handle()
    {
        global $INPUT;

        $this->facilities = $this->getFacilities();
        $this->facility = $INPUT->str('facility');
        if (!in_array($this->facility, $this->facilities)) {
            $this->facility = $this->facilities[0];
        }

        $this->date = $INPUT->str('date');
        if (!preg_match('/^\d\d\d\d-\d\d-\d\d$/', $this->date)) {
            $this->date = gmdate('Y-m-d');
        }
    }

    /** @inheritDoc */
    public function html()
    {
        echo '<div id="plugin__logviewer">';
        echo $this->locale_xhtml('intro');
        $this->displayTabs();
        $this->displayLog();
        echo '</div>';
    }

    /**
     * Show the navigational tabs and date picker
     */
    protected function displayTabs()
    {
        global $ID;

        $form = new dokuwiki\Form\Form(['method'=>'GET']);
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'logviewer');
        $form->setHiddenField('facility', $this->facility);
        $form->addTextInput('date',$this->getLang('date'))
             ->attr('type','date')->val($this->date)->addClass('quickselect');
        $form->addButton('submit','>')->attr('type','submit');
        echo $form->toHTML();

        echo '<ul class="tabs">';
        foreach ($this->facilities as $facility) {
            echo '<li>';
            if ($facility == $this->facility) {
                echo '<strong>' . hsc($facility) . '</strong>';
            } else {
                $link = wl($ID,
                    ['do' => 'admin', 'page' => 'logviewer', 'date' => $this->date, 'facility' => $facility]);
                echo '<a href="' . $link . '">' . hsc($facility) . '</a>';
            }
            echo '</li>';
        }
        echo '</ul>';

    }

    /**
     * Output the logfile contents
     */
    protected function displayLog()
    {
        $logfile = Logger::getInstance($this->facility)->getLogfile($this->date);
        if (!file_exists($logfile)) {
            echo $this->locale_xhtml('nolog');
            return;
        }

        // loop through the file an print it
        echo '<dl>';
        $lines = file($logfile);
        $cnt = count($lines);
        for ($i = 0; $i < $cnt; $i++) {
            $line = $lines[$i];

            if ($line[0] === ' ' && $line[1] === ' ') {
                // lines indented by two spaces are details, aggregate them
                echo '<dd>';
                while ($line[0] === ' ' && $line[1] === ' ') {
                    echo hsc(substr($line, 2)) . '<br />';
                    $line = $lines[++$i];
                }
                echo '</dd>';
                $i -= 1; // rewind the counter
            } else {
                // other lines are actual log lines in three parts
                list($dt, $file, $msg) = explode("\t", $line, 3);
                echo '<dt>';
                echo '<span class="datetime">' . hsc($dt) . '</span>';
                echo '<span class="log">';
                echo '<span class="msg">' . hsc($msg) . '</span>';
                echo '<span class="file">' . hsc($file) . '</span>';
                echo '</span>';
                echo '</dt>';
            }
        }
        echo '</dl>';
    }

    /**
     * Get the available logging facilities
     *
     * @return array
     */
    protected function getFacilities()
    {
        global $conf;
        $conf['logdir'];

        // default facilities first
        $facilities = [
            Logger::LOG_ERROR,
            Logger::LOG_DEPRECATED,
            Logger::LOG_DEBUG,
        ];

        // add all other dirs
        $dirs = glob($conf['logdir'] . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $facilities[] = basename($dir);
        }
        $facilities = array_unique($facilities);

        return $facilities;
    }

}

