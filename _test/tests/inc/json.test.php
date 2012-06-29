<?php
/**
 * Unit tests for JSON.
 *
 * @author      Michal Migurski <mike-json@teczno.com>
 * @author      Matt Knapp <mdknapp[at]gmail[dot]com>
 * @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
 * @copyright   2005 Michal Migurski
 * @version     CVS: $Id: Test-JSON.php,v 1.28 2006/06/28 05:54:17 migurski Exp $
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @link        http://pear.php.net/pepr/pepr-proposal-show.php?id=198
 * @link        http://mike.teczno.com/JSON/Test-JSON.phps
 */

class JSON_EncDec_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json = new JSON();
        $this->json->skipnative = true;

        $obj = new stdClass();
        $obj->a_string = '"he":llo}:{world';
        $obj->an_array = array(1, 2, 3);
        $obj->obj = new stdClass();
        $obj->obj->a_number = 123;

        $this->obj = $obj;
        $this->obj_j = '{"a_string":"\"he\":llo}:{world","an_array":[1,2,3],"obj":{"a_number":123}}';
        $this->obj_d = 'object with properties, nested object and arrays';

        $this->arr = array(null, true, array(1, 2, 3), "hello\"],[world!");
        $this->arr_j = '[null,true,[1,2,3],"hello\"],[world!"]';
        $this->arr_d = 'array with elements and nested arrays';

        $this->str1 = 'hello world';
        $this->str1_j = '"hello world"';
        $this->str1_j_ = "'hello world'";
        $this->str1_d = 'hello world';
        $this->str1_d_ = 'hello world, double quotes';

        $this->str2 = "hello\t\"world\"";
        $this->str2_j = '"hello\\t\\"world\\""';
        $this->str2_d = 'hello world, with tab, double-quotes';

        $this->str3 = "\\\r\n\t\"/";
        $this->str3_j = '"\\\\\\r\\n\\t\\"\\/"';
        $this->str3_d = 'backslash, return, newline, tab, double-quote';

        $this->str4 = 'héllö wørłd';
        $this->str4_j = '"h\u00e9ll\u00f6 w\u00f8r\u0142d"';
        $this->str4_j_ = '"héllö wørłd"';
        $this->str4_d = 'hello world, with unicode';
    }

    function test_to_JSON() {
        $this->assertEquals('null', $this->json->encode(null), 'type case: null');
        $this->assertEquals('true', $this->json->encode(true), 'type case: boolean true');
        $this->assertEquals('false', $this->json->encode(false), 'type case: boolean false');

        $this->assertEquals('1', $this->json->encode(1), 'numeric case: 1');
        $this->assertEquals('-1', $this->json->encode(-1), 'numeric case: -1');
        $this->assertEquals('1.000000', $this->json->encode(1.0), 'numeric case: 1.0');
        $this->assertEquals('1.100000', $this->json->encode(1.1), 'numeric case: 1.1');

        $this->assertEquals($this->str1_j, $this->json->encode($this->str1), "string case: {$this->str1_d}");
        $this->assertEquals($this->str2_j, $this->json->encode($this->str2), "string case: {$this->str2_d}");
        $this->assertEquals($this->str3_j, $this->json->encode($this->str3), "string case: {$this->str3_d}");
        $this->assertEquals($this->str4_j, $this->json->encode($this->str4), "string case: {$this->str4_d}");

        $this->assertEquals($this->arr_j, $this->json->encode($this->arr), "array case: {$this->arr_d}");
        $this->assertEquals($this->obj_j, $this->json->encode($this->obj), "object case: {$this->obj_d}");
    }

    function test_from_JSON() {
        $this->assertEquals(null, $this->json->decode('null'), 'type case: null');
        $this->assertEquals(true, $this->json->decode('true'), 'type case: boolean true');
        $this->assertEquals(false, $this->json->decode('false'), 'type case: boolean false');

        $this->assertEquals(1, $this->json->decode('1'), 'numeric case: 1');
        $this->assertEquals(-1, $this->json->decode('-1'), 'numeric case: -1');
        $this->assertEquals(1.0, $this->json->decode('1.0'), 'numeric case: 1.0');
        $this->assertEquals(1.1, $this->json->decode('1.1'), 'numeric case: 1.1');

        $this->assertEquals(11.0, $this->json->decode('1.1e1'), 'numeric case: 1.1e1');
        $this->assertEquals(11.0, $this->json->decode('1.10e+1'), 'numeric case: 1.10e+1');
        $this->assertEquals(0.11, $this->json->decode('1.1e-1'), 'numeric case: 1.1e-1');
        $this->assertEquals(-0.11, $this->json->decode('-1.1e-1'), 'numeric case: -1.1e-1');

        $this->assertEquals($this->str1, $this->json->decode($this->str1_j),  "string case: {$this->str1_d}");
        $this->assertEquals($this->str1, $this->json->decode($this->str1_j_), "string case: {$this->str1_d_}");
        $this->assertEquals($this->str2, $this->json->decode($this->str2_j),  "string case: {$this->str2_d}");
        $this->assertEquals($this->str3, $this->json->decode($this->str3_j),  "string case: {$this->str3_d}");
        $this->assertEquals($this->str4, $this->json->decode($this->str4_j),  "string case: {$this->str4_d}");
        $this->assertEquals($this->str4, $this->json->decode($this->str4_j_),  "string case: {$this->str4_d}");

        $this->assertEquals($this->arr, $this->json->decode($this->arr_j), "array case: {$this->arr_d}");
        $this->assertEquals($this->obj, $this->json->decode($this->obj_j), "object case: {$this->obj_d}");
    }

    function test_to_then_from_JSON() {
        $this->assertEquals(null, $this->json->decode($this->json->encode(null)), 'type case: null');
        $this->assertEquals(true, $this->json->decode($this->json->encode(true)), 'type case: boolean true');
        $this->assertEquals(false, $this->json->decode($this->json->encode(false)), 'type case: boolean false');

        $this->assertEquals(1, $this->json->decode($this->json->encode(1)), 'numeric case: 1');
        $this->assertEquals(-1, $this->json->decode($this->json->encode(-1)), 'numeric case: -1');
        $this->assertEquals(1.0, $this->json->decode($this->json->encode(1.0)), 'numeric case: 1.0');
        $this->assertEquals(1.1, $this->json->decode($this->json->encode(1.1)), 'numeric case: 1.1');

        $this->assertEquals($this->str1, $this->json->decode($this->json->encode($this->str1)), "string case: {$this->str1_d}");
        $this->assertEquals($this->str2, $this->json->decode($this->json->encode($this->str2)), "string case: {$this->str2_d}");
        $this->assertEquals($this->str3, $this->json->decode($this->json->encode($this->str3)), "string case: {$this->str3_d}");
        $this->assertEquals($this->str4, $this->json->decode($this->json->encode($this->str4)), "string case: {$this->str4_d}");

        $this->assertEquals($this->arr, $this->json->decode($this->json->encode($this->arr)), "array case: {$this->arr_d}");
        $this->assertEquals($this->obj, $this->json->decode($this->json->encode($this->obj)), "object case: {$this->obj_d}");
    }

    function test_from_then_to_JSON() {
        $this->assertEquals('null', $this->json->encode($this->json->decode('null')), 'type case: null');
        $this->assertEquals('true', $this->json->encode($this->json->decode('true')), 'type case: boolean true');
        $this->assertEquals('false', $this->json->encode($this->json->decode('false')), 'type case: boolean false');

        $this->assertEquals('1', $this->json->encode($this->json->decode('1')), 'numeric case: 1');
        $this->assertEquals('-1', $this->json->encode($this->json->decode('-1')), 'numeric case: -1');
        $this->assertEquals('1.0', $this->json->encode($this->json->decode('1.0')), 'numeric case: 1.0');
        $this->assertEquals('1.1', $this->json->encode($this->json->decode('1.1')), 'numeric case: 1.1');

        $this->assertEquals($this->str1_j, $this->json->encode($this->json->decode($this->str1_j)), "string case: {$this->str1_d}");
        $this->assertEquals($this->str2_j, $this->json->encode($this->json->decode($this->str2_j)), "string case: {$this->str2_d}");
        $this->assertEquals($this->str3_j, $this->json->encode($this->json->decode($this->str3_j)), "string case: {$this->str3_d}");
        $this->assertEquals($this->str4_j, $this->json->encode($this->json->decode($this->str4_j)), "string case: {$this->str4_d}");
        $this->assertEquals($this->str4_j, $this->json->encode($this->json->decode($this->str4_j_)), "string case: {$this->str4_d}");

        $this->assertEquals($this->arr_j, $this->json->encode($this->json->decode($this->arr_j)), "array case: {$this->arr_d}");
        $this->assertEquals($this->obj_j, $this->json->encode($this->json->decode($this->obj_j)), "object case: {$this->obj_d}");
    }
}

