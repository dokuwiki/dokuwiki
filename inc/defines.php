<?php
/**
 * Set up globally available constants
 */

/**
 * Auth Levels
 * @file inc/auth.php
 */
define('AUTH_NONE', 0);
define('AUTH_READ', 1);
define('AUTH_EDIT', 2);
define('AUTH_CREATE', 4);
define('AUTH_UPLOAD', 8);
define('AUTH_DELETE', 16);
define('AUTH_ADMIN', 255);

/**
 * Message types
 * @see msg()
 */
define('MSG_PUBLIC', 0);
define('MSG_USERS_ONLY', 1);
define('MSG_MANAGERS_ONLY', 2);
define('MSG_ADMINS_ONLY', 4);

/**
 * Lexer constants
 * @see \dokuwiki\Parsing\Lexer\Lexer
 */
define('DOKU_LEXER_ENTER', 1);
define('DOKU_LEXER_MATCHED', 2);
define('DOKU_LEXER_UNMATCHED', 3);
define('DOKU_LEXER_EXIT', 4);
define('DOKU_LEXER_SPECIAL', 5);

/**
 * Constants for known core changelog line types.
 * @file inc/changelog.php
 */
define('DOKU_CHANGE_TYPE_CREATE', 'C');
define('DOKU_CHANGE_TYPE_EDIT', 'E');
define('DOKU_CHANGE_TYPE_MINOR_EDIT', 'e');
define('DOKU_CHANGE_TYPE_DELETE', 'D');
define('DOKU_CHANGE_TYPE_REVERT', 'R');

/**
 * Changelog filter constants
 * @file inc/changelog.php
 */
define('RECENTS_SKIP_DELETED', 2);
define('RECENTS_SKIP_MINORS', 4);
define('RECENTS_SKIP_SUBSPACES', 8);
define('RECENTS_MEDIA_CHANGES', 16);
define('RECENTS_MEDIA_PAGES_MIXED', 32);
define('RECENTS_ONLY_CREATION', 64);

/**
 * Media error types
 * @file inc/media.php
 */
define('DOKU_MEDIA_DELETED', 1);
define('DOKU_MEDIA_NOT_AUTH', 2);
define('DOKU_MEDIA_INUSE', 4);
define('DOKU_MEDIA_EMPTY_NS', 8);
