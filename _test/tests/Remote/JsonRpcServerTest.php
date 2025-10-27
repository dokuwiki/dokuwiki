<?php

namespace dokuwiki\test\Remote;


use dokuwiki\test\Remote\Mock\JsonRpcServer;

/**
 * @todo test different request formats
 */
class JsonRpcServerTest extends \DokuWikiTest
{
    protected $server;

    function setUp(): void
    {
        parent::setUp();
        global $conf;

        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 0;

        $this->server = new JsonRpcServer();
    }


    function testFullArgs()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/wiki.twoArgWithDefaultArg';

        $positional = json_encode(['arg1', 'arg2']);
        $named = json_encode(['string1' => 'arg1', 'string2' => 'arg2']);
        $expect = json_encode(['arg1', 'arg2']);

        $response = json_encode($this->server->serve($positional)['result']);
        $this->assertJsonStringEqualsJsonString($expect, $response);

        $response = json_encode($this->server->serve($named)['result']);
        $this->assertJsonStringEqualsJsonString($expect, $response);
    }

    function testDefaultArgs()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/wiki.twoArgWithDefaultArg';

        $positional = json_encode(['arg1']);
        $named = json_encode(['string1' => 'arg1']);
        $expect = json_encode(['arg1', 'default']);

        $response = json_encode($this->server->serve($positional)['result']);
        $this->assertJsonStringEqualsJsonString($expect, $response);

        $response = json_encode($this->server->serve($named)['result']);
        $this->assertJsonStringEqualsJsonString($expect, $response);
    }

    function testStructResponse()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/wiki.getStructuredData';

        $expect = json_encode([
            'type' => 'internal',
            'page' => 'wiki:dokuwiki',
            'href' => 'https://www.dokuwiki.org/wiki:dokuwiki'
        ]);

        $response = json_encode($this->server->serve('[]')['result']);
        $this->assertJsonStringEqualsJsonString($expect, $response);
    }
}
