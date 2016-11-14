<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Title;
use dokuwiki\plugin\struct\test\mock\AccessTable;
use dokuwiki\plugin\struct\test\mock\Lookup;

/**
 * Testing the Dropdown Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Lookup_struct_test extends StructTest {

    protected function prepareLookup() {
        saveWikiText('title1', 'test', 'test');
        $title = new Title('title1');
        $title->setTitle('This is a title');

        saveWikiText('title2', 'test', 'test');
        $title = new Title('title2');
        $title->setTitle('This is a 2nd title');

        saveWikiText('title3', 'test', 'test');
        $title = new Title('title3');
        $title->setTitle('Another Title');

        $this->loadSchemaJSON('pageschema', '', 0, true);
        $access = AccessTable::byTableName('pageschema', 0);
        $access->saveData(
            array(
                'singlepage' => 'title1',
                'multipage' => array('title1'),
                'singletitle' => 'title1',
                'multititle' => array('title1'),
            )
        );
        $access = AccessTable::byTableName('pageschema', 0);
        $access->saveData(
            array(
                'singlepage' => 'title2',
                'multipage' => array('title2'),
                'singletitle' => 'title2',
                'multititle' => array('title2'),
            )
        );
        $access = AccessTable::byTableName('pageschema', 0);
        $access->saveData(
            array(
                'singlepage' => 'title3',
                'multipage' => array('title3'),
                'singletitle' => 'title3',
                'multititle' => array('title3'),
            )
        );
    }

    protected function prepareTranslation() {
        $this->loadSchemaJSON('translation', '', 0, true);
        $access = AccessTable::byTableName('translation', 0);
        $access->saveData(
            array(
                'en' => 'shoe',
                'de' => 'Schuh',
                'fr' => 'chaussure'
            )
        );

        $access = AccessTable::byTableName('translation', 0);
        $access->saveData(
            array(
                'en' => 'dog',
                'de' => 'Hund',
                'fr' => 'chien'
            )
        );

        $access = AccessTable::byTableName('translation', 0);
        $access->saveData(
            array(
                'en' => 'cat',
                'de' => 'Katze',
                'fr' => 'Chat'
            )
        );
    }

    protected function preparePages() {
        $this->loadSchemaJSON('dropdowns');
        $this->saveData('test1', 'dropdowns', array('drop1' => '1', 'drop2' => '1', 'drop3' => 'John'));
        $this->saveData('test2', 'dropdowns', array('drop1' => '2', 'drop2' => '2', 'drop3' => 'Jane'));
        $this->saveData('test3', 'dropdowns', array('drop1' => '3', 'drop2' => '3', 'drop3' => 'Tarzan'));
    }

    public function test_data() {
        $this->prepareLookup();
        $this->preparePages();

        $access = AccessTable::byTableName('dropdowns', 'test1');
        $data = $access->getData();

        $this->assertEquals('["1","[\\"title1\\",\\"This is a title\\"]"]', $data[0]->getValue());
        $this->assertEquals('["1","title1"]', $data[1]->getValue());

        $this->assertEquals('1', $data[0]->getRawValue());
        $this->assertEquals('1', $data[1]->getRawValue());

        $this->assertEquals('This is a title', $data[0]->getDisplayValue());
        $this->assertEquals('title1', $data[1]->getDisplayValue());

        $R = new \Doku_Renderer_xhtml();
        $data[0]->render($R, 'xhtml');
        $pq = \phpQuery::newDocument($R->doc);
        $this->assertEquals('This is a title', $pq->find('a')->text());
        $this->assertContains('title1', $pq->find('a')->attr('href'));

        $R = new \Doku_Renderer_xhtml();
        $data[1]->render($R, 'xhtml');
        $pq = \phpQuery::newDocument($R->doc);
        $this->assertEquals('title1', $pq->find('a')->text());
        $this->assertContains('title1', $pq->find('a')->attr('href'));
    }

    public function test_translation() {
        global $conf;
        $this->prepareTranslation();

        // lookup in english
        $dropdown = new Lookup(
            array(
                'schema' => 'translation',
                'field' => '$LANG'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            3 => 'cat',
            2 => 'dog',
            1 => 'shoe',
        );
        $this->assertEquals($expect, $dropdown->getOptions());

        // fallback to english
        $conf['lang'] = 'zh';
        $dropdown = new Lookup(
            array(
                'schema' => 'translation',
                'field' => '$LANG'
            ),
            'test',
            false,
            0
        );
        $this->assertEquals($expect, $dropdown->getOptions());

        // german
        $conf['lang'] = 'de';
        $dropdown = new Lookup(
            array(
                'schema' => 'translation',
                'field' => '$LANG'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            2 => 'Hund',
            3 => 'Katze',
            1 => 'Schuh',
        );
        $this->assertEquals($expect, $dropdown->getOptions());

        // french
        $conf['lang'] = 'fr';
        $dropdown = new Lookup(
            array(
                'schema' => 'translation',
                'field' => '$LANG'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            2 => 'chien',
            3 => 'Chat',
            1 => 'chaussure',
        );
        $this->assertEquals($expect, $dropdown->getOptions());
    }

    public function test_getOptions() {
        $this->prepareLookup();

        // lookup with titles
        $dropdown = new Lookup(
            array(
                'schema' => 'pageschema',
                'field' => 'singletitle'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            3 => 'Another Title',
            2 => 'This is a 2nd title',
            1 => 'This is a title',
        );
        $this->assertEquals($expect, $dropdown->getOptions());

        // lookup with pages
        $dropdown = new Lookup(
            array(
                'schema' => 'pageschema',
                'field' => 'singlepage'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            1 => 'title1',
            2 => 'title2',
            3 => 'title3',
        );
        $this->assertEquals($expect, $dropdown->getOptions());

    }

}
