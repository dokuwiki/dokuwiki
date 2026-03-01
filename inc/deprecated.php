<?php
// phpcs:ignoreFile -- this file violates PSR2 by definition
/**
 * These classes and functions are deprecated and will be removed in future releases
 *
 * Note: when adding to this file, please also add appropriate actions to _test/rector.php
 */

use dokuwiki\Debug\DebugHelper;

/**
 * @deprecated since 2021-11-11 use \dokuwiki\Remote\IXR\Client instead!
 */
class IXR_Client extends \dokuwiki\Remote\IXR\Client
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($server, $path = false, $port = 80, $timeout = 15, $timeout_io = null)
    {
        DebugHelper::dbgDeprecatedFunction(dokuwiki\Remote\IXR\Client::class);
        parent::__construct($server, $path, $port, $timeout, $timeout_io);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Client\ClientMulticall instead!
 */
class IXR_ClientMulticall extends \IXR\Client\ClientMulticall
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($server, $path = false, $port = 80)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Client\ClientMulticall::class);
        parent::__construct($server, $path, $port);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Server\Server instead!
 */
class IXR_Server extends \IXR\Server\Server
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($callbacks = false, $data = false, $wait = false)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Server\Server::class);
        parent::__construct($callbacks, $data, $wait);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Server\IntrospectionServer instead!
 */
class IXR_IntrospectionServer extends \IXR\Server\IntrospectionServer
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct()
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Server\IntrospectionServer::class);
        parent::__construct();
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Request\Request instead!
 */
class IXR_Request extends \IXR\Request\Request
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($method, $args)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Request\Request::class);
        parent::__construct($method, $args);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Message\Message instead!
 */
class IXR_Message extends IXR\Message\Message
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($message)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Message\Message::class);
        parent::__construct($message);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\Message\Error instead!
 */
class IXR_Error extends \IXR\Message\Error
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($code, $message)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\Message\Error::class);
        parent::__construct($code, $message);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\DataType\Date instead!
 */
class IXR_Date extends \IXR\DataType\Date
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($time)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\DataType\Date::class);
        parent::__construct($time);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\DataType\Base64 instead!
 */
class IXR_Base64 extends \IXR\DataType\Base64
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($data)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\DataType\Base64::class);
        parent::__construct($data);
    }
}
/**
 * @deprecated since 2021-11-11 use \IXR\DataType\Value instead!
 */
class IXR_Value extends \IXR\DataType\Value
{
    /**
     * @inheritdoc
     * @deprecated 2021-11-11
     */
    public function __construct($data, $type = null)
    {
        DebugHelper::dbgDeprecatedFunction(IXR\DataType\Value::class);
        parent::__construct($data, $type);
    }
}

/**
 * print a newline terminated string
 *
 * You can give an indention as optional parameter
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $string  line of text
 * @param int    $indent  number of spaces indention
 * @deprecated 2023-08-31 use echo instead
 */
function ptln($string, $indent = 0)
{
    DebugHelper::dbgDeprecatedFunction('echo');
    echo str_repeat(' ', $indent) . "$string\n";
}
