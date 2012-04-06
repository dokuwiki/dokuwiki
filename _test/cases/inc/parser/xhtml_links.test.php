<?php
if (!defined('DOKU_BASE')) define('DOKU_BASE','./');
require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/parser/xhtml.php';
require_once DOKU_INC.'inc/pageutils.php';

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
        $page = 'my:space';

        global $ID;
        $ID = $page;

        global $conf;
        $conf['start'] = 'start';

        global $conf;
        $conf['basedir']     = '/';
        $conf['useheading']  = 0;
        $conf['userewrite']  = 0;
        $conf['useslash']    = 0;
        $conf['canonical']   = 0;

        $p = new Doku_Renderer_xhtml();
        $p->internallink('');


        if (page_exists($page)) {
            $class = 'wikilink1';
            $rel = '';
        }
        else {
            $class = 'wikilink2';
            $rel = ' rel="nofollow"';
        }

        $parts = split(':', $page);
        $caption = $parts[count($parts)-1];

        $expect = '<span class="curid"><a href="/./doku.php?id='.$page.'" class="'.$class.'" title="'.$page.'"'.$rel.'>'.$caption.'</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[ |my caption]]
      */
    function test_empty_internallink_with_caption(){
        $page = 'my:space';
        $caption = 'my caption';

        global $ID;
        $ID = $page;

        global $conf;
        $conf['basedir']     = '/';
        $conf['useheading']  = 0;
        $conf['userewrite']  = 0;
        $conf['useslash']    = 0;
        $conf['canonical']   = 0;

        $p = new Doku_Renderer_xhtml();
        $p->internallink('', $caption);

        if (page_exists($page)) {
            $class = 'wikilink1';
            $rel = '';
        }
        else {
            $class = 'wikilink2';
            $rel = ' rel="nofollow"';
        }

        $expect = '<span class="curid"><a href="/./doku.php?id='.$page.'" class="'.$class.'" title="'.$page.'"'.$rel.'>'.$caption.'</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[?do=index]]
      */
    function test_empty_internallink_index(){
        $page = 'my:space';

        global $ID;
        $ID = $page;

        global $conf;
        $conf['start'] = 'start';

        global $conf;
        $conf['basedir']     = '/';
        $conf['useheading']  = 0;
        $conf['userewrite']  = 0;
        $conf['useslash']    = 0;
        $conf['canonical']   = 0;

        $p = new Doku_Renderer_xhtml();
        $p->internallink('?do=index');

        if (page_exists($page)) {
            $class = 'wikilink1';
            $rel = '';
        }
        else {
            $class = 'wikilink2';
            $rel = ' rel="nofollow"';
        }

        $parts = split(':', $page);
        $caption = $parts[count($parts)-1];

        $expect = '<span class="curid"><a href="/./doku.php?id='.$page.'&amp;do=index" class="'.$class.'" title="'.$page.'"'.$rel.'>'.$caption.'</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[?do=index|my caption]]
      */
    function test_empty_internallink_index_with_caption(){
        $page = 'my:space';
        $caption = 'my caption';

        global $ID;
        $ID = $page;

        global $conf;
        $conf['basedir']     = '/';
        $conf['useheading']  = 0;
        $conf['userewrite']  = 0;
        $conf['useslash']    = 0;
        $conf['canonical']   = 0;

        $p = new Doku_Renderer_xhtml();
        $p->internallink('?do=index', $caption);

        if (page_exists($page)) {
            $class = 'wikilink1';
            $rel = '';
        }
        else {
            $class = 'wikilink2';
            $rel = ' rel="nofollow"';
        }

        $expect = '<span class="curid"><a href="/./doku.php?id='.$page.'&amp;do=index" class="'.$class.'" title="'.$page.'"'.$rel.'>'.$caption.'</a></span>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[#test]]
      */
    function test_empty_locallink(){
        $page = 'my:spacex';
        global $ID;
        $ID = $page;

        global $conf;
        $conf['basedir']     = '/';
        $conf['useheading']  = 0;
        $conf['userewrite']  = 0;
        $conf['useslash']    = 0;
        $conf['canonical']   = 0;

        $p = new Doku_Renderer_xhtml();
        $p->locallink('test');

        $expect = '<a href="#test" title="'.$page.' &crarr;" class="wikilink1">test</a>';

        $this->assertEqual($p->doc, $expect);
    }

    /**
      *  Produced by syntax like [[#test|my caption]]
      */
    function test_empty_locallink_with_caption(){
        $page = 'my:spacex';
        $caption = 'my caption';

        global $ID;
        $ID = $page;

        global $conf;
        $conf['basedir']     = '/';
        $conf['useheading']  = 0;
        $conf['userewrite']  = 0;
        $conf['useslash']    = 0;
        $conf['canonical']   = 0;

        $p = new Doku_Renderer_xhtml();
        $p->locallink('test', $caption);

        $expect = '<a href="#test" title="'.$page.' &crarr;" class="wikilink1">'.$caption.'</a>';

        $this->assertEqual($p->doc, $expect);
    }
}
