<?php

namespace dokuwiki\Service;

use dokuwiki\Mail\Message;

class MailManager
{

    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setMailer(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param $subject
     * @param $from
     * @param $to
     * @param $body
     * @param array $replacements
     * @param null $bcc
     * @param null $cc
     * @param array $headers
     * @return Message
     */
    public function createMessage($subject, $from, $to, $body, array $replacements = [], $bcc = null, $cc = null, array $headers = [])
    {
        $message = (new Message())
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBcc($bcc)
            ->setCc($cc)
            ->setAllowHtml($GLOBALS['conf']['htmlmail'])
            ->setBodyReplacements($replacements)
            ->setBody($body)
            ->prepareBody();

        foreach ($headers as $header => $value) {
            $message->getHeaders()->addTextHeader($header, $value);
        }

        return $message;
    }

    public function send(\Swift_Message $message)
    {
        global $conf;

        if (empty($message->getFrom())) {
            $message->setFrom($conf['mailfrom']);
        }

        //dbg($message->__toString());

        $result = false;
        // do our thing if BEFORE hook approves
        $evt = new \Doku_Event('MAIL_MESSAGE_SEND', $data);
        if($evt->advise_before(true)) {
            $result = $this->mailer->send($message);
        }

        // any AFTER actions?
        $evt->advise_after();

        return $result;
    }

}