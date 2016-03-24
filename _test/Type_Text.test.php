<?php

namespace plugin\struct\test;

use plugin\struct\types\Text;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the Text Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Text_struct_test extends StructTest {

    public function test_compare_simple() {
        $text = new Text(array('prefix' => '', 'postfix' => ''));
        list($sql, $opt) = $text->compare('col', '=', 'val');
        $this->assertEquals('col = ?', $sql);
        $this->assertEquals(array('val'), $opt);

        $text = new Text(array('prefix' => 'before', 'postfix' => ''));
        list($sql, $opt) = $text->compare('col', '=', 'val');
        $this->assertEquals('? || col = ?', $sql);
        $this->assertEquals(array('before', 'val'), $opt);

        $text = new Text(array('prefix' => '', 'postfix' => 'after'));
        list($sql, $opt) = $text->compare('col', '=', 'val');
        $this->assertEquals('col || ? = ?', $sql);
        $this->assertEquals(array('after', 'val'), $opt);

        $text = new Text(array('prefix' => 'before', 'postfix' => 'after'));
        list($sql, $opt) = $text->compare('col', '=', 'val');
        $this->assertEquals('? || col || ? = ?', $sql);
        $this->assertEquals(array('before', 'after', 'val'), $opt);
    }

    public function test_compare_like() {
        $text = new Text(array('prefix' => '', 'postfix' => ''));
        list($sql, $opt) = $text->compare('col', '~', '%val%');
        $this->assertEquals('col LIKE ?', $sql);
        $this->assertEquals(array('%val%'), $opt);

        $text = new Text(array('prefix' => 'before', 'postfix' => ''));
        list($sql, $opt) = $text->compare('col', '~', '%val%');
        $this->assertEquals('? || col LIKE ?', $sql);
        $this->assertEquals(array('before', '%val%'), $opt);

        $text = new Text(array('prefix' => '', 'postfix' => 'after'));
        list($sql, $opt) = $text->compare('col', '~', '%val%');
        $this->assertEquals('col || ? LIKE ?', $sql);
        $this->assertEquals(array('after', '%val%'), $opt);

        $text = new Text(array('prefix' => 'before', 'postfix' => 'after'));
        list($sql, $opt) = $text->compare('col', '~', '%val%');
        $this->assertEquals('? || col || ? LIKE ?', $sql);
        $this->assertEquals(array('before', 'after', '%val%'), $opt);
    }

    public function test_compare_notlike() {
        $text = new Text(array('prefix' => '', 'postfix' => ''));
        list($sql, $opt) = $text->compare('col', '!~', '%val%');
        $this->assertEquals('col NOT LIKE ?', $sql);
        $this->assertEquals(array('%val%'), $opt);

        $text = new Text(array('prefix' => 'before', 'postfix' => ''));
        list($sql, $opt) = $text->compare('col', '!~', '%val%');
        $this->assertEquals('? || col NOT LIKE ?', $sql);
        $this->assertEquals(array('before', '%val%'), $opt);

        $text = new Text(array('prefix' => '', 'postfix' => 'after'));
        list($sql, $opt) = $text->compare('col', '!~', '%val%');
        $this->assertEquals('col || ? NOT LIKE ?', $sql);
        $this->assertEquals(array('after', '%val%'), $opt);

        $text = new Text(array('prefix' => 'before', 'postfix' => 'after'));
        list($sql, $opt) = $text->compare('col', '!~', '%val%');
        $this->assertEquals('? || col || ? NOT LIKE ?', $sql);
        $this->assertEquals(array('before', 'after', '%val%'), $opt);
    }

}
