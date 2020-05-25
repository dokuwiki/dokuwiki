<?php

namespace dokuwiki\Subscriptions;

use dokuwiki\Input\Input;
use DokuWiki_Auth_Plugin;
use Exception;

class SubscriberManager
{

    /**
     * Check if subscription system is enabled
     *
     * @return bool
     */
    public function isenabled()
    {
        return actionOK('subscribe');
    }

    /**
     * Adds a new subscription for the given page or namespace
     *
     * This will automatically overwrite any existent subscription for the given user on this
     * *exact* page or namespace. It will *not* modify any subscription that may exist in higher namespaces.
     *
     * @throws Exception when user or style is empty
     *
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     * @param string $user
     * @param string $style
     * @param string $data
     *
     * @return bool
     */
    public function add($id, $user, $style, $data = '')
    {
        if (!$this->isenabled()) {
            return false;
        }

        // delete any existing subscription
        $this->remove($id, $user);

        $user = auth_nameencode(trim($user));
        $style = trim($style);
        $data = trim($data);

        if (!$user) {
            throw new Exception('no subscription user given');
        }
        if (!$style) {
            throw new Exception('no subscription style given');
        }
        if (!$data) {
            $data = time();
        } //always add current time for new subscriptions

        $line = "$user $style $data\n";
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
     * @param string       $id The target object’s (namespace or page) id
     * @param string|array $user
     * @param string|array $style
     * @param string|array $data
     *
     * @return bool
     */
    public function remove($id, $user = null, $style = null, $data = null)
    {
        if (!$this->isenabled()) {
            return false;
        }

        $file = $this->file($id);
        if (!file_exists($file)) {
            return true;
        }

        $regexBuilder = new SubscriberRegexBuilder();
        $re = $regexBuilder->buildRegex($user, $style, $data);
        return io_deleteFromFile($file, $re, true);
    }

    /**
     * Get data for $INFO['subscribed']
     *
     * $INFO['subscribed'] is either false if no subscription for the current page
     * and user is in effect. Else it contains an array of arrays with the fields
     * “target”, “style”, and optionally “data”.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $id   Page ID, defaults to global $ID
     * @param string $user User, defaults to $_SERVER['REMOTE_USER']
     *
     * @return array|false
     */
    public function userSubscription($id = '', $user = '')
    {
        if (!$this->isenabled()) {
            return false;
        }

        global $ID;
        /** @var Input $INPUT */
        global $INPUT;
        if (!$id) {
            $id = $ID;
        }
        if (!$user) {
            $user = $INPUT->server->str('REMOTE_USER');
        }

        if (empty($user)) {
            // not logged in
            return false;
        }

        $subs = $this->subscribers($id, $user);
        if (!count($subs)) {
            return false;
        }

        $result = [];
        foreach ($subs as $target => $info) {
            $result[] = [
                'target' => $target,
                'style' => $info[$user][0],
                'data' => $info[$user][1],
            ];
        }

        return $result;
    }

    /**
     * Recursively search for matching subscriptions
     *
     * This function searches all relevant subscription files for a page or
     * namespace.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string       $page The target object’s (namespace or page) id
     * @param string|array $user
     * @param string|array $style
     * @param string|array $data
     *
     * @return array
     */
    public function subscribers($page, $user = null, $style = null, $data = null)
    {
        if (!$this->isenabled()) {
            return [];
        }

        // Construct list of files which may contain relevant subscriptions.
        $files = [':' => $this->file(':')];
        do {
            $files[$page] = $this->file($page);
            $page = getNS(rtrim($page, ':')) . ':';
        } while ($page !== ':');

        $regexBuilder = new SubscriberRegexBuilder();
        $re = $regexBuilder->buildRegex($user, $style, $data);

        // Handle files.
        $result = [];
        foreach ($files as $target => $file) {
            if (!file_exists($file)) {
                continue;
            }

            $lines = file($file);
            foreach ($lines as $line) {
                // fix old style subscription files
                if (strpos($line, ' ') === false) {
                    $line = trim($line) . " every\n";
                }

                // check for matching entries
                if (!preg_match($re, $line, $m)) {
                    continue;
                }

                $u = rawurldecode($m[1]); // decode the user name
                if (!isset($result[$target])) {
                    $result[$target] = [];
                }
                $result[$target][$u] = [$m[2], $m[3]]; // add to result
            }
        }
        return array_reverse($result);
    }

    /**
     * Default callback for COMMON_NOTIFY_ADDRESSLIST
     *
     * Aggregates all email addresses of user who have subscribed the given page with 'every' style
     *
     * @author Adrian Lang <lang@cosmocode.de>
     * @author Steven Danz <steven-danz@kc.rr.com>
     *
     * @todo   move the whole functionality into this class, trigger SUBSCRIPTION_NOTIFY_ADDRESSLIST instead,
     *         use an array for the addresses within it
     *
     * @param array &$data Containing the entries:
     *                     - $id (the page id),
     *                     - $self (whether the author should be notified,
     *                     - $addresslist (current email address list)
     *                     - $replacements (array of additional string substitutions, @KEY@ to be replaced by value)
     */
    public function notifyAddresses(&$data)
    {
        if (!$this->isenabled()) {
            return;
        }

        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        global $conf;
        /** @var \Input $INPUT */
        global $INPUT;

        $id = $data['id'];
        $self = $data['self'];
        $addresslist = $data['addresslist'];

        $subscriptions = $this->subscribers($id, null, 'every');

        $result = [];
        foreach ($subscriptions as $target => $users) {
            foreach ($users as $user => $info) {
                $userinfo = $auth->getUserData($user);
                if ($userinfo === false) {
                    continue;
                }
                if (!$userinfo['mail']) {
                    continue;
                }
                if (!$self && $user == $INPUT->server->str('REMOTE_USER')) {
                    continue;
                } //skip our own changes

                $level = auth_aclcheck($id, $user, $userinfo['grps']);
                if ($level >= AUTH_READ) {
                    if (strcasecmp($userinfo['mail'], $conf['notify']) != 0) { //skip user who get notified elsewhere
                        $result[$user] = $userinfo['mail'];
                    }
                }
            }
        }
        $data['addresslist'] = trim($addresslist . ',' . implode(',', $result), ',');
    }

    /**
     * Return the subscription meta file for the given ID
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $id The target page or namespace, specified by id; Namespaces
     *                   are identified by appending a colon.
     *
     * @return string
     */
    protected function file($id)
    {
        $meta_fname = '.mlist';
        if ((substr($id, -1, 1) === ':')) {
            $meta_froot = getNS($id);
            $meta_fname = '/' . $meta_fname;
        } else {
            $meta_froot = $id;
        }
        return metaFN((string)$meta_froot, $meta_fname);
    }
}
