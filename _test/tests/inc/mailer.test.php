<?php

use dokuwiki\HTTP\HTTPClient;

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

/**
 * @group mailer_class
 */
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
        if (isWindows()) {
            $this->markTestSkipped();
        }

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

        $mail->to('"Andreas Gohr" <andi@splitbrain.org>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('"Andreas Gohr" <andi@splitbrain.org>', $headers['To']);

        $mail->to('Andreas Gohr <andi@splitbrain.org> , foo <foo@example.com>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('Andreas Gohr <andi@splitbrain.org>, foo <foo@example.com>', $headers['To']);

        $mail->to('"Foo, Dr." <foo@example.com> , foo <foo@example.com>');
        $mail->cleanHeaders();
        $headers = $mail->prop('headers');
        $this->assertEquals('=?UTF-8?B?IkZvbywgRHIuIg==?= <foo@example.com>, foo <foo@example.com>', $headers['To']);

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

        $mailbody = 'A test mail in ASCII';
        $mail = new TestMailer();
        $mail->to('test@example.com');
        $mail->setBody($mailbody);

        $dump = $mail->dump();

        // construct the expected mail body text - include the expected dokuwiki signature
        $replacements = $mail->prop('replacements');
        $expected_mail_body = chunk_split(base64_encode($mailbody.$replacements['text']['EMAILSIGNATURE']),72,MAILHEADER_EOL);

        $this->assertNotRegexp('/Content-Type: multipart/',$dump);
        $this->assertRegexp('#Content-Type: text/plain; charset=UTF-8#',$dump);
        $this->assertRegexp('/'.preg_quote($expected_mail_body,'/').'/',$dump);

        $conf['htmlmail'] = 1;
    }

    function test_replacements(){
        $mail = new TestMailer();

        $replacements = array( '@DATE@','@BROWSER@','@IPADDRESS@','@HOSTNAME@','@EMAILSIGNATURE@',
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
        $results = $html->post('https://tools.ietf.org/tools/msglint/msglint', array('msg'=>$msg));
        if($results === false) {
            $this->markTestSkipped('no response from validator');
            return;
        }

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
                if(strpos($line, "bare newline in text body decoded")) continue; #we don't send mail bodies as CRLF, yet
                if(strpos($line, "last decoded line too long")) continue; #we don't send mail bodies as CRLF, yet

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

        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    function test_simplemailsignature() {
        global $conf;
        $conf['htmlmail'] = 0;

        $mailbody = 'A test mail in ASCII';
        $signature = "\n-- \n" . 'This mail was generated by DokuWiki at' . "\n" . DOKU_URL . "\n";
        $mail = new TestMailer();
        $mail->to('test@example.com');
        $mail->setBody($mailbody);

        $dump = $mail->dump();

        // construct the expected mail body text - include the expected dokuwiki signature
        $expected_mail_body = chunk_split(base64_encode($mailbody . $signature), 72, MAILHEADER_EOL);
        $this->assertRegexp('/' . preg_quote($expected_mail_body, '/') . '/', $dump);

        $conf['htmlmail'] = 1;
    }

    function test_htmlmailsignature() {
        $mailbody_text = 'A test mail in ASCII :)';
        $mailbody_html = 'A test mail in <strong>html</strong>';
        $htmlmsg_expected = '<html>
<head>
    <title>My Test Wiki</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

A test mail in <strong>html</strong>

<br /><hr />
<small>This mail was generated by DokuWiki at<br /><a href="' . DOKU_URL . '">' . DOKU_URL . '</a></small>
</body>
</html>
';

        $mail = new TestMailer();
        $mail->to('test@example.com');
        $mail->setBody($mailbody_text, null, null, $mailbody_html);

        $dump = $mail->dump();

        // construct the expected mail body text - include the expected dokuwiki signature
        $expected_mail_body = chunk_split(base64_encode($htmlmsg_expected), 72, MAILHEADER_EOL);

        $this->assertRegexp('/Content-Type: multipart/', $dump);
        $this->assertRegexp('#Content-Type: text/plain; charset=UTF-8#', $dump);
        $this->assertRegexp('/' . preg_quote($expected_mail_body, '/') . '/', $dump);

    }

    function test_htmlmailsignaturecustom() {
        global $lang;
        $lang['email_signature_html'] = 'Official message from your DokuWiki @DOKUWIKIURL@<br />Created by wonderful mail class <a href="https://www.dokuwiki.org/devel:mail">https://www.dokuwiki.org/devel:mail</a>';

        $mailbody_text = 'A test mail in ASCII :)';
        $mailbody_html = 'A test mail in <strong>html</strong>';
        $htmlmsg_expected = '<html>
<head>
    <title>My Test Wiki</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

A test mail in <strong>html</strong>

<br /><hr />
<small>Official message from your DokuWiki <a href="' . DOKU_URL . '">' . DOKU_URL . '</a><br />Created by wonderful mail class <a href="https://www.dokuwiki.org/devel:mail">https://www.dokuwiki.org/devel:mail</a></small>
</body>
</html>
';

        $mail = new TestMailer();
        $mail->to('test@example.com');
        $mail->setBody($mailbody_text, null, null, $mailbody_html);

        $dump = $mail->dump();

        // construct the expected mail body text - include the expected dokuwiki signature
        $replacements = $mail->prop('replacements');
        $expected_mail_body = chunk_split(base64_encode($htmlmsg_expected), 72, MAILHEADER_EOL);

        $this->assertRegexp('/' . preg_quote($expected_mail_body, '/') . '/', $dump);

    }

    function test_getCleanName() {
        $mail = new TestMailer();
        $name = $mail->getCleanName('Foo Bar');
        $this->assertEquals('Foo Bar', $name);
        $name = $mail->getCleanName('Foo, Bar');
        $this->assertEquals('"Foo, Bar"', $name);
        $name = $mail->getCleanName('Foo" Bar');
        $this->assertEquals('"Foo\" Bar"', $name);
    }
}
//Setup VIM: ex: et ts=4 :
