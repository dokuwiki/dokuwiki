<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * GFM ATX heading: 1-6 leading `#` characters, a mandatory space (or end of
 * line for an empty heading), and optional body; emits the same
 * header / section_open / section_close instructions as DokuWiki's Header
 * so renderers and TOC treat it identically.
 *
 * Setext headings (=== / --- underlines) are deliberately not supported —
 * they collide with DokuWiki's horizontal rule and heading delimiters.
 *
 * Leading indentation is also not supported: GFM allows 0-3 spaces before
 * the opener, but DokuWiki uses 2-space indent for Preformatted blocks
 * and that collision isn't worth untangling for a tolerance feature. The
 * opener must sit at column 0.
 */
class GfmHeader extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 50;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Entry pattern breakdown:
        //   (?<=\n)              — line start (Parser prepends a newline);
        //                          a lookbehind, so the newline is NOT part
        //                          of the match and the reported position
        //                          lands on the first `#`. Consuming it
        //                          instead would push the section-edit start
        //                          onto the blank line above the heading and
        //                          eat it on save.
        //   #{1,6}(?!#)          — 1-6 `#` characters; the lookahead
        //                          rejects 7+ so `####### foo` stays as
        //                          paragraph text
        //   (?:[ \t][^\n]*)?     — optional body starting with a space
        //                          or tab; a hash touching a letter
        //                          (`#hashtag`) has no body match and
        //                          the `(?=\n)` below fails unless the
        //                          whole line is just the hashes
        //   (?=\n)               — must end the line
        $this->Lexer->addSpecialPattern(
            '(?<=\n)#{1,6}(?!#)(?:[ \t][^\n]*)?(?=\n)',
            $mode,
            'gfm_header'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $level = strspn($match, '#');
        $title = trim(substr($match, $level));

        // Optional closing `#` run. The sequence must be preceded by
        // whitespace; a `#` touching the body (`# foo#`) is content.
        // A body that is nothing but `#`s is a closer with no title.
        if ($title !== '' && preg_match('/^#+$/', $title)) {
            $title = '';
        } elseif (preg_match('/^(.*?)[ \t]+#+$/', $title, $m)) {
            $title = rtrim($m[1]);
        }

        if ($handler->getStatus('section')) {
            $handler->addCall('section_close', [], $pos);
        }
        $handler->addCall('header', [$title, $level, $pos], $pos);
        $handler->addCall('section_open', [$level], $pos);
        $handler->setStatus('section', true);
        return true;
    }
}
