<?php

namespace dokuwiki\test\Feed;

use dokuwiki\Feed\FeedCreator;
use dokuwiki\Feed\FeedCreatorOptions;
use dokuwiki\HTTP\DokuHTTPClient;

/**
 * @group internet
 */
class FeedCreatorTest extends \DokuWikiTest
{

    /**
     * @todo This only test the default feed, various configurations could be tested
     *
     * @return void
     * @throws \Exception
     */
    public function testValidate()
    {

        $options = new FeedCreatorOptions();
        $creator = new FeedCreator($options);
        $feed = $creator->build();

        $http = new DokuHTTPClient();
        $result = $http->post('https://validator.w3.org/feed/check.cgi', [
            'rawdata' => $feed,
            'output' => 'soap12',
        ]);

        if (!$result) {
            $this->markTestSkipped('Could not validate feed');
        }
        //print($result);

        $xml = new \SimpleXMLElement($result);
        $ns = $xml->getNamespaces(true);
        foreach ($ns as $key => $value) {
            $xml->registerXPathNamespace($key, $value);
        }

        $warningCount = (int)$xml->xpath('//m:warnings/m:warningcount')[0];
        if ($warningCount > 0) {
            $line = (int)$xml->xpath('//m:warnings/m:warninglist/warning/line')[0];
            $text = (string)$xml->xpath('//m:warnings/m:warninglist/warning/text')[0];
            $element = (string)$xml->xpath('//m:warnings/m:warninglist/warning/element')[0];
            $parent = (string)$xml->xpath('//m:warnings/m:warninglist/warning/parent')[0];

            $lines = explode("\n", $feed);
            $show = trim($lines[$line - 1]);

            $this->addWarning(
                "Feed validation produced a warning:\n" .
                "Line $line: $text\n" .
                "$parent -> $element\n" .
                $show
            );
        }

        $errorCount = (int)$xml->xpath('//m:errors/m:errorcount')[0];
        if ($errorCount > 0) {
            $line = (int)$xml->xpath('//m:errors/m:errorlist/error/line')[0];
            $text = (string)$xml->xpath('//m:errors/m:errorlist/error/text')[0];
            $element = (string)$xml->xpath('//m:errors/m:errorlist/error/element')[0];
            $parent = (string)$xml->xpath('//m:errors/m:errorlist/error/parent')[0];

            $lines = explode("\n", $feed);
            $show = trim($lines[$line - 1]);

            $this->fail(
                "Feed validation produced an error:\n" .
                "Line $line: $text\n" .
                "$parent -> $element\n" .
                $show
            );
        }

        $this->assertTrue(true);
    }


}
