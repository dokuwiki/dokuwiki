<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * @group plugin_struct
 * @group plugins
 *
 * @ covers \action_plugin_struct_output
 */
class output_struct_test extends StructTest {

    /** @var array add the extra plugins */
    protected $pluginsEnabled = array('struct', 'sqlite', 'log', 'include');

    public function setUp() {
        parent::setUp();

        $this->loadSchemaJSON('schema1');
        $page = 'page01';
        $includedPage = 'foo';
        $this->saveData(
            $page,
            'schema1',
            array(
                'first' => 'first data',
                'second' => array('second data', 'more data', 'even more'),
                'third' => 'third data',
                'fourth' => 'fourth data'
            )
        );
        $this->saveData(
            $includedPage,
            'schema1',
            array(
                'first' => 'first data',
                'second' => array('second data', 'more data', 'even more'),
                'third' => 'third data',
                'fourth' => 'fourth data'
            )
        );
    }

    public function test_output() {
        global $ID;
        $page = 'page01';
        $ID = $page;

        saveWikiText($page, "====== abc ======\ndef",'');
        $instructions = p_cached_instructions(wikiFN($page), false, $page);
        $this->assertEquals('document_start', $instructions[0][0]);
        $this->assertEquals('header', $instructions[1][0]);
        $this->assertEquals('plugin', $instructions[2][0]);
        $this->assertEquals('struct_output', $instructions[2][1][0]);
    }

    public function test_include_missing_output() {
        global $ID;
        $page = 'page01';
        $includedPage = 'foo';

        saveWikiText($page, "====== abc ======\n{{page>foo}}\n", '');
        saveWikiText($includedPage, "====== included page ======\nqwe\n",'');


        plugin_load('action', 'struct_output', true);
        $ID = $page;
        $insMainPage = p_cached_instructions(wikiFN($page), false, $page);
        $this->assertEquals('document_start', $insMainPage[0][0]);
        $this->assertEquals('header', $insMainPage[1][0]);
        $this->assertEquals('plugin', $insMainPage[2][0]);
        $this->assertEquals('struct_output', $insMainPage[2][1][0]);

        plugin_load('action', 'struct_output', true);
        $ID = $includedPage;
        $insIncludedPage = p_cached_instructions(wikiFN($includedPage), false, $includedPage);
        $this->assertEquals('document_start', $insIncludedPage[0][0]);
        $this->assertEquals('header', $insIncludedPage[1][0]);
        $this->assertEquals('plugin', $insIncludedPage[2][0]);
        $this->assertEquals('struct_output', $insIncludedPage[2][1][0]);

    }

    public function test_log_conflict() {
        global $ID;
        $page = 'page01';
        $ID = $page;

        saveWikiText($page, "====== abc ======\n{{log}}\n", '');
        saveWikiText($page.':log', '====== abc log ======
Log for [[page01]]:

  * 2017-02-24 10:54:13 //Example User//: foo bar','');
        $instructions = p_cached_instructions(wikiFN($page), false, $page);
        $this->assertEquals('document_start', $instructions[0][0]);
        $this->assertEquals('header', $instructions[1][0]);
        $this->assertEquals('plugin', $instructions[2][0]);
        $this->assertEquals('struct_output', $instructions[2][1][0]);
    }
}
