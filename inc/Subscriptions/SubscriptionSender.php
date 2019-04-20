<?php

namespace dokuwiki\Subscriptions;


use Mailer;

abstract class SubscriptionSender
{
    private $mailer;

    public function __construct(Mailer $mailer = null)
    {
        if ($mailer === null) {
            $mailer = new Mailer();
        }
        $this->mailer = $mailer;
    }


    /**
     * Helper function for sending a mail
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param string $subscriber_mail The target mail address
     * @param string $subject         The lang id of the mail subject (without the
     *                                prefix “mail_”)
     * @param string $context         The context of this mail, eg. page or namespace id
     * @param string $template        The name of the mail template
     * @param array  $trep            Predefined parameters used to parse the
     *                                template (in text format)
     * @param array  $hrep            Predefined parameters used to parse the
     *                                template (in HTML format), null to default to $trep
     * @param array  $headers         Additional mail headers in the form 'name' => 'value'
     * @return bool
     */
    protected function send($subscriber_mail, $subject, $context, $template, $trep, $hrep = null, $headers = array()) {
        global $lang;
        global $conf;

        $text = rawLocale($template);
        $subject = $lang['mail_'.$subject].' '.$context;
        $mail = $this->mailer;
        $mail->bcc($subscriber_mail);
        $mail->subject($subject);
        $mail->setBody($text, $trep, $hrep);
        if(in_array($template, array('subscr_list', 'subscr_digest'))){
            $mail->from($conf['mailfromnobody']);
        }
        if(isset($trep['SUBSCRIBE'])) {
            $mail->setHeader('List-Unsubscribe', '<'.$trep['SUBSCRIBE'].'>', false);
        }

        foreach ($headers as $header => $value) {
            $mail->setHeader($header, $value);
        }

        return $mail->send();
    }

}
