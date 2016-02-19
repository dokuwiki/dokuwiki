<?php

namespace plugin\struct\test;

use \plugin\struct\types\AbstractBaseType;
use plugin\struct\meta;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader',));

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
                    'new1' => array('label' => 'first', 'class' => 'Text', 'sort' => 10, 'ismulti' => 0),
                    'new2' => array('label' => 'second', 'class' => 'Text', 'sort' => 20, 'ismulti' => 1),
                    'new3' => array('label' => 'third', 'class' => 'Text', 'sort' => 30, 'ismulti' => 0),
                    'new4' => array('label' => 'fourth', 'class' => 'Text', 'sort' => 40, 'ismulti' => 0),
                )
            )
        );
        $sb->build();

        $sb = new meta\SchemaBuilder(
            'schema2',
            array(
                'new' => array(
                    'new1' => array('label' => 'afirst', 'class' => 'Text', 'sort' => 10, 'ismulti' => 0),
                    'new2' => array('label' => 'asecond', 'class' => 'Text', 'sort' => 20, 'ismulti' => 1),
                    'new3' => array('label' => 'athird', 'class' => 'Text', 'sort' => 30, 'ismulti' => 0),
                    'new4' => array('label' => 'afourth', 'class' => 'Text', 'sort' => 40, 'ismulti' => 0),
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
        $expected_html = '<h3>schema1</h3><label>first <input name="struct_schema_data[schema1][first]" value="first data" /></label><br /><label>second <input name="struct_schema_data[schema1][second]" value="second data, more data, even more" /></label><br /><label>third <input name="struct_schema_data[schema1][third]" value="third data" /></label><br /><label>fourth <input name="struct_schema_data[schema1][fourth]" value="fourth data" /></label><br />';
        $this->assertEquals($expected_html, $test_html);
    }

    public function test_createForm_emptyData() {
        $entry = new action_plugin_struct_entry();
        global $ID;
        $ID = 'page02';
        $test_html = $entry->createForm('schema1');
        $expected_html = '<h3>schema1</h3><label>first <input name="struct_schema_data[schema1][first]" value="" /></label><br /><label>second <input name="struct_schema_data[schema1][second]" value="" /></label><br /><label>third <input name="struct_schema_data[schema1][third]" value="" /></label><br /><label>fourth <input name="struct_schema_data[schema1][fourth]" value="" /></label><br />';
        $this->assertEquals($expected_html, $test_html);
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
        $expected_html = '<h3>schema1</h3><label>first <input name="struct_schema_data[schema1][first]" value="first post data" /></label><br /><label>second <input name="struct_schema_data[schema1][second]" value="second post data, more post data, even more post data" /></label><br /><label>third <input name="struct_schema_data[schema1][third]" value="third post data" /></label><br /><label>fourth <input name="struct_schema_data[schema1][fourth]" value="fourth post data" /></label><br />';
        $this->assertEquals($expected_html, $test_html);
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
}
