<?php

namespace dokuwiki\Service;

use dokuwiki\Mail\Message;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

class MailManager
{
    private $mailer;
    private $emailValidator;

    public function __construct(\Swift_Mailer $mailer, EmailValidator $emailValidator)
    {
        $this->mailer = $mailer;
        $this->emailValidator = $emailValidator;
        $this->mail_setup();
    }

    public function isValid($email)
    {
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation(),
            new DNSCheckValidation()
        ]);
        return $this->emailValidator ->isValid($email, $multipleValidations);
    }

    /**
     * Prepare mailfrom replacement patterns
     *
     * Also prepares a mailfromnobody config that contains an autoconstructed address
     * if the mailfrom one is userdependent and this might not be wanted (subscriptions)
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function mail_setup(){
        global $conf;
        global $USERINFO;
        /** @var Input $INPUT */
        global $INPUT;

        // auto constructed address
        $host = @parse_url(DOKU_URL,PHP_URL_HOST);
        if(!$host) $host = 'example.com';
        $noreply = 'noreply@'.$host;

        $replace = array();
        if(!empty($USERINFO['mail'])){
            $replace['@MAIL@'] = $USERINFO['mail'];
        }else{
            $replace['@MAIL@'] = $noreply;
        }

        // use 'noreply' if no user
        $replace['@USER@'] = $INPUT->server->str('REMOTE_USER', 'noreply', true);

        if(!empty($USERINFO['name'])){
            $replace['@NAME@'] = $USERINFO['name'];
        }else{
            $replace['@NAME@'] = '';
        }

        // apply replacements
        $from = str_replace(array_keys($replace),
            array_values($replace),
            $conf['mailfrom']);

        // any replacements done? set different mailfromnone
        if($from != $conf['mailfrom']){
            $conf['mailfromnobody'] = $noreply;
        }else{
            $conf['mailfromnobody'] = $from;
        }
        $conf['mailfrom'] = $from;
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