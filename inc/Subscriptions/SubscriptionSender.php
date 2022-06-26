<?php

namespace dokuwiki\Subscriptions;

use Mailer;

abstract class SubscriptionSender
{
    protected $mailer;

    public function __construct(Mailer $mailer = null)
    {
        if ($mailer === null) {
            $mailer = new Mailer();
        }
        $this->mailer = $mailer;
    }

    /**
     * Get a valid message id for a certain $id and revision (or the current revision)
     *
     * @param string $id  The id of the page (or media file) the message id should be for
     * @param string $rev The revision of the page, set to the current revision of the page $id if not set
     *
     * @return string
     */
    protected function getMessageID($id, $rev = null)
    {
        static $listid = null;
        if (is_null($listid)) {
            $server = parse_url(DOKU_URL, PHP_URL_HOST);
            $listid = join('.', array_reverse(explode('/', DOKU_BASE))) . $server;
            $listid = urlencode($listid);
            $listid = strtolower(trim($listid, '.'));
        }

        if (is_null($rev)) {
            $rev = @filemtime(wikiFN($id));
        }

        return "<$id?rev=$rev@$listid>";
    }

    /**
     * Helper function for sending a mail
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
     *
     * @return bool
     * @author Adrian Lang <lang@cosmocode.de>
     *
     */
    protected function send($subscriber_mail, $subject, $context, $template, $trep, $hrep = null, $headers = [])
    {
        global $lang;
        global $conf;

        $text = rawLocale($template);
        $subject = $lang['mail_' . $subject] . ' ' . $context;
        $mail = $this->mailer;
        $mail->bcc($subscriber_mail);
        $mail->subject($subject);
        $mail->setBody($text, $trep, $hrep);
        if (in_array($template, ['subscr_list', 'subscr_digest'])) {
            $mail->from($conf['mailfromnobody']);
        }
        if (isset($trep['SUBSCRIBE'])) {
            $mail->setHeader('List-Unsubscribe', '<' . $trep['SUBSCRIBE'] . '>', false);
        }

        foreach ($headers as $header => $value) {
            $mail->setHeader($header, $value);
        }

        return $mail->send();
    }
}
