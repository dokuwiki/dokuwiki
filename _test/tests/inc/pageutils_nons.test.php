<?php

class init_noNS_test extends DokuWikiTest {

    /**
     * @dataProvider noNSProvider
     */
    public function test_noNS($input, $expected)
    {
        global $conf;
        $conf['start'] = 'start';

        $this->assertSame($expected, noNS($input), $input);
    }

    public function noNSProvider()
    {
        return [
            ['0:0:0', '0'],
            ['0:0:start', 'start'],
            ['foo:0', '0'],
            ['0', '0'],
        ];
    }

    /**
     * @dataProvider noNSorNSProvider
     */
    public function test_noNSorNS($input, $expected)
    {
        global $conf;
        $conf['start'] = 'start';

        $this->assertSame($expected, noNSorNS($input), $input);
    }

    public function noNSorNSProvider()
    {
        return [
            ['0:0:0', '0'],
            ['0:0:start', '0'],
            ['0:start', '0'],
            ['0:foo', 'foo'],
            ['foo:0', '0'],
            ['0', '0'],
            ['0:', '0'],
            ['a:b:', 'b'], // breadcrumbs code passes IDs ending with a colon #3114
        ];
    }

}
//Setup VIM: ex: et ts=4 :
