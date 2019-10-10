<?php
// phpcs:ignoreFile
use dokuwiki\Cache\CacheParser;
use dokuwiki\Cache\CacheInstructions;
use dokuwiki\Cache\CacheRenderer;
use dokuwiki\Debug\DebugHelper;

/**
 * @deprecated since 2019-02-02 use \dokuwiki\Cache\Cache instead!
 */
class cache extends \dokuwiki\Cache\Cache
{
    public function __construct($key, $ext)
    {
        DebugHelper::dbgDeprecatedFunction(dokuwiki\Cache\Cache::class);
        parent::__construct($key, $ext);
    }
}

/**
 * @deprecated since 2019-02-02 use \dokuwiki\Cache\CacheParser instead!
 */
class cache_parser extends \dokuwiki\Cache\CacheParser
{

    public function __construct($id, $file, $mode)
    {
        DebugHelper::dbgDeprecatedFunction(CacheParser::class);
        parent::__construct($id, $file, $mode);
    }

}

/**
 * @deprecated since 2019-02-02 use \dokuwiki\Cache\CacheRenderer instead!
 */
class cache_renderer extends \dokuwiki\Cache\CacheRenderer
{

    public function __construct($id, $file, $mode)
    {
        DebugHelper::dbgDeprecatedFunction(CacheRenderer::class);
        parent::__construct($id, $file, $mode);
    }
}

/**
 * @deprecated since 2019-02-02 use \dokuwiki\Cache\CacheInstructions instead!
 */
class cache_instructions extends \dokuwiki\Cache\CacheInstructions
{
    public function __construct($id, $file)
    {
        DebugHelper::dbgDeprecatedFunction(CacheInstructions::class);
        parent::__construct($id, $file);
    }
}
