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

        $form = new dokuwiki\Form\Form(['method' => 'GET']);
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'logviewer');
        $form->setHiddenField('facility', $this->facility);
        $form->addTextInput('date', $this->getLang('date'))
            ->attr('type', 'date')->val($this->date)->addClass('quickselect');
        $form->addButton('submit', '>')->attr('type', 'submit');
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
        
        $logfileSize = filesize($logfile) / 1_048_576; // in MB
        $lines = [];

        if($logfileSize > 128) { // PHP default memory limit            
            $lines = $this->readEndOfLogFile($logfile, 1500);
            
            // remove incomplete lines from the beginning
            foreach($lines as $line) {
                if (substr($line, 0, 2) === '  ') {
                    array_shift($lines);
                } else {
                    break;
                }
            }

            // create the url to log file
            $pathItems = explode('/', $logfile);
            foreach($pathItems as $item) {
                if($item != 'data') {
                    array_shift($pathItems);
                } else {
                    break;
                }
            }
            $logURL = '/' . implode('/', $pathItems);
            echo "<p><span style='color:red;'>WARNING</span>: the file is too large, <a href='{$logURL}' target='_blank'>click here</a> to see the complete version.</p>";
        } else { // file is small, print it all
            $lines = file($logfile);
        }

        echo '<dl>';
        $this->printLogLines($lines);
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

    private function readEndOfLogFile($logfile, $numberOfLinesLimit)
    {
        $lines = [];
        $fp = fopen($logfile, 'r');
        
        fseek($fp, -1, SEEK_END);
        if(fgetc($fp) == PHP_EOL) { // if the last line is EOL ignore it
            $pos = -2;
        } else { // otherwise start from the last character
            $pos = -1;
        }

        $currentLine = '';

        while (count($lines) < $numberOfLinesLimit && (-1 !== fseek($fp, $pos, SEEK_END))) {
            $char = fgetc($fp);
            if (PHP_EOL == $char) {
                $lines[] = $currentLine;
                $currentLine = '';
            } else {
                $currentLine = $char . $currentLine;
            }
            $pos--;
        }

        fclose($fp);
        return array_reverse($lines);
    }

    private function printLogLines($lines)
    {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (substr($line, 0, 2) === '  ') {
                // lines indented by two spaces are details, aggregate them
                echo '<dd>';
                while (substr($line, 0, 2) === '  ') {
                    echo hsc(substr($line, 2)) . '<br />';
                    $i++;
                    $line = $lines[$i] ?? '';
                }
                echo '</dd>';
                $i -= 1; // rewind the counter
            } else {
                // other lines are actual log lines in three parts
                list($dt, $file, $msg) = sexplode("\t", $line, 3, '');
                echo '<dt>';
                echo '<span class="datetime">' . hsc($dt) . '</span>';
                echo '<span class="log">';
                echo '<span class="msg">' . hsc($msg) . '</span>';
                echo '<span class="file">' . hsc($file) . '</span>';
                echo '</span>';
                echo '</dt>';
            }
        }
    }
}

