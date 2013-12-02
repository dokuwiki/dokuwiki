<?php

/**
 * Extends the mailer class to expose internal variables for testing
 */
class TestMailer extends Mailer {
    public $use_mock_mail = true;
    public $msgs = array();

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

    protected function _mail($to, $subject, $body, $headers, $sendparam=null){
        if ($this->use_mock_mail) {
            $this->msgs[] = compact('to','subject','body','headers','sendparam');
            return true;
        } else {
            return parent::_mail($to, $subject, $body, $headers, $sendparam);
        }
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
        $mail->setHeader('from',"Thös ne\needs\x00serious cleaning\$§%.");
        $mail->setHeader('bad',"Thös ne\needs\x00serious cleaning\$§%.",false);
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
        $this->assertEquals("Thös ne\needs\x00serious cleaning\$§%.",$headers['Bad']);
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
        $this->assertEquals(0, preg_match('/(^|\n)Cc: (\n|$)/', $header), 'Cc found in headers.');
    }

    function test_nullTOorCCorBCC() {
        $mail = new TestMailer();
        $headers = &$mail->propRef('headers');
        $headers['Bcc'] = NULL;
        $headers['Cc'] = NULL;
        $headers['To'] = NULL;
        $header = $mail->prepareHeaders();
        $this->assertEquals(0, preg_match('/(^|\n)Bcc: (\n|$)/', $header), 'Bcc found in headers.');
        $this->assertEquals(0, preg_match('/(^|\n)Cc: (\n|$)/', $header), 'Cc found in headers.');
        $this->assertEquals(0, preg_match('/(^|\n)To: (\n|$)/', $header), 'To found in headers.');
    }

    /**
     * @group internet
     */
    function test_lint(){
        // prepare a simple multipart message
        $mail = new TestMailer();
        $mail->to(array('Möp <moep@example.com> ',' foo <foo@example.com>'));
        $mail->from('Me <test@example.com>');
        $mail->subject('This is a töst');
        $mail->setBody('Hello Wörld,

        please don\'t burn, okay?
        ');
        $mail->attachContent('some test data', 'text/plain', 'a text.txt');
        $msg = $mail->dump();
        $msglines = explode("\n", $msg);

        //echo $msg;

        // ask message lint if it is okay
        $html = new HTTPClient();
        $results = $html->post('http://tools.ietf.org/tools/msglint/msglint', array('msg'=>$msg));
        $this->assertTrue($results !== false);

        // parse the result lines
        $lines = explode("\n", $results);
        $rows  = count($lines);
        $i=0;
        while(trim($lines[$i]) != '-----------' && $i<$rows) $i++; //skip preamble
        for($i=$i+1; $i<$rows; $i++){
            $line = trim($lines[$i]);
            if($line == '-----------') break; //skip appendix

            // get possible continuation of the line
            while($lines[$i+1][0] == ' '){
                $line .= ' '.trim($lines[$i+1]);
                $i++;
            }

            // check the line for errors
            if(substr($line,0,5) == 'ERROR' || substr($line,0,7) == 'WARNING'){
                // ignore some errors
                if(strpos($line, "missing mandatory header 'return-path'")) continue; #set by MDA
                if(strpos($line, "bare newline in text body decoded")) continue; #seems to be false positive

                // get the context in which the error occured
                $errorin = '';
                if(preg_match('/line (\d+)$/', $line, $m)){
                    $errorin .= "\n".$msglines[$m[1] - 1];
                }
                if(preg_match('/lines (\d+)-(\d+)$/', $line, $m)){
                    for($x=$m[1]-1; $x<$m[2]; $x++){
                        $errorin .= "\n".$msglines[$x];
                    }
                }

                // raise the error
                throw new Exception($line.$errorin);
            }
        }
    }

    /**
     * check that Mailer is handing off the correct parameters to php's mail()
     */
    function test_send() {
        global $conf, $EVENT_HANDLER;
        $old_conf = $conf;

        $to = 'foo <foo@example.com>';
        $from = 'Me <test@example.com>';
        $subject = 'Subject';
        $body = 'Hello Wörld';
        $conf['title'] = 'MailTest';

        global $EVENT_HANDLER;
        $before = 0;
        $after = 0;

        $EVENT_HANDLER->register_hook('MAIL_MESSAGE_SEND', 'BEFORE', null,
            function() use (&$before) {
                $before++;
            }
        );

        $EVENT_HANDLER->register_hook('MAIL_MESSAGE_SEND', 'AFTER', null,
            function() use (&$after) {
                $after++;
            }
        );

        $mail = new TestMailer();
        $mail->to(array($to));
        $mail->from($from);
        $mail->subject($subject);
        $mail->setHeader('X-Mailer','DokuWiki Mail Tester');
        $mail->setBody($body);
        $mail->attachContent('some test data', 'text/plain', 'test.txt');
        $ok = $mail->send();

        // One and only one call to mail()
        $this->assertTrue($ok, 'send failed');
        $this->assertCount(1, $mail->msgs, 'mail() called '.count($mail->msgs).' times, should be 1');

        // To: handling...
        $this->assertEquals($to, $mail->msgs[0]['to'], '$to param not set correctly');
        $this->assertEquals(0,preg_match('/(^|\n)To: /',$mail->msgs[0]['headers']), 'To: found in headers');

        // Subject handling...
        $this->assertEquals('[MailTest] '.$subject, $mail->msgs[0]['subject'], '$subject param not set correctly');
        $this->assertEquals(0,preg_match('/(^|\n)Subject: /', $mail->msgs[0]['headers']), 'Subject: found in headers');

        // headers present, just check "From: " & our "X-Mailer:"
        $this->assertEquals(1, preg_match_all('/(^|\n)From: '.$from.'(\n|$)/', $mail->msgs[0]['headers']), 'From: missing from headers');
        $this->assertEquals(1, preg_match_all('/(^|\n)X-Mailer: DokuWiki Mail Tester(\n|$)/', $mail->msgs[0]['headers']),'X-Mailer: header incorrect');

        // body is present, contains our string and an attachment
        $this->assertEquals(1, preg_match_all('/'.preg_quote(base64_encode($body),'/').'/', $mail->msgs[0]['body']),'expected content not found in $body param');
        $this->assertEquals(1, preg_match_all('/Content-Disposition: attachment; filename=test.txt/', $mail->msgs[0]['body']),'expected attachment header not found in $body param');

        // MAIL_MESSAGE_SEND triggered correctly
        $this->assertEquals(1, $before, 'MAIL_MESSAGE_SEND.BEFORE event triggered '.$before.' times, should be 1');
        $this->assertEquals(1, $after, 'MAIL_MESSAGE_SEND.AFTER event triggered '.$after.' times, should be 1');

        $conf = $old_conf;
    }

    function test_dontsend_norecipients() {
        $to = 'foo <foo@example.com>';
        $from = 'Me <test@example.com>';
        $subject = 'Subject';
        $body = 'Hello Wörld';

        $mail = new TestMailer();
        $mail->from($from);
        $mail->subject($subject);
        $mail->setBody($body);

        $ok = $mail->send();

        $this->assertFalse($ok, 'Attempted send with no recipients');
    }

    function test_dontsend_nobody() {
        $to = 'foo <foo@example.com>';
        $from = 'Me <test@example.com>';
        $subject = 'Subject';
        $body = 'Hello Wörld';

        $mail = new TestMailer();
        $mail->to($to);
        $mail->from($from);
        $mail->subject($subject);

        $ok = $mail->send();

        $this->assertFalse($ok, 'Attempted send with no body');
    }
}
//Setup VIM: ex: et ts=4 :
