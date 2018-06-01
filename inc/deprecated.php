<?php

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
