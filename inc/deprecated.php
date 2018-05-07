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
        dbg_deprecated('dokuwiki\Remote\AccessDeniedException');
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
        dbg_deprecated('dokuwiki\\Remote\\RemoteException');
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
    dbg_deprecated('dokuwiki\\Parsing\\Lexer\\Lexer::escape');
    return \dokuwiki\Parsing\Lexer\Lexer::escape($str);
}
