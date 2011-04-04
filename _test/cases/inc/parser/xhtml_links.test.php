<?php
if (!defined('DOKU_BASE')) define('DOKU_BASE','./');
require_once DOKU_INC.'inc/parser/xhtml.php';

class xhtml_links_test extends UnitTestCase {

    function test_emaillink(){
        global $conf;
        $conf['mailguard'] = 'visible';
        $conf['userewrite'] = 0;

        $p = new Doku_Renderer_xhtml();
        $p->emaillink('foo@example.com','<script>alert(\'"alert"\');</script>');

        $expect = '<a href="mailto:foo%20%5Bat%5D%20example%20%5Bdot%5D%20com" class="mail" title="foo [at] example [dot] com">&lt;script&gt;alert(&#039;&quot;alert&quot;&#039;);&lt;/script&gt;</a>';

        $this->assertEqual($p->doc,$expect);
    }

    function test_emaillink_with_media(){
        global $conf;
        $conf['mailguard'] = 'visible';
        $conf['userewrite'] = 2;

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

        $p = new Doku_Renderer_xhtml();
        $p->emaillink('foo@example.com',$image);

        $expect = '<a href="mailto:foo%20%5Bat%5D%20example%20%5Bdot%5D%20com" class="media" title="foo [at] example [dot] com"><img src="'.DOKU_BASE.'lib/exe/fetch.php/img.gif?w=10&amp;h=20&amp;cache=nocache" class="media" title="Some Image" alt="Some Image" width="10" height="20" /></a>';

        $this->assertEqual($p->doc,$expect);
    }

    /**
      *  Produced by syntax like [[ ]]
      */
    function test_empty_internallink(){
        global $ID;
        $ID = 'my:space';

        $p = new Doku_Renderer_xhtml();
        $p->internallink('');

        $expect = '<span class="curid"><a href="/./doku.php/my:space" class="wikilink1" title="my:space">start</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[ |my caption]]
      */
    function test_empty_internallink_with_caption(){
        global $ID;
        $ID = 'my:space';

        $p = new Doku_Renderer_xhtml();
        $p->internallink('', 'my caption');

        $expect = '<span class="curid"><a href="/./doku.php/my:space" class="wikilink1" title="my:space">my caption</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[?do=index]]
      */
    function test_empty_internallink_index(){
        global $ID;
        $ID = 'my:space';

        $p = new Doku_Renderer_xhtml();
        $p->internallink('?do=index');

        $expect = '<span class="curid"><a href="/./doku.php/my:space?do=index" class="wikilink1" title="my:space">start</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[?do=index|my caption]]
      */
    function test_empty_internallink_index_with_caption(){
        global $ID;
        $ID = 'my:space';

        $p = new Doku_Renderer_xhtml();
        $p->internallink('?do=index', 'my caption');

        $expect = '<span class="curid"><a href="/./doku.php/my:space?do=index" class="wikilink1" title="my:space">my caption</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[#test]]
      */
    function test_empty_locallink(){
        global $ID;
        $ID = 'my:space';

        $p = new Doku_Renderer_xhtml();
        $p->locallink('test');

        $expect = '<a href="#test" title="my:space &crarr;" class="wikilink1">test</a>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[#test|my caption]]
      */
    function test_empty_locallink_with_caption(){
        global $ID;
        $ID = 'my:space';

        $p = new Doku_Renderer_xhtml();
        $p->locallink('test', 'my caption');

        $expect = '<a href="#test" title="my:space &crarr;" class="wikilink1">my caption</a>';

        $this->assertEqual($p->doc, $expect);
    }
}
