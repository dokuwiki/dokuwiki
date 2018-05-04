<?php

/**
 * Escapes regex characters other than (, ) and /
 *
 * @param string $str
 * @return string
 * @deprecated 2018-05-04
 */
function Doku_Lexer_Escape($str) {
    dbg_deprecated('dokuwiki\\Lexer\\Lexer::escape');
    return dokuwiki\Lexer\Lexer::escape($str);
}
