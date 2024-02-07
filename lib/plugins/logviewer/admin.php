<?php

use dokuwiki\Extension\AdminPlugin;
use dokuwiki\Form\Form;
use dokuwiki\Logger;

/**
 * DokuWiki Plugin logviewer (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
class admin_plugin_logviewer extends AdminPlugin
{
    protected const MAX_READ_SIZE = 1_048_576; // 1 MB

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

        $form = new Form(['method' => 'GET']);
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
                $link = wl(
                    $ID,
                    ['do' => 'admin', 'page' => 'logviewer', 'date' => $this->date, 'facility' => $facility]
                );
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
        $logfile = Logger::getInstance($this->facility)->getLogfile($this->date);
        if (!file_exists($logfile)) {
            echo $this->locale_xhtml('nolog');
            return;
        }

        try {
            $lines = $this->getLogLines($logfile);
            $this->printLogLines($lines);
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }
    }

    /**
     * Get the available logging facilities
     *
     * @return array
     */
    protected function getFacilities()
    {
        global $conf;

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
     * Read the lines of the logfile and return them as array
     *
     * @param string $logfilePath
     * @return array
     * @throws Exception when reading fails
     */
    protected function getLogLines($logfilePath)
    {
        global $lang;
        $size = filesize($logfilePath);
        $fp = fopen($logfilePath, 'r');

        if (!$fp) throw new Exception($lang['log_file_failed_to_open']);

        try {
            if ($size < self::MAX_READ_SIZE) {
                $toread = $size;
            } else {
                $toread = self::MAX_READ_SIZE;
                fseek($fp, -$toread, SEEK_END);
            }

            $logData = fread($fp, $toread);
            if (!$logData) throw new Exception($lang['log_file_failed_to_read']);

            $lines = explode("\n", $logData);
            unset($logData); // free memory early

            if ($size >= self::MAX_READ_SIZE) {
                array_shift($lines); // Discard the first line
                while ($lines !== [] && str_starts_with($lines[0], '  ')) {
                    array_shift($lines); // Discard indented lines
                }

                // A message to inform users that previous lines are skipped
                array_unshift($lines, "******\t" . "\t" . '[' . $lang['log_file_too_large'] . ']');
            }
        } finally {
            fclose($fp);
        }

        return $lines;
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
            if (str_starts_with($line, '  ')) {
                // lines indented by two spaces are details, aggregate them
                echo '<dd>';
                while (str_starts_with($line, '  ')) {
                    echo hsc(substr($line, 2)) . '<br />';
                    $i++;
                    $line = $lines[$i] ?? '';
                }
                echo '</dd>';
                --$i; // rewind the counter
            } else {
                // other lines are actual log lines in three parts
                [$dt, $file, $msg] = sexplode("\t", $line, 3, '');
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
