<?php

class ixr_library_ixr_message_test extends DokuWikiTest {

    function test_untypedvalue1(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>wiki.getBackLinks</methodName><params><param><value> change  </value></param></params></methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.getBackLinks');
        $this->assertEquals($ixrmsg->params,array(' change  '));
    }

    function test_untypedvalue2(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>wiki.getBackLinks</methodName>
                    <params>
                        <param>
                            <value> change  </value>
                        </param>
                    </params>
                </methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.getBackLinks');
        $this->assertEquals($ixrmsg->params,array(' change  '));
    }

    function test_stringvalue1(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>wiki.getBackLinks</methodName><params><param><value><string> change  </string></value></param></params></methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.getBackLinks');
        $this->assertEquals($ixrmsg->params,array(' change  '));
    }

    function test_stringvalue2(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>wiki.getBackLinks</methodName>
                    <params>
                        <param>
                            <value>
                                <string> change  </string>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.getBackLinks');
        $this->assertEquals($ixrmsg->params,array(' change  '));
    }

    function test_emptyvalue1(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>wiki.getBackLinks</methodName><params><param><value><string></string></value></param></params></methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.getBackLinks');
        $this->assertEquals($ixrmsg->params,array(''));
    }

    function test_emptyvalue2(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>wiki.getBackLinks</methodName>
                    <params>
                        <param>
                            <value>
                                <string></string>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.getBackLinks');
        $this->assertEquals($ixrmsg->params,array(''));
    }

    function test_struct(){
        $xml = '<?xml version=\'1.0\'?>
                <methodCall>
                <methodName>wiki.putPage</methodName>
                <params>
                <param>
                <value><string>start</string></value>
                </param>
                <param>
                <value><string>test text</string></value>
                </param>
                <param>
                <value><struct>
                <member>
                <name>sum</name>
                <value><string>xmlrpc edit</string></value>
                </member>
                <member>
                <name>minor</name>
                <value><string>1</string></value>
                </member>
                </struct></value>
                </param>
                </params>
                </methodCall>';

        $ixrmsg = new IXR_Message($xml);
        $ixrmsg->parse();

        $this->assertEquals($ixrmsg->messageType,'methodCall');
        $this->assertEquals($ixrmsg->methodName,'wiki.putPage');
        $this->assertEquals($ixrmsg->params,array('start','test text',array('sum'=>'xmlrpc edit','minor'=>'1')));
    }

}
//Setup VIM: ex: et ts=4 :
