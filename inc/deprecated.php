<?php
// phpcs:ignoreFile -- this file violates PSR2 by definition
/**
 * These classes and functions are deprecated and will be removed in future releases
 */

/**
 * @inheritdoc
 * @deprecated 2018-05-07
 */
class RemoteAccessDeniedException extends \dokuwiki\Remote\AccessDeniedException
{
    /** @inheritdoc */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        dbg_deprecated(\dokuwiki\Remote\AccessDeniedException::class);
        parent::__construct($message, $code, $previous);
    }

}

/**
 * @inheritdoc
 * @deprecated 2018-05-07
 */
class RemoteException extends \dokuwiki\Remote\RemoteException
{
    /** @inheritdoc */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        dbg_deprecated(\dokuwiki\Remote\RemoteException::class);
        parent::__construct($message, $code, $previous);
    }

}

/**
 * Escapes regex characters other than (, ) and /
 *
 * @param string $str
 * @return string
 * @deprecated 2018-05-04
 */
function Doku_Lexer_Escape($str)
{
    dbg_deprecated('\\dokuwiki\\Parsing\\Lexer\\Lexer::escape()');
    return \dokuwiki\Parsing\Lexer\Lexer::escape($str);
}

/**
 * @inheritdoc
 * @deprecated 2018-06-01
 */
class setting extends \dokuwiki\plugin\config\core\Setting\Setting
{
    /** @inheritdoc */
    public function __construct($key, array $params = null)
    {
        dbg_deprecated(\dokuwiki\plugin\config\core\Setting\Setting::class);
        parent::__construct($key, $params);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-01
 */
class setting_authtype extends \dokuwiki\plugin\config\core\Setting\SettingAuthtype
{
    /** @inheritdoc */
    public function __construct($key, array $params = null)
    {
        dbg_deprecated(\dokuwiki\plugin\config\core\Setting\SettingAuthtype::class);
        parent::__construct($key, $params);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-01
 */
class setting_string extends \dokuwiki\plugin\config\core\Setting\SettingString
{
    /** @inheritdoc */
    public function __construct($key, array $params = null)
    {
        dbg_deprecated(\dokuwiki\plugin\config\core\Setting\SettingString::class);
        parent::__construct($key, $params);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-15
 */
class PageChangelog extends \dokuwiki\ChangeLog\PageChangeLog
{
    /** @inheritdoc */
    public function __construct($id, $chunk_size = 8192)
    {
        dbg_deprecated(\dokuwiki\ChangeLog\PageChangeLog::class);
        parent::__construct($id, $chunk_size);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-15
 */
class MediaChangelog extends \dokuwiki\ChangeLog\MediaChangeLog
{
    /** @inheritdoc */
    public function __construct($id, $chunk_size = 8192)
    {
        dbg_deprecated(\dokuwiki\ChangeLog\MediaChangeLog::class);
        parent::__construct($id, $chunk_size);
    }
}

/** Behavior switch for JSON::decode() */
define('JSON_LOOSE_TYPE', 16);

/** Behavior switch for JSON::decode() */
define('JSON_STRICT_TYPE', 0);

/**
 * Encode/Decode JSON
 * @deprecated 2018-07-27
 */
class JSON
{
    protected $use = 0;

    /**
     * @param int $use JSON_*_TYPE flag
     * @deprecated  2018-07-27
     */
    public function __construct($use = JSON_STRICT_TYPE)
    {
        $this->use = $use;
    }

    /**
     * Encode given structure to JSON
     *
     * @param mixed $var
     * @return string
     * @deprecated  2018-07-27
     */
    public function encode($var)
    {
        dbg_deprecated('json_encode');
        return json_encode($var);
    }

    /**
     * Alias for encode()
     * @param $var
     * @return string
     * @deprecated  2018-07-27
     */
    public function enc($var) {
        return $this->encode($var);
    }

    /**
     * Decode given string from JSON
     *
     * @param string $str
     * @return mixed
     * @deprecated  2018-07-27
     */
    public function decode($str)
    {
        dbg_deprecated('json_encode');
        return json_decode($str, ($this->use == JSON_LOOSE_TYPE));
    }

    /**
     * Alias for decode
     *
     * @param $str
     * @return mixed
     * @deprecated  2018-07-27
     */
    public function dec($str) {
        return $this->decode($str);
    }
}

/**
 * @inheritdoc
 * @deprecated 2019-02-19
 */
class Input extends \dokuwiki\Input\Input {
    /**
     * @inheritdoc
     * @deprecated 2019-02-19
     */
    public function __construct()
    {
        dbg_deprecated(\dokuwiki\Input\Input::class);
        parent::__construct();
    }
}

/**
 * @inheritdoc
 * @deprecated 2019-02-19
 */
class PostInput extends \dokuwiki\Input\Post {
    /**
     * @inheritdoc
     * @deprecated 2019-02-19
     */
    public function __construct()
    {
        dbg_deprecated(\dokuwiki\Input\Post::class);
        parent::__construct();
    }
}

/**
 * @inheritdoc
 * @deprecated 2019-02-19
 */
class GetInput extends \dokuwiki\Input\Get {
    /**
     * @inheritdoc
     * @deprecated 2019-02-19
     */
    public function __construct()
    {
        dbg_deprecated(\dokuwiki\Input\Get::class);
        parent::__construct();
    }
}

/**
 * @inheritdoc
 * @deprecated 2019-02-19
 */
class ServerInput extends \dokuwiki\Input\Server {
    /**
     * @inheritdoc
     * @deprecated 2019-02-19
     */
    public function __construct()
    {
        dbg_deprecated(\dokuwiki\Input\Server::class);
        parent::__construct();
    }
}

/**
 * @inheritdoc
 * @deprecated 2019-03-06
 */
class PassHash extends \dokuwiki\PassHash {
    /**
     * @inheritdoc
     * @deprecated 2019-03-06
     */
    public function __construct()
    {
        dbg_deprecated(\dokuwiki\PassHash::class);
    }
}

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
