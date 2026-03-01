<?php

namespace dokuwiki\test\Feed;

use dokuwiki\Feed\FeedPageProcessor;
use DOMWrap\Document;

class FeedPageProcessorTest extends \DokuWikiTest
{

    public function provideData()
    {
        // an Item returned by FeedCreator::fetchItemsFromRecentChanges()
        yield ([
            [
                'date' => 1705501370,
                'ip' => '::1',
                'type' => 'E',
                'id' => 'wiki:dokuwiki',
                'user' => 'testuser',
                'sum' => 'test editing',
                'extra' => '',
                'sizechange' => 41,
                'perms' => 8,
                'mode' => 'page',
            ],
            1705501370, // fixed revision
            ['testuser@undisclosed.example.com', 'Arthur Dent'], // proper author
            'test editing', // summary
        ]);

        // an Item returned by FeedCreator::fetchItemsFromNamespace()
        yield ([
            [
                'id' => 'wiki:dokuwiki',
                'ns' => 'wiki',
                'perm' => 8,
                'type' => 'f',
                'level' => 1,
                'open' => true,
            ],
            null, // current revision
            ['anonymous@undisclosed.example.com', 'Anonymous'], // unknown author
            '', // no summary
        ]);

        // an Item returned by FeedCreator::fetchItemsFromSearch()
        yield ([
            [
                'id' => 'wiki:dokuwiki',
            ],
            null, // current revision
            ['anonymous@undisclosed.example.com', 'Anonymous'], // unknown author
            '', // no summary
        ]);
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

        // if no expected mtime is given, we expect the filemtime of the page
        // see https://github.com/dokuwiki/dokuwiki/pull/4156#issuecomment-1911842452 why we can't
        // create this in the data provider
        if ($expectedMtime === null) {
            $expectedMtime = filemtime(wikiFN($data['id']));
        }

        $proc = new FeedPageProcessor($data);

        $this->assertEquals('wiki:dokuwiki', $proc->getId());
        $this->assertEquals('DokuWiki', $proc->getTitle());
        $this->assertEquals($expectedAuthor, $proc->getAuthor());
        $this->assertEquals($expectedMtime, $proc->getRev());
        $this->assertEquals(null, $proc->getPrev());
        $this->assertTrue($proc->isExisting());
        $this->assertEquals(['wiki'], $proc->getCategory());
        $this->assertStringContainsString('standards compliant', $proc->getAbstract());
        $this->assertEquals($expectedSummary, $proc->getSummary());

        $this->assertEquals(
            "http://wiki.example.com/doku.php?id=wiki:dokuwiki&rev=$expectedMtime",
            $proc->getURL('page')
        );
        $this->assertEquals(
            "http://wiki.example.com/doku.php?id=wiki:dokuwiki&rev=$expectedMtime&do=revisions",
            $proc->getURL('rev')
        );
        $this->assertEquals(
            'http://wiki.example.com/doku.php?id=wiki:dokuwiki',
            $proc->getURL('current')
        );
        $this->assertEquals(
            "http://wiki.example.com/doku.php?id=wiki:dokuwiki&rev=$expectedMtime&do=diff",
            $proc->getURL('diff')
        );

        $diff = explode("\n", $proc->getBody('diff'));
        $this->assertEquals('<pre>', $diff[0]);
        $this->assertStringStartsWith('@@', $diff[1]);

        $doc = new Document();
        $doc->html($proc->getBody('htmldiff'));
        $th = $doc->find('table th');
        $this->assertGreaterThanOrEqual(2, $th->count());

        $doc = new Document();
        $doc->html($proc->getBody('html'));
        $home = $doc->find('a[href^="https://www.dokuwiki.org/manual"]');
        $this->assertGreaterThanOrEqual(1, $home->count());

        $this->assertStringContainsString('standards compliant', $proc->getBody('abstract'));
    }

}
