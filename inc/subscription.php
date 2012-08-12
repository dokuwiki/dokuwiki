<?php
/**
 * Utilities for handling (email) subscriptions
 *
 * The public interface of this file consists of the functions
 * - subscription_find
 * - subscription_send_digest
 * - subscription_send_list
 * - subscription_set
 * - get_info_subscribed
 * - subscription_addresslist
 * - subscription_lock
 * - subscription_unlock
 *
 * @fixme handle $conf['subscribers'] and disable actions and $auth == null
 *
 * @author  Adrian Lang <lang@cosmocode.de>
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

class Subscription {

    /**
     * Check if subscription system is enabled
     *
     * @return bool
     */
    public function isenabled() {
        return actionOK('subscribe');
    }

    /**
     * Return the subscription meta file for the given ID
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     * @return string
     */
    protected function file($id) {
        $meta_fname = '.mlist';
        if((substr($id, -1, 1) === ':')) {
            $meta_froot = getNS($id);
            $meta_fname = '/'.$meta_fname;
        } else {
            $meta_froot = $id;
        }
        return metaFN((string) $meta_froot, $meta_fname);
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

        if($conf['dperm']) chmod($lock, $conf['dperm']);
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
     * Construct a regular expression for parsing a subscription definition line
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string|array $user
     * @param string|array $style
     * @param string|array $data
     * @return string complete regexp including delimiters
     * @throws Exception when no data is passed
     */
    protected function buildregex($user = null, $style = null, $data = null) {
        // always work with arrays
        $user  = (array) $user;
        $style = (array) $style;
        $data  = (array) $data;

        // clean
        $user  = array_filter(array_map('trim', $user));
        $style = array_filter(array_map('trim', $style));
        $data  = array_filter(array_map('trim', $data));

        // user names are encoded
        $user = array_map('auth_nameencode', $user);

        // quote
        $user  = array_map('preg_quote_cb', $user);
        $style = array_map('preg_quote_cb', $style);
        $data  = array_map('preg_quote_cb', $data);

        // join
        $user  = join('|', $user);
        $style = join('|', $style);
        $data  = join('|', $data);

        // any data at all?
        if($user.$style.$data === '') throw new Exception('no data passed');

        // replace empty values, set which ones are optional
        $sopt = '';
        $dopt = '';
        if($user === '') {
            $user = '\S+';
        }
        if($style === '') {
            $style = '\S+';
            $sopt  = '?';
        }
        if($data === '') {
            $data = '\S+';
            $dopt = '?';
        }

        // assemble
        return "/^($user)(?:\\s+($style))$sopt(?:\\s+($data))$dopt$/";
    }

    /**
     * Recursively search for matching subscriptions
     *
     * This function searches all relevant subscription files for a page or
     * namespace.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string         $page The target object’s (namespace or page) id
     * @param string|array   $user
     * @param string|array   $style
     * @param string|array   $data
     * @return array
     */
    public function subscribers($page, $user = null, $style = null, $data = null) {
        if(!$this->isenabled()) return array();

        // Construct list of files which may contain relevant subscriptions.
        $files = array(':' => $this->file(':'));
        do {
            $files[$page] = $this->file($page);
            $page         = getNS(rtrim($page, ':')).':';
        } while($page !== ':');

        $re = $this->buildregex($user, $style, $data);

        // Handle files.
        $result = array();
        foreach($files as $target => $file) {
            if(!@file_exists($file)) continue;

            $lines = file($file);
            foreach($lines as $line) {
                // fix old style subscription files
                if(strpos($line, ' ') === false) $line = trim($line)." every\n";

                // check for matching entries
                if(!preg_match($re, $line, $m)) continue;

                $u = rawurldecode($m[1]); // decode the user name
                if(!isset($result[$target])) $result[$target] = array();
                $result[$target][$u] = array($m[2], $m[3]); // add to result
            }
        }
        return array_reverse($result);
    }

    /**
     * Adds a new subscription for the given page or namespace
     *
     * This will automatically overwrite any existent subscription for the given user on this
     * *exact* page or namespace. It will *not* modify any subscription that may exist in higher namespaces.
     *
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     * @param string $user
     * @param string $style
     * @param string $data
     * @throws Exception when user or style is empty
     * @return bool
     */
    public function add($id, $user, $style, $data = '') {
        if(!$this->isenabled()) return false;

        // delete any existing subscription
        $this->remove($id, $user);

        $user  = auth_nameencode(trim($user));
        $style = trim($style);
        $data  = trim($data);

        if(!$user) throw new Exception('no subscription user given');
        if(!$style) throw new Exception('no subscription style given');

        $line = "$user $style";
        if($data) $line .= " $data";
        $line .= "\n";

        $file = $this->file($id);

        return io_saveFile($file, $line, true);
    }

    /**
     * Removes a subscription for the given page or namespace
     *
     * This removes all subscriptions matching the given criteria on the given page or
     * namespace. It will *not* modify any subscriptions that may exist in higher
     * namespaces.
     *
     * @param string         $id   The target object’s (namespace or page) id
     * @param string|array   $user
     * @param string|array   $style
     * @param string|array   $data
     * @return bool
     */
    public function remove($id, $user = null, $style = null, $data = null) {
        if(!$this->isenabled()) return false;

        $file = $this->file($id);
        if(!file_exists($file)) return true;

        $re = $this->buildregex($user, $style, $data);
        return io_deleteFromFile($file, $re, true);
    }

    /**
     * Get data for $INFO['subscribed']
     *
     * $INFO['subscribed'] is either false if no subscription for the current page
     * and user is in effect. Else it contains an array of arrays with the fields
     * “target”, “style”, and optionally “data”.
     *
     * @param string $id  Page ID, defaults to global $ID
     * @param string $user User, defaults to $_SERVER['REMOTE_USER']
     * @return array
     * @author Adrian Lang <lang@cosmocode.de>
     */
    function user_subscription($id = '', $user = '') {
        if(!$this->isenabled()) return false;

        global $ID;
        if(!$id) $id = $ID;
        if(!$user) $user = $_SERVER['REMOTE_USER'];

        $subs = $this->subscribers($id, $user);
        if(!count($subs)) return false;

        $result = array();
        foreach($subs as $target => $info) {
            $result[] = array(
                'target' => $target,
                'style'  => $info[$user][0],
                'data'   => $info[$user][1]
            );
        }

        return $result;
    }

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
    public function send_bulk($page) {
        if(!$this->isenabled()) return 0;

        /** @var auth_basic $auth */
        global $auth;
        global $conf;
        global $USERINFO;
        $count = 0;

        $subscriptions = $this->subscribers($page, null, array('digest', 'list'));

        // remember current user info
        $olduinfo = $USERINFO;
        $olduser  = $_SERVER['REMOTE_USER'];

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
                $USERINFO               = $auth->getUserData($user);
                $_SERVER['REMOTE_USER'] = $user;
                if($USERINFO === false) continue;
                if(!$USERINFO['mail']) continue;

                if(substr($target, -1, 1) === ':') {
                    // subscription target is a namespace, get all changes within
                    $changes = getRecentsSince($lastupdate, null, getNS($target));
                } else {
                    // single page subscription, check ACL ourselves
                    if(auth_quickaclcheck($target) < AUTH_READ) continue;
                    $meta    = p_get_metadata($target);
                    $changes = array($meta['last_change']);
                }

                // Filter out pages only changed in small and own edits
                $change_ids = array();
                foreach($changes as $rev) {
                    $n = 0;
                    while(!is_null($rev) && $rev['date'] >= $lastupdate &&
                        ($_SERVER['REMOTE_USER'] === $rev['user'] ||
                            $rev['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT)) {
                        $rev = getRevisions($rev['id'], $n++, 1);
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
                        $this->send_digest(
                            $USERINFO['mail'], $change_id,
                            $lastupdate
                        );
                        $count++;
                    }
                } elseif($style === 'list') {
                    $this->send_list($USERINFO['mail'], $change_ids, $target);
                    $count++;
                }
                // TODO: Handle duplicate subscriptions.

                // Update notification time.
                $this->add($target, $user, $style, time());
            }
            $this->unlock($target);
        }

        // restore current user info
        $USERINFO               = $olduinfo;
        $_SERVER['REMOTE_USER'] = $olduser;
        return $count;
    }

    /**
     * Send a digest mail
     *
     * Sends a digest mail showing a bunch of changes.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $subscriber_mail The target mail address
     * @param array  $id              The ID
     * @param int    $lastupdate      Time of the last notification
     */
    protected function send_digest($subscriber_mail, $id, $lastupdate) {
        $n = 0;
        do {
            $rev = getRevisions($id, $n++, 1);
            $rev = (count($rev) > 0) ? $rev[0] : null;
        } while(!is_null($rev) && $rev > $lastupdate);

        $replaces = array(
            'NEWPAGE'   => wl($id, '', true, '&'),
            'SUBSCRIBE' => wl($id, array('do' => 'subscribe'), true, '&')
        );
        if(!is_null($rev)) {
            $subject             = 'changed';
            $replaces['OLDPAGE'] = wl($id, "rev=$rev", true, '&');
            $df                  = new Diff(explode("\n", rawWiki($id, $rev)),
                                            explode("\n", rawWiki($id)));
            $dformat             = new UnifiedDiffFormatter();
            $replaces['DIFF']    = $dformat->format($df);
        } else {
            $subject             = 'newpage';
            $replaces['OLDPAGE'] = 'none';
            $replaces['DIFF']    = rawWiki($id);
        }
        $this->send(
            $subscriber_mail, $replaces, $subject, $id,
            'subscr_digest'
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
     */
    protected function send_list($subscriber_mail, $ids, $ns_id) {
        if(count($ids) === 0) return;
        global $conf;
        $list = '';
        foreach($ids as $id) {
            $list .= '* '.wl($id, array(), true).NL;
        }
        $this->send(
            $subscriber_mail,
            array(
                 'DIFF'      => rtrim($list),
                 'SUBSCRIBE' => wl(
                     $ns_id.$conf['start'],
                     array('do' => 'subscribe'),
                     true, '&'
                 )
            ),
            'subscribe_list',
            prettyprint_id($ns_id),
            'subscr_list'
        );
    }

    /**
     * Helper function for sending a mail
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $subscriber_mail The target mail address
     * @param array  $replaces        Predefined parameters used to parse the
     *                                template
     * @param string $subject         The lang id of the mail subject (without the
     *                                prefix “mail_”)
     * @param string $id              The page or namespace id
     * @param string $template        The name of the mail template
     * @return bool
     */
    protected function send($subscriber_mail, $replaces, $subject, $id, $template) {
        global $lang;

        $text = rawLocale($template);
        $trep = array_merge($replaces, array('PAGE' => $id));

        $subject = $lang['mail_'.$subject].' '.$id;
        $mail    = new Mailer();
        $mail->bcc($subscriber_mail);
        $mail->subject($subject);
        $mail->setBody($text, $trep);
        $mail->setHeader(
            'List-Unsubscribe',
            '<'.wl($id, array('do'=> 'subscribe'), true, '&').'>',
            false
        );
        return $mail->send();
    }

    /**
     * Default callback for COMMON_NOTIFY_ADDRESSLIST
     *
     * Aggregates all email addresses of user who have subscribed the given page with 'every' style
     *
     * @author Steven Danz <steven-danz@kc.rr.com>
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @todo move the whole functionality into this class, trigger SUBSCRIPTION_NOTIFY_ADDRESSLIST instead,
     *       use an array for the addresses within it
     *
     * @param array &$data Containing $id (the page id), $self (whether the author
     *                     should be notified, $addresslist (current email address
     *                     list)
     * @return string
     */
    public function notifyaddresses(&$data) {
        if(!$this->isenabled()) return false;

        /** @var auth_basic $auth */
        global $auth;
        global $conf;

        $id          = $data['id'];
        $self        = $data['self'];
        $addresslist = $data['addresslist'];

        $subscriptions = $this->subscribers($id, null, 'every');

        $result = array();
        foreach($subscriptions as $target => $users) {
            foreach($users as $user => $info) {
                $userinfo = $auth->getUserData($user);
                if($userinfo === false) continue;
                if(!$userinfo['mail']) continue;
                if(!$self && $user == $_SERVER['REMOTE_USER']) continue; //skip our own changes

                $level = auth_aclcheck($id, $user, $userinfo['grps']);
                if($level >= AUTH_READ) {
                    if(strcasecmp($userinfo['mail'], $conf['notify']) != 0) { //skip user who get notified elsewhere
                        $result[$user] = $userinfo['mail'];
                    }
                }
            }
        }
        $data['addresslist'] = trim($addresslist.','.implode(',', $result), ',');
    }
}