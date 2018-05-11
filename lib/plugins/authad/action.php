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
        $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE', $this, 'handleHtmlLoginformOutput');
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
    public function handleHtmlLoginformOutput(Doku_Event $event, $param)
    {
        global $INPUT;
        /** @var auth_plugin_authad $auth */
        global $auth;
        if (!is_a($auth, 'auth_plugin_authad')) return; // AD not even used
        $domains = $auth->getConfiguredDomains();
        if (count($domains) <= 1) return; // no choice at all

        /** @var Doku_Form $form */
        $form =& $event->data;

        // any default?
        $dom = '';
        if ($INPUT->has('u')) {
            $usr = $auth->cleanUser($INPUT->str('u'));
            $dom = $auth->getUserDomain($usr);

            // update user field value
            if ($dom) {
                $usr          = $auth->getUserName($usr);
                $pos          = $form->findElementByAttribute('name', 'u');
                $ele          =& $form->getElementAt($pos);
                $ele['value'] = $usr;
            }
        }

        // add select box
        $element = form_makeListboxField('dom', $domains, $dom, $this->getLang('domain'), '', 'block');
        $pos     = $form->findElementByAttribute('name', 'p');
        $form->insertElement($pos + 1, $element);
    }
}

// vim:ts=4:sw=4:et:
