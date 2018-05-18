<?php

namespace dokuwiki;

use Doku_Event;
use Sitemapper;
use Subscription;

/**
 * Class TaskRunner
 *
 * Run an asynchronous task.
 */
class TaskRunner
{
    /**
     * Run the next task
     *
     * @todo refactor to remove dependencies on globals
     * @triggers INDEXER_TASKS_RUN
     */
    public function run()
    {
        global $INPUT, $conf, $ID;

        // keep running after browser closes connection
        @ignore_user_abort(true);

        // check if user abort worked, if yes send output early
        $defer = !@ignore_user_abort() || $conf['broken_iua'];
        $output = $INPUT->has('debug') && $conf['allowdebug'];
        if(!$defer && !$output){
            $this->sendGIF();
        }

        $ID = cleanID($INPUT->str('id'));

        // Catch any possible output (e.g. errors)
        if(!$output) {
            ob_start();
        } else {
            header('Content-Type: text/plain');
        }

        // run one of the jobs
        $tmp = []; // No event data
        $evt = new Doku_Event('INDEXER_TASKS_RUN', $tmp);
        if ($evt->advise_before()) {
            $this->runIndexer() or
            $this->runSitemapper() or
            $this->sendDigest() or
            $this->runTrimRecentChanges() or
            $this->runTrimRecentChanges(true) or
            $evt->advise_after();
        }

        if(!$output) {
            ob_end_clean();
            if($defer) {
                $this->sendGIF();
            }
        }
    }

    /**
     * Just send a 1x1 pixel blank gif to the browser
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Harry Fuecks <fuecks@gmail.com>
     */
    protected function sendGIF()
    {
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
        header('Content-Type: image/gif');
        header('Content-Length: '.strlen($img));
        header('Connection: Close');
        print $img;
        tpl_flush();
        // Browser should drop connection after this
        // Thinks it's got the whole image
    }

    /**
     * Trims the recent changes cache (or imports the old changelog) as needed.
     *
     * @param bool $media_changes   If the media changelog shall be trimmed instead of
     *                              the page changelog
     *
     * @return bool
     * @triggers TASK_RECENTCHANGES_TRIM
     * @author Ben Coburn <btcoburn@silicodon.net>
     */
    protected function runTrimRecentChanges($media_changes = false)
    {
        global $conf;

        echo "runTrimRecentChanges($media_changes): started" . NL;

        $fn = ($media_changes ? $conf['media_changelog'] : $conf['changelog']);

        // Trim the Recent Changes
        // Trims the recent changes cache to the last $conf['changes_days'] recent
        // changes or $conf['recent'] items, which ever is larger.
        // The trimming is only done once a day.
        if (file_exists($fn) &&
            (@filemtime($fn . '.trimmed') + 86400) < time() &&
            !file_exists($fn . '_tmp')) {
            @touch($fn . '.trimmed');
            io_lock($fn);
            $lines = file($fn);
            if (count($lines) <= $conf['recent']) {
                // nothing to trim
                io_unlock($fn);
                echo "runTrimRecentChanges($media_changes): finished" . NL;
                return false;
            }

            io_saveFile($fn . '_tmp', '');          // presave tmp as 2nd lock
            $trim_time = time() - $conf['recent_days'] * 86400;
            $out_lines = [];
            $old_lines = [];
            for ($i = 0; $i < count($lines); $i++) {
                $log = parseChangelogLine($lines[$i]);
                if ($log === false) {
                    continue;
                }                      // discard junk
                if ($log['date'] < $trim_time) {
                    $old_lines[$log['date'] . ".$i"] = $lines[$i];     // keep old lines for now (append .$i to prevent key collisions)
                } else {
                    $out_lines[$log['date'] . ".$i"] = $lines[$i];     // definitely keep these lines
                }
            }

            if (count($lines) == count($out_lines)) {
                // nothing to trim
                @unlink($fn . '_tmp');
                io_unlock($fn);
                echo "runTrimRecentChanges($media_changes): finished" . NL;
                return false;
            }

            // sort the final result, it shouldn't be necessary,
            //   however the extra robustness in making the changelog cache self-correcting is worth it
            ksort($out_lines);
            $extra = $conf['recent'] - count($out_lines);        // do we need extra lines do bring us up to minimum
            if ($extra > 0) {
                ksort($old_lines);
                $out_lines = array_merge(array_slice($old_lines, -$extra), $out_lines);
            }

            $eventData = [
                'isMedia' => $media_changes,
                'trimmedChangelogLines' => $out_lines,
                'removedChangelogLines' => $extra > 0 ? array_slice($old_lines, 0, -$extra) : $old_lines,
            ];
            trigger_event('TASK_RECENTCHANGES_TRIM', $eventData);
            $out_lines = $eventData['trimmedChangelogLines'];

            // save trimmed changelog
            io_saveFile($fn . '_tmp', implode('', $out_lines));
            @unlink($fn);
            if (!rename($fn . '_tmp', $fn)) {
                // rename failed so try another way...
                io_unlock($fn);
                io_saveFile($fn, implode('', $out_lines));
                @unlink($fn . '_tmp');
            } else {
                io_unlock($fn);
            }
            echo "runTrimRecentChanges($media_changes): finished" . NL;
            return true;
        }

        // nothing done
        echo "runTrimRecentChanges($media_changes): finished" . NL;
        return false;
    }


    /**
     * Runs the indexer for the current page
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function runIndexer()
    {
        global $ID;
        global $conf;
        print 'runIndexer(): started' . NL;

        if (!$ID) {
            return false;
        }

        // do the work
        return idx_addPage($ID, true);
    }

    /**
     * Builds a Google Sitemap of all public pages known to the indexer
     *
     * The map is placed in the root directory named sitemap.xml.gz - This
     * file needs to be writable!
     *
     * @author Andreas Gohr
     * @link   https://www.google.com/webmasters/sitemaps/docs/en/about.html
     */
    protected function runSitemapper()
    {
        print 'runSitemapper(): started' . NL;
        $result = Sitemapper::generate() && Sitemapper::pingSearchEngines();
        print 'runSitemapper(): finished' . NL;
        return $result;
    }

    /**
     * Send digest and list mails for all subscriptions which are in effect for the
     * current page
     *
     * @author Adrian Lang <lang@cosmocode.de>
     */
    protected function sendDigest()
    {
        global $conf;
        global $ID;

        echo 'sendDigest(): started' . NL;
        if (!actionOK('subscribe')) {
            echo 'sendDigest(): disabled' . NL;
            return false;
        }
        $sub = new Subscription();
        $sent = $sub->send_bulk($ID);

        echo "sendDigest(): sent $sent mails" . NL;
        echo 'sendDigest(): finished' . NL;
        return (bool)$sent;
    }
}
