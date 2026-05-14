<?php

/**
 * Test sorting
 *
 * Principle copied from _test/tests/lib/exe/ajax_requests.test.php
 *
 * @group ajax
 * @group plugin_indexmenu
 * @group plugins
 */
class AjaxRequestsTest extends DokuWikiTest
{
    public function setUp(): void
    {
        $this->pluginsEnabled[] = 'indexmenu';
        parent::setUp(); // this enables the indexmenu plugin

        //needed for 'tsort' to use First headings, sets title during search, otherwise as fallback page name used.
        global $conf;
        $conf['useheading'] = 'navigation';


        // for testing sorting pages
        saveWikiText('ns2:cpage', "======Bb======\nText", 'Sort different page/title/creation date');
        sleep(1); // ensure different timestamps for 'dsort'
        saveWikiText('ns2:bpage', "======Aa======\nText", 'Sort different page/title/creation date');
        sleep(1);
        saveWikiText('ns2:apage', "======Cc======\nText", 'Sort different page/title/creation date');

        //ensures title is added to metadata of page
        idx_addPage('ns2:cpage');
        idx_addPage('ns2:bpage');
        idx_addPage('ns2:apage');

        // pages on different levels
        saveWikiText('ns1:ns2:apage', "======Bb======\nPage on level 2", 'Created page on level 2');
        saveWikiText('ns1:ns1:apage', "======Ee======\nPage on level 2", 'Created page on level 2');
        saveWikiText('ns1:ns1:lvl3:lvl4:apage', "======Cc======\nPage on levl 4", 'Page on level 4');
        saveWikiText('ns1:ns1:start', "======Aa======\nPage on level 2", 'Startpage on level 2');
        saveWikiText('ns1:ns0:bpage', "======Aa2======\nPage on level 2", 'Created page on level 2');
        saveWikiText('ns1:apage', "======Dd======\nPage on level 1", 'Created page on level 1');

        //ensures title is added to metadata
        idx_addPage('ns1:ns1:apage');
        idx_addPage('ns1:ns1:lvl3:lvl4:apage');
        idx_addPage('ns1:ns1:start');
        idx_addPage('ns1:ns2:apage');
        idx_addPage('ns1:ns0:bpage');
        idx_addPage('ns1:apage');
    }

    /**
     * DataProvider for the builtin Ajax calls
     *
     * @return array
     */
    public static function indexmenuCalls()
    {
        return [
            // Call, POST parameters, result function
            [
                'indexmenu',
                AjaxRequestsTest::prepareParams(['level' => 1]),
                'expectedResultWiki'
            ],
            [
                'indexmenu',
                AjaxRequestsTest::prepareParams(['ns' => 'ns2', 'level' => 1]),
                'expectedResultNs2PageSort'
            ],
            [
                'indexmenu',
                AjaxRequestsTest::prepareParams(['ns' => 'ns2', 'level' => 1, 'sort' => 't']),
                'expectedResultNs2TitleSort'
            ],
            [
                'indexmenu',
                AjaxRequestsTest::prepareParams(['ns' => 'ns2', 'level' => 1, 'sort' => 'd']),
                'expectedResultNs2CreationDateSort'
            ],
            [
                'indexmenu',
                AjaxRequestsTest::prepareParams(['ns' => 'ns1', 'level' => 1, 'sort' => 't']),
                'expectedResultNs1TitleSort'
            ],
            [
                'indexmenu',
                AjaxRequestsTest::prepareParams(['ns' => 'ns1', 'level' => 1, 'sort' => 't', 'nsort' => 1]),
                'expectedResultNs1TitleSortNamespaceSort'
            ]
        ];
    }

    /**
     * @dataProvider indexmenuCalls
     *
     * @param string $call
     * @param array $post
     * @param $expectedResult
     */
    public function testBasicSorting($call, $post, $expectedResult)
    {
        $request = new TestRequest();
        $response = $request->post(['call' => $call] + $post, '/lib/exe/ajax.php');
//        $this->assertNotEquals("AJAX call '$call' unknown!\n", $response->getContent());

//var_export(json_decode($response->getContent()), true); // print as PHP array

        $actualArray = json_decode($response->getContent(), true);
        unset($actualArray['debug']);
        unset($actualArray['sort']);
        unset($actualArray['opts']);

        $this->assertEquals($this->$expectedResult(), $actualArray);

//        $regexp: null, or regexp pattern to match
//        example: '/^<div class="odd type_d/'
//        if (!empty($regexp)) {
//            $this->assertRegExp($regexp, $response->getContent());
//        }

    }

    public function test_params()
    {
//        print_r(AjaxRequestsTest::prepareParams(['level' => 2]));

        $this->assertTrue(true);
    }

