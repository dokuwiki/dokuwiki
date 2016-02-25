<?php

namespace plugin\struct\test;

use \plugin\struct\types\AbstractBaseType;
use plugin\struct\meta;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

class action_plugin_struct_entry extends \action_plugin_struct_entry {

    /**
     * Validate the given data
     *
     * Catches the Validation exceptions and transforms them into proper messages.
     *
     * Blank values are not validated and always pass
     *
     * @param AbstractBaseType $type
     * @param string $label
     * @param array|string|int $data
     * @return bool true if the data validates, otherwise false
     */
    public function validate(AbstractBaseType $type, $label, $data) {
        return parent::validate($type, $label, $data);
    }

    /**
     * Create the form to edit schemadata
     *
     * @param string $tablename
     * @return string The HTML for this schema's form
     */
    public function createForm($tablename) {
        return parent::createForm($tablename);
    }

    public static function getVAR() {
        return self::$VAR;
    }

}

/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 * @covers action_plugin_struct_entry
 *
 *
 */
class entry_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct','sqlite');
    protected $lang;

    public function setUp() {
        parent::setUp();

        $sb = new meta\SchemaBuilder(
            'schema1',
            array(
                'new' => array(
                    'new1' => array('label' => 'first', 'class' => 'Text', 'sort' => 10, 'ismulti' => 0, 'isenabled' => 1),
                    'new2' => array('label' => 'second', 'class' => 'Text', 'sort' => 20, 'ismulti' => 1, 'isenabled' => 1),
                    'new3' => array('label' => 'third', 'class' => 'Text', 'sort' => 30, 'ismulti' => 0, 'isenabled' => 1),
                    'new4' => array('label' => 'fourth', 'class' => 'Text', 'sort' => 40, 'ismulti' => 0, 'isenabled' => 1)
                )
            )
        );
        $sb->build();

        $sb = new meta\SchemaBuilder(
            'schema2',
            array(
                'new' => array(
                    'new1' => array('label' => 'afirst', 'class' => 'Text', 'sort' => 10, 'ismulti' => 0, 'isenabled' => 1),
                    'new2' => array('label' => 'asecond', 'class' => 'Text', 'sort' => 20, 'ismulti' => 1, 'isenabled' => 1),
                    'new3' => array('label' => 'athird', 'class' => 'Text', 'sort' => 30, 'ismulti' => 0, 'isenabled' => 1),
                    'new4' => array('label' => 'afourth', 'class' => 'Integer', 'sort' => 40, 'ismulti' => 0, 'isenabled' => 1)
                )
            )
        );
        $sb->build();

        $sd = new meta\SchemaData('schema1', 'page01', time());
        $sd->saveData(
            array(
                'first' => 'first data',
                'second' => array('second data', 'more data', 'even more'),
                'third' => 'third data',
                'fourth' => 'fourth data'
            )
        );

        $path = DOKU_PLUGIN . 'struct/lang/';
        $lang = array();
        // don't include once, in case several plugin components require the same language file
        @include($path . 'en/lang.php');
        $this->lang = $lang;
    }

    public function tearDown() {
        parent::tearDown();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
    }

    public function test_createForm_storedData() {
        $entry = new action_plugin_struct_entry();
        global $ID;
        $ID = 'page01';
        $test_html = $entry->createForm('schema1');

        $this->assertContains('<legend>schema1</legend>', $test_html);
        $this->assertContains('first', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][first]" value="first data" />', $test_html);
        $this->assertContains('second', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][second]" value="second data, more data, even more" />', $test_html);
        $this->assertContains('third', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][third]" value="third data" />', $test_html);
        $this->assertContains('fourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][fourth]" value="fourth data" />', $test_html);
    }

    public function test_createForm_emptyData() {
        $entry = new action_plugin_struct_entry();
        global $ID;
        $ID = 'page02';
        $test_html = $entry->createForm('schema1');

        $this->assertContains('<legend>schema1</legend>', $test_html);
        $this->assertContains('first', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][first]" value="" />', $test_html);
        $this->assertContains('second', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][second]" value="" />', $test_html);
        $this->assertContains('third', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][third]" value="" />', $test_html);
        $this->assertContains('fourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][fourth]" value="" />', $test_html);
    }

    public function test_createForm_postData() {
        global $INPUT, $ID;
        $ID = 'page01';
        $structdata = array('schema1' => array(
            'first' => 'first post data',
            'second' => array('second post data', 'more post data', 'even more post data'),
            'third' => 'third post data',
            'fourth' => 'fourth post data'
        ));
        $INPUT->set(action_plugin_struct_entry::getVAR(),$structdata);

        $entry = new action_plugin_struct_entry();
        $test_html = $entry->createForm('schema1');

        $this->assertContains('<legend>schema1</legend>', $test_html);
        $this->assertContains('first', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][first]" value="first post data" />', $test_html);
        $this->assertContains('second', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][second]" value="second post data, more post data, even more post data" />', $test_html);
        $this->assertContains('third', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][third]" value="third post data" />', $test_html);
        $this->assertContains('fourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[schema1][fourth]" value="fourth post data" />', $test_html);
    }

    public function test_validate_nonArray() {
        global $MSG;
        $label = 'label';
        $errormsg = sprintf($this->lang['validation_prefix'] . $this->lang['Validation Exception Integer needed'],$label);
        $integer = new \plugin\struct\types\Integer();
        $entry = new action_plugin_struct_entry();

        $entry->validate($integer, $label, 'NaN');

        $this->assertEquals(array('lvl' => 'error', 'msg' => $errormsg, 'allow' => 0),$MSG[0]);
    }

    public function test_validate_array() {
        global $MSG;
        $label = 'label';
        $errormsg = sprintf($this->lang['validation_prefix'] . $this->lang['Validation Exception Integer needed'],$label);
        $integer = new \plugin\struct\types\Integer();
        $entry = new action_plugin_struct_entry();

        $entry->validate($integer, $label, array('NaN','NaN'));

        $this->assertEquals(array('lvl' => 'error', 'msg' => $errormsg, 'allow' => 0),$MSG[0]);
        $this->assertEquals(array('lvl' => 'error', 'msg' => $errormsg, 'allow' => 0),$MSG[1]);
    }

    public function test_validate_blank() {
        global $MSG;
        $integer = new \plugin\struct\types\Integer();
        $entry = new action_plugin_struct_entry();

        $entry->validate($integer, 'label', null);

        $this->assertEquals(null,$MSG);
    }

    public function test_edit_page_wo_schema() {
        $page = 'test_edit_page_wo_schema';

        $request = new \TestRequest();
        $response = $request->get(array('id' => $page, 'do' => 'edit'), '/doku.php');
        $structElement = $response->queryHTML('.struct');

        $this->assertEquals(1,count($structElement));
        $this->assertEquals($structElement->html(),'');
    }

    public function test_edit_page_with_schema() {
        $page = 'test_edit_page_with_schema';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);

        $request = new \TestRequest();
        $response = $request->get(array('id' => $page, 'do' => 'edit'), '/doku.php');
        $test_html = trim($response->queryHTML('.struct')->html());

        $this->assertContains('<legend>Schema2</legend>', $test_html);
        $this->assertContains('afirst', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afirst]" value="">', $test_html);
        $this->assertContains('asecond', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][asecond]" value="">', $test_html);
        $this->assertContains('athird', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][athird]" value="">', $test_html);
        $this->assertContains('afourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afourth]" value="">', $test_html);
    }

    public function test_preview_page_invaliddata() {
        $page = 'test_preview_page_invaliddata';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);

        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => 'Eve'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $response = $request->post(array('id' => $page, 'do' => 'preview'), '/doku.php');
        $expected_errormsg = sprintf($this->lang['validation_prefix'] . $this->lang['Validation Exception Integer needed'],'afourth');
        $actual_errormsg = $response->queryHTML('.error')->html();
        $test_html = trim($response->queryHTML('.struct')->html());

        $this->assertEquals($expected_errormsg, $actual_errormsg, 'If there is invalid data, then there should be an error message.');
        $this->assertContains('<legend>Schema2</legend>', $test_html);
        $this->assertContains('afirst', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afirst]" value="foo">', $test_html);
        $this->assertContains('asecond', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][asecond]" value="bar, baz">', $test_html);
        $this->assertContains('athird', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][athird]" value="foobar">', $test_html);
        $this->assertContains('afourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afourth]" value="Eve">', $test_html);
    }

    public function test_preview_page_validdata() {
        $page = 'test_preview_page_validdata';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);

        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => '42'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $response = $request->post(array('id' => $page, 'do' => 'preview'), '/doku.php');
        $actual_errormsg = $response->queryHTML('.error')->get();
        $test_html = trim($response->queryHTML('.struct')->html());

        $this->assertEquals($actual_errormsg,array(), "If all data is valid, then there should be no error message.");
        $this->assertContains('<legend>Schema2</legend>', $test_html);
        $this->assertContains('afirst', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afirst]" value="foo">', $test_html);
        $this->assertContains('asecond', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][asecond]" value="bar, baz">', $test_html);
        $this->assertContains('athird', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][athird]" value="foobar">', $test_html);
        $this->assertContains('afourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afourth]" value="42">', $test_html);
    }

    public function test_fail_saving_empty_page() {
        $page = 'test_fail_saving_empty_page';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);

        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => '42'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('summary','only struct data saved');
        $response = $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');
        $expected_errormsg = $this->lang['emptypage'];
        $actual_errormsg = $response->queryHTML('.error')->html();
        $pagelog = new \PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);

        $this->assertEquals(0, count($revisions));
        $this->assertEquals($expected_errormsg,$actual_errormsg, "An empty page should not be saved.");
    }

    public function test_fail_saveing_page_with_invaliddata() {
        $page = 'test_fail_saveing_page_with_invaliddata';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);

        $wikitext = 'teststring';
        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => 'Eve'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('wikitext',$wikitext);
        $request->setPost('summary','content and struct data saved');
        $response = $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');
        $actual_wikitext = trim($response->queryHTML('#wiki__text')->html());
        $expected_wikitext = $wikitext;

        $actual_errormsg = $response->queryHTML('.error')->html();
        $expected_errormsg = sprintf($this->lang['validation_prefix'] . $this->lang['Validation Exception Integer needed'],'afourth');

        $test_html = trim($response->queryHTML('.struct')->html());

        $pagelog = new \PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);

        // assert
        $this->assertEquals(0, count($revisions));
        $this->assertEquals($expected_errormsg, $actual_errormsg, 'If there is invalid data, then there should be an error message.');
        $this->assertEquals($expected_wikitext,$actual_wikitext);

        $this->assertContains('<legend>Schema2</legend>', $test_html);
        $this->assertContains('afirst', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afirst]" value="foo">', $test_html);
        $this->assertContains('asecond', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][asecond]" value="bar, baz">', $test_html);
        $this->assertContains('athird', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][athird]" value="foobar">', $test_html);
        $this->assertContains('afourth', $test_html);
        $this->assertContains('<input name="struct_schema_data[Schema2][afourth]" value="Eve">', $test_html);

        // todo: assert that no struct data has been saved
    }

    public function test_save_page() {
        $page = 'test_save_page';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);

        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => '42'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('wikitext','teststring');
        $request->setPost('summary','content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        $pagelog = new \PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = new meta\SchemaData($schema, $page, 0);
        $actual_struct_data = $schemaData->getDataArray();
        $expected_struct_data = array(
            'afirst' => 'foo',
            'asecond' => array('bar', 'baz'),
            'athird' => 'foobar',
            'afourth' => 42
        );

        $this->assertEquals(1, count($revisions));
        $this->assertEquals('content and struct data saved', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals($expected_struct_data, $actual_struct_data);
        // todo: assert that pagerevision and struct data have the same timestamp
    }

    /**
     * @group slow
     */
    public function test_save_page_without_new_text() {
        $page = 'test_save_page_without_new_text';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);
        $wikitext = 'teststring';

        // first save;
        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => '42'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('wikitext',$wikitext);
        $request->setPost('summary','content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        sleep(1);

        // second save - only struct data
        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo2',
                'asecond' => 'bar2, baz2',
                'athird' => 'foobar2',
                'afourth' => '43'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('wikitext',$wikitext);
        $request->setPost('summary','2nd revision');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        // assert
        $pagelog = new \PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = new meta\SchemaData($schema, $page, 0);
        $actual_struct_data = $schemaData->getDataArray();
        $expected_struct_data = array(
            'afirst' => 'foo2',
            'asecond' => array('bar2', 'baz2'),
            'athird' => 'foobar2',
            'afourth' => 43
        );

        $this->assertEquals(2, count($revisions), 'there should be 2 (two) revisions');
        $this->assertEquals('2nd revision', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals($expected_struct_data, $actual_struct_data);
        // todo: assert that pagerevisions and struct entries have the same timestamps
    }


    /**
     * @group slow
     */
    public function test_delete_page() {
        $page = 'test_delete_page';
        $assignment = new meta\Assignments();
        $schema = 'Schema2';
        $assignment->addPattern($page, $schema);
        $wikitext = 'teststring';

        // first save;
        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo',
                'asecond' => 'bar, baz',
                'athird' => 'foobar',
                'afourth' => '42'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('wikitext',$wikitext);
        $request->setPost('summary','content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        sleep(1);

        // delete
        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo2',
                'asecond' => 'bar2, baz2',
                'athird' => 'foobar2',
                'afourth' => '43'
            )
        );
        $request->setPost('struct_schema_data',$structData);
        $request->setPost('wikitext','');
        $request->setPost('summary','delete page');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        // assert
        $pagelog = new \PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = new meta\SchemaData($schema, $page, 0);
        $actual_struct_data = $schemaData->getDataArray();
        $expected_struct_data = array(
            'afirst' => '',
            'asecond' => array(),
            'athird' => '',
            'afourth' => ''
        );

        $this->assertEquals(2, count($revisions), 'there should be 2 (two) revisions');
        $this->assertEquals('delete page', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);
        $this->assertEquals($expected_struct_data, $actual_struct_data);
        // todo: timestamps?
    }
}
