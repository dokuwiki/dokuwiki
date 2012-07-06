<?php

/**
 * @group integration
 */
class InttestsBasicTest extends DokuWikiTest {
    /**
     * Execute the simplest possible request and expect
     * a dokuwiki page which obviously has the word "DokuWiki"
     * in it somewhere.
     */
    function testSimpleRun() {
        $request = new TestRequest();

        $response = $request->execute();

        $this->assertTrue(
            strpos($response->getContent(), 'DokuWiki') >= 0,
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
        $this->assertTrue(strpos($response->getContent(), 'Andreas Gohr') >= 0);
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
        $this->assertTrue(strpos($response->getContent(), 'Andreas Gohr') >= 0);
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
        $this->assertTrue(strpos($response->getContent(), 'Andreas Gohr') >= 0);
    }


}