class JSON_AssocArray_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json_l = new JSON(JSON_LOOSE_TYPE);
        $this->json_l->skipnative = true;
        $this->json_s = new JSON();
        $this->json_s->skipnative = true;

        $this->arr = array('car1'=> array('color'=> 'tan', 'model' => 'sedan'),
            'car2' => array('color' => 'red', 'model' => 'sports'));
        $this->arr_jo = '{"car1":{"color":"tan","model":"sedan"},"car2":{"color":"red","model":"sports"}}';
        $this->arr_d = 'associative array with nested associative arrays';

        $this->arn = array(0=> array(0=> 'tan\\', 'model\\' => 'sedan'), 1 => array(0 => 'red', 'model' => 'sports'));
        $this->arn_ja = '[{"0":"tan\\\\","model\\\\":"sedan"},{"0":"red","model":"sports"}]';
        $this->arn_d = 'associative array with nested associative arrays, and some numeric keys thrown in';

        $this->arrs = array (1 => 'one', 2 => 'two', 5 => 'five');
        $this->arrs_jo = '{"1":"one","2":"two","5":"five"}';
        $this->arrs_d = 'associative array numeric keys which are not fully populated in a range of 0 to length-1';
    }

    function test_type() {
        $this->assertEquals('array',  gettype($this->json_l->decode($this->arn_ja)), "loose type should be array");
        $this->assertEquals('array',  gettype($this->json_s->decode($this->arn_ja)), "strict type should be array");
    }

    function test_to_JSON() {
        // both strict and loose JSON should result in an object
        $this->assertEquals($this->arr_jo, $this->json_l->encode($this->arr), "array case - loose: {$this->arr_d}");
        $this->assertEquals($this->arr_jo, $this->json_s->encode($this->arr), "array case - strict: {$this->arr_d}");

        // ...unless the input array has some numeric indeces, in which case the behavior is to degrade to a regular array
        $this->assertEquals($this->arn_ja, $this->json_s->encode($this->arn), "array case - strict: {$this->arn_d}");

        // Test a sparsely populated numerically indexed associative array
        $this->assertEquals($this->arrs_jo, $this->json_l->encode($this->arrs), "sparse numeric assoc array: {$this->arrs_d}");
    }

    function test_to_then_from_JSON() {
        // these tests motivated by a bug in which strings that end
        // with backslashes followed by quotes were incorrectly decoded.

        foreach(array('\\"', '\\\\"', '\\"\\"', '\\""\\""', '\\\\"\\\\"') as $v) {
            $this->assertEquals(array($v), $this->json_l->decode($this->json_l->encode(array($v))));
            $this->assertEquals(array('a' => $v), $this->json_l->decode($this->json_l->encode(array('a' => $v))));
        }
    }
}

class JSON_NestedArray_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json = new JSON(JSON_LOOSE_TYPE);
        $this->json->skipnative = true;

        $this->str1 = '[{"this":"that"}]';
        $this->arr1 = array(array('this' => 'that'));

        $this->str2 = '{"this":["that"]}';
        $this->arr2 = array('this' => array('that'));

        $this->str3 = '{"params":[{"foo":["1"],"bar":"1"}]}';
        $this->arr3 = array('params' => array(array('foo' => array('1'), 'bar' => '1')));

        $this->str4 = '{"0": {"foo": "bar", "baz": "winkle"}}';
        $this->arr4 = array('0' => array('foo' => 'bar', 'baz' => 'winkle'));

        $this->str5 = '{"params":[{"options": {"old": [ ], "new": {"0": {"elements": {"old": [], "new": {"0": {"elementName": "aa", "isDefault": false, "elementRank": "0", "priceAdjust": "0", "partNumber": ""}}}, "optionName": "aa", "isRequired": false, "optionDesc": null}}}}]}';
        $this->arr5 = array (
          'params' => array (
            0 => array (
              'options' =>
              array (
                'old' => array(),
                'new' => array (
                  0 => array (
                    'elements' => array (
                      'old' => array(),
                      'new' => array (
                        0 => array (
                          'elementName' => 'aa',
                          'isDefault' => false,
                          'elementRank' => '0',
                          'priceAdjust' => '0',
                          'partNumber' => '',
                        ),
                      ),
                    ),
                    'optionName' => 'aa',
                    'isRequired' => false,
                    'optionDesc' => NULL,
                  ),
                ),
              ),
            ),
          ),
        );
    }

    function test_type() {
        $this->assertEquals('array', gettype($this->json->decode($this->str1)), "loose type should be array");
        $this->assertEquals('array', gettype($this->json->decode($this->str2)), "loose type should be array");
        $this->assertEquals('array', gettype($this->json->decode($this->str3)), "loose type should be array");
    }

    function test_from_JSON() {
        $this->assertEquals($this->arr1, $this->json->decode($this->str1), "simple compactly-nested array");
        $this->assertEquals($this->arr2, $this->json->decode($this->str2), "simple compactly-nested array");
        $this->assertEquals($this->arr3, $this->json->decode($this->str3), "complex compactly nested array");
        $this->assertEquals($this->arr4, $this->json->decode($this->str4), "complex compactly nested array");
        $this->assertEquals($this->arr5, $this->json->decode($this->str5), "super complex compactly nested array");
    }

    function _test_from_JSON() {
        $super = '{"params":[{"options": {"old": {}, "new": {"0": {"elements": {"old": {}, "new": {"0": {"elementName": "aa", "isDefault": false, "elementRank": "0", "priceAdjust": "0", "partNumber": ""}}}, "optionName": "aa", "isRequired": false, "optionDesc": ""}}}}]}';
        print("trying {$super}...\n");
        print var_export($this->json->decode($super));
    }
}

class JSON_Object_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json_l = new JSON(JSON_LOOSE_TYPE);
        $this->json_l->skipnative = true;
        $this->json_s = new JSON();
        $this->json_s->skipnative = true;

        $this->obj_j = '{"a_string":"\"he\":llo}:{world","an_array":[1,2,3],"obj":{"a_number":123}}';

        $this->obj1 = new stdClass();
        $this->obj1->car1 = new stdClass();
        $this->obj1->car1->color = 'tan';
        $this->obj1->car1->model = 'sedan';
        $this->obj1->car2 = new stdClass();
        $this->obj1->car2->color = 'red';
        $this->obj1->car2->model = 'sports';
        $this->obj1_j = '{"car1":{"color":"tan","model":"sedan"},"car2":{"color":"red","model":"sports"}}';
        $this->obj1_d = 'Object with nested objects';
    }

    function test_type() {
        $this->assertEquals('object', gettype($this->json_s->decode($this->obj_j)), "checking whether decoded type is object");
        $this->assertEquals('array',  gettype($this->json_l->decode($this->obj_j)), "checking whether decoded type is array");
    }

    function test_to_JSON() {
        $this->assertEquals($this->obj1_j, $this->json_s->encode($this->obj1), "object - strict: {$this->obj1_d}");
        $this->assertEquals($this->obj1_j, $this->json_l->encode($this->obj1), "object - loose: {$this->obj1_d}");
    }

    function test_from_then_to_JSON() {
        $this->assertEquals($this->obj_j, $this->json_s->encode($this->json_s->decode($this->obj_j)), "object case");
        $this->assertEquals($this->obj_j, $this->json_l->encode($this->json_l->decode($this->obj_j)), "array case");
    }
}

