<?php

namespace dokuwiki\Subscriptions;

class RegistrationSubscriptionSender extends SubscriptionSender
{

    /**
     * Send a notify mail on new registration
     *
     * @param string $login    login name of the new user
     * @param string $fullname full name of the new user
     * @param string $email    email address of the new user
     *
     * @return bool true if a mail was sent
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function sendRegister($login, $fullname, $email)
    {
        global $conf;
        if (empty($conf['registernotify'])) {
            return false;
        }

        $trep = [
            'NEWUSER' => $login,
            'NEWNAME' => $fullname,
            'NEWEMAIL' => $email,
        ];

        return $this->send(
            $conf['registernotify'],
            'new_user',
            $login,
            'registermail',
            $trep
        );
    }
}
