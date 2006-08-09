<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Links extends TestOfDoku_Parser {
    
    function TestOfDoku_Parser_Links() {
        $this->UnitTestCase('TestOfDoku_Parser_Links');
    }
    
    
    function testExternalLinkSimple() {
        $this->P->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
        $this->P->parse("Foo http://www.google.com Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externallink',array('http://www.google.com', NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testExternalLinkCase() {
        $this->P->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
        $this->P->parse("Foo HTTP://WWW.GOOGLE.COM Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externallink',array('HTTP://WWW.GOOGLE.COM', NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testExternalLinkJavascript() {
        $this->P->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
        $this->P->parse("Foo javascript:alert('XSS'); Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nFoo javascript:alert('XSS'); Bar\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testExternalWWWLink() {
        $this->P->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
        $this->P->parse("Foo www.google.com Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externallink',array('www.google.com', NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testExternalFTPLink() {
        $this->P->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
        $this->P->parse("Foo ftp.sunsite.com Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externallink',array('ftp.sunsite.com', NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    function testEmail() {
/*		$this->fail('The emaillink mode seems to cause php 5.0.5 to segfault');
		return; //FIXME: is this still true?*/
        $this->P->addMode('emaillink',new Doku_Parser_Mode_Emaillink());
        $this->P->parse("Foo <bugs@php.net> Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('emaillink',array('bugs@php.net', NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
	
    function testInternalLinkOneChar() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[l]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('l',NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testInternalLinkNamespaceNoTitle() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[foo:bar]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('foo:bar',NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testInternalLinkNamespace() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|Test]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('x:1:y:foo_bar:z','Test')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testInternalLinkSectionRef() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[wiki:syntax#internal|Syntax]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('wiki:syntax#internal','Syntax')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testExternalInInternalLink() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[http://www.google.com|Google]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externallink',array('http://www.google.com','Google')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testInterwikiLink() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[iw>somepage|Some Page]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('interwikilink',array('iw>somepage','Some Page','iw','somepage')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testInterwikiLinkCase() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[IW>somepage|Some Page]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('interwikilink',array('IW>somepage','Some Page','iw','somepage')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testInterwikiPedia() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[wp>Callback_(computer_science)|callbacks]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('interwikilink',array('wp>Callback_(computer_science)','callbacks','wp','Callback_(computer_science)')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testCamelCase() {
        $this->P->addMode('camelcaselink',new Doku_Parser_Mode_CamelCaseLink());
        $this->P->parse("Foo FooBar Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('camelcaselink',array('FooBar')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testFileLink() {
        $this->P->addMode('filelink',new Doku_Parser_Mode_FileLink());
        $this->P->parse('Foo file://temp/file.txt Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('filelink',array('file://temp/file.txt ',NULL)),
            array('cdata',array('Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testFileLinkInternal() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse('Foo [[file://temp/file.txt|Some File]] Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externallink',array('file://temp/file.txt','Some File')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testWindowsShareLink() {
        $this->P->addMode('windowssharelink',new Doku_Parser_Mode_WindowsShareLink());
        $this->P->parse('Foo \\\server\share Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('windowssharelink',array('\\\server\share',NULL)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testWindowsShareLinkInternal() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse('Foo [[\\\server\share|My Documents]] Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('windowssharelink',array('\\\server\share','My Documents')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInternal() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{img.gif}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('img.gif',NULL,NULL,NULL,NULL,'cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaNotImage() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{foo.txt?10x10|Some File}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('foo.txt','Some File',null,10,10,'cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInternalLAlign() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{img.gif }} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('img.gif',NULL,'left',NULL,NULL,'cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInternalRAlign() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{ img.gif}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('img.gif',NULL,'right',NULL,NULL,'cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInternalCenter() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{ img.gif }} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('img.gif',NULL,'center',NULL,NULL,'cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInternalParams() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{img.gif?50x100nocache}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('img.gif',NULL,NULL,'50','100','nocache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInternalTitle() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{img.gif?50x100|Some Image}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',array('img.gif','Some Image',NULL,'50','100','cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaExternal() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externalmedia',array('http://www.google.com/img.gif',NULL,NULL,NULL,NULL,'cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaExternalParams() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif?50x100nocache}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externalmedia',array('http://www.google.com/img.gif',NULL,NULL,'50','100','nocache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaExternalTitle() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif?50x100|Some Image}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('externalmedia',
            array('http://www.google.com/img.gif','Some Image',NULL,'50','100','cache','details')),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaInInternalLink() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|{{img.gif?10x20nocache|Some Image}}]] Bar");
        
        $image = array(
            'type'=>'internalmedia',
            'src'=>'img.gif',
            'title'=>'Some Image',
            'align'=>NULL,
            'width'=>10,
            'height'=>20,
            'cache'=>'nocache',
						'linking'=>'details',
        );
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('x:1:y:foo_bar:z',$image)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testMediaNoImageInInternalLink() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|{{foo.txt?10x20nocache|Some Image}}]] Bar");
        
        $image = array(
            'type'=>'internalmedia',
            'src'=>'foo.txt',
            'title'=>'Some Image',
            'align'=>NULL,
            'width'=>10,
            'height'=>20,
            'cache'=>'nocache',
						'linking'=>'details',
        );
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('x:1:y:foo_bar:z',$image)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testNestedMedia() {
        $this->P->addMode('media',new Doku_Parser_Mode_Media());
        $this->P->parse('Foo {{img.gif|{{foo.gif|{{bar.gif|Bar}}}}}} Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internalmedia',
            array('img.gif','{{foo.gif|{{bar.gif|Bar',NULL,NULL,NULL,'cache','details')),
            array('cdata',array('}}}} Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }

}

