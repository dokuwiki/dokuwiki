<?php
require_once DOKU_INC.'inc/mail.php';

class mail_send extends UnitTestCase {

    /**
     * These tests will try to send a bunch of mails to dokuwiki1@spam.la and
     * dokuwiki2@spam.la - check the correctness at http://spam.la
     */
    function test1(){
        $addr = array(
                'dokuwiki1@spam.la',
                'dokuwiki2@spam.la',
                'Test User <dokuwiki1@spam.la>',
                'dokuwiki1@spam.la, dokuwiki2@spam.la',
                'Test User 1 <dokuwiki1@spam.la>, Test User 2 <dokuwiki2@spam.la>'
                );


        $run = 0;
        foreach($addr as $ad){
            $run++;
            $data = array(
                        'to'      => $ad,
                        'subject' => 'mailtest 1-'.$run,
                        'body'    => "Mailtest run 1-$run using to: $ad from:",
                         );
            $this->assertTrue((bool) _mail_send_action($data));

            $data = array(
                        'to'      => $ad,
                        'from'    => 'dokuwiki1@spam.la',
                        'subject' => 'mailtest 2-'.$run,
                        'body'    => "Mailtest run 2-$run using to: $ad from: dokuwiki1@spam.la",
                         );
            $this->assertTrue((bool) _mail_send_action($data));

            $data = array(
                        'to'      => $ad,
                        'from'    => '"Foo Bar" <dokuwiki@spam.la>',
                        'subject' => 'mailtest 3-'.$run,
                        'body'    => "Mailtest run 3-$run using to: $ad from: \"Foo Bar\" <dokuwiki@spam.la>",
                         );
            $this->assertTrue((bool) _mail_send_action($data));
        }
    }

}
//Setup VIM: ex: et ts=4 :
