<?php

/**
 * Extends the mailer class to expose internal variables for testing
 */
class TestMailer extends Mailer {
    public function prop($name){
        return $this->$name;
    }

    public function &propRef($name) {
        return $this->$name;
    }

    public function prepareHeaders() {
        return parent::prepareHeaders();
    }

    public function cleanHeaders() {
        parent::cleanHeaders();
    }

}

class mailer_test extends DokuWikiTest {


    function test_userheader(){
        $mail = new TestMailer();
        $headers = $mail->prop('headers');
        $this->assertArrayNotHasKey('X-Dokuwiki-User',$headers);

        $_SERVER['REMOTE_USER'] = 'andi';
        $mail = new TestMailer();
        $headers = $mail->prop('headers');
        $this->assertArrayHasKey('X-Dokuwiki-User',$headers);
    }

    function test_setHeader(){
        $mail = new TestMailer();

        // check existance of default headers
        $headers = $mail->prop('headers');
        $this->assertArrayHasKey('X-Mailer',$headers);
        $this->assertArrayHasKey('X-Dokuwiki-Title',$headers);
        $this->assertArrayHasKey('X-Dokuwiki-Server',$headers);
        $this->assertArrayHasKey('X-Auto-Response-Suppress',$headers);
        $this->assertArrayHasKey('List-Id',$headers);

        // set a bunch of test headers
        $mail->setHeader('test-header','bla');
        $mail->setHeader('to','A valid ASCII name <test@example.com>');
        $mail->setHeader('from',"Thös ne\needs\x00serious cleaning$§%.");
        $mail->setHeader('bad',"Thös ne\needs\x00serious cleaning$§%.",false);
        $mail->setHeader("weird\n*+\x00foo.-_@bar?",'now clean');

        // are they set?
        $headers = $mail->prop('headers');
        $this->assertArrayHasKey('Test-Header',$headers);
        $this->assertEquals('bla',$headers['Test-Header']);
        $this->assertArrayHasKey('To',$headers);
        $this->assertEquals('A valid ASCII name <test@example.com>',$headers['To']);
        $this->assertArrayHasKey('From',$headers);
        $this->assertEquals('Ths neeedsserious cleaning.',$headers['From']);
        $this->assertArrayHasKey('Bad',$headers);
        $this->assertEquals("Thös ne\needs\x00serious cleaning$§%.",$headers['Bad']);
        $this->assertArrayHasKey('Weird+foo.-_@bar',$headers);

        // unset a header again
        $mail->setHeader('test-header','');
        $headers = $mail->prop('headers');
        $this->assertArrayNotHasKey('Test-Header',$headers);
    }

    function test_addresses(){
        $mail = new TestMailer();

        $mail->to('andi@splitbrain.org');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('andi@splitbrain.org', $headers['To']);

        $mail->to('<andi@splitbrain.org>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('andi@splitbrain.org', $headers['To']);

        $mail->to('Andreas Gohr <andi@splitbrain.org>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('Andreas Gohr <andi@splitbrain.org>', $headers['To']);

        $mail->to('Andreas Gohr <andi@splitbrain.org> , foo <foo@example.com>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('Andreas Gohr <andi@splitbrain.org>, foo <foo@example.com>', $headers['To']);

        $mail->to('Möp <moep@example.com> , foo <foo@example.com>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('=?UTF-8?B?TcO2cA==?= <moep@example.com>, foo <foo@example.com>', $headers['To']);

        $mail->to(array('Möp <moep@example.com> ',' foo <foo@example.com>'));
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('=?UTF-8?B?TcO2cA==?= <moep@example.com>, foo <foo@example.com>', $headers['To']);

        $mail->to(array('Beet, L van <lvb@example.com>',' foo <foo@example.com>'));
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('=?UTF-8?B?QmVldCwgTCB2YW4=?= <lvb@example.com>, foo <foo@example.com>', $headers['To']);


    }

    function test_simplemail(){
        global $conf;
        $conf['htmlmail'] = 0;
        $mail = new TestMailer();
        $mail->to('test@example.com');
        $mail->setBody('A test mail in ASCII');

        $dump = $mail->dump();
        $this->assertNotRegexp('/Content-Type: multipart/',$dump);
        $this->assertRegexp('#Content-Type: text/plain; charset=UTF-8#',$dump);
        $this->assertRegexp('/'.base64_encode('A test mail in ASCII').'/',$dump);

        $conf['htmlmail'] = 1;
    }

    function test_replacements(){
        $mail = new TestMailer();

        $replacements = array( '@DATE@','@BROWSER@','@IPADDRESS@','@HOSTNAME@',
                               '@TITLE@','@DOKUWIKIURL@','@USER@','@NAME@','@MAIL@');
        $mail->setBody('A test mail in with replacements '.join(' ',$replacements));

        $text = $mail->prop('text');
        $html = $mail->prop('html');

        foreach($replacements as $repl){
            $this->assertNotRegexp("/$repl/",$text,"$repl replacement still in text");
            $this->assertNotRegexp("/$repl/",$html,"$repl replacement still in html");
        }
    }

    /**
     * @see https://forum.dokuwiki.org/post/35822
     */
    function test_emptyBCCorCC() {
        $mail = new TestMailer();
        $headers = &$mail->propRef('headers');
        $headers['Bcc'] = '';
        $headers['Cc'] = '';
        $header = $mail->prepareHeaders();
        $this->assertEquals(0, preg_match('/(^|\n)Bcc: (\n|$)/', $header), 'Bcc found in headers.');
        $this->assertEquals(0, preg_match('/(^|\n)Cc: (\n|$)/', $header), 'Bcc found in headers.');
    }
}
//Setup VIM: ex: et ts=4 :
