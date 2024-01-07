<?php

namespace dokuwiki\test\Remote;


use dokuwiki\test\Remote\Mock\XmlRpcServer;

class XmlRpcServerTest extends \DokuWikiTest
{
    protected $server;

    function setUp(): void
    {
        parent::setUp();
        global $conf;

        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 0;

        $this->server = new XmlRpcServer(true);
    }


    function testFullArgs()
    {
        $request = <<<EOD
<?xml version="1.0"?>
<methodCall>
    <methodName>wiki.twoArgWithDefaultArg</methodName>
    <param>
        <value>
            <string>arg1</string>
        </value>
    </param>
    <param>
        <value>
            <string>arg2</string>
        </value>
    </param>
</methodCall>
EOD;

        $expected = <<<EOD
<methodResponse>
    <params>
        <param>
            <value>
                <array>
                    <data>
                        <value>
                            <string>arg1</string>
                        </value>
                        <value>
                            <string>arg2</string>
                        </value>
                    </data>
                </array>
            </value>
        </param>
    </params>
</methodResponse>
EOD;

        $_SERVER['CONTENT_TYPE'] = 'text/xml';
        $this->server->serve($request);
        $this->assertXmlStringEqualsXmlString(trim($expected), trim($this->server->output));
    }

    function testDefaultArgs()
    {
        $request = <<<EOD
<?xml version="1.0"?>
<methodCall>
    <methodName>wiki.twoArgWithDefaultArg</methodName>
    <param>
        <value>
            <string>arg1</string>
        </value>
    </param>
</methodCall>
EOD;

        $expected = <<<EOD
<methodResponse>
    <params>
        <param>
            <value>
                <array>
                    <data>
                        <value>
                            <string>arg1</string>
                        </value>
                        <value>
                            <string>default</string>
                        </value>
                    </data>
                </array>
            </value>
        </param>
    </params>
</methodResponse>
EOD;

        $_SERVER['CONTENT_TYPE'] = 'text/xml';
        $this->server->serve($request);
        $this->assertXmlStringEqualsXmlString(trim($expected), trim($this->server->output));
    }

    function testStructResponse()
    {
        $request = <<<EOD
<?xml version="1.0"?>
    <methodCall>
        <methodName>wiki.getStructuredData</methodName>
   </methodCall>
EOD;
        $expected = <<<EOD
<methodResponse>
    <params>
        <param>
            <value>
                <struct>
                    <member>
                        <name>type</name>
                        <value><string>internal</string></value>
                    </member>
                    <member>
                        <name>page</name>
                        <value><string>wiki:dokuwiki</string></value>
                    </member>
                    <member>
                        <name>href</name>
                        <value><string>https://www.dokuwiki.org/wiki:dokuwiki</string></value>
                    </member>
                </struct>
            </value>
        </param>
    </params>
</methodResponse>
EOD;

        $_SERVER['CONTENT_TYPE'] = 'text/xml';
        $this->server->serve($request);
        $this->assertXmlStringEqualsXmlString(trim($expected), trim($this->server->output));
    }
}
