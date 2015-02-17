<?php

/**
 * @group integration
 */
class InttestsBasicTest extends DokuWikiTest {

    private $some_headers =  array(
          'Content-Type: image/png',
          'Date: Fri, 22 Mar 2013 16:10:01 GMT',
          'X-Powered-By: PHP/5.3.15',
          'Expires: Sat, 23 Mar 2013 17:03:46 GMT',
          'Cache-Control: public, proxy-revalidate, no-transform, max-age=86400',
          'Pragma: public',
          'Last-Modified: Fri, 22 Mar 2013 01:48:28 GMT',
          'ETag: "63daab733b38c30c337229b2e587f8fb"',
          'Content-Disposition: inline; filename="fe389b0db8c1088c336abb502d2f9ae7.media.200x200.png',
          'Accept-Ranges: bytes',
          'Content-Type: image/png',
          'Content-Length: 62315',
          'Status: 200 OK',
          'Status: 404 Not Found',
     );

    /**
     * Execute the simplest possible request and expect
     * a dokuwiki page which obviously has the word "DokuWiki"
     * in it somewhere.
     */
    function testSimpleRun() {
        $request = new TestRequest();

        $response = $request->execute();

        $this->assertTrue(
            strpos($response->getContent(), 'DokuWiki') !== false,
            'DokuWiki was not a word in the output'
        );
    }

    function testPost() {
        $request = new TestRequest();

        $input = array(
            'string' => 'A string',
            'array'  => array(1, 2, 3),
            'id'     => 'wiki:dokuwiki'
        );

        $response = $request->post($input);

        // server var check
        $this->assertEquals('POST',$request->getServer('REQUEST_METHOD'));
        $this->assertEquals('',$request->getServer('QUERY_STRING'));
        $this->assertEquals('/doku.php',$request->getServer('REQUEST_URI'));

        // variable setup check
        $this->assertEquals('A string', $request->getPost('string'));
        $this->assertEquals(array(1, 2, 3), $request->getPost('array'));
        $this->assertEquals('wiki:dokuwiki', $request->getPost('id'));

        // output check
        $this->assertTrue(strpos($response->getContent(), 'Andreas Gohr') !== false);
    }

    function testPostGet() {
        $request = new TestRequest();

        $input = array(
            'string' => 'A string',
            'array'  => array(1, 2, 3),
        );

        $response = $request->post($input,'/doku.php?id=wiki:dokuwiki');

        // server var check
        $this->assertEquals('POST',$request->getServer('REQUEST_METHOD'));
        $this->assertEquals('?id=wiki:dokuwiki',$request->getServer('QUERY_STRING'));
        $this->assertEquals('/doku.php?id=wiki:dokuwiki',$request->getServer('REQUEST_URI'));

        // variable setup check
        $this->assertEquals('A string', $request->getPost('string'));
        $this->assertEquals(array(1, 2, 3), $request->getPost('array'));
        $this->assertEquals('wiki:dokuwiki', $request->getGet('id'));

        // output check
        $this->assertTrue(strpos($response->getContent(), 'Andreas Gohr') !== false);
    }

    function testGet() {
        $request = new TestRequest();

        $input = array(
            'string' => 'A string',
            'array'  => array(1, 2, 3),
            'test'   => 'bar'
        );

        $response = $request->get($input,'/doku.php?id=wiki:dokuwiki&test=foo');

        // server var check
        $this->assertEquals('GET',$request->getServer('REQUEST_METHOD'));
        $this->assertEquals(
            '?id=wiki:dokuwiki&test=bar&string=A+string&array[0]=1&array[1]=2&array[2]=3',
            $request->getServer('QUERY_STRING')
        );
        $this->assertEquals(
            '/doku.php?id=wiki:dokuwiki&test=bar&string=A+string&array[0]=1&array[1]=2&array[2]=3',
            $request->getServer('REQUEST_URI')
        );

        // variable setup check
        $this->assertEquals('A string', $request->getGet('string'));
        $this->assertEquals(array(1, 2, 3), $request->getGet('array'));
        $this->assertEquals('wiki:dokuwiki', $request->getGet('id'));
        $this->assertEquals('bar', $request->getGet('test'));

        // output check
        $this->assertTrue(strpos($response->getContent(), 'Andreas Gohr') !== false);
    }

    function testScripts() {
        $request = new TestRequest();

        // doku
        $response = $request->get();
        $this->assertEquals('doku.php',$request->getScript());

        $response = $request->get(array(),'/doku.php?id=wiki:dokuwiki&test=foo');
        $this->assertEquals('doku.php',$request->getScript());

        // fetch
        $response = $request->get(array(),'/lib/exe/fetch.php?media=wiki:dokuwiki-128.png');
        $this->assertEquals('lib/exe/fetch.php',$request->getScript());

        // detail
        $response = $request->get(array(),'/lib/exe/detail.php?id=start&media=wiki:dokuwiki-128.png');
        $this->assertEquals('lib/exe/detail.php',$request->getScript());
    }

    function testHeaders(){
        header('X-Test: check headers working');
        $header_check = function_exists('xdebug_get_headers') ? xdebug_get_headers() : headers_list();
        if (empty($header_check)) {
            $this->markTestSkipped('headers not returned, perhaps your sapi does not return headers, try xdebug');
        } else {
            header_remove('X-Test');
        }

        $request = new TestRequest();
        $response = $request->get(array(),'/lib/exe/fetch.php?media=wiki:dokuwiki-128.png');
        $headers = $response->getHeaders();
        $this->assertTrue(!empty($headers));
    }

    function testGetHeader(){
        $response = new TestResponse('',$this->some_headers);

        $this->assertEquals('Pragma: public', $response->getHeader('Pragma'));
        $this->assertEmpty($response->getHeader('Junk'));
        $this->assertEquals(array('Content-Type: image/png','Content-Type: image/png'), $response->getHeader('Content-Type'));
    }

    function testGetStatus(){
       $response = new TestResponse('',$this->some_headers);
       $this->assertEquals(404, $response->getStatusCode());

       $response = new TestResponse('',array_slice($this->some_headers,0,-2));  // slice off the last two headers to leave no status header
       $this->assertNull($response->getStatusCode());
    }
    
    function testINPUT() {
        $request = new TestRequest();
        $response = $request->get(array('id' => 'mailinglist'), '/doku.php');

        // output check
        $this->assertTrue(strpos($response->getContent(), 'Netiquette') !== false);
    }

}
