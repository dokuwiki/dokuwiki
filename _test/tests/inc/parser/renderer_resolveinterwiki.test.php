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
        //this was the only link with host/port/path/query. Keep it here for regression
        $Renderer->interwiki['coral'] = 'http://{HOST}.{PORT}.nyud.net:8090{PATH}?{QUERY}';

        $tests = array(
            // shortcut, reference and expected
            array('wp', 'foo [\\]^`{|}~@+#%?/#txt', 'https://en.wikipedia.org/wiki/foo %5B%5C%5D%5E%60%7B%7C%7D~@+%23%25?/#txt'),
            array('amazon', 'foo [\\]^`{|}~@+#%?/#txt', 'https://www.amazon.com/exec/obidos/ASIN/foo%20%5B%5C%5D%5E%60%7B%7C%7D~%40%2B%23%25%3F%2F/splitbrain-20/#txt'),
            array('doku', 'foo [\\]^`{|}~@+#%?/#txt', 'https://www.dokuwiki.org/foo%20%5B%5C%5D%5E%60%7B%7C%7D~%40%2B%23%25%3F%2F#txt'),
            array('coral', 'http://example.com:83/path/naar/?query=foo%20%40%2B%25%3F%2F', 'http://example.com.83.nyud.net:8090/path/naar/?query=foo%20%40%2B%25%3F%2F'),
            array('scheme', 'ftp://foo @+%/#txt', 'ftp://example.com#txt'),
            //relative url
            array('withslash', 'foo [\\]^`{|}~@+#%?/#txt', '/testfoo%20%5B%5C%5D%5E%60%7B%7C%7D~%40%2B%23%25%3F%2F#txt'),
            array('skype',  'foo [\\]^`{|}~@+#%?/#txt', 'skype:foo %5B%5C%5D%5E%60%7B%7C%7D~@+%23%25?/#txt'),
            //dokuwiki id's
            array('onlytext', 'foo [\\]^`{|}~@+#%/#txt', DOKU_BASE.'doku.php?id=onlytextfoo#txt'),
            array('user', 'foo [\\]^`{|}~@+#%/#txt', DOKU_BASE.'doku.php?id=user:foo#txt'),
            array('withquery', 'foo [\\]^`{|}~@+#%/#txt', DOKU_BASE.'doku.php?id=anyns:foo&amp;do=edit#txt')
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
        $expected = 'https://www.google.com/search?q=foo%20%40%2B%25%2F&amp;btnI=lucky';

        $this->assertEquals($expected, $url);
    }

}