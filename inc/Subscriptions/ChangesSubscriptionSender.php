<?php

namespace dokuwiki\Subscriptions;


use Diff;
use dokuwiki\ChangeLog\PageChangeLog;
use DokuWiki_Auth_Plugin;
use InlineDiffFormatter;
use dokuwiki\Input\Input;
use UnifiedDiffFormatter;

class ChangesSubscriptionSender extends SubscriptionSender
{

    /**
     * Send digest and list subscriptions
     *
     * This sends mails to all subscribers that have a subscription for namespaces above
     * the given page if the needed $conf['subscribe_time'] has passed already.
     *
     * This function is called form lib/exe/indexer.php
     *
     * @param string $page
     * @return int number of sent mails
     */
    public function sendBulk($page) {
        $subscriberManager = new SubscriberManager();
        if(!$subscriberManager->isenabled()) return 0;

        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        global $conf;
        global $USERINFO;
        /** @var Input $INPUT */
        global $INPUT;
        $count = 0;

        $subscriptions = $subscriberManager->subscribers($page, null, array('digest', 'list'));

        // remember current user info
        $olduinfo = $USERINFO;
        $olduser = $INPUT->server->str('REMOTE_USER');

        foreach($subscriptions as $target => $users) {
            if(!$this->lock($target)) continue;

            foreach($users as $user => $info) {
                list($style, $lastupdate) = $info;

                $lastupdate = (int) $lastupdate;
                if($lastupdate + $conf['subscribe_time'] > time()) {
                    // Less than the configured time period passed since last
                    // update.
                    continue;
                }

                // Work as the user to make sure ACLs apply correctly
                $USERINFO = $auth->getUserData($user);
                $INPUT->server->set('REMOTE_USER',$user);
                if($USERINFO === false) continue;
                if(!$USERINFO['mail']) continue;

                if(substr($target, -1, 1) === ':') {
                    // subscription target is a namespace, get all changes within
                    $changes = getRecentsSince($lastupdate, null, getNS($target));
                } else {
                    // single page subscription, check ACL ourselves
                    if(auth_quickaclcheck($target) < AUTH_READ) continue;
                    $meta = p_get_metadata($target);
                    $changes = array($meta['last_change']);
                }

                // Filter out pages only changed in small and own edits
                $change_ids = array();
                foreach($changes as $rev) {
                    $n = 0;
                    while(!is_null($rev) && $rev['date'] >= $lastupdate &&
                        ($INPUT->server->str('REMOTE_USER') === $rev['user'] ||
                            $rev['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT)) {
                        $pagelog = new PageChangeLog($rev['id']);
                        $rev = $pagelog->getRevisions($n++, 1);
                        $rev = (count($rev) > 0) ? $rev[0] : null;
                    }

                    if(!is_null($rev) && $rev['date'] >= $lastupdate) {
                        // Some change was not a minor one and not by myself
                        $change_ids[] = $rev['id'];
                    }
                }

                // send it
                if($style === 'digest') {
                    foreach($change_ids as $change_id) {
                        $this->sendDigest(
                            $USERINFO['mail'], $change_id,
                            $lastupdate
                        );
                        $count++;
                    }
                } elseif($style === 'list') {
                    $this->sendList($USERINFO['mail'], $change_ids, $target);
                    $count++;
                }
                // TODO: Handle duplicate subscriptions.

                // Update notification time.
                $subscriberManager->add($target, $user, $style, time());
            }
            $this->unlock($target);
        }

        // restore current user info
        $USERINFO = $olduinfo;
        $INPUT->server->set('REMOTE_USER',$olduser);
        return $count;
    }

    /**
     * Send the diff for some page change
     *
     * @param string   $subscriber_mail The target mail address
     * @param string   $template        Mail template ('subscr_digest', 'subscr_single', 'mailtext', ...)
     * @param string   $id              Page for which the notification is
     * @param int|null $rev             Old revision if any
     * @param string   $summary         Change summary if any
     * @return bool                     true if successfully sent
     */
    public function sendPageDiff($subscriber_mail, $template, $id, $rev = null, $summary = '') {
        global $DIFF_INLINESTYLES;

        // prepare replacements (keys not set in hrep will be taken from trep)
        $trep = array(
            'PAGE' => $id,
            'NEWPAGE' => wl($id, '', true, '&'),
            'SUMMARY' => $summary,
            'SUBSCRIBE' => wl($id, array('do' => 'subscribe'), true, '&')
        );
        $hrep = array();

        if($rev) {
            $subject = 'changed';
            $trep['OLDPAGE'] = wl($id, "rev=$rev", true, '&');

            $old_content = rawWiki($id, $rev);
            $new_content = rawWiki($id);

            $df = new Diff(explode("\n", $old_content),
                explode("\n", $new_content));
            $dformat = new UnifiedDiffFormatter();
            $tdiff = $dformat->format($df);

            $DIFF_INLINESTYLES = true;
            $df = new Diff(explode("\n", $old_content),
                explode("\n", $new_content));
            $dformat = new InlineDiffFormatter();
            $hdiff = $dformat->format($df);
            $hdiff = '<table>'.$hdiff.'</table>';
            $DIFF_INLINESTYLES = false;
        } else {
            $subject = 'newpage';
            $trep['OLDPAGE'] = '---';
            $tdiff = rawWiki($id);
            $hdiff = nl2br(hsc($tdiff));
        }

        $trep['DIFF'] = $tdiff;
        $hrep['DIFF'] = $hdiff;

        $headers = array('Message-Id' => $this->getMessageID($id));
        if ($rev) {
            $headers['In-Reply-To'] =  $this->getMessageID($id, $rev);
        }

        return $this->send(
            $subscriber_mail, $subject, $id,
            $template, $trep, $hrep, $headers
        );
    }

    /**
     * Send the diff for some media change
     *
     * @fixme this should embed thumbnails of images in HTML version
     *
     * @param string   $subscriber_mail The target mail address
     * @param string   $template        Mail template ('uploadmail', ...)
     * @param string   $id              Media file for which the notification is
     * @param int|bool $rev             Old revision if any
     */
    public function sendMediaDiff($subscriber_mail, $template, $id, $rev = false) {
        global $conf;

        $file = mediaFN($id);
        list($mime, /* $ext */) = mimetype($id);

        $trep = array(
            'MIME'  => $mime,
            'MEDIA' => ml($id,'',true,'&',true),
            'SIZE'  => filesize_h(filesize($file)),
        );

        if ($rev && $conf['mediarevisions']) {
            $trep['OLD'] = ml($id, "rev=$rev", true, '&', true);
        } else {
            $trep['OLD'] = '---';
        }

        $headers = array('Message-Id' => $this->getMessageID($id, @filemtime($file)));
        if ($rev) {
            $headers['In-Reply-To'] =  $this->getMessageID($id, $rev);
        }

        $this->send($subscriber_mail, 'upload', $id, $template, $trep, null, $headers);

    }

    /**
     * Get a valid message id for a certain $id and revision (or the current revision)
     *
     * @param string $id  The id of the page (or media file) the message id should be for
     * @param string $rev The revision of the page, set to the current revision of the page $id if not set
     * @return string
     */
    protected function getMessageID($id, $rev = null) {
        static $listid = null;
        if (is_null($listid)) {
            $server = parse_url(DOKU_URL, PHP_URL_HOST);
            $listid = join('.', array_reverse(explode('/', DOKU_BASE))).$server;
            $listid = urlencode($listid);
            $listid = strtolower(trim($listid, '.'));
        }

        if (is_null($rev)) {
            $rev = @filemtime(wikiFN($id));
        }

        return "<$id?rev=$rev@$listid>";
    }

    /**
     * Lock subscription info
     *
     * We don't use io_lock() her because we do not wait for the lock and use a larger stale time
     *
     * @author Adrian Lang <lang@cosmocode.de>
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     * @return bool true, if you got a succesful lock
     */
    protected function lock($id) {
        global $conf;

        $lock = $conf['lockdir'].'/_subscr_'.md5($id).'.lock';

        if(is_dir($lock) && time() - @filemtime($lock) > 60 * 5) {
            // looks like a stale lock - remove it
            @rmdir($lock);
        }

        // try creating the lock directory
        if(!@mkdir($lock, $conf['dmode'])) {
            return false;
        }

        if(!empty($conf['dperm'])) chmod($lock, $conf['dperm']);
        return true;
    }

    /**
     * Unlock subscription info
     *
     * @author Adrian Lang <lang@cosmocode.de>
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     * @return bool
     */
    protected function unlock($id) {
        global $conf;
        $lock = $conf['lockdir'].'/_subscr_'.md5($id).'.lock';
        return @rmdir($lock);
    }

    /**
     * Send a digest mail
     *
     * Sends a digest mail showing a bunch of changes of a single page. Basically the same as send_diff()
     * but determines the last known revision first
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $subscriber_mail The target mail address
     * @param string $id              The ID
     * @param int    $lastupdate      Time of the last notification
     * @return bool
     */
    protected function sendDigest($subscriber_mail, $id, $lastupdate) {
        $pagelog = new PageChangeLog($id);
        $n = 0;
        do {
            $rev = $pagelog->getRevisions($n++, 1);
            $rev = (count($rev) > 0) ? $rev[0] : null;
        } while(!is_null($rev) && $rev > $lastupdate);

        return $this->sendPageDiff(
            $subscriber_mail,
            'subscr_digest',
            $id, $rev
        );
    }

    /**
     * Send a list mail
     *
     * Sends a list mail showing a list of changed pages.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $subscriber_mail The target mail address
     * @param array  $ids             Array of ids
     * @param string $ns_id           The id of the namespace
     * @return bool true if a mail was sent
     */
    protected function sendList($subscriber_mail, $ids, $ns_id) {
        if(count($ids) === 0) return false;

        $tlist = '';
        $hlist = '<ul>';
        foreach($ids as $id) {
            $link = wl($id, array(), true);
            $tlist .= '* '.$link.NL;
            $hlist .= '<li><a href="'.$link.'">'.hsc($id).'</a></li>'.NL;
        }
        $hlist .= '</ul>';

        $id = prettyprint_id($ns_id);
        $trep = array(
            'DIFF' => rtrim($tlist),
            'PAGE' => $id,
            'SUBSCRIBE' => wl($id, array('do' => 'subscribe'), true, '&')
        );
        $hrep = array(
            'DIFF' => $hlist
        );

        return $this->send(
            $subscriber_mail,
            'subscribe_list',
            $ns_id,
            'subscr_list', $trep, $hrep
        );
    }
}
