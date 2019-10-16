<?php

use dokuwiki\Remote\XmlRpcServer;


class XmlRpcServerTest extends DokuWikiTest
{

    protected $userinfo;
    protected $server;


    function setUp()
    {
        parent::setUp();
        global $conf;
        global $USERINFO;

        parent::setUp();

        // mock plugin controller to return our test plugins


        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 0;

        $this->userinfo = $USERINFO;
        $this->server = new XmlRpcServer(false,false,true);

    }

    function tearDown()
    {
        global $USERINFO;
        $USERINFO = $this->userinfo;
    }

    function testDateFormat()
    {

        $pageName = ":wiki:dokuwiki";
        $file = wikiFN($pageName);
        $timestamp = filemtime($file);
        $ixrModifiedTime = (new DateTime('@' . $timestamp))->format(IXR_Date::XMLRPC_ISO8601);

        $request = <<<EOD
<?xml version="1.0"?>
   <methodCall>
     <methodName>wiki.getPageInfo</methodName>
     		<param> 
			<value>
				<string>$pageName</string>
			</value>
		</param>
   </methodCall>
EOD;
        $expected = <<<EOD
<methodResponse>
  <params>
    <param>
      <value>
        <struct>
  <member><name>name</name><value><string>wiki:dokuwiki</string></value></member>
  <member><name>lastModified</name><value><dateTime.iso8601>$ixrModifiedTime</dateTime.iso8601></value></member>
  <member><name>author</name><value><string></string></value></member>
  <member><name>version</name><value><int>$timestamp</int></value></member>
</struct>
      </value>
    </param>
  </params>
</methodResponse>
EOD;

        $response = $this->server->serve($request, true);
        $this->assertEquals(trim($expected), trim($response));
    }
}
