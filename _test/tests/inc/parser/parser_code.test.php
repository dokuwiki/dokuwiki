<?php
require_once 'parser.inc.php';

/**
 * Tests to ensure functionality of the <code> syntax tag.
 *
 * @group parser_code
 */
class TestOfDoku_Parser_Code extends TestOfDoku_Parser {

    function setUp() {
        parent::setUp();
        $this->P->addMode('code',new Doku_Parser_Mode_Code());
    }

    function testCode() {
        $this->P->parse('Foo <code>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test',null,null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeBash() {
        $this->P->parse('Foo <code bash>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','bash',null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeDownload() {
        $this->P->parse('Foo <code bash script.sh>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','bash','script.sh')),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeToken() {
        $this->P->parse('Foo <code2>Bar</code2><code>Test</code>');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo <code2>Bar</code2>')),
            array('p_close',array()),
            array('code',array('Test',null,null)),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_OneOption() {
        $this->P->parse('Foo <code C [enable_line_numbers]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '5585858532b5e05a615a00b936bde16a',
                                     'enable_line_numbers' => 1
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_TwoOptions() {
        $this->P->parse('Foo <code C [enable_line_numbers, highlight_lines_extra="3"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => 'acc4b062686a5e93b70811478e3556e8',
                                     'enable_line_numbers' => 1,
                                     'highlight_lines_extra' => array(3)
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_UnknownOption() {
        $this->P->parse('Foo <code C [unknown="I will be deleted/ignored!"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null, null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_EnableLineNumbers1() {
        $this->P->parse('Foo <code C [enable_line_numbers]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '5585858532b5e05a615a00b936bde16a',
                                     'enable_line_numbers' => 1
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_EnableLineNumbers2() {
        $this->P->parse('Foo <code C [enable_line_numbers="GESHI_NORMAL_LINE_NUMBERS"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '3c620e51c2e8e3af4920c538179177aa',
                                     'enable_line_numbers' => 1
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_EnableLineNumbers3() {
        $this->P->parse('Foo <code C [enable_line_numbers="GESHI_FANCY_LINE_NUMBERS"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '8b7d3e7fba49c8df82d7c3c273296444',
                                     'enable_line_numbers' => 0
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_EnableLineNumbers4() {
        $this->P->parse('Foo <code C [enable_line_numbers="GESHI_NO_LINE_NUMBERS"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '80bd88409e303f141e7d1e1500080905',
                                     'enable_line_numbers' => 0
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_HighlightLinesExtra1() {
        $this->P->parse('Foo <code C [enable_line_numbers, highlight_lines_extra="42, 123, 456, 789"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '50fbd6c074a35cbee0e76731819fb085',
                                     'enable_line_numbers' => 1,
                                     'highlight_lines_extra' => array(42, 123, 456, 789)
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_HighlightLinesExtra2() {
        $this->P->parse('Foo <code C [enable_line_numbers, highlight_lines_extra]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => 'bfd1d8290b79b27923c63114aeed3ee6',
                                     'enable_line_numbers' => 1
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_HighlightLinesExtra3() {
        $this->P->parse('Foo <code C [enable_line_numbers, highlight_lines_extra=""]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => 'e9423868eaa15e33b6bc189f53412c3a',
                                     'enable_line_numbers' => 1
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_StartLineNumbersAt1() {
        $this->P->parse('Foo <code C [enable_line_numbers, [enable_line_numbers, start_line_numbers_at="42"]]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => 'f82658c3e9157076e3af310ae9640951',
                                     'enable_line_numbers' => 1,
                                     'start_line_numbers_at' => 42
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_StartLineNumbersAt2() {
        $this->P->parse('Foo <code C [enable_line_numbers, [enable_line_numbers, start_line_numbers_at]]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => 'ef59b4dc711f8949dc0115140a52457e',
                                     'enable_line_numbers' => 1,
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_StartLineNumbersAt3() {
        $this->P->parse('Foo <code C [enable_line_numbers, [enable_line_numbers, start_line_numbers_at=""]]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '9842cb5060cfc74d4e6a74b7580a7dcb',
                                     'enable_line_numbers' => 1,
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_EnableKeywordLinks1() {
        $this->P->parse('Foo <code C [enable_keyword_links="false"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => '7419a9eed9c667c3d31aaa2bd8df496e',
                                     'enable_keyword_links' => false
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeOptionsArray_EnableKeywordLinks2() {
        $this->P->parse('Foo <code C [enable_keyword_links="true"]>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','C', null,
                               array('md5' => 'c96401aa5cbdf6bbe108028cb0550d83',
                                     'enable_keyword_links' => true
                               ))),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

}

