<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * GFM inline link [text](url) with optional title [text](url "title").
 *
 * Deliberately not supported (see skip.php for the affected spec examples):
 *
 *   - Reference links [text][id] / [text][] / [foo] — the single-pass
 *     lexer cannot resolve forward references to [foo]: url definitions.
 *   - Pointy-bracket destinations [link](<foo bar>) — rarely used,
 *     regex cost outweighs the benefit.
 *   - Balanced-parens inside URLs [link](foo(bar)) — uncommon, complex.
 *   - Image-in-link [![alt](img)](url) — requires GfmMedia plus nested
 *     recursion across modes.
 *   - Title HTML attribute — DokuWiki link handler instructions have no
 *     title-attribute slot, and plumbing one through every renderer just
 *     for this is out of scope. The title parses cleanly but is discarded.
 */
class GfmLink extends AbstractMode
{
    use LinkDispatch;

    /** @inheritdoc */
    public function getSort()
    {
        return 300;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Pattern breakdown:
        //   \[(?!\[)               — single `[`, not part of DW `[[`
        //   [^\[\]\n]+              — text: no nested brackets, single line
        //   \]\(                   — `]` immediately followed by `(` (GFM
        //                             forbids whitespace between them)
        //   \s*                    — optional whitespace around the URL
        //   [^\s()\n]+             — URL: no whitespace, no parens, single line
        //   (?:\s+(?:"[^"\n]*"
        //          |'[^'\n]*'))?   — optional title in "..." or '...'
        //   \s*\)                  — optional trailing whitespace, close paren
        $pattern = '\[(?!\[)[^\[\]\n]+\]\('
            . '\s*[^\s()\n]+'
            . '(?:\s+(?:"[^"\n]*"|\'[^\'\n]*\'))?'
            . '\s*\)';
        $this->Lexer->addSpecialPattern($pattern, $mode, 'gfm_link');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        // The entry pattern has already validated the `[text](url)`
        // shape, so we can destructure with plain string ops. Split on
        // `](` to separate text from "url and optional title"; the URL
        // is the first whitespace-delimited token of the remainder, and
        // anything after it is the title — discarded, since DokuWiki
        // link instructions have no title-attribute slot.
        $sep    = strpos($match, '](');
        $text   = substr($match, 1, $sep - 1);
        $inside = trim(substr($match, $sep + 2, -1));
        $url    = substr($inside, 0, strcspn($inside, " \t\n"));

        $this->dispatchLink($url, $text, $pos, $handler);
        return true;
    }
}
