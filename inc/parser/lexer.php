<?php

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
