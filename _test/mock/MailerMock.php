<?php

namespace dokuwiki\test\mock;

class MailerMock extends \Mailer
{

    public $mails = [];

    public function send()
    {
        $this->mails[] = $this->headers;
        return true;
    }

}
