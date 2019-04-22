<?php


namespace dokuwiki\Subscriptions;


use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\Input\Input;
use DokuWiki_Auth_Plugin;

class BulkSubscriptionSender extends SubscriptionSender
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
     *
     * @return int number of sent mails
     */
    public function sendBulk($page)
    {
        $subscriberManager = new SubscriberManager();
        if (!$subscriberManager->isenabled()) {
            return 0;
        }

        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        global $conf;
        global $USERINFO;
        /** @var Input $INPUT */
        global $INPUT;
        $count = 0;

        $subscriptions = $subscriberManager->subscribers($page, null, ['digest', 'list']);

        // remember current user info
        $olduinfo = $USERINFO;
        $olduser = $INPUT->server->str('REMOTE_USER');

        foreach ($subscriptions as $target => $users) {
            if (!$this->lock($target)) {
                continue;
            }

            foreach ($users as $user => $info) {
                list($style, $lastupdate) = $info;

                $lastupdate = (int)$lastupdate;
                if ($lastupdate + $conf['subscribe_time'] > time()) {
                    // Less than the configured time period passed since last
                    // update.
                    continue;
                }

                // Work as the user to make sure ACLs apply correctly
                $USERINFO = $auth->getUserData($user);
                $INPUT->server->set('REMOTE_USER', $user);
                if ($USERINFO === false) {
                    continue;
                }
                if (!$USERINFO['mail']) {
                    continue;
                }

                if (substr($target, -1, 1) === ':') {
                    // subscription target is a namespace, get all changes within
                    $changes = getRecentsSince($lastupdate, null, getNS($target));
                } else {
                    // single page subscription, check ACL ourselves
                    if (auth_quickaclcheck($target) < AUTH_READ) {
                        continue;
                    }
                    $meta = p_get_metadata($target);
                    $changes = [$meta['last_change']];
                }

                // Filter out pages only changed in small and own edits
                $change_ids = [];
                foreach ($changes as $rev) {
                    $n = 0;
                    while (!is_null($rev) && $rev['date'] >= $lastupdate &&
                        ($INPUT->server->str('REMOTE_USER') === $rev['user'] ||
                            $rev['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT)) {
                        $pagelog = new PageChangeLog($rev['id']);
                        $rev = $pagelog->getRevisions($n++, 1);
                        $rev = (count($rev) > 0) ? $rev[0] : null;
                    }

                    if (!is_null($rev) && $rev['date'] >= $lastupdate) {
                        // Some change was not a minor one and not by myself
                        $change_ids[] = $rev['id'];
                    }
                }

                // send it
                if ($style === 'digest') {
                    foreach ($change_ids as $change_id) {
                        $this->sendDigest(
                            $USERINFO['mail'],
                            $change_id,
                            $lastupdate
                        );
                        $count++;
                    }
                } else {
                    if ($style === 'list') {
                        $this->sendList($USERINFO['mail'], $change_ids, $target);
                        $count++;
                    }
                }
                // TODO: Handle duplicate subscriptions.

                // Update notification time.
                $subscriberManager->add($target, $user, $style, time());
            }
            $this->unlock($target);
        }

        // restore current user info
        $USERINFO = $olduinfo;
        $INPUT->server->set('REMOTE_USER', $olduser);
        return $count;
    }

    /**
     * Lock subscription info
     *
     * We don't use io_lock() her because we do not wait for the lock and use a larger stale time
     *
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     *
     * @return bool true, if you got a succesful lock
     * @author Adrian Lang <lang@cosmocode.de>
     */
    protected function lock($id)
    {
        global $conf;

        $lock = $conf['lockdir'] . '/_subscr_' . md5($id) . '.lock';

        if (is_dir($lock) && time() - @filemtime($lock) > 60 * 5) {
            // looks like a stale lock - remove it
            @rmdir($lock);
        }

        // try creating the lock directory
        if (!@mkdir($lock, $conf['dmode'])) {
            return false;
        }

        if (!empty($conf['dperm'])) {
            chmod($lock, $conf['dperm']);
        }
        return true;
    }

    /**
     * Unlock subscription info
     *
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     *
     * @return bool
     * @author Adrian Lang <lang@cosmocode.de>
     */
    protected function unlock($id)
    {
        global $conf;
        $lock = $conf['lockdir'] . '/_subscr_' . md5($id) . '.lock';
        return @rmdir($lock);
    }

    /**
     * Send a digest mail
     *
     * Sends a digest mail showing a bunch of changes of a single page. Basically the same as sendPageDiff()
     * but determines the last known revision first
     *
     * @param string $subscriber_mail The target mail address
     * @param string $id              The ID
     * @param int    $lastupdate      Time of the last notification
     *
     * @return bool
     * @author Adrian Lang <lang@cosmocode.de>
     *
     */
    protected function sendDigest($subscriber_mail, $id, $lastupdate)
    {
        $pagelog = new PageChangeLog($id);
        $n = 0;
        do {
            $rev = $pagelog->getRevisions($n++, 1);
            $rev = (count($rev) > 0) ? $rev[0] : null;
        } while (!is_null($rev) && $rev > $lastupdate);

        // TODO I'm not happy with the following line and passing $this->mailer around. Not sure how to solve it better
        $pageSubSender = new PageSubscriptionSender($this->mailer);
        return $pageSubSender->sendPageDiff(
            $subscriber_mail,
            'subscr_digest',
            $id,
            $rev
        );
    }

    /**
     * Send a list mail
     *
     * Sends a list mail showing a list of changed pages.
     *
     * @param string $subscriber_mail The target mail address
     * @param array  $ids             Array of ids
     * @param string $ns_id           The id of the namespace
     *
     * @return bool true if a mail was sent
     * @author Adrian Lang <lang@cosmocode.de>
     *
     */
    protected function sendList($subscriber_mail, $ids, $ns_id)
    {
        if (count($ids) === 0) {
            return false;
        }

        $tlist = '';
        $hlist = '<ul>';
        foreach ($ids as $id) {
            $link = wl($id, [], true);
            $tlist .= '* ' . $link . NL;
            $hlist .= '<li><a href="' . $link . '">' . hsc($id) . '</a></li>' . NL;
        }
        $hlist .= '</ul>';

        $id = prettyprint_id($ns_id);
        $trep = [
            'DIFF' => rtrim($tlist),
            'PAGE' => $id,
            'SUBSCRIBE' => wl($id, ['do' => 'subscribe'], true, '&'),
        ];
        $hrep = [
            'DIFF' => $hlist,
        ];

        return $this->send(
            $subscriber_mail,
            'subscribe_list',
            $ns_id,
            'subscr_list',
            $trep,
            $hrep
        );
    }
}
