<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Media as MediaHelper;

/**
 * GFM inline image ![alt](url) with optional title ![alt](url "title").
 *
 * Emits the same internalmedia/externalmedia handler calls as DokuWiki's
 * {{...}} media mode so renderers, indexers, and reverse renderers need no
 * changes. Width, height, cache, linking, and alignment directives are
 * accepted via the same URL-parameter vocabulary as DW media
 * (?100x200&nolink&recache, ?right, ?center, etc.) through shared parsing
 * in Helpers\Media::parseParameters() — the last `?` in the URL delimits
 * the DW parameter block, so query-bearing URLs like
 * https://example.com/img?v=2?100x100&right still work. GFM has no native
 * alignment syntax, so the `?left`/`?right`/`?center` keywords are the
 * canonical way to align an inline GFM image.
 *
 * Deliberately not supported (see skip.php for the affected spec examples):
 *
 *   - Reference-style images ![text][id] / ![text][] / ![foo] — the
 *     single-pass lexer cannot resolve forward references to [foo]: url
 *     definitions.
 *   - Pointy-bracket destinations ![alt](<foo bar>) — rarely used.
 *   - Nested brackets in alt text (![foo ![bar](x)](y), ![foo [bar](x)](y))
 *     — leftmost-match cannot reorder; outer falls back to literal.
 *   - Title HTML attribute — DokuWiki media instructions have no separate
 *     title-attribute slot (alt is used as the caption). The title parses
 *     cleanly but is discarded.
 *   - Mixed syntax: ![alt](url) inside [[dw|link]] or {{dw|media}} inside
 *     [gfm](link) — cross-syntax nesting is out of scope.
 */
class GfmMedia extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 310;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Outer shape: `![alt](url)`. Alt class forbids brackets and
        // newlines; URL slot is permissive (`[^)\n]+`) — handle() does
        // URL / title splitting post-entry, mirroring how GfmLink and DW
        // Internallink work.
        $this->Lexer->addSpecialPattern('!\[[^\[\]\n]*\]\([^)\n]+\)', $mode, 'gfm_media');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $sep    = strpos($match, '](');
        $alt    = substr($match, 2, $sep - 2);
        $inside = trim(substr($match, $sep + 2, -1));
        $url    = substr($inside, 0, strcspn($inside, " \t\n"));

        $p = MediaHelper::parseParameters($url);

        $call = (media_isexternal($p['src']) || link_isinterwiki($p['src']))
            ? 'externalmedia'
            : 'internalmedia';

        $handler->addCall(
            $call,
            [$p['src'], $alt !== '' ? $alt : null, $p['align'], $p['width'], $p['height'], $p['cache'], $p['linking']],
            $pos
        );
        return true;
    }
}
