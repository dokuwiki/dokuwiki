<?php
require_once DOKU_INC.'inc/parser/xhtml.php';

class xhtml_links_test extends UnitTestCase {

    function test_emaillink(){
        global $conf;
        $conf['mailguard'] = 'visible';

        $p = new Doku_Renderer_xhtml();
        $p->emaillink('foo@example.com','<script>alert(\'"alert"\');</script>');

        $expect = '<a href="mailto:foo%20%5Bat%5D%20example%20%5Bdot%5D%20com" class="mail JSnocheck" title="foo [at] example [dot] com">&lt;script&gt;alert(\'&quot;alert&quot;\');&lt;/script&gt;</a>';

        $this->assertEqual($p->doc,$expect);
    }

    function test_emaillink_with_media(){
        global $conf;
        $conf['mailguard'] = 'visible';

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

        $expect = '<a href="mailto:foo%20%5Bat%5D%20example%20%5Bdot%5D%20com" class="media JSnocheck" title="foo [at] example [dot] com"><img src="./lib/exe/fetch.php/img.gif?w=10&amp;h=20&amp;cache=nocache" class="media" title="Some Image" alt="Some Image" width="10" height="20" /></a>';

        $this->assertEqual($p->doc,$expect);
    }

}
