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
    const MAX_READ_SIZE = 1048576; // 1 MB

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
     * Read and output the logfile contents
     */
    protected function displayLog()
    {
        global $lang;

        $logfile = Logger::getInstance($this->facility)->getLogfile($this->date);
        if (!file_exists($logfile)) {
            echo $this->locale_xhtml('nolog');
            return;
        }
        
        $lines = [];
        $size = filesize($logfile);

        if ($size <= self::MAX_READ_SIZE) {
            $lines = file($logfile);
        } else {
            $fp = fopen($logfile, 'r');

            if (is_null($fp)) {
                msg($lang['log_file_failed_to_open'], -1);
                return;
            }

            fseek($fp, -self::MAX_READ_SIZE, SEEK_END);
            $logData = fread($fp, self::MAX_READ_SIZE);

            if (!$logData) {
                msg($lang['log_file_failed_to_read'], -1);
                return;
            }

            $lines = explode("\n", $logData);
            $logData = null;

            array_shift($lines); // Discard the first line

            while (!empty($lines) && (substr($lines[0], 0, 2) === '  '))
                array_shift($lines); // Discard indented lines

            // A message to inform users that previous lines are skipeed
            array_unshift($lines, "******\t" . "\t" . '[' . $lang['log_file_too_large'] . ']');

            fclose($fp);
        }

        $this->printLogLines($lines);
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

    /**
     * Get an array of log lines and print them using appropriate styles 
     *
     * @param array $lines
     */
    protected function printLogLines($lines)
    {
        $numberOfLines = count($lines);

        echo "<dl>";
        for ($i = 0; $i < $numberOfLines; $i++) {
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
        echo "</dl>";
    }
}
