<?php

require_once DOKU_INC . 'inc/parser/renderer.php';

/**
 * Tests for Doku_Renderer::_resolveInterWiki()
 */
class Test_resolveInterwiki extends PHPUnit_Framework_TestCase {


    function testDefaults() {
        $Renderer = new Doku_Renderer();
        $Renderer->interwiki = getInterwiki();
        $Renderer->interwiki['scheme'] = '{SCHEME}://example.com';
        $Renderer->interwiki['slash'] = '/test';
        $Renderer->interwiki['onlytext'] = 'onlytext';

         //var_dump($Renderer->interwiki);

        $tests = array(
            // shortcut, reference and expected
            array('wp', 'foo @+%/', 'http://en.wikipedia.org/wiki/foo @+%/'),
            array('amazon', 'foo @+%/', 'http://www.amazon.com/exec/obidos/ASIN/foo%20%40%2B%25%2F/splitbrain-20/'),
            array('doku', 'foo @+%/', 'http://www.dokuwiki.org/foo%20%40%2B%25%2F'),
            //ToDo: Check needed, is double slash in path desired
            array('coral', 'http://example.com:83/path/naar/?query=foo%20%40%2B%25%2F', 'http://example.com.83.nyud.net:8090//path/naar/?query=foo%20%40%2B%25%2F'),
            array('scheme', 'ftp://foo @+%/', 'ftp://example.com'),
            //relative url
            array('slash', 'foo @+%/', '/testfoo%20%40%2B%25%2F'),
            //dokuwiki id's
            array('onlytext', 'foo @+%/', 'onlytextfoo%20%40%2B%25%2F'),
            array('user', 'foo @+%/', 'wiki:users:foo%20%40%2B%25%2F')
        );

        foreach($tests as $test) {
            $url = $Renderer->_resolveInterWiki($test[0], $test[1]);

            $this->assertEquals($test[2], $url);
        }
    }

    function testNonexisting() {
        $Renderer = new Doku_Renderer();
        $Renderer->interwiki = getInterwiki();

        $shortcut = 'nonexisting';
        $reference = 'foo @+%/';
        $url = $Renderer->_resolveInterWiki($shortcut, $reference);
        $expected = 'http://www.google.com/search?q=foo%20%40%2B%25%2F&amp;btnI=lucky';

        $this->assertEquals($expected, $url);
    }

}