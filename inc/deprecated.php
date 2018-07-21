<?php
// phpcs:ignoreFile -- this file violates PSR2 by definition
/**
 * These classes and functions are deprecated and will be removed in future releases
 */

/**
 * @inheritdoc
 * @deprecated 2018-05-07
 */
class RemoteAccessDeniedException extends \dokuwiki\Remote\AccessDeniedException {
    /** @inheritdoc */
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        dbg_deprecated(\dokuwiki\Remote\AccessDeniedException::class);
        parent::__construct($message, $code, $previous);
    }

}

/**
 * @inheritdoc
 * @deprecated 2018-05-07
 */
class RemoteException extends \dokuwiki\Remote\RemoteException {
    /** @inheritdoc */
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
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
function Doku_Lexer_Escape($str) {
    dbg_deprecated('\\dokuwiki\\Parsing\\Lexer\\Lexer::escape()');
    return \dokuwiki\Parsing\Lexer\Lexer::escape($str);
}

/**
 * @inheritdoc
 * @deprecated 2018-06-01
 */
class setting extends \dokuwiki\plugin\config\core\Setting\Setting {
    /** @inheritdoc */
    public function __construct($key, array $params = null) {
        dbg_deprecated(\dokuwiki\plugin\config\core\Setting\Setting::class);
        parent::__construct($key, $params);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-01
 */
class setting_authtype extends \dokuwiki\plugin\config\core\Setting\SettingAuthtype {
    /** @inheritdoc */
    public function __construct($key, array $params = null) {
        dbg_deprecated(\dokuwiki\plugin\config\core\Setting\SettingAuthtype::class);
        parent::__construct($key, $params);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-01
 */
class setting_string extends \dokuwiki\plugin\config\core\Setting\SettingString {
    /** @inheritdoc */
    public function __construct($key, array $params = null) {
        dbg_deprecated(\dokuwiki\plugin\config\core\Setting\SettingString::class);
        parent::__construct($key, $params);
    }
}

/**
 * @inheritdoc
 * @deprecated 2018-06-15
 */
class PageChangelog extends \dokuwiki\ChangeLog\PageChangeLog {
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
class MediaChangelog extends \dokuwiki\ChangeLog\MediaChangeLog {
    /** @inheritdoc */
    public function __construct($id, $chunk_size = 8192)
    {
        dbg_deprecated(\dokuwiki\ChangeLog\MediaChangeLog::class);
        parent::__construct($id, $chunk_size);
    }
}

/**
 * function wrapper to process (create, trigger and destroy) an event
 *
 * @param  string   $name               name for the event
 * @param  mixed    $data               event data
 * @param  callback $action             (optional, default=NULL) default action, a php callback function
 * @param  bool     $canPreventDefault  (optional, default=true) can hooks prevent the default action
 *
 * @return mixed                        the event results value after all event processing is complete
 *                                      by default this is the return value of the default action however
 *                                      it can be set or modified by event handler hooks
 * @deprecated 2018-06-15
 */
function trigger_event($name, &$data, $action=null, $canPreventDefault=true) {
    dbg_deprecated('\dokuwiki\Extension\Event::createAndTrigger');
    return \dokuwiki\Extension\Event::createAndTrigger($name, $data, $action, $canPreventDefault);
}

/**
 * @inheritdoc
 * @deprecated 2018-06-15
 */
class Doku_Plugin_Controller extends \dokuwiki\Extension\PluginController {
    /** @inheritdoc */
    public function __construct()
    {
        dbg_deprecated(\dokuwiki\Extension\PluginController::class);
        parent::__construct();
    }
}
