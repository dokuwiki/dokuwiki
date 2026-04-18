<?php

use dokuwiki\Parsing\ParserMode\Code;

class TestOfDoku_Handler_ParseHighlightOptions extends DokuWikiTest {

    public function dataProvider() {
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
     * @dataProvider dataProvider
     * @param string $input options to parse
     * @param array|null $expect expected outcome
     */
    public function testOptionParser($input, $expect) {
        $code = new Code();
        $output = $this->callInaccessibleMethod($code, 'parseHighlightOptions', [$input]);

        $this->assertEquals($expect, $output);
    }
}
