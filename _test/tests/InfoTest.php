<?php

namespace dokuwiki\test;

class InfoTest extends \DokuWikiTest
{

    /**
     * @see testParseVersionString
     */
    public function provideVersions()
    {
        return [
            [
                'rc2010-10-07 "Lazy Sunday"',
                [
                    'type' => 'rc',
                    'date' => '2010-10-07',
                    'hotfix' => '',
                    'version' => '2010-10-07rc',
                    'codename' => 'Lazy Sunday',
                    'raw' => 'rc2010-10-07 "Lazy Sunday"',
                ],
            ],
            [
                '2017-02-19d "Frusterick Manners"',
                [
                    'type' => 'stable',
                    'date' => '2017-02-19',
                    'hotfix' => 'd',
                    'version' => '2017-02-19d',
                    'codename' => 'Frusterick Manners',
                    'raw' => '2017-02-19d "Frusterick Manners"',
                ],
            ],
            [
                '2017-02-19 "Frusterick Manners"',
                [
                    'type' => 'stable',
                    'date' => '2017-02-19',
                    'hotfix' => '',
                    'version' => '2017-02-19',
                    'codename' => 'Frusterick Manners',
                    'raw' => '2017-02-19 "Frusterick Manners"',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideVersions
     */
    public function testParseVersionString($version, $expected)
    {
        $this->assertEquals($expected, \dokuwiki\Info::parseVersionString($version));
    }

}
