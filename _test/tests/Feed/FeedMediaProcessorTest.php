<?php

namespace dokuwiki\test\Feed;

use dokuwiki\Feed\FeedMediaProcessor;
use DOMWrap\Document;

class FeedMediaProcessorTest extends \DokuWikiTest
{

    public function provideData()
    {
        // an Item returned by FeedCreator::fetchItemsFromRecentChanges()
        yield ([
            array(
                'date' => 1705511543,
                'ip' => '::1',
                'type' => 'C',
                'id' => 'wiki:dokuwiki-128.png',
                'user' => 'testuser',
                'sum' => 'created',
                'extra' => '',
                'sizechange' => 52618,
                'perms' => 8,
                'mode' => 'media',
            ),
            1705511543, // fixed revision
            ['testuser@undisclosed.example.com', 'Arthur Dent'], // proper author
            'created', // summary
        ]);

        // FeedCreator::fetchItemsFromNamespace() currently does not support media files
        // FeedCreator::fetchItemsFromSearch() currently does not support media files
    }


    /**
     * @dataProvider provideData
     */
    public function testProcessing($data, $expectedMtime, $expectedAuthor, $expectedSummary)
    {
        global $conf;
        $conf['useacl'] = 1;
        $conf['showuseras'] = 'username';
        $conf['useheading'] = 1;

        $proc = new FeedMediaProcessor($data);

        $this->assertEquals('wiki:dokuwiki-128.png', $proc->getId());
        $this->assertEquals('dokuwiki-128.png', $proc->getTitle());
        $this->assertEquals($expectedAuthor, $proc->getAuthor());
        $this->assertEquals($expectedMtime, $proc->getRev());
        $this->assertEquals(null, $proc->getPrev());
        $this->assertTrue($proc->isExisting());
        $this->assertTrue($proc->isExisting());
        $this->assertEquals(['wiki'], $proc->getCategory());
        $this->assertEquals($expectedSummary, $proc->getSummary());

        $this->assertEquals(
            "http://wiki.example.com/doku.php?image=wiki%3Adokuwiki-128.png&ns=wiki&rev=$expectedMtime&do=media",
            $proc->getURL('page')
        );
        $this->assertEquals(
            "http://wiki.example.com/doku.php?image=wiki%3Adokuwiki-128.png&ns=wiki&rev=$expectedMtime&tab_details=history&do=media",
            $proc->getURL('rev')
        );
        $this->assertEquals(
            "http://wiki.example.com/doku.php?image=wiki%3Adokuwiki-128.png&ns=wiki&do=media",
            $proc->getURL('current')
        );
        $this->assertEquals(
            "http://wiki.example.com/doku.php?image=wiki%3Adokuwiki-128.png&ns=wiki&rev=$expectedMtime&tab_details=history&media_do=diff&do=media",
            $proc->getURL('diff')
        );


        $doc = new Document();
        $doc->html($proc->getBody('diff'));
        $th = $doc->find('table th');
        $this->assertGreaterThanOrEqual(2, $th->count());

        $doc = new Document();
        $doc->html($proc->getBody('htmldiff'));
        $th = $doc->find('table th');
        $this->assertGreaterThanOrEqual(2, $th->count());

        $doc = new Document();
        $doc->html($proc->getBody('html'));
        $home = $doc->find('img');
        $this->assertEquals(1, $home->count());
    }

}
