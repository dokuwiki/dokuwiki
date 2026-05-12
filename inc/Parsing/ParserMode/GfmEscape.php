<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Escape;

/**
 * GFM backslash escapes: a backslash before any ASCII punctuation
 * character produces the literal punctuation character; the backslash
 * itself is consumed and the following char loses any markup meaning.
 *
 * Backslashes before any other character (letters, digits, multibyte,
 * spaces, tabs, newlines) are NOT escapes — those sequences stay
 * literal because the pattern doesn't match them and the lexer leaves
 * them as cdata.
 *
 * Sort 5 places this mode ahead of every other inline mode so that
 * leftmost-then-priority resolution claims `\X` before any competing
 * delimiter (emphasis `*`, heading `#`, link `[`, …) can match the
 * unescaped char.
 *
 * Category SUBSTITUTION (alongside Smiley and Entity) so the mode is
 * reachable everywhere those run: inside paragraphs, formatting
 * modes (emphasis, strong, deleted), list items, table cells, headers
 * — every container whose allowedModes include SUBSTITUTION. Whole-span
 * code modes (GfmCode, GfmFile, GfmBacktickSingle, GfmBacktickDouble)
 * capture their entire body in one regex shot and therefore bypass
 * GfmEscape on their content — matching GFM's rule that escapes don't
 * fire inside code blocks or code spans.
 *
 * Modes that capture a literal string and need GFM unescape applied
 * post-hoc (link URL/label, fence info string) call
 * {@see \dokuwiki\Parsing\Helpers\Escape::unescapeBackslashes()} from
 * their handle() — same character class.
 */
class GfmEscape extends AbstractMode
{
    public function __construct()
    {
        $this->allowedModes = [];
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 5;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            '\\\\' . Escape::PUNCTUATION_CHAR_CLASS,
            $mode,
            'gfm_escape'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('cdata', [substr($match, 1)], $pos);
        return true;
    }
}
