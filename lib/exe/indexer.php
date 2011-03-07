<?php
/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
define('DOKU_DISABLE_GZIP_OUTPUT',1);
require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session
if(!defined('NL')) define('NL',"\n");

// keep running after browser closes connection
@ignore_user_abort(true);

// check if user abort worked, if yes send output early
$defer = !@ignore_user_abort() || $conf['broken_iua'];
if(!$defer){
    sendGIF(); // send gif
}

$ID = cleanID($_REQUEST['id']);

// Catch any possible output (e.g. errors)
$output = isset($_REQUEST['debug']) && $conf['allowdebug'];
if(!$output) ob_start();

// run one of the jobs
$tmp = array(); // No event data
$evt = new Doku_Event('INDEXER_TASKS_RUN', $tmp);
if ($evt->advise_before()) {
  runIndexer() or
  runSitemapper() or
  sendDigest() or
  runTrimRecentChanges() or
  runTrimRecentChanges(true) or
  $evt->advise_after();
}
if($defer) sendGIF();

if(!$output) ob_end_clean();
exit;

// --------------------------------------------------------------------

/**
 * Trims the recent changes cache (or imports the old changelog) as needed.
 *
 * @param media_changes If the media changelog shall be trimmed instead of
 * the page changelog
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function runTrimRecentChanges($media_changes = false) {
    global $conf;

    $fn = ($media_changes ? $conf['media_changelog'] : $conf['changelog']);

    // Trim the Recent Changes
    // Trims the recent changes cache to the last $conf['changes_days'] recent
    // changes or $conf['recent'] items, which ever is larger.
    // The trimming is only done once a day.
    if (@file_exists($fn) &&
        (@filemtime($fn.'.trimmed')+86400)<time() &&
        !@file_exists($fn.'_tmp')) {
            @touch($fn.'.trimmed');
            io_lock($fn);
            $lines = file($fn);
            if (count($lines)<=$conf['recent']) {
                // nothing to trim
                io_unlock($fn);
                return false;
            }

            io_saveFile($fn.'_tmp', '');          // presave tmp as 2nd lock
            $trim_time = time() - $conf['recent_days']*86400;
            $out_lines = array();

            for ($i=0; $i<count($lines); $i++) {
              $log = parseChangelogLine($lines[$i]);
              if ($log === false) continue;                      // discard junk
              if ($log['date'] < $trim_time) {
                $old_lines[$log['date'].".$i"] = $lines[$i];     // keep old lines for now (append .$i to prevent key collisions)
              } else {
                $out_lines[$log['date'].".$i"] = $lines[$i];     // definitely keep these lines
              }
            }

            if (count($lines)==count($out_lines)) {
              // nothing to trim
              @unlink($fn.'_tmp');
              io_unlock($fn);
              return false;
            }

            // sort the final result, it shouldn't be necessary,
            //   however the extra robustness in making the changelog cache self-correcting is worth it
            ksort($out_lines);
            $extra = $conf['recent'] - count($out_lines);        // do we need extra lines do bring us up to minimum
            if ($extra > 0) {
              ksort($old_lines);
              $out_lines = array_merge(array_slice($old_lines,-$extra),$out_lines);
            }

            // save trimmed changelog
            io_saveFile($fn.'_tmp', implode('', $out_lines));
            @unlink($fn);
            if (!rename($fn.'_tmp', $fn)) {
                // rename failed so try another way...
                io_unlock($fn);
                io_saveFile($fn, implode('', $out_lines));
                @unlink($fn.'_tmp');
            } else {
                io_unlock($fn);
            }
            return true;
    }

    // nothing done
    return false;
}

/**
 * Runs the indexer for the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function runIndexer(){
    global $ID;
    global $conf;
    print "runIndexer(): started".NL;

    if(!$ID) return false;

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
function runSitemapper(){
    print "runSitemapper(): started".NL;
    $result = Sitemapper::generate() && Sitemapper::pingSearchEngines();
    print 'runSitemapper(): finished'.NL;
    return $result;
}

/**
 * Send digest and list mails for all subscriptions which are in effect for the
 * current page
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function sendDigest() {
    echo 'sendDigest(): start'.NL;
    global $ID;
    global $conf;
    if (!$conf['subscribers']) {
        return;
    }
    $subscriptions = subscription_find($ID, array('style' => '(digest|list)',
                                                  'escaped' => true));
    global $auth;
    global $lang;
    global $conf;
    global $USERINFO;

    // remember current user info
    $olduinfo = $USERINFO;
    $olduser  = $_SERVER['REMOTE_USER'];

    foreach($subscriptions as $id => $users) {
        if (!subscription_lock($id)) {
            continue;
        }
        foreach($users as $data) {
            list($user, $style, $lastupdate) = $data;
            $lastupdate = (int) $lastupdate;
            if ($lastupdate + $conf['subscribe_time'] > time()) {
                // Less than the configured time period passed since last
                // update.
                continue;
            }

            // Work as the user to make sure ACLs apply correctly
            $USERINFO = $auth->getUserData($user);
            $_SERVER['REMOTE_USER'] = $user;
            if ($USERINFO === false) {
                continue;
            }

            if (substr($id, -1, 1) === ':') {
                // The subscription target is a namespace
                $changes = getRecentsSince($lastupdate, null, getNS($id));
            } else {
                if(auth_quickaclcheck($id) < AUTH_READ) continue;

                $meta = p_get_metadata($id);
                $changes = array($meta['last_change']);
            }

            // Filter out pages only changed in small and own edits
            $change_ids = array();
            foreach($changes as $rev) {
                $n = 0;
                while (!is_null($rev) && $rev['date'] >= $lastupdate &&
                       ($_SERVER['REMOTE_USER'] === $rev['user'] ||
                        $rev['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT)) {
                    $rev = getRevisions($rev['id'], $n++, 1);
                    $rev = (count($rev) > 0) ? $rev[0] : null;
                }

                if (!is_null($rev) && $rev['date'] >= $lastupdate) {
                    // Some change was not a minor one and not by myself
                    $change_ids[] = $rev['id'];
                }
            }

            if ($style === 'digest') {
                foreach($change_ids as $change_id) {
                    subscription_send_digest($USERINFO['mail'], $change_id,
                                             $lastupdate);
                }
            } elseif ($style === 'list') {
                subscription_send_list($USERINFO['mail'], $change_ids, $id);
            }
            // TODO: Handle duplicate subscriptions.

            // Update notification time.
            subscription_set($user, $id, $style, time(), true);
        }
        subscription_unlock($id);
    }

    // restore current user info
    $USERINFO = $olduinfo;
    $_SERVER['REMOTE_USER'] = $olduser;
}

/**
 * Just send a 1x1 pixel blank gif to the browser
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <fuecks@gmail.com>
 */
function sendGIF(){
    if(isset($_REQUEST['debug'])){
        header('Content-Type: text/plain');
        return;
    }
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header('Content-Type: image/gif');
    header('Content-Length: '.strlen($img));
    header('Connection: Close');
    print $img;
    flush();
    // Browser should drop connection after this
    // Thinks it's got the whole image
}

//Setup VIM: ex: et ts=4 :
// No trailing PHP closing tag - no output please!
// See Note at http://www.php.net/manual/en/language.basic-syntax.instruction-separation.php
