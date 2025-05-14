<?php

class html_hilight_test extends DokuWikiTest {

    function testHighlightOneWord() {
        $html = 'Foo bar Foo';
        $this->assertMatchesRegularExpression(
            '/Foo <span.*>bar<\/span> Foo/',
            html_hilight($html,'bar')
        );
    }

    function testHighlightTwoWords() {
        $html = 'Foo bar Foo php Foo';
        $this->assertMatchesRegularExpression(
            '/Foo <span.*>bar<\/span> Foo <span.*>php<\/span> Foo/',
            html_hilight($html,array('bar','php'))
        );
    }

    function testHighlightTwoWordsHtml() {
        $html = 'Foo <b>bar</b> <i>Foo</i> php Foo';
        $this->assertMatchesRegularExpression(
            '/Foo <b><span.*>bar<\/span><\/b> <i>Foo<\/i> <span.*>php<\/span> Foo/',
            html_hilight($html,array('bar','php'))
        );
    }

    function testNoMatchHtml() {
        $html = 'Foo <font>font</font> Bar';
        $this->assertMatchesRegularExpression(
            '/Foo <font><span.*>font<\/span><\/font> Bar/',
            html_hilight($html,'font')
        );
    }

    function testWildcardRight() {
        $html = 'foo bar foobar barfoo foobarfoo foo';
        $this->assertMatchesRegularExpression(
            '/foo <span.*>bar<\/span> foobar <span.*>bar<\/span>foo foobarfoo foo/',
            html_hilight($html,'bar*')
        );
    }

    function testWildcardLeft() {
        $html = 'foo bar foobar barfoo foobarfoo foo';
        $this->assertMatchesRegularExpression(
            '/foo <span.*>bar<\/span> foo<span.*>bar<\/span> barfoo foobarfoo foo/',
            html_hilight($html,'*bar')
        );
    }

    function testWildcardBoth() {
        $html = 'foo bar foobar barfoo foobarfoo foo';
        $this->assertMatchesRegularExpression(
            '/foo <span.*>bar<\/span> foo<span.*>bar<\/span> <span.*>bar<\/span>foo foo<span.*>bar<\/span>foo foo/',
            html_hilight($html,'*bar*')
        );
    }

    function testNoHighlight() {
        $html = 'Foo bar Foo';
        $this->assertMatchesRegularExpression(
            '/Foo bar Foo/',
            html_hilight($html,'php')
        );
    }

    function testMatchAttribute() {
        $html = 'Foo <b class="x">bar</b> Foo';
        $this->assertMatchesRegularExpression(
            '/Foo <b class="x">bar<\/b> Foo/',
            html_hilight($html,'class="x"')
        );
    }

    function testMatchAttributeWord() {
        $html = 'Foo <b class="x">bar</b> Foo';
        $this->assertEquals(
            'Foo <b class="x">bar</b> Foo',
            html_hilight($html,'class="x">bar')
        );
    }

    function testRegexInjection() {
        $html = 'Foo bar Foo';
        $this->assertMatchesRegularExpression(
            '/Foo bar Foo/',
            html_hilight($html,'*')
        );
    }

    function testRegexInjectionSlash() {
        $html = 'Foo bar Foo';
        $this->assertMatchesRegularExpression(
            '/Foo bar Foo/',
            html_hilight($html,'x/')
        );
    }

    function testMB() {
        $html = 'foo ДокуВики bar';
        $this->assertMatchesRegularExpression(
            '/foo <span.*>ДокуВики<\/span> bar/',
            html_hilight($html,'ДокуВики')
        );
    }

    function testMBright() {
        $html = 'foo ДокуВики bar';
        $this->assertMatchesRegularExpression(
            '/foo <span.*>Доку<\/span>Вики bar/',
            html_hilight($html,'Доку*')
        );
    }

    function testMBleft() {
        $html = 'foo ДокуВики bar';
        $this->assertMatchesRegularExpression(
            '/foo Доку<span.*>Вики<\/span> bar/',
            html_hilight($html,'*Вики')
        );
    }

    function testMBboth() {
        $html = 'foo ДокуВики bar';
        $this->assertMatchesRegularExpression(
            '/foo До<span.*>куВи<\/span>ки bar/',
            html_hilight($html,'*куВи*')
        );
    }
}
