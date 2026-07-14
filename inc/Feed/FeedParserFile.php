<?php

namespace dokuwiki\Feed;

use dokuwiki\HTTP\DokuHTTPClient;
use SimplePie\File;

/**
 * Fetch an URL using our own HTTPClient
 *
 * Replaces SimplePie's own File class.
 */
class FeedParserFile extends File
{
    /** @var DokuHTTPClient */
    protected $http;

    /** @var string the requested URL */
    protected $requestUrl;
    /** @var int the HTTP status code of the response */
    protected $responseStatus;
    /** @var array<string, string[]> response headers in SimplePie's representation */
    protected $responseHeaders = [];
    /** @var string the response body */
    protected $responseBody = '';

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Fetches the given URL through DokuHTTPClient
     *
     * SimplePie creates this object through its registry and reads the response via the
     * get_*() methods below, so the fetch has to happen here in the constructor.
     *
     * @inheritdoc
     */
    public function __construct($url)
    {
        $this->http = $this->initHTTPClient();
        $this->success = $this->http->sendRequest($url);

        $this->requestUrl = $url;
        // DokuHTTPClient reports transport failures as negative pseudo statuses (-100 etc.),
        // but SimplePie only surfaces our error message when the status code is 0
        $this->responseStatus = max(0, (int)$this->http->status);
        $this->responseBody = (string)$this->http->resp_body;
        $this->responseHeaders = $this->normalizeHeaders($this->http->resp_headers);
        // DokuHTTPClient uses an empty string for "no error", but SimplePie's FileClient
        // treats any non-null error combined with a zero status code as a failed request
        $this->error = $this->http->error ?: null;
    }

    /**
     * Creates the HTTP client used to fetch the feed
     *
     * Separated out so tests can inject a client with a canned response
     *
     * @return DokuHTTPClient
     */
    protected function initHTTPClient()
    {
        return new DokuHTTPClient();
    }

    /**
     * Converts DokuHTTPClient's "name => value" headers into SimplePie's
     * "name => [values]" representation
     *
     * A header that occurred more than once is stored by DokuHTTPClient as an array
     * of values, so each value is normalized on its own and comma-separated lists are
     * split into their individual parts.
     *
     * @param array<string, string|string[]> $headers
     * @return array<string, string[]>
     */
    protected function normalizeHeaders($headers)
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $values = [];
            foreach ((array)$value as $line) {
                foreach (explode(',', (string)$line) as $part) {
                    $values[] = trim($part);
                }
            }
            $normalized[$name] = $values;
        }
        return $normalized;
    }

    // the following methods implement SimplePie's Response interface and have to keep its naming
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    /** @inheritdoc */
    public function get_final_requested_uri(): string
    {
        return (string)$this->requestUrl;
    }

    /** @inheritdoc */
    public function get_permanent_uri(): string
    {
        return (string)$this->requestUrl;
    }

    /** @inheritdoc */
    public function get_status_code(): int
    {
        return $this->responseStatus;
    }

    /** @inheritdoc */
    public function get_headers(): array
    {
        return $this->responseHeaders;
    }

    /** @inheritdoc */
    public function has_header(string $name): bool
    {
        return isset($this->responseHeaders[strtolower($name)]);
    }

    /** @inheritdoc */
    public function get_header(string $name): array
    {
        return $this->responseHeaders[strtolower($name)] ?? [];
    }

    /** @inheritdoc */
    public function get_header_line(string $name): string
    {
        return implode(', ', $this->get_header($name));
    }

    /** @inheritdoc */
    public function get_body_content(): string
    {
        return $this->responseBody;
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}
