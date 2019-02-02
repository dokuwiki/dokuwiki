<?php
// phpcs:ignoreFile

/**
 * @deprecated since 2019-02-02 use \dokuwiki\Cache\Cache instead!
 */
class cache extends \dokuwiki\Cache\Cache
{

    public function __construct($key, $ext)
    {
        trigger_error(
            'cache is deprecated since 2019-02-02. Use \dokuwiki\Cache\Cache instead',
            E_USER_DEPRECATED
        );
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
        trigger_error(
            'cache_parser is deprecated since 2019-02-02. Use \dokuwiki\Cache\CacheParser instead',
            E_USER_DEPRECATED
        );
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
        trigger_error(
            'cache_renderer is deprecated since 2019-02-02. Use \dokuwiki\Cache\CacheRenderer instead',
            E_USER_DEPRECATED
        );
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
        trigger_error(
            'cache_instructions is deprecated since 2019-02-02. Use \dokuwiki\Cache\CacheInstructions instead',
            E_USER_DEPRECATED
        );
        parent::__construct($id, $file);
    }
}
