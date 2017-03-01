<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * @group plugin_struct
 * @group plugins
 *
 * @covers action_plugin_struct_entry
 * @covers action_plugin_struct_revert
 * @covers action_plugin_struct_edit
 */
class entry_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();

        $this->loadSchemaJSON('schema1');
        $this->loadSchemaJSON('schema2', 'schema2int');
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

    protected function checkField(\phpQueryObject $pq, $schema, $name, $value) {
        $this->assertEquals(1, $pq->find("span.label:contains('$name')")->length, "Field $schema.$name not found");
        $this->assertEquals($value, $pq->find("input[name='struct_schema_data[$schema][$name]']")->val(), "Field $schema.$name has wrong value");
    }

    public function test_createForm_storedData() {
        $entry = new mock\action_plugin_struct_entry();
        global $ID;
        $ID = 'page01';
        $test_html = $entry->createForm('schema1');

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema1', $pq->find('legend')->text());
        $this->checkField($pq, 'schema1', 'first', 'first data');
        $this->checkField($pq, 'schema1', 'second', 'second data, more data, even more');
        $this->checkField($pq, 'schema1', 'third', 'third data');
        $this->checkField($pq, 'schema1', 'fourth', 'fourth data');
    }

    public function test_createForm_emptyData() {
        $entry = new mock\action_plugin_struct_entry();
        global $ID;
        $ID = 'page02';
        $test_html = $entry->createForm('schema1');

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema1', $pq->find('legend')->text());
        $this->checkField($pq, 'schema1', 'first', '');
        $this->checkField($pq, 'schema1', 'second', '');
        $this->checkField($pq, 'schema1', 'third', '');
        $this->checkField($pq, 'schema1', 'fourth', '');
    }

    public function test_createForm_postData() {
        global $INPUT, $ID;
        $ID = 'page01';
        $structdata = array(
            'schema1' => array(
                'first' => 'first post data',
                'second' => array('second post data', 'more post data', 'even more post data'),
                'third' => 'third post data',
                'fourth' => 'fourth post data'
            )
        );
        $INPUT->set(mock\action_plugin_struct_entry::getVAR(), $structdata);

        $entry = new mock\action_plugin_struct_entry();
        $test_html = $entry->createForm('schema1');

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema1', $pq->find('legend')->text());
        $this->checkField($pq, 'schema1', 'first', 'first post data');
        $this->checkField($pq, 'schema1', 'second', 'second post data, more post data, even more post data');
        $this->checkField($pq, 'schema1', 'third', 'third post data');
        $this->checkField($pq, 'schema1', 'fourth', 'fourth post data');
    }

    public function test_edit_page_wo_schema() {
        $page = 'test_edit_page_wo_schema';

        $request = new \TestRequest();
        $response = $request->get(array('id' => $page, 'do' => 'edit'), '/doku.php');
        $structElement = $response->queryHTML('.struct_entry_form');

        $this->assertEquals(1, count($structElement));
        $this->assertEquals($structElement->html(), '');
    }

    public function test_edit_page_with_schema() {
        $page = 'test_edit_page_with_schema';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
        $assignment->addPattern($page, $schema);

        $request = new \TestRequest();
        $response = $request->get(array('id' => $page, 'do' => 'edit'), '/doku.php');
        $test_html = trim($response->queryHTML('.struct_entry_form')->html());

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema2', $pq->find('legend')->text());
        $this->checkField($pq, 'schema2', 'afirst', '');
        $this->checkField($pq, 'schema2', 'asecond', '');
        $this->checkField($pq, 'schema2', 'athird', '');
        $this->checkField($pq, 'schema2', 'afourth', '');
    }

    public function test_preview_page_invaliddata() {
        $page = 'test_preview_page_invaliddata';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $response = $request->post(array('id' => $page, 'do' => 'preview'), '/doku.php');
        $expected_errormsg = sprintf($this->getLang('validation_prefix') . $this->getLang('Validation Exception Decimal needed'), 'afourth');
        $actual_errormsg = $response->queryHTML('.error')->html();
        $test_html = trim($response->queryHTML('.struct_entry_form')->html());

        $this->assertEquals($expected_errormsg, $actual_errormsg, 'If there is invalid data, then there should be an error message.');

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema2', $pq->find('legend')->text());
        $this->checkField($pq, 'schema2', 'afirst', 'foo');
        $this->checkField($pq, 'schema2', 'asecond', 'bar, baz');
        $this->checkField($pq, 'schema2', 'athird', 'foobar');
        $this->checkField($pq, 'schema2', 'afourth', 'Eve');
    }

    public function test_preview_page_validdata() {
        $page = 'test_preview_page_validdata';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $response = $request->post(array('id' => $page, 'do' => 'preview'), '/doku.php');
        $actual_errormsg = $response->queryHTML('.error')->get();
        $test_html = trim($response->queryHTML('.struct_entry_form')->html());

        $this->assertEquals($actual_errormsg, array(), "If all data is valid, then there should be no error message.");

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema2', $pq->find('legend')->text());
        $this->checkField($pq, 'schema2', 'afirst', 'foo');
        $this->checkField($pq, 'schema2', 'asecond', 'bar, baz');
        $this->checkField($pq, 'schema2', 'athird', 'foobar');
        $this->checkField($pq, 'schema2', 'afourth', '42');
    }

    public function test_fail_saving_empty_page() {
        $page = 'test_fail_saving_empty_page';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('summary', 'only struct data saved');
        $response = $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');
        $expected_errormsg = $this->getLang('emptypage');
        $actual_errormsg = $response->queryHTML('.error')->html();
        $pagelog = new \PageChangelog($page);
        $revisions = $pagelog->getRevisions(-1, 200);

        $this->assertEquals(0, count($revisions));
        $this->assertEquals($expected_errormsg, $actual_errormsg, "An empty page should not be saved.");
    }

    public function test_fail_saveing_page_with_invaliddata() {
        $page = 'test_fail_saveing_page_with_invaliddata';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', $wikitext);
        $request->setPost('summary', 'content and struct data saved');
        $response = $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');
        $actual_wikitext = trim($response->queryHTML('#wiki__text')->html());
        $expected_wikitext = $wikitext;

        $actual_errormsg = $response->queryHTML('.error')->html();
        $expected_errormsg = sprintf($this->getLang('validation_prefix') . $this->getLang('Validation Exception Decimal needed'), 'afourth');

        $test_html = trim($response->queryHTML('.struct_entry_form')->html());

        $pagelog = new \PageChangelog($page);
        $revisions = $pagelog->getRevisions(-1, 200);

        // assert
        $this->assertEquals(0, count($revisions));
        $this->assertEquals($expected_errormsg, $actual_errormsg, 'If there is invalid data, then there should be an error message.');
        $this->assertEquals($expected_wikitext, $actual_wikitext);

        $pq = \phpQuery::newDocument($test_html);
        $this->assertEquals('schema2', $pq->find('legend')->text());
        $this->checkField($pq, 'schema2', 'afirst', 'foo');
        $this->checkField($pq, 'schema2', 'asecond', 'bar, baz');
        $this->checkField($pq, 'schema2', 'athird', 'foobar');
        $this->checkField($pq, 'schema2', 'afourth', 'Eve');

        // todo: assert that no struct data has been saved
    }

    public function test_save_page() {
        $page = 'test_save_page';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', 'teststring');
        $request->setPost('summary', 'content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        $pagelog = new \PageChangelog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = meta\AccessTable::byTableName($schema, $page, 0);
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
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', $wikitext);
        $request->setPost('summary', 'content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        $this->waitForTick(true);

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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', $wikitext);
        $request->setPost('summary', '2nd revision');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        // assert
        $pagelog = new \PageChangelog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = meta\AccessTable::byTableName($schema, $page, 0);
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
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', $wikitext);
        $request->setPost('summary', 'content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        $this->waitForTick(true);

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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', '');
        $request->setPost('summary', 'delete page');
        $request->post(array('id' => $page, 'do' => 'save'), '/doku.php');

        // assert
        $pagelog = new \PageChangelog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = meta\AccessTable::byTableName($schema, $page, 0);
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

    /**
     * @group slow
     */
    public function test_revert_page() {
        $page = 'test_revert_page';
        $assignment = mock\Assignments::getInstance();
        $schema = 'schema2';
        $assignment->addPattern($page, $schema);
        $wikitext = 'teststring';

        global $conf;
        $conf['useacl'] = 1;
        $conf['superuser'] = 'admin';
        $_SERVER['REMOTE_USER'] = 'admin'; //now it's testing as admin
        global $default_server_vars;
        $default_server_vars['REMOTE_USER'] = 'admin';  //Hack until Issue #1099 is fixed
        $USERINFO['grps'] = array('admin', 'user');

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
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', $wikitext);
        $request->setPost('summary', 'content and struct data saved');
        $request->post(array('id' => $page, 'do' => 'save', 'sectok' => getSecurityToken()), '/doku.php');

        $this->waitForTick(true);

        // second save
        $request = new \TestRequest();
        $structData = array(
            $schema => array(
                'afirst' => 'foo2',
                'asecond' => 'bar2, baz2',
                'athird' => 'foobar2',
                'afourth' => '43'
            )
        );
        $request->setPost('struct_schema_data', $structData);
        $request->setPost('wikitext', $wikitext . $wikitext);
        $request->setPost('summary', 'delete page');
        $request->post(array('id' => $page, 'do' => 'save', 'sectok' => getSecurityToken()), '/doku.php');

        $this->waitForTick(true);

        // revert to first save
        $actpagelog = new \PageChangelog($page);
        $actrevisions = $actpagelog->getRevisions(0, 200);

        $actrevinfo = $actpagelog->getRevisionInfo($actrevisions[0]);
        $request = new \TestRequest();
        $request->setPost('summary', 'revert page');
        $request->post(array('id' => $page, 'do' => 'revert', 'rev' => $actrevinfo['date'], 'sectok' => getSecurityToken()), '/doku.php');

        // assert
        $pagelog = new \PageChangelog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $schemaData = meta\AccessTable::byTableName($schema, $page, 0);
        $actual_struct_data = $schemaData->getDataArray();
        $expected_struct_data = array(
            'afirst' => 'foo',
            'asecond' => array('bar', 'baz'),
            'athird' => 'foobar',
            'afourth' => '42'
        );

        $this->assertEquals(3, count($revisions), 'there should be 3 (three) revisions');
        $this->assertContains('restored', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_REVERT, $revinfo['type']);
        $this->assertEquals($expected_struct_data, $actual_struct_data);
        // todo: timestamps?
    }

}
