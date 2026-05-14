<?php

use DOMWrap\Document;

require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * @group plugin_indexmenu
 */
class IndexmenuSyntaxTest extends DokuWikiTest
{

    public function setup(): void
    {
//        global $conf;
        $this->pluginsEnabled[] = 'indexmenu';
        parent::setup();

        //$conf['plugin']['indexmenu']['headpage'] = '';
        //$conf['plugin']['indexmenu']['hide_headpage'] = false;

        //saveWikiText('titleonly:sub:test', "====== Title ====== \n content", 'created');
        //saveWikiText('test', "====== Title ====== \n content", 'created');
        //idx_addPage('titleonly:sub:test');
        //idx_addPage('test');
    }

//    public function __construct() {
////        $this->exampleIndex = "{{indexmenu>:}}";
//
//        parent::__construct();
//    }

    /**
     * Create from list of values the output array of handle()
     *
     * @param array $values
     * @return array aligned similar to output of handle()
     */
    private function createData($values)
    {

        [
            $ns, $theme, $identifier, $nocookie, $navbar, $noscroll, $maxjs, $notoc, $jsajax, $context, $nomenu,
            $sort, $msort, $rsort, $nsort, $level, $nons, $nopg, $subnss, $max, $maxAjax, $js, $skipns, $skipfile,
            $skipnscombined, $skipfilecombined, $hsort, $headpage, $hide_headpage, $jsVersion
        ] = $values;

        return [
            $ns,
            [
                'theme' => $theme,
                'identifier' => $identifier,
                'nocookie' => $nocookie,
                'navbar' => $navbar,
                'noscroll' => $noscroll,
                'maxJs' => $maxjs,
                'notoc' => $notoc,
                'jsAjax' => $jsajax,
                'context' => $context,
                'nomenu' => $nomenu,
            ],
            [
                'sort' => $sort,
                'msort' => $msort,
                'rsort' => $rsort,
                'nsort' => $nsort,
                'hsort' => $hsort,
            ],
            [
                'level' => $level,
                'nons' => $nons,
                'nopg' => $nopg,
                'subnss' => $subnss,
                'max' => $max,
                'js' => $js,
                'skipns' => $skipns,
                'skipfile' => $skipfile,
                'skipnscombined' => $skipnscombined,
                'skipfilecombined' => $skipfilecombined,
                'headpage' => $headpage,
                'hide_headpage' => $hide_headpage,
                'maxajax' => $maxAjax,
                'navbar' => $navbar,
                'theme' => $theme
            ],
            $jsVersion
        ];
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public static function someSyntaxes()
    {
        return [
            //root ns (empty is not recognized..)
            // [syntax, data]
            [
                "{{indexmenu>:}}",
                [
                    '', 'default', 'random', false, false, false, 1, false, '', false, false,
                    0, false, false, false, -1, false, false, [], 0, 1, false, '', '', [''], [''], false,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=1, js renderer
            [
                "{{indexmenu>#1|js}}",
                [
                    '', 'default', 'random', false, false, false, 1, false, '', false, false,
                    0, false, false, false, 1, false, false, [], 0, 1, true, '', '', [''], [''], false,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=2, all not js specific options  (nocookie is from context)
            [
                "{{indexmenu>#2 test#6|navbar context tsort dsort msort hsort rsort nsort nons nopg}}",
                [
                    '', 'default', 'random', true, true, false, 1, false, '&sort=t&msort=indexmenu_n&rsort=1&nsort=1&hsort=1&nopg=1', true, false,
                    't', 'indexmenu_n', true, true, 2, true, true, [['test', 6]], 0, 1, false, '', '', [''], [''], true,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=2, js renderer, all not js specific options
            [
                "{{indexmenu>#2 test#6|navbar js#bj_ubuntu.png context tsort dsort msort hsort rsort nsort nons nopg}}",
                [
                    '', 'bj_ubuntu.png', 'random', true, true, false, 1, false, '&sort=t&msort=indexmenu_n&rsort=1&nsort=1&hsort=1&nopg=1', true, false,
                    't', 'indexmenu_n', true, true, 2, true, true, [['test', 6]], 0, 1, true, '', '', [''], [''], true,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=1, all options
            [
                "{{indexmenu>#1|navbar context nocookie noscroll notoc nomenu dsort msort#date:modified hsort rsort nsort nons nopg max#2#4 maxjs#3 id#54321}}",
                [
                    '', 'default', 'random', true, true, true, 1, true, '&sort=d&msort=date modified&rsort=1&nsort=1&hsort=1&nopg=1', true, true,
                    'd', 'date modified', true, true, 1, true, true, [], 0, 1, false, '', '', [''], [''], true,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=1, js renderer, all options
            [
                "{{indexmenu>#1|js#bj_ubuntu.png navbar context nocookie noscroll notoc nomenu dsort msort#date:modified hsort rsort nsort nons nopg max#2#4 maxjs#3 id#54321}}",
                [
                    '', 'bj_ubuntu.png', 54321, true, true, true, 3, true, '&sort=d&msort=date modified&rsort=1&nsort=1&hsort=1&nopg=1&max=4', true, true,
                    'd', 'date modified', true, true, 1, true, true, [], 2, 4, true, '', '', [''], [''], true,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=1, skipfile and ns

            [
                "{{indexmenu>#1 test|skipfile+/(^myusers:spaces$|privatens:userss)/ skipns=/(^myusers:spaces$|privatens:users)/ id#ns}}",
                [
                    '', 'default', 'random', false, false, false, 1, false, '&skipns=%3D/%28%5Emyusers%3Aspaces%24%7Cprivatens%3Ausers%29/&skipfile=%2B/%28%5Emyusers%3Aspaces%24%7Cprivatens%3Auserss%29/', false, false,
                    0, false, false, false, 1, false, false, [['test', -1]], 0, 1, false, '=/(^myusers:spaces$|privatens:users)/',
                    '+/(^myusers:spaces$|privatens:userss)/', ['/(^myusers:spaces$|privatens:users)/'], ['', '/(^myusers:spaces$|privatens:userss)/'], false,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ],
            //root ns, #levels=1, js renderer, skipfile and ns
            [
                "{{indexmenu>#1 test|js skipfile=/(^myusers:spaces$|privatens:userss)/ skipns+/(^myusers:spaces$|privatens:userssss)/ id#ns}}",
                [
                    '', 'default', 0, false, false, false, 1, false, '&skipns=%2B/%28%5Emyusers%3Aspaces%24%7Cprivatens%3Auserssss%29/&skipfile=%3D/%28%5Emyusers%3Aspaces%24%7Cprivatens%3Auserss%29/', false, false,
                    0, false, false, false, 1, false, false, [['test', -1]], 0, 1, true, '+/(^myusers:spaces$|privatens:userssss)/',
                    '=/(^myusers:spaces$|privatens:userss)/', ['', '/(^myusers:spaces$|privatens:userssss)/'], ['/(^myusers:spaces$|privatens:userss)/'], false,
                    ":start:,:same:,:inside:", 1, 1
                ]
            ]
        ];
    }

    /**
     * Parse the syntax to options
     * expect: different combinations with or without js option, covers recognizing all syntax options
     *
     * @dataProvider someSyntaxes
     */
    public function testHandle($syntax, $changedData)
    {
        $plugin = new syntax_plugin_indexmenu_indexmenu();

        $null = new Doku_Handler();
        $result = $plugin->handle($syntax, 0, 40, $null);

        //copy unique generated number, which is about 23 characters
        $len_id = strlen($result[1]['identifier']);
        if (!is_numeric($changedData[2]) && ($len_id > 18 && $len_id <= 23)) {
            $changedData[2] = $result[1]['identifier'];
        }
        $data = $this->createData($changedData);

        $this->assertEquals($data, $result, 'Data array corrupted');
    }


    /**
     * Data provider
     *
     * @return array[]
     */
    public static function differentNSs()
    {
        $pageInRoot = 'page';
        $pageInLvl1 = 'ns:page';
        $pageInLvl2 = 'ns1:ns2:page';
        return [
            //indexmenu on page at root level
            ['{{indexmenu>|}}', '', [], $pageInRoot],
            ['{{indexmenu>#1}}', '', [], $pageInRoot],
            ['{{indexmenu>:}}', '', [], $pageInRoot],
            ['{{indexmenu>.}}', '', [], $pageInRoot],
            ['{{indexmenu>.:}}', '', [], $pageInRoot],
            ['{{indexmenu>..}}', '', [], $pageInRoot],
            ['{{indexmenu>..:}}', '', [], $pageInRoot],
            ['{{indexmenu>myns}}', 'myns', [], $pageInRoot],
            ['{{indexmenu>:myns}}', 'myns', [], $pageInRoot],
            ['{{indexmenu>.myns}}', 'myns', [], $pageInRoot],
            ['{{indexmenu>.:myns}}', 'myns', [], $pageInRoot],
            ['{{indexmenu>..myns}}', 'myns', [], $pageInRoot],
            ['{{indexmenu>..:myns}}', 'myns', [], $pageInRoot],

            //indexmenu on page in a namespace
            ['{{indexmenu>|}}', '', [], $pageInLvl1],
            ['{{indexmenu>#1}}', '', [], $pageInLvl1],
            ['{{indexmenu>:}}', '', [], $pageInLvl1],
            ['{{indexmenu>.}}', 'ns', [], $pageInLvl1],
            ['{{indexmenu>.:}}', 'ns', [], $pageInLvl1],
            ['{{indexmenu>..}}', '', [], $pageInLvl1],
            ['{{indexmenu>..:}}', '', [], $pageInLvl1],
            ['{{indexmenu>myns}}', 'myns', [], $pageInLvl1], //was ns:myns
            ['{{indexmenu>:myns}}', 'myns', [], $pageInLvl1],
            ['{{indexmenu>.myns}}', 'ns:myns', [], $pageInLvl1],
            ['{{indexmenu>.:myns}}', 'ns:myns', [], $pageInLvl1],
            ['{{indexmenu>..myns}}', 'myns', [], $pageInLvl1],
            ['{{indexmenu>..:myns}}', 'myns', [], $pageInLvl1],
            ['{{indexmenu>myns:myns}}', 'myns:myns', [], $pageInLvl2],

            //indexmenu on page in a namespace
            ['{{indexmenu>|}}', '', [], $pageInLvl2],
            ['{{indexmenu>#1}}', '', [], $pageInLvl2],
            ['{{indexmenu>:}}', '', [], $pageInLvl2],
            ['{{indexmenu>.}}', 'ns1:ns2', [], $pageInLvl2],
            ['{{indexmenu>.:}}', 'ns1:ns2', [], $pageInLvl2],
            ['{{indexmenu>..}}', '', [], $pageInLvl2], //strange indexmenu specific exception! TODO remove?
            ['{{indexmenu>..:}}', 'ns1', [], $pageInLvl2],
            ['{{indexmenu>myns}}', 'myns', [], $pageInLvl2], //was ns1:ns2:myns
            ['{{indexmenu>:myns}}', 'myns', [], $pageInLvl2],
            ['{{indexmenu>.myns}}', 'ns1:ns2:myns', [], $pageInLvl2],
            ['{{indexmenu>.:myns}}', 'ns1:ns2:myns', [], $pageInLvl2],
            ['{{indexmenu>..myns}}', 'ns1:myns', [], $pageInLvl2],
            ['{{indexmenu>..:myns}}', 'ns1:myns', [], $pageInLvl2],
            ['{{indexmenu>myns:myns}}', 'myns:myns', [], $pageInLvl2],

            ['{{indexmenu>..:..:myns}}', 'ns1:myns', [], 'ns1:ns2:ns3:page'],
            ['{{indexmenu>0}}', '0', [], 'ns1:page'], //was ns1:0

            //indexmenu on page at root level and subns
            ['{{indexmenu> #1|}}', '', [], $pageInLvl2], //no subns, spaces before are removed
            ['{{indexmenu>#1 #1}}', '', [['', 1]], $pageInLvl2],
            ['{{indexmenu>: :}}', '', [['', -1]], $pageInLvl2],
            ['{{indexmenu>. .}}', 'ns1:ns2', [['ns1:ns2', -1]], $pageInLvl2],
            ['{{indexmenu>.: .:}}', 'ns1:ns2', [['ns1:ns2', -1]], $pageInLvl2],
            ['{{indexmenu>.. ..}}', '', [['', -1]], $pageInLvl2],
            ['{{indexmenu>..: ..:}}', 'ns1', [['ns1', -1]], $pageInLvl2],
            ['{{indexmenu>myns myns}}', 'myns', [['myns', -1]], $pageInLvl2], //was ns1:ns2:myns
            ['{{indexmenu>:myns :myns}}', 'myns', [['myns', -1]], $pageInLvl2],
            ['{{indexmenu>.myns .myns}}', 'ns1:ns2:myns', [['ns1:ns2:myns', -1]], $pageInLvl2],
            ['{{indexmenu>.:myns .:myns}}', 'ns1:ns2:myns', [['ns1:ns2:myns', -1]], $pageInLvl2],
            ['{{indexmenu>..myns ..myns}}', 'ns1:myns', [['ns1:myns', -1]], $pageInLvl2],
            ['{{indexmenu>..:myns ..myns}}', 'ns1:myns', [['ns1:myns', -1]], $pageInLvl2],
            ['{{indexmenu>myns:myns myns:myns}}', 'myns:myns', [['myns:myns', -1]], $pageInLvl2],

            //indexmenu on page in a namespace
            ['{{indexmenu>|}}', '', [], $pageInLvl2],
            ['{{indexmenu>#1}}', '', [], $pageInLvl2],
            ['{{indexmenu>:}}', '', [], $pageInLvl2],
            ['{{indexmenu>.}}', 'ns1:ns2', [], $pageInLvl2],
            ['{{indexmenu>.:}}', 'ns1:ns2', [], $pageInLvl2],
            ['{{indexmenu>..}}', '', [], $pageInLvl2], //strange indexmenu specific exception! TODO remove?
            ['{{indexmenu>..:}}', 'ns1', [], $pageInLvl2],
            ['{{indexmenu>myns:}}', 'myns', [], $pageInLvl2], //was ns1:ns2:myns
            ['{{indexmenu>:myns:}}', 'myns', [], $pageInLvl2],
            ['{{indexmenu>.myns:}}', 'ns1:ns2:myns', [], $pageInLvl2],
            ['{{indexmenu>.:myns:}}', 'ns1:ns2:myns', [], $pageInLvl2],
            ['{{indexmenu>..myns:}}', 'ns1:myns', [], $pageInLvl2],
            ['{{indexmenu>..:myns:}}', 'ns1:myns', [], $pageInLvl2],
            ['{{indexmenu>myns:myns:}}', 'myns:myns', [], $pageInLvl2],
        ];
    }

    /**
     * Parse the syntax to options
     * expect: different combinations with or without js option, covers recognizing all syntax options
     *
     * @dataProvider differentNSs
     */
    public function testResolving($syntax, $expectedNs, $expectedSubNss, $pageWithIndexmenu)
    {
        global $ID;
        $ID = $pageWithIndexmenu;

        $plugin = new syntax_plugin_indexmenu_indexmenu();

        $null = new Doku_Handler();
        $result = $plugin->handle($syntax, 0, 40, $null);

        $this->assertEquals($expectedNs, $result[0], 'check resolved ns');
        $this->assertEquals($expectedSubNss, $result[3]['subnss'], 'check resolved subNSs');
    }

    /**
     * Rendering for nonexisting namespace
     * expect: no paragraph due to no message set
     * expect: one paragraph, since message set
     * expect: contains namespace which replaced {{ns}}
     * expect: message contained rendered italic syntax
     */
    public function testRenderEmptymsg()
    {
        global $conf;

        $noexistns = 'nonexisting:namespace';
        $emptyindexsyntax = "{{indexmenu>$noexistns}}";

        $xhtml = new Doku_Renderer_xhtml();
        $plugin = new syntax_plugin_indexmenu_indexmenu();

        $null = new Doku_Handler();
        $result = $plugin->handle($emptyindexsyntax, 0, 10, $null);

        //no empty message
        $plugin->render('xhtml', $xhtml, $result);

        $doc = (new Document())->html($xhtml->doc);
        $this->assertEquals(0, $doc->find('p')->count());

        // Fill in empty message
        $conf['plugin']['indexmenu']['empty_msg'] = 'This namespace is //empty//: {{ns}}';
        $plugin->render('xhtml', $xhtml, $result);
        $doc = (new Document())->html($xhtml->doc);

        $this->assertEquals(1, $doc->find('p')->count());
//        $this->assertEquals(1, $doc->find("p:contains($noexistns)")->count());
        $this->assertEquals(1, $doc->find("p em")->count());
    }

}
