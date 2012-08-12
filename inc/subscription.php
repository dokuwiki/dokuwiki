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
 * @fixme handle $conf['subscribers'] and disable actions
 *
 * @author  Adrian Lang <lang@cosmocode.de>
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

class Subscription {

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
    function user_subscription($id='', $user='') {
        global $ID;
        global $conf;
        if(!$conf['subscribers']) return false;

        if(!$id)   $id = $ID;
        if(!$user) $user = $_SERVER['REMOTE_USER'];


        $subs = $this->subscribers($id, $user);
        if(!count($subs)) return false;

        $result = array();
        foreach($subs as $target => $data) {
            $result[] = array(
                'target' => $target,
                'style'  => $data[$user][0],
                'data'   => $data[$user][1]
            );
        }

        return $result;
    }

    /**
     * Return a string with the email addresses of all the
     * users subscribed to a page
     *
     * This is the default action for COMMON_NOTIFY_ADDRESSLIST.
     *
     * @author Steven Danz <steven-danz@kc.rr.com>
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @todo this does NOT return a string but uses a reference to write back, either fix function or docs
     * @param array $data Containing $id (the page id), $self (whether the author
     *                    should be notified, $addresslist (current email address
     *                    list)
     * @return string
     */
    function subscription_addresslist(&$data) {
        global $conf;
        /** @var auth_basic $auth */
        global $auth;

        $id          = $data['id'];
        $self        = $data['self'];
        $addresslist = $data['addresslist'];

        if(!$conf['subscribers'] || $auth === null) {
            return '';
        }
        $pres = array('style' => 'every', 'escaped' => true);
        if(!$self && isset($_SERVER['REMOTE_USER'])) {
            $pres['user'] = '((?!'.preg_quote_cb($_SERVER['REMOTE_USER']).
                '(?: |$))\S+)';
        }
        $subs   = subscription_find($id, $pres);
        $emails = array();
        foreach($subs as $by_targets) {
            foreach($by_targets as $sub) {
                $info = $auth->getUserData($sub[0]);
                if($info === false) continue;
                $level = auth_aclcheck($id, $sub[0], $info['grps']);
                if($level >= AUTH_READ) {
                    if(strcasecmp($info['mail'], $conf['notify']) != 0) {
                        $emails[$sub[0]] = $info['mail'];
                    }
                }
            }
        }
        $data['addresslist'] = trim($addresslist.','.implode(',', $emails), ',');
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
    function subscription_send_digest($subscriber_mail, $id, $lastupdate) {
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
        subscription_send(
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
    function subscription_send_list($subscriber_mail, $ids, $ns_id) {
        if(count($ids) === 0) return;
        global $conf;
        $list = '';
        foreach($ids as $id) {
            $list .= '* '.wl($id, array(), true).NL;
        }
        subscription_send(
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
    function subscription_send($subscriber_mail, $replaces, $subject, $id, $template) {
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

}