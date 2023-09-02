<?php

namespace dokuwiki;

use SimplePie\File;
use SimplePie\SimplePie;
use dokuwiki\HTTP\DokuHTTPClient;

/**
 * Fetch an URL using our own HTTPClient
 *
 * Replaces SimplePie's own class
 */
class FeedParserFile extends File
{
    protected $http;
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Inititializes the HTTPClient
     *
     * We ignore all given parameters - they are set in DokuHTTPClient
     *
     * @inheritdoc
     */
    public function __construct(
        $url
    ) {
        $this->http = new DokuHTTPClient();
        $this->success = $this->http->sendRequest($url);

        $this->headers = $this->http->resp_headers;
        $this->body = $this->http->resp_body;
        $this->error = $this->http->error;

        $this->method = SimplePie::FILE_SOURCE_REMOTE | SimplePie::FILE_SOURCE_FSOCKOPEN;

        return $this->success;
    }

    /** @inheritdoc */
    public function headers()
    {
        return $this->headers;
    }

    /** @inheritdoc */
    public function body()
    {
        return $this->body;
    }

    /** @inheritdoc */
    public function close()
    {
        return true;
    }
}