    public static function prepareParams($params = [])
    {
        $defaults = [
            'ns' => 'wiki',
            'req' => 'fancytree',
            'level' => 1,
            'nons' => 0,
            'nopg' => 0,
            'max' => 0,
            'skipns' => ['/^board:(first|second|third|fourth|fifth)$/'],
            'skipfile' => ['/(:start$)/'],
            'sort' => 0,
            'msort' => 0,
            'rsort' => 0,
            'nsort' => 0,
            'hsort' => 0,
            'init' => 1
        ];
        $return = [];
        foreach ($defaults as $key => $default) {
            $return[$key] = $params[$key] ?? $default;
        }
        return $return;
    }

    public function expectedResultWiki()
    {
        return [
            'children' => [
                0 => [
                    'title' => 'dokuwiki',
                    'key' => 'wiki:dokuwiki',
                    'hns' => false,
                    'url' => '/./doku.php?id=wiki:dokuwiki'
                ],
                1 => [
                    'title' => 'syntax',
                    'key' => 'wiki:syntax',
                    'hns' => false,
                    'url' => '/./doku.php?id=wiki:syntax'
                ]
            ]];
    }

    public function expectedResultNs1()
    {
        return [
            'children' => [
                0 => [
                    'title' => 'dokuwiki',
                    'key' => 'wiki:dokuwiki',
                    'hns' => false,
                    'url' => '/./doku.php?id=wiki:dokuwiki'
                ],
                1 => [
                    'title' => 'syntax',
                    'key' => 'wiki:syntax',
                    'hns' => false,
                    'url' => '/./doku.php?id=wiki:syntax'
                ]
            ]];
    }

    public function expectedResultNs2PageSort()
    {
        return [
            'children' => [
                0 => [
                    'title' => 'Cc',
                    'key' => 'ns2:apage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:apage'
                ],
                1 => [
                    'title' => 'Aa',
                    'key' => 'ns2:bpage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:bpage'
                ],
                2 => [
                    'title' => 'Bb',
                    'key' => 'ns2:cpage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:cpage'
                ]
            ]];
    }

    public function expectedResultNs2TitleSort()
    {
        return [
            'children' => [
                0 => [
                    'title' => 'Aa',
                    'key' => 'ns2:bpage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:bpage'
                ],
                1 => [
                    'title' => 'Bb',
                    'key' => 'ns2:cpage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:cpage'
                ],
                2 => [
                    'title' => 'Cc',
                    'key' => 'ns2:apage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:apage'
                ]
            ]];
    }

    public function expectedResultNs2CreationDateSort()
    {
        return [
            'children' => [
                0 => [
                    'title' => 'Bb',
                    'key' => 'ns2:cpage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:cpage'
                ],
                1 => [
                    'title' => 'Aa',
                    'key' => 'ns2:bpage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:bpage'
                ],
                2 => [
                    'title' => 'Cc',
                    'key' => 'ns2:apage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns2:apage'
                ]
            ]];
    }

    public function expectedResultNs1TitleSort()
    {
        return [
            'children' => [
                0 => [
                    'title' => 'ns0',
                    'key' => 'ns1:ns0:',
                    'hns' => false,
                    'folder' => true,
                    'lazy' => true,
                    'url' => false
                ],
                1 => [
                    'title' => 'Aa',
                    'key' => 'ns1:ns1:',
                    'hns' => 'ns1:ns1:start',
                    'folder' => true,
                    'lazy' => true,
                    'url' => '/./doku.php?id=ns1:ns1:start'
                ],
                2 => [
                    'title' => 'ns2',
                    'key' => 'ns1:ns2:',
                    'hns' => false,
                    'folder' => true,
                    'lazy' => true,
                    'url' => false
                ],
                3 => [
                    'title' => 'Dd',
                    'key' => 'ns1:apage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns1:apage'
                ]
            ]];
    }

    public function expectedResultNs1TitleSortNamespaceSort()
    {
        // 'nsort' let the sort explicitly use the namespace name as sort key.
        // 'nsort' + 'tsort' works only for nsort if head pages are used.
        return [
            'children' => [
                0 => [
                    'title' => 'Aa',
                    'key' => 'ns1:ns1:',
                    'hns' => 'ns1:ns1:start',
                    'folder' => true,
                    'lazy' => true,
                    'url' => '/./doku.php?id=ns1:ns1:start'
                ],
                1 => [
                    'title' => 'Dd',
                    'key' => 'ns1:apage',
                    'hns' => false,
                    'url' => '/./doku.php?id=ns1:apage'
                ],
                2 => [
                    'title' => 'ns0',
                    'key' => 'ns1:ns0:',
                    'hns' => false,
                    'folder' => true,
                    'lazy' => true,
                    'url' => false
                ],
                3 => [
                    'title' => 'ns2',
                    'key' => 'ns1:ns2:',
                    'hns' => false,
                    'folder' => true,
                    'lazy' => true,
                    'url' => false
                ]
            ]];
    }
}
