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
 * @author  Adrian Lang <lang@cosmocode.de>
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

/**
 * Get the name of the metafile tracking subscriptions to target page or
 * namespace
 *
 * @param string $id The target page or namespace, specified by id; Namespaces
 *                   are identified by appending a colon.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_filename($id) {
    $meta_fname = '.mlist';
    if ((substr($id, -1, 1) === ':')) {
        $meta_froot = getNS($id);
        $meta_fname = '/' . $meta_fname;
    } else {
        $meta_froot = $id;
    }
    return metaFN((string) $meta_froot, $meta_fname);
}

/**
 * Lock subscription info for an ID
 *
 * @param string $id The target page or namespace, specified by id; Namespaces
 *                   are identified by appending a colon.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_lock_filename ($id){
    global $conf;
    return $conf['lockdir'].'/_subscr_' . md5($id) . '.lock';
}

function subscription_lock($id) {
    global $conf;
    $lock = subscription_lock_filename($id);

    if (is_dir($lock) && time()-@filemtime($lock) > 60*5) {
        // looks like a stale lock - remove it
        @rmdir($lock);
    }

    // try creating the lock directory
    if (!@mkdir($lock,$conf['dmode'])) {
        return false;
    }

    if($conf['dperm']) chmod($lock, $conf['dperm']);
    return true;
}

/**
 * Unlock subscription info for an ID
 *
 * @param string $id The target page or namespace, specified by id; Namespaces
 *                   are identified by appending a colon.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_unlock($id) {
    $lockf = subscription_lock_filename($id);
    return @rmdir($lockf);
}

/**
 * Set subscription information
 *
 * Allows to set subscription information for permanent storage in meta files.
 * Subscriptions consist of a target object, a subscribing user, a subscribe
 * style and optional data.
 * A subscription may be deleted by specifying an empty subscribe style.
 * Only one subscription per target and user is allowed.
 * The function returns false on error, otherwise true. Note that no error is
 * returned if a subscription should be deleted but the user is not subscribed
 * and the subscription meta file exists.
 *
 * @param string $user      The subscriber or unsubscriber
 * @param string $page      The target object (page or namespace), specified by
 *                          id; Namespaces are identified by a trailing colon.
 * @param string $style     The subscribe style; DokuWiki currently implements
 *                          “every”, “digest”, and “list”.
 * @param string $data      An optional data blob
 * @param bool   $overwrite Whether an existing subscription may be overwritten
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_set($user, $page, $style, $data = null,
                          $overwrite = false) {
    global $lang;
    if (is_null($style)) {
        // Delete subscription.
        $file = subscription_filename($page);
        if (!@file_exists($file)) {
            msg(sprintf($lang['subscr_not_subscribed'], $user,
                        prettyprint_id($page)), -1);
            return false;
        }

        // io_deleteFromFile does not return false if no line matched.
        return io_deleteFromFile($file,
                                 subscription_regex(array('user' => auth_nameencode($user))),
                                 true);
    }

    // Delete subscription if one exists and $overwrite is true. If $overwrite
    // is false, fail.
    $subs = subscription_find($page, array('user' => $user));
    if (count($subs) > 0 && array_pop(array_keys($subs)) === $page) {
        if (!$overwrite) {
            msg(sprintf($lang['subscr_already_subscribed'], $user,
                        prettyprint_id($page)), -1);
            return false;
        }
        // Fail if deletion failed, else continue.
        if (!subscription_set($user, $page, null)) {
            return false;
        }
    }

    $file = subscription_filename($page);
    $content = auth_nameencode($user) . ' ' . $style;
    if (!is_null($data)) {
        $content .= ' ' . $data;
    }
    return io_saveFile($file, $content . "\n", true);
}

/**
 * Recursively search for matching subscriptions
 *
 * This function searches all relevant subscription files for a page or
 * namespace.
 *
 * @param string $page The target object’s (namespace or page) id
 * @param array  $pre  A hash of predefined values
 *
 * @see function subscription_regex for $pre documentation
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_find($page, $pre) {
    // Construct list of files which may contain relevant subscriptions.
    $filenames = array(':' => subscription_filename(':'));
    do {
        $filenames[$page] = subscription_filename($page);
        $page = getNS(rtrim($page, ':')) . ':';
    } while ($page !== ':');

    // Handle files.
    $matches = array();
    foreach ($filenames as $cur_page => $filename) {
        if (!@file_exists($filename)) {
            continue;
        }
        $subscriptions = file($filename);
        foreach ($subscriptions as $subscription) {
            if (strpos($subscription, ' ') === false) {
                // This is an old subscription file.
                $subscription = trim($subscription) . " every\n";
            }

            list($user, $rest) = explode(' ', $subscription, 2);
            $subscription = rawurldecode($user) . " " . $rest;

            if (preg_match(subscription_regex($pre), $subscription,
                           $line_matches) === 0) {
                continue;
            }
            $match = array_slice($line_matches, 1);
            if (!isset($matches[$cur_page])) {
                $matches[$cur_page] = array();
            }
            $matches[$cur_page][] = $match;
        }
    }
    return array_reverse($matches);
}

/**
 * Get data for $INFO['subscribed']
 *
 * $INFO['subscribed'] is either false if no subscription for the current page
 * and user is in effect. Else it contains an array of arrays with the fields
 * “target”, “style”, and optionally “data”.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function get_info_subscribed() {
    global $ID;
    global $conf;
    if (!$conf['subscribers']) {
        return false;
    }

    $subs = subscription_find($ID, array('user' => $_SERVER['REMOTE_USER']));
    if (count($subs) === 0) {
        return false;
    }

    $_ret = array();
    foreach ($subs as $target => $subs_data) {
        $new = array('target' => $target,
                     'style'  => $subs_data[0][0]);
        if (count($subs_data[0]) > 1) {
            $new['data'] = $subs_data[0][1];
        }
        $_ret[] = $new;
    }

    return $_ret;
}

/**
 * Construct a regular expression parsing a subscription definition line
 *
 * @param array $pre A hash of predefined values; “user”, “style”, and
 *                   “data” may be set to limit the results to
 *                   subscriptions matching these parameters. If
 *                   “escaped” is true, these fields are inserted into the
 *                   regular expression without escaping.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_regex($pre = array()) {
    if (!isset($pre['escaped']) || $pre['escaped'] === false) {
        $pre = array_map('preg_quote_cb', $pre);
    }
    foreach (array('user', 'style', 'data') as $key) {
        if (!isset($pre[$key])) {
            $pre[$key] = '(\S+)';
        }
    }
    return '/^' . $pre['user'] . '(?: ' . $pre['style'] .
           '(?: ' . $pre['data'] . ')?)?$/';
}

/**
 * Return a string with the email addresses of all the
 * users subscribed to a page
 *
 * This is the default action for COMMON_NOTIFY_ADDRESSLIST.
 *
 * @param array $data Containing $id (the page id), $self (whether the author
 *                    should be notified, $addresslist (current email address
 *                    list)
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_addresslist(&$data){
    global $conf;
    global $auth;

    $id = $data['id'];
    $self = $data['self'];
    $addresslist = $data['addresslist'];

    if (!$conf['subscribers'] || $auth === null) {
        return '';
    }
    $pres = array('style' => 'every', 'escaped' => true);
    if (!$self && isset($_SERVER['REMOTE_USER'])) {
        $pres['user'] = '((?!' . preg_quote_cb($_SERVER['REMOTE_USER']) .
                        '(?: |$))\S+)';
    }
    $subs = subscription_find($id, $pres);
    $emails = array();
    foreach ($subs as $by_targets) {
        foreach ($by_targets as $sub) {
            $info = $auth->getUserData($sub[0]);
            if ($info === false) continue;
            $level = auth_aclcheck($id, $sub[0], $info['grps']);
            if ($level >= AUTH_READ) {
                if (strcasecmp($info['mail'], $conf['notify']) != 0) {
                    $emails[$sub[0]] =  $info['mail'];
                }
            }
        }
    }
    $data['addresslist'] = trim($addresslist . ',' . implode(',', $emails), ',');
}

/**
 * Send a digest mail
 *
 * Sends a digest mail showing a bunch of changes.
 *
 * @param string $subscriber_mail The target mail address
 * @param array  $id              The ID
 * @param int    $lastupdate      Time of the last notification
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_send_digest($subscriber_mail, $id, $lastupdate) {
    $n = 0;
    do {
        $rev = getRevisions($id, $n++, 1);
        $rev = (count($rev) > 0) ? $rev[0] : null;
    } while (!is_null($rev) && $rev > $lastupdate);

    $replaces = array('NEWPAGE'   => wl($id, '', true, '&'),
                      'SUBSCRIBE' => wl($id, array('do' => 'subscribe'), true, '&'));
    if (!is_null($rev)) {
        $subject = 'changed';
        $replaces['OLDPAGE'] = wl($id, "rev=$rev", true, '&');
        $df = new Diff(explode("\n", rawWiki($id, $rev)),
                        explode("\n", rawWiki($id)));
        $dformat = new UnifiedDiffFormatter();
        $replaces['DIFF'] = $dformat->format($df);
    } else {
        $subject = 'newpage';
        $replaces['OLDPAGE'] = 'none';
        $replaces['DIFF'] = rawWiki($id);
    }
    subscription_send($subscriber_mail, $replaces, $subject, $id,
                      'subscr_digest');
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
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_send_list($subscriber_mail, $ids, $ns_id) {
    if (count($ids) === 0) return;
    global $conf;
    $list = '';
    foreach ($ids as $id) {
        $list .= '* ' . wl($id, array(), true) . NL;
    }
    subscription_send($subscriber_mail,
                      array('DIFF'      => rtrim($list),
                            'SUBSCRIBE' => wl($ns_id . $conf['start'],
                                              array('do' => 'subscribe'),
                                              true, '&')),
                      'subscribe_list',
                      prettyprint_id($ns_id),
                      'subscr_list');
}

/**
 * Helper function for sending a mail
 *
 * @param string $subscriber_mail The target mail address
 * @param array  $replaces        Predefined parameters used to parse the
 *                                template
 * @param string $subject         The lang id of the mail subject (without the
 *                                prefix “mail_”)
 * @param string $id              The page or namespace id
 * @param string $template        The name of the mail template
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_send($subscriber_mail, $replaces, $subject, $id, $template) {
    global $conf;

    $text = rawLocale($template);
    $replaces = array_merge($replaces, array('TITLE'       => $conf['title'],
                                             'DOKUWIKIURL' => DOKU_URL,
                                             'PAGE'        => $id));

    foreach ($replaces as $key => $substitution) {
        $text = str_replace('@'.strtoupper($key).'@', $substitution, $text);
    }

    global $lang;
    $subject = $lang['mail_' . $subject] . ' ' . $id;
    mail_send('', '['.$conf['title'].'] '. $subject, $text,
              $conf['mailfrom'], '', $subscriber_mail);
}
