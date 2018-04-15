<?php

class html_scedit_pattern_test extends DokuWikiTest {


    public function dataProviderForTestSecEditPattern() {
        return [
            [
                '<!-- EDIT{"target":"SECTION","name":"Plugins","hid":"plugins","codeblockOffset":0,"secid":5,"range":"1406-"} -->',
                [
                    'secid' => 5,
                    'target' => 'SECTION',
                    'name' => 'Plugins',
                    'hid' => 'plugins',
                    'range' => '1406-',
                ],
                'basic section edit',
            ],
            [
                '<!-- EDIT{"target":"TABLE","name":"","hid":"table4","codeblockOffset":0,"secid":10,"range":"11908-14014"} -->',
                [
                    'secid' => 10,
                    'target' => 'TABLE',
                    'name' => '',
                    'hid' => 'table4',
                    'range' => '11908-14014',
                ],
                'table edit'
            ],
            [
                '<!-- EDIT{"target":"PLUGIN_DATA","name":"","hid":"","codeblockOffset":0,"secid":2,"range":"27-432"} -->',
                [
                    'secid' => 2,
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
        $data = json_decode($matches[1], true);
        foreach ($expectedMatches as $key => $expected_value) {
            $this->assertSame($expected_value, $data[$key], $msg);
        }
    }

    public function testSecEditHTMLInjection() {
        $ins = p_get_instructions("====== Foo ======\n\n===== } --> <script> =====\n\n===== Bar =====\n");
        $info = array();
        $xhtml = p_render('xhtml', $ins, $info);

        $this->assertNotNull($xhtml);

        $xhtml_without_secedit = html_secedit($xhtml, false);

        $this->assertFalse(strpos($xhtml_without_secedit, '<script>'), 'Plain <script> tag found in output - HTML/JS injection might be possible!');
    }
}
