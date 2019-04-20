<?php

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\Debug\DebugHelper;
use dokuwiki\Subscriptions\ChangesSubscriptionSender;
use dokuwiki\Subscriptions\SubscriberManager;
use dokuwiki\Subscriptions\RegistrationSubscriptionSender;

/**
 * Class for handling (email) subscriptions
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\SubscriberManager::isenabled
     */
    public function isenabled() {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\SubscriberManager::isenabled');
        $subscriberManager = new SubscriberManager();
        return $subscriberManager->isenabled();
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\SubscriberManager::subscribers
     */
    public function subscribers($page, $user = null, $style = null, $data = null) {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\SubscriberManager::subscribers');
        $manager = new SubscriberManager();
        return $manager->subscribers($page, $user, $style, $data);
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\SubscriberManager::add
     */
    public function add($id, $user, $style, $data = '') {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\SubscriberManager::add');
        $manager = new SubscriberManager();
        return $manager->add($id, $user, $style, $data);
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\SubscriberManager::remove
     */
    public function remove($id, $user = null, $style = null, $data = null) {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\SubscriberManager::remove');
        $manager = new SubscriberManager();
        return $manager->remove($id, $user, $style, $data);
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
     * @return array|false
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\SubscriberManager::userSubscription
     */
    public function user_subscription($id = '', $user = '') {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\SubscriberManager::userSubscription');
        $manager = new SubscriberManager();
        return $manager->userSubscription($id, $user);
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\ChangesSubscriptionSender::sendBulk
     */
    public function send_bulk($page) {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\ChangesSubscriptionSender::sendBulk');
        $subscriptionSender = new ChangesSubscriptionSender();
        return $subscriptionSender->sendBulk($page);
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\ChangesSubscriptionSender::sendPageDiff
     */
    public function send_diff($subscriber_mail, $template, $id, $rev = null, $summary = '') {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\ChangesSubscriptionSender::sendPageDiff');
        $subscriptionSender = new ChangesSubscriptionSender();
        return $subscriptionSender->sendPageDiff($subscriber_mail, $template, $id, $rev, $summary);
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
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\ChangesSubscriptionSender::sendMediaDiff
     */
    public function send_media_diff($subscriber_mail, $template, $id, $rev = false) {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\ChangesSubscriptionSender::sendMediaDiff');
        $subscriptionSender = new ChangesSubscriptionSender();
        return $subscriptionSender->sendMediaDiff($subscriber_mail, $template, $id, $rev);
    }

    /**
     * Send a notify mail on new registration
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $login    login name of the new user
     * @param string $fullname full name of the new user
     * @param string $email    email address of the new user
     * @return bool true if a mail was sent
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\RegistrationSubscriptionSender::sendRegister
     */
    public function send_register($login, $fullname, $email) {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\RegistrationSubscriptionSender::sendRegister');
        $subscriptionSender = new RegistrationSubscriptionSender();
        return $subscriptionSender->sendRegister($login, $fullname, $email);
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
     * @param array &$data Containing the entries:
     *    - $id (the page id),
     *    - $self (whether the author should be notified,
     *    - $addresslist (current email address list)
     *    - $replacements (array of additional string substitutions, @KEY@ to be replaced by value)
     *
     * @deprecated 2019-04-20 \dokuwiki\Subscriptions\SubscriberManager::notifyAddresses
     */
    public function notifyaddresses(&$data) {
        DebugHelper::dbgDeprecatedFunction('\dokuwiki\Subscriptions\SubscriberManager::notifyAddresses');
        $manager = new SubscriberManager();
        $manager->notifyAddresses($data);
    }
}
