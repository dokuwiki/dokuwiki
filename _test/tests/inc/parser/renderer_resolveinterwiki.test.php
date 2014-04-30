<?php

require_once DOKU_INC . 'inc/parser/renderer.php';

/**
 * Tests for Doku_Renderer::_resolveInterWiki()
 */
class Test_resolveInterwiki extends DokuWikiTest {

    function testDefaults() {
        $Renderer = new Doku_Renderer();
        $Renderer->interwiki = getInterwiki();
        $Renderer->interwiki['scheme'] = '{SCHEME}://example.com';
        $Renderer->interwiki['withslash'] = '/test';
        $Renderer->interwiki['onlytext'] = ':onlytext{NAME}'; //with {URL} double urlencoded
        $Renderer->interwiki['withquery'] = ':anyns:{NAME}?do=edit';

        $tests = array(
            // shortcut, reference and expected
            array('wp', 'foo @+%/#txt', 'http://en.wikipedia.org/wiki/foo @+%/#txt'),
            array('amazon', 'foo @+%/#txt', 'http://www.amazon.com/exec/obidos/ASIN/foo%20%40%2B%25%2F/splitbrain-20/#txt'),
            array('doku', 'foo @+%/#txt', 'http://www.dokuwiki.org/foo%20%40%2B%25%2F#txt'),
            array('coral', 'http://example.com:83/path/naar/?query=foo%20%40%2B%25%2F', 'http://example.com.83.nyud.net:8090/path/naar/?query=foo%20%40%2B%25%2F'),
            array('scheme', 'ftp://foo @+%/#txt', 'ftp://example.com#txt'),
            //relative url
            array('withslash', 'foo @+%/#txt', '/testfoo%20%40%2B%25%2F#txt'),
            array('skype',  'foo @+%/#txt', 'skype:foo @+%/#txt'),
            //dokuwiki id's
            array('onlytext', 'foo @+%#txt', DOKU_BASE.'doku.php?id=onlytextfoo#txt'),
            array('user', 'foo @+%#txt', DOKU_BASE.'doku.php?id=user:foo#txt'),
            array('withquery', 'foo @+%#txt', DOKU_BASE.'doku.php?id=anyns:foo&amp;do=edit#txt')
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