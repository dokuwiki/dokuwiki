<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Escape;
use dokuwiki\Parsing\Helpers\HtmlEntity;
use dokuwiki\Parsing\Helpers\Link;
use dokuwiki\Parsing\Helpers\Media as MediaHelper;

/**
 * GFM inline link [text](url) with optional title [text](url "title").
 *
 * The link text may be either plain text (the common case) or an inline
 * image `![alt](imgUrl)` — the Markdown equivalent of DW's
 * `[[target|{{imgUrl}}]]`. The image-as-label form emits a single link
 * handler call with a media descriptor array in the label slot, reusing
 * the same flow that `Internallink` already drives. No new handler
 * instructions; renderers (xhtml, odt, metadata, …) already know how to
 * render a link whose label is a media descriptor.
 *
 * Mirrors DW's `Internallink` architecture: a permissive outer pattern
 * plus handle-time parsing, rather than encoding every GFM rule at
 * pattern level.
 *
 * Deliberately not supported (see skip.php for the affected spec examples):
 *
 *   - Reference links [text][id] / [text][] / [foo] — the single-pass
 *     lexer cannot resolve forward references to [foo]: url definitions.
 *   - Pointy-bracket destinations [link](<foo bar>) — the simplified
 *     pattern will happily match, but handle() produces an internallink
 *     with a broken src; spec tests for this stay in skip.php.
 *   - Balanced-parens inside URLs [link](foo(bar)) — matches truncate
 *     at first `)`, producing odd output; also in skip.php.
 *   - Title HTML attribute — DokuWiki link handler instructions have no
 *     title-attribute slot, and plumbing one through every renderer just
 *     for this is out of scope. The title parses cleanly but is discarded.
 *   - Mixed text + image in the label ([prefix ![alt](img) suffix](url))
 *     — matches DW's policy: Internallink only converts the label to a
 *     media descriptor when it matches `^{{…}}$` exactly.
 */
class GfmLink extends AbstractMode
{
    // URL slot character set: any non-paren / non-newline char, OR a
    // backslash-escape sequence so an escaped `\)` doesn't terminate the
    // URL early (spec examples 504/506/508). Backslash-unescape is
    // applied post-extraction; the pattern only needs to keep escaped
    // close-parens from prematurely ending the match.
    private const URL_CHAR = '(?:\\\\.|[^)\n])';

    // Label character set: forbids unescaped `[` / `]` so the outer
    // bracket pair stays balanced, but allows `\[` / `\]` so an escaped
    // bracket can appear inside the label (spec example 523). The same
    // backslash-escape trick the URL slot already uses.
    private const LABEL_CHAR = '(?:\\\\.|[^\[\]\n])';

    // Image sub-pattern reused for both the label alternative in the main
    // pattern and the image-as-label detector in handle(). No capture
    // groups here — the lexer wraps user patterns in a capture and
    // additional captures would renumber unpredictably.
    private const IMAGE_SUB = '!\[' . self::LABEL_CHAR . '*\]\(' . self::URL_CHAR . '+\)';

    /** @inheritdoc */
    public function getSort()
    {
        return 300;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Outer shape: `[text-or-image](url)`. Text class forbids
        // unescaped brackets and newlines but allows `\[` / `\]`; the
        // image alternative explicitly matches one inline image. URL
        // slot is permissive — handle() does URL / title splitting
        // post-entry, mirroring how DW Internallink parses inside `[[...]]`.
        $pattern = '\[(?!\[)(?:' . self::LABEL_CHAR . '+|' . self::IMAGE_SUB . ')\]\(' . self::URL_CHAR . '+\)';
        $this->Lexer->addSpecialPattern($pattern, $mode, 'gfm_link');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        // Detect image-as-label `[![alt](img)](target)`. Parallels
        // Internallink's `^{{…}}$` check — when the label is exactly an
        // inline image, parse it into a media descriptor; otherwise
        // treat the label as plain text.
        if (preg_match('/^\[(' . self::IMAGE_SUB . ')\]\((' . self::URL_CHAR . '+)\)$/', $match, $m)) {
            $label     = $this->parseImageDescriptor($m[1]);
            $targetUrl = $this->extractUrl($m[2]);
        } else {
            // Plain text label can't contain `]`, so the first `](` is
            // the label/target separator.
            $sep       = strpos($match, '](');
            $label     = Escape::unescapeBackslashes(substr($match, 1, $sep - 1));
            $targetUrl = $this->extractUrl(substr($match, $sep + 2, -1));
        }

        // Classify on the raw URL so windowssharelink detection sees the
        // literal `\\host\path` runs intact — GFM's `\\` → `\` collapse
        // would otherwise destroy the share prefix.
        [$call, $args] = Link::classify($targetUrl, $label);
        if ($call !== 'windowssharelink') {
            $args[0] = Escape::unescapeBackslashes($args[0]);
        }
        $handler->addCall($call, $args, $pos);
        return true;
    }

    /**
     * Extract the URL from a parenthesized payload: trim surrounding
     * whitespace, take the first whitespace-delimited token, then
     * apply GFM's URL-slot transformations (entity decoding;
     * backslash-unescape happens later, after Link::classify, because
     * windowssharelink detection needs the raw `\\` runs intact).
     * Any trailing title is discarded (no renderer slot for it).
     */
    private function extractUrl(string $inside): string
    {
        $inside = trim($inside);
        $url    = substr($inside, 0, strcspn($inside, " \t\n")); // remove optional title
        return HtmlEntity::decode($url);
    }

    /**
     * Parse an inline image sub-match `![alt](imgUrl)` into the media
     * descriptor shape Media::parseMedia() returns, so the link handler
     * can treat it as a media label identically to `[[page|{{img}}]]`.
     */
    private function parseImageDescriptor(string $imageMatch): array
    {
        $sep    = strpos($imageMatch, '](');
        $alt    = Escape::unescapeBackslashes(substr($imageMatch, 2, $sep - 2));
        $imgUrl = Escape::unescapeBackslashes($this->extractUrl(substr($imageMatch, $sep + 2, -1)));

        $p = MediaHelper::parseParameters($imgUrl);
        $type = (media_isexternal($p['src']) || link_isinterwiki($p['src']))
            ? 'externalmedia'
            : 'internalmedia';

        return [
            'type'    => $type,
            'src'     => $p['src'],
            'title'   => $alt !== '' ? $alt : null,
            'align'   => $p['align'],
            'width'   => $p['width'],
            'height'  => $p['height'],
            'cache'   => $p['cache'],
            'linking' => $p['linking'],
        ];
    }
}
