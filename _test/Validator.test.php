<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\test\mock\Assignments;
use dokuwiki\plugin\struct\types\Decimal;
use dokuwiki\plugin\struct\types\Text;

/**
 * Tests for the basic validation functions
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class Validator_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();

        $this->loadSchemaJSON('schema1');
        $this->loadSchemaJSON('schema2');

        $this->saveData(
            'page01',
            'schema1',
            array(
                'first' => 'first data',
                'second' => array('second data', 'more data', 'even more'),
                'third' => 'third data',
                'fourth' => 'fourth data'
            )
        );
    }

    protected function tearDown() {
        parent::tearDown();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
        Assignments::reset();
    }

    public function test_validate_nonArray() {
        $label = 'label';
        $errormsg = sprintf($this->getLang('validation_prefix') . $this->getLang('Validation Exception Decimal needed'), $label);
        $integer = new Decimal();

        $validator = new mock\ValueValidator();
        $value = 'NaN';
        $this->assertFalse($validator->validateField($integer, $label, $value));
        $this->assertEquals(array($errormsg), $validator->getErrors());
    }

    public function test_validate_array() {
        $label = 'label';
        $errormsg = sprintf($this->getLang('validation_prefix') . $this->getLang('Validation Exception Decimal needed'), $label);
        $integer = new Decimal();

        $validator = new mock\ValueValidator();
        $value = array('NaN', 'NaN');
        $this->assertFalse($validator->validateField($integer, $label, $value));
        $this->assertEquals(array($errormsg, $errormsg), $validator->getErrors());
    }

    public function test_validate_blank() {
        $integer = new Decimal();

        $validator = new mock\ValueValidator();
        $value = null;
        $this->assertTrue($validator->validateField($integer, 'label', $value));
        $this->assertEquals(array(), $validator->getErrors());
    }

    public function test_validate_clean() {
        $text = new Text();

        $validator = new mock\ValueValidator();
        $value = '  foo  ';
        $this->assertTrue($validator->validateField($text, 'label', $value));
        $this->assertEquals('foo', $value);

        $value = array('  foo  ', '  bar  ');
        $this->assertTrue($validator->validateField($text, 'label', $value));
        $this->assertEquals(array('foo', 'bar'), $value);
    }

}
