<?php

class actions_act_clean_test extends DokuWikiTest {

    /**
     * Requested actions and their normalized form
     *
     * @return array
     */
    public function data() {
        return [
            // input, expected
            ['media', 'media'],
            ['MEDIA', 'media'], // case is folded, not rejected
            ['  media  ', 'media'],
            ['me<>dia', 'media'],
            ['export_raw', 'export_raw'],
            ['export_html', 'export_xhtml'],
            ['export_htmlbody', 'export_xhtmlbody'],
            ['dw2pdf_export0', 'dw2pdf_export0'], // digits are kept
            [['save' => 1], 'save'], // image buttons submit the action as array key
            [null, 'show'],
            ['', 'show'],
            ['+++', 'show'],
        ];
    }

    /**
     * @dataProvider data
     * @param array|string|null $input
     * @param string $expected
     */
    public function test_act_clean($input, $expected) {
        $this->assertSame($expected, act_clean($input));
    }
}
