<?php

namespace plugin\struct\test;

use plugin\struct\meta;
use plugin\struct\types\Integer;
use plugin\struct\types\Text;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class Validator_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');
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

    protected function tearDown() {
        parent::tearDown();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
    }


    public function test_validate_nonArray() {
        $label = 'label';
        $errormsg = sprintf($this->lang['validation_prefix'] . $this->lang['Validation Exception Integer needed'],$label);
        $integer = new Integer();

        $validator = new mock\Validator();
        $value = 'NaN';
        $this->assertFalse($validator->validateField($integer, $label, $value));
        $this->assertEquals(array($errormsg), $validator->getErrors());
    }

    public function test_validate_array() {
        $label = 'label';
        $errormsg = sprintf($this->lang['validation_prefix'] . $this->lang['Validation Exception Integer needed'],$label);
        $integer = new Integer();

        $validator = new mock\Validator();
        $value = array('NaN','NaN');
        $this->assertFalse($validator->validateField($integer, $label, $value));
        $this->assertEquals(array($errormsg, $errormsg), $validator->getErrors());
    }

    public function test_validate_blank() {
        $integer = new Integer();

        $validator = new mock\Validator();
        $value = null;
        $this->assertTrue($validator->validateField($integer, 'label', $value));
        $this->assertEquals(array(), $validator->getErrors());
    }

    public function test_validate_clean() {
        $text = new Text();

        $validator = new mock\Validator();
        $value = '  foo  ';
        $this->assertTrue($validator->validateField($text, 'label', $value));
        $this->assertEquals('foo', $value);

        $value = array('  foo  ', '  bar  ');
        $this->assertTrue($validator->validateField($text, 'label', $value));
        $this->assertEquals(array('foo', 'bar'), $value);
    }

}