class JSON_Spaces_Comments_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json = new JSON(JSON_LOOSE_TYPE);
        $this->json->skipnative = true;

        $this->obj_j = '{"a_string":"\"he\":llo}:{world","an_array":[1,2,3],"obj":{"a_number":123}}';

        $this->obj_js = '{"a_string": "\"he\":llo}:{world",
                          "an_array":[1, 2, 3],
                          "obj": {"a_number":123}}';

        $this->obj_jc1 = '{"a_string": "\"he\":llo}:{world",
                          // here is a comment, hoorah
                          "an_array":[1, 2, 3],
                          "obj": {"a_number":123}}';

        $this->obj_jc2 = '/* this here is the sneetch */ "the sneetch"
                          // this has been the sneetch.';

        $this->obj_jc3 = '{"a_string": "\"he\":llo}:{world",
                          /* here is a comment, hoorah */
                          "an_array":[1, 2, 3 /* and here is another */],
                          "obj": {"a_number":123}}';

        $this->obj_jc4 = '{\'a_string\': "\"he\":llo}:{world",
                          /* here is a comment, hoorah */
                          \'an_array\':[1, 2, 3 /* and here is another */],
                          "obj": {"a_number":123}}';
    }

    function test_spaces() {
        $this->assertEquals($this->json->decode($this->obj_j), $this->json->decode($this->obj_js), "checking whether notation with spaces works");
    }

    function test_comments() {
        $this->assertEquals($this->json->decode($this->obj_j), $this->json->decode($this->obj_jc1), "checking whether notation with single line comments works");
        $this->assertEquals('the sneetch', $this->json->decode($this->obj_jc2), "checking whether notation with multiline comments works");
        $this->assertEquals($this->json->decode($this->obj_j), $this->json->decode($this->obj_jc3), "checking whether notation with multiline comments works");
        $this->assertEquals($this->json->decode($this->obj_j), $this->json->decode($this->obj_jc4), "checking whether notation with single-quotes and multiline comments works");
    }
}

class JSON_Empties_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json_l = new JSON(JSON_LOOSE_TYPE);
        $this->json_l->skipnative = true;
        $this->json_l->skipnative = true;
        $this->json_s = new JSON();
        $this->json_s->skipnative = true;

        $this->obj0_j = '{}';
        $this->arr0_j = '[]';

        $this->obj1_j = '{ }';
        $this->arr1_j = '[ ]';

        $this->obj2_j = '{ /* comment inside */ }';
        $this->arr2_j = '[ /* comment inside */ ]';
    }

    function test_type() {
        $this->assertEquals('array',   gettype($this->json_l->decode($this->arr0_j)), "should be array");
        $this->assertEquals('object',  gettype($this->json_s->decode($this->obj0_j)), "should be object");

        $this->assertEquals(0,  count($this->json_l->decode($this->arr0_j)), "should be empty array");
        $this->assertEquals(0,  count(get_object_vars($this->json_s->decode($this->obj0_j))), "should be empty object");

        $this->assertEquals('array',   gettype($this->json_l->decode($this->arr1_j)), "should be array, even with space");
        $this->assertEquals('object',  gettype($this->json_s->decode($this->obj1_j)), "should be object, even with space");

        $this->assertEquals(0,  count($this->json_l->decode($this->arr1_j)), "should be empty array, even with space");
        $this->assertEquals(0,  count(get_object_vars($this->json_s->decode($this->obj1_j))), "should be empty object, even with space");

        $this->assertEquals('array',   gettype($this->json_l->decode($this->arr2_j)), "should be array, despite comment");
        $this->assertEquals('object',  gettype($this->json_s->decode($this->obj2_j)), "should be object, despite comment");

        $this->assertEquals(0,  count($this->json_l->decode($this->arr2_j)), "should be empty array, despite comment");
        $this->assertEquals(0,  count(get_object_vars($this->json_s->decode($this->obj2_j))), "should be empty object, despite commentt");
    }
}

class JSON_UnquotedKeys_TestCase extends DokuWikiTest {

    function setUp() {
        parent::setUp();

        $this->json = new JSON(JSON_LOOSE_TYPE);
        $this->json->skipnative = true;

        $this->arn = array(0=> array(0=> 'tan', 'model' => 'sedan'), 1 => array(0 => 'red', 'model' => 'sports'));
        $this->arn_ja = '[{0:"tan","model":"sedan"},{"0":"red",model:"sports"}]';
        $this->arn_d = 'associative array with unquoted keys, nested associative arrays, and some numeric keys thrown in';

        $this->arrs = array (1 => 'one', 2 => 'two', 5 => 'fi"ve');
        $this->arrs_jo = '{"1":"one",2:"two","5":\'fi"ve\'}';
        $this->arrs_d = 'associative array with unquoted keys, single-quoted values, numeric keys which are not fully populated in a range of 0 to length-1';
    }

    function test_from_JSON() {
        // ...unless the input array has some numeric indeces, in which case the behavior is to degrade to a regular array
        $this->assertEquals($this->arn, $this->json->decode($this->arn_ja), "array case - strict: {$this->arn_d}");

        // Test a sparsely populated numerically indexed associative array
        $this->assertEquals($this->arrs, $this->json->decode($this->arrs_jo), "sparse numeric assoc array: {$this->arrs_d}");
    }
}

