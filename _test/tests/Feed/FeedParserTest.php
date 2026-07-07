<?php

namespace dokuwiki\test\Feed;

use dokuwiki\Feed\FeedParser;
use dokuwiki\Feed\FeedParserFile;
use dokuwiki\HTTP\DokuHTTPClient;

/**
 * Tests for fetching and parsing remote feeds
 */
class FeedParserTest extends \DokuWikiTest
{
    protected const FEED = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel>
<title>Example Feed</title>
<item><title>First Item</title><link>http://example.com/1</link><description>One</description></item>
<item><title>Second Item</title><link>http://example.com/2</link><description>Two</description></item>
</channel></rss>
XML;

    /**
     * Builds a FeedParserFile that fetches through a mocked DokuHTTPClient.
     *
     * FeedParserFile creates its client in initHTTPClient() and maps the
     * DokuHTTPClient fields onto the SimplePie response in its constructor, so we stub
     * the factory method and then run the real constructor against the canned response.
     *
     * @return FeedParserFile
     */
    protected function fetchedFile($status, $error, $body, $headers = ['content-type' => 'text/xml'])
    {
        $http = $this->createMock(DokuHTTPClient::class);
        $http->method('sendRequest')->willReturnCallback(
            function () use ($http, $status, $error, $body, $headers) {
                $http->status = $status;
                $http->error = $error;
                $http->resp_body = $body;
                $http->resp_headers = $headers;
                return $status >= 200 && $status < 300;
            }
        );

        $file = $this->getMockBuilder(FeedParserFile::class)
            ->onlyMethods(['initHTTPClient'])
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('initHTTPClient')->willReturn($http);
        $file->__construct('http://example.com/feed.xml');

        return $file;
    }

    /**
     * On success DokuHTTPClient reports an empty string error and exposes the status
     * separately. SimplePie's FileClient rejects any response whose error is non-null
     * while the status code is zero, so the fetched file has to carry a real status code
     * and a null error. Regression test for feed aggregation breaking after the
     * SimplePie 1.9 upgrade.
     */
    public function testSuccessfulResponseIsAcceptedBySimplePie()
    {
        $file = $this->fetchedFile(200, '', self::FEED);

        $this->assertSame(200, $file->get_status_code());
        $this->assertNull($file->error);
        $this->assertNotEmpty($file->get_body_content());
    }

    /**
     * A genuine connection failure must stay distinguishable from a success
     */
    public function testFailedResponseKeepsStatusAndError()
    {
        $file = $this->fetchedFile(-100, 'Could not connect to server', '');

        $this->assertSame(-100, $file->get_status_code());
        $this->assertSame('Could not connect to server', $file->error);
    }

    /**
     * DokuHTTPClient stores a header that occurred more than once as an array of
     * values. Those must be flattened into SimplePie's list representation instead of
     * being cast to the literal string "Array".
     */
    public function testRepeatedHeadersAreFlattened()
    {
        $file = $this->fetchedFile(200, '', self::FEED, [
            'content-type' => 'text/xml',
            'set-cookie' => ['a=1', 'b=2'],
        ]);

        $this->assertSame(['text/xml'], $file->get_header('content-type'));
        $this->assertSame(['a=1', 'b=2'], $file->get_header('set-cookie'));
    }

    /**
     * The fetched body is parsed into feed items
     */
    public function testFeedItemsAreParsed()
    {
        $feed = new FeedParser();
        $feed->enable_order_by_date(false);
        $feed->set_feed_url('http://example.com/feed.xml');
        $feed->set_file($this->fetchedFile(200, '', self::FEED));
        $rc = $feed->init();

        $this->assertTrue($rc);
        $this->assertSame(2, $feed->get_item_quantity());
        $this->assertSame('First Item', $feed->get_item(0)->get_title());
        $this->assertSame('http://example.com/1', $feed->get_item(0)->get_permalink());
        $this->assertSame('Second Item', $feed->get_item(1)->get_title());
    }
}
