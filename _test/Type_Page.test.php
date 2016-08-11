<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Title;
use dokuwiki\plugin\struct\meta\Value;
use dokuwiki\plugin\struct\test\mock\Search;
use dokuwiki\plugin\struct\types\Page;

/**
 * Testing the Page Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Page_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();

        saveWikiText('syntax', 'dummy', 'test');

        // make sure the search index is initialized
        idx_addPage('wiki:syntax');
        idx_addPage('syntax');
        idx_addPage('wiki:welcome');
        idx_addPage('wiki:dokuwiki');

    }

    public function test_sort() {

        saveWikiText('title1', 'test', 'test');
        $title = new Title('title1');
        $title->setTitle('This is a title');

        saveWikiText('title2', 'test', 'test');
        $title = new Title('title2');
        $title->setTitle('This is a title');

        saveWikiText('title3', 'test', 'test');
        $title = new Title('title3');
        $title->setTitle('Another Title');


        $this->loadSchemaJSON('pageschema');
        $this->saveData('test1', 'pageschema', array('singletitle' => 'title1'));
        $this->saveData('test2', 'pageschema', array('singletitle' => 'title2'));
        $this->saveData('test3', 'pageschema', array('singletitle' => 'title3'));

        $search = new Search();
        $search->addSchema('pageschema');
        $search->addColumn('%pageid%');
        $search->addColumn('singletitle');
        $search->addSort('singletitle', true);
        /** @var Value[][] $result */
        $result = $search->execute();

        $this->assertEquals(3, count($result));
        $this->assertEquals('test3', $result[0][0]->getValue());
        $this->assertEquals('test1', $result[1][0]->getValue());
        $this->assertEquals('test2', $result[2][0]->getValue());
    }


    public function test_search() {
        // prepare some data
        $this->loadSchemaJSON('pageschema');
        $this->saveData(
            'syntax',
            'pageschema',
            array(
                'singlepage' => 'wiki:dokuwiki',
                'multipage' => array('wiki:dokuwiki', 'wiki:syntax', 'wiki:welcome'),
                'singletitle' => 'wiki:dokuwiki',
                'multititle' => array('wiki:dokuwiki', 'wiki:syntax', 'wiki:welcome'),
            )
        );

        // make sure titles for some pages are known (not for wiki:welcome)
        $title = new Title('wiki:dokuwiki');
        $title->setTitle('DokuWiki Overview');
        $title = new Title('wiki:syntax');
        $title->setTitle('DokuWiki Foobar Syntax');

        // search
        $search = new Search();
        $search->addSchema('pageschema');
        $search->addColumn('singlepage');
        $search->addColumn('multipage');
        $search->addColumn('singletitle');
        $search->addColumn('multititle');

        /** @var Value[][] $result */
        $result = $search->execute();

        // no titles:
        $this->assertEquals('wiki:dokuwiki', $result[0][0]->getValue());
        $this->assertEquals(array('wiki:dokuwiki', 'wiki:syntax', 'wiki:welcome'), $result[0][1]->getValue());
        // titles as JSON:
        $this->assertEquals('["wiki:dokuwiki","DokuWiki Overview"]', $result[0][2]->getValue());
        $this->assertEquals(
            array(
                '["wiki:dokuwiki","DokuWiki Overview"]',
                '["wiki:syntax","DokuWiki Foobar Syntax"]',
                '["wiki:welcome",null]' // no title for this
            ),
            $result[0][3]->getValue()
        );

        // search single with title
        $single = clone $search;
        $single->addFilter('singletitle', 'Overview', '*~', 'AND');
        $result = $single->execute();
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));

        // search multi with title
        $multi = clone $search;
        $multi->addFilter('multititle', 'Foobar', '*~', 'AND');
        $result = $multi->execute();
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));

        // search single with page
        $single = clone $search;
        $single->addFilter('singletitle', 'wiki:dokuwiki', '*~', 'AND');
        $result = $single->execute();
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));

        // search multi with page
        $multi = clone $search;
        $multi->addFilter('multititle', 'welcome', '*~', 'AND');
        $result = $multi->execute();
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
    }

    public function test_ajax_default() {
        global $INPUT;

        $page = new Page(
            array(
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                    'namespace' => '',
                    'postfix' => '',
                ),
            )
        );

        $INPUT->set('search', 'syntax');
        $this->assertEquals(
            array(
                array('label' => 'syntax', 'value' => 'syntax'),
                array('label' => 'syntax (wiki)', 'value' => 'wiki:syntax')
            ), $page->handleAjax()
        );

        $INPUT->set('search', 'ynt');
        $this->assertEquals(
            array(
                array('label' => 'syntax', 'value' => 'syntax'),
                array('label' => 'syntax (wiki)', 'value' => 'wiki:syntax')
            ), $page->handleAjax()
        );

        $INPUT->set('search', 's'); // under mininput
        $this->assertEquals(array(), $page->handleAjax());
    }

    public function test_ajax_namespace() {
        global $INPUT;

        $page = new Page(
            array(
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                    'namespace' => 'wiki',
                    'postfix' => '',
                ),
            )
        );

        $INPUT->set('search', 'ynt');
        $this->assertEquals(array(array('label' => 'syntax (wiki)', 'value' => 'wiki:syntax')), $page->handleAjax());
    }

    public function test_ajax_postfix() {
        global $INPUT;

        $page = new Page(
            array(
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                    'namespace' => '',
                    'postfix' => 'iki',
                ),
            )
        );

        $INPUT->set('search', 'oku');
        $this->assertEquals(array(array('label' => 'dokuwiki (wiki)', 'value' => 'wiki:dokuwiki')), $page->handleAjax());
    }

}
