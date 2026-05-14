<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Code;

/**
 * Tests to ensure functionality of the <code> syntax tag.
 *
 * @group parser_code
 */
class CodeTest extends ParserTestBase
{

    function setUp() : void {
        parent::setUp();
        $this->P->addMode('code',new Code());
    }

    function testCode() {
        $this->P->parse('Foo <code>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test',null,null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeBash() {
        $this->P->parse('Foo <code bash>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','bash',null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeDownload() {
        $this->P->parse('Foo <code bash script.sh>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','bash','script.sh']],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeToken() {
        $this->P->parse('Foo <code2>Bar</code2><code>Test</code>');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo <code2>Bar</code2>']],
            ['p_close',[]],
            ['code',['Test',null,null]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_OneOption() {
        $this->P->parse('Foo <code C [enable_line_numbers]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => 1]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_TwoOptions() {
        $this->P->parse('Foo <code C [enable_line_numbers highlight_lines_extra="3"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'highlight_lines_extra' => [3]
                               ]]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_UnknownOption() {
        $this->P->parse('Foo <code C [unknown="I will be deleted/ignored!"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null, null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_EnableLineNumbers1() {
        $this->P->parse('Foo <code C [enable_line_numbers]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_EnableLineNumbers2() {
        $this->P->parse('Foo <code C [enable_line_numbers="1"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_EnableLineNumbers3() {
        $this->P->parse('Foo <code C [enable_line_numbers="0"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => false]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_EnableLineNumbers4() {
        $this->P->parse('Foo <code C [enable_line_numbers=""]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_HighlightLinesExtra1() {
        $this->P->parse('Foo <code C [enable_line_numbers highlight_lines_extra="42, 123, 456, 789"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'highlight_lines_extra' => [42, 123, 456, 789]
                               ]]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_HighlightLinesExtra2() {
        $this->P->parse('Foo <code C [enable_line_numbers highlight_lines_extra]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'highlight_lines_extra' => [1]]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_HighlightLinesExtra3() {
        $this->P->parse('Foo <code C [enable_line_numbers highlight_lines_extra=""]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'highlight_lines_extra' => [1]]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_StartLineNumbersAt1() {
        $this->P->parse('Foo <code C [enable_line_numbers [enable_line_numbers start_line_numbers_at="42"]]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'start_line_numbers_at' => 42]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_StartLineNumbersAt2() {
        $this->P->parse('Foo <code C [enable_line_numbers [enable_line_numbers start_line_numbers_at]]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'start_line_numbers_at' => 1]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_StartLineNumbersAt3() {
        $this->P->parse('Foo <code C [enable_line_numbers [enable_line_numbers start_line_numbers_at=""]]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_line_numbers' => true,
                                     'start_line_numbers_at' => 1]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_EnableKeywordLinks1() {
        $this->P->parse('Foo <code C [enable_keyword_links="false"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_keyword_links' => false]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeOptionsArray_EnableKeywordLinks2() {
        $this->P->parse('Foo <code C [enable_keyword_links="true"]>Test</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['Test','C', null,
                               ['enable_keyword_links' => true]
                               ]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function highlightOptionsProvider() {
        return [
            ['', null],
            ['something weird', null],
            ['enable_line_numbers', ['enable_line_numbers' => true]],
            ['enable_line_numbers=1', ['enable_line_numbers' => true]],
            ['enable_line_numbers="1"', ['enable_line_numbers' => true]],
            ['enable_line_numbers=0', ['enable_line_numbers' => false]],
            ['enable_line_numbers="0"', ['enable_line_numbers' => false]],
            ['enable_line_numbers=false', ['enable_line_numbers' => false]],
            ['enable_line_numbers="false"', ['enable_line_numbers' => false]],
            ['highlight_lines_extra', ['highlight_lines_extra' => [1]]],
            ['highlight_lines_extra=17', ['highlight_lines_extra' => [17]]],
            ['highlight_lines_extra=17,19', ['highlight_lines_extra' => [17, 19]]],
            ['highlight_lines_extra="17,19"', ['highlight_lines_extra' => [17, 19]]],
            ['highlight_lines_extra="17,19,17"', ['highlight_lines_extra' => [17, 19]]],
            ['start_line_numbers_at', ['start_line_numbers_at' => 1]],
            ['start_line_numbers_at=12', ['start_line_numbers_at' => 12]],
            ['start_line_numbers_at="12"', ['start_line_numbers_at' => 12]],
            ['enable_keyword_links', ['enable_keyword_links' => true]],
            ['enable_keyword_links=1', ['enable_keyword_links' => true]],
            ['enable_keyword_links="1"', ['enable_keyword_links' => true]],
            ['enable_keyword_links=0', ['enable_keyword_links' => false]],
            ['enable_keyword_links="0"', ['enable_keyword_links' => false]],
            ['enable_keyword_links=false', ['enable_keyword_links' => false]],
            ['enable_keyword_links="false"', ['enable_keyword_links' => false]],
            [
                'enable_line_numbers weird nothing highlight_lines_extra=17,19 start_line_numbers_at="12" enable_keyword_links=false',
                [
                    'enable_line_numbers' => true,
                    'highlight_lines_extra' => [17, 19],
                    'start_line_numbers_at' => 12,
                    'enable_keyword_links' => false
                ]
            ],
        ];
    }

    /**
     * @dataProvider highlightOptionsProvider
     * @param string $input options to parse
     * @param array|null $expect expected outcome
     */
    public function testHighlightOptionParser($input, $expect) {
        $code = new Code();
        $output = $this->callInaccessibleMethod($code, 'parseHighlightOptions', [$input]);
        $this->assertEquals($expect, $output);
    }
}
