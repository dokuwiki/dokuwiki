<?php

class html_scedit_pattern_test extends DokuWikiTest {


    public function dataProviderForTestSecEditPattern() {
        return [
            [
                '<!-- EDIT5 SECTION "Plugins" "plugins" [1406-] -->',
                [
                    'secid' => '5',
                    'target' => 'SECTION',
                    'name' => 'Plugins',
                    'hid' => 'plugins',
                    'range' => '1406-',
                ],
                'basic section edit',
            ],
            [
                '<!-- EDIT10 TABLE "" "table4" [11908-14014] -->',
                [
                    'secid' => '10',
                    'target' => 'TABLE',
                    'name' => '',
                    'hid' => 'table4',
                    'range' => '11908-14014',
                ],
                'table edit'
            ],
            [
                '<!-- EDIT2 PLUGIN_DATA [27-432] -->',
                [
                    'secid' => '2',
                    'target' => 'PLUGIN_DATA',
                    'name' => '',
                    'hid' => '',
                    'range' => '27-432',
                ],
                'data plugin'
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestSecEditPattern
     *
     * @param $text
     * @param $expectedMatches
     * @param $msg
     */
    public function testSecEditPattern($text, $expectedMatches, $msg) {
        preg_match(SEC_EDIT_PATTERN, $text, $matches);
        foreach ($expectedMatches as $key => $expected_value) {
            $this->assertSame($expected_value, $matches[$key], $msg);
        }
    }

}
