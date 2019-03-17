<?php
// phpcs:ignoreFile

use dokuwiki\Debug\DebugHelper;

/**
 * @deprecated since 2019-03-17 use \dokuwiki\HTTPClient\HTTPClientException instead!
 */
class HTTPClientException extends \dokuwiki\HTTPClient\HTTPClientException {

    /**
     * @inheritdoc
     * @deprecated 2019-03-17
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        DebugHelper::dbgDeprecatedFunction(dokuwiki\HTTPClient\HTTPClientException::class);
        parent::__construct($message, $code, $previous);
    }
}

/**
 * @deprecated since 2019-03-17 use \dokuwiki\HTTPClient\HTTPClient instead!
 */
class HTTPClient extends \dokuwiki\HTTPClient\HTTPClient {

    /**
     * @inheritdoc
     * @deprecated 2019-03-17
     */
    public function __construct()
    {
        DebugHelper::dbgDeprecatedFunction(dokuwiki\HTTPClient\HTTPClient::class);
        parent::__construct();
    }
}

/**
 * @deprecated since 2019-03-17 use \dokuwiki\HTTPClient\DokuHTTPClient instead!
 */
class DokuHTTPClient extends \dokuwiki\HTTPClient\DokuHTTPClient {

    /**
     * @inheritdoc
     * @deprecated 2019-03-17
     */
    public function __construct()
    {
        DebugHelper::dbgDeprecatedFunction(dokuwiki\HTTPClient\DokuHTTPClient::class);
        parent::__construct();
    }

}
