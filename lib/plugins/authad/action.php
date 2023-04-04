<?php
/**
 * DokuWiki Plugin addomain (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

/**
 * Class action_plugin_addomain
 */
class action_plugin_authad extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('AUTH_LOGIN_CHECK', 'BEFORE', $this, 'handleAuthLoginCheck');
        $controller->register_hook('FORM_LOGIN_OUTPUT', 'BEFORE', $this, 'handleFormLoginOutput');
    }

    /**
     * Adds the selected domain as user postfix when attempting a login
     *
     * @param Doku_Event $event
     * @param array      $param
     */
    public function handleAuthLoginCheck(Doku_Event $event, $param)
    {
        global $INPUT;

        /** @var auth_plugin_authad $auth */
        global $auth;
        if (!is_a($auth, 'auth_plugin_authad')) return; // AD not even used

        if ($INPUT->str('dom')) {
            $usr = $auth->cleanUser($event->data['user']);
            $dom = $auth->getUserDomain($usr);
            if (!$dom) {
                $usr = "$usr@".$INPUT->str('dom');
            }
            $INPUT->post->set('u', $usr);
            $event->data['user'] = $usr;
        }
    }

    /**
     * Shows a domain selection in the login form when more than one domain is configured
     *
     * @param Doku_Event $event
     * @param array      $param
     */
    public function handleFormLoginOutput(Doku_Event $event, $param)
    {
        global $INPUT;
        /** @var auth_plugin_authad $auth */
        global $auth;
        if (!is_a($auth, 'auth_plugin_authad')) return; // AD not even used
        $domains = $auth->getConfiguredDomains();
        if (count($domains) <= 1) return; // no choice at all

        /** @var dokuwiki\Form\Form $form */
        $form =& $event->data;

        // find the username input box
        $pos = $form->findPositionByAttribute('name', 'u');
        if ($pos === false) return;

        // any default?
        if ($INPUT->has('u')) {
            $usr = $auth->cleanUser($INPUT->str('u'));
            $dom = $auth->getUserDomain($usr);

            // update user field value
            if ($dom) {
                $usr = $auth->getUserName($usr);
                $element = $form->getElementAt($pos);
                $element->val($usr);
            }
        }

        // add locate domain selector just after the username input box
        $element = $form->addDropdown('dom', $domains, $this->getLang('domain'), $pos +1);
        $element->addClass('block');
    }
}

// vim:ts=4:sw=4:et:
