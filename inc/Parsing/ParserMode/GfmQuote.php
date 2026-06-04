<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Nest;
use dokuwiki\Parsing\ModeRegistry;

/**
 * Block quotes — single mode covering both DokuWiki and GFM dialects.
 *
 * Captures one or more consecutive column-0 `>`-prefixed lines via
 * addSpecialPattern. Nesting is resolved at this level by counting
 * leading `>` markers per line and emitting `quote_open` / `quote_close`
 * pairs around per-depth body segments — sub-parser recursion is
 * deliberately not used because each sub-parser invocation needs its
 * own Handler instance and threading the nesting through the registry
 * pool would only buy us back what depth-walking already provides.
 *
 * Each per-depth segment's body is sub-parsed via
 * ModeRegistry::withSubParser() so block content (lists, fenced code,
 * tables) works inside the body. The sub-parser excludes BASEONLY so
 * headers do not fire inside a blockquote — same rationale as
 * GfmListblock: header instructions drive TOC entries, section-edit
 * anchors, and section_open/section_close ranges that don't compose
 * with a `<blockquote>` container. The sub-parser also excludes
 * gfm_quote itself; nesting is handled at this level, not via
 * sub-parser recursion. When a list inside a quote re-fires gfm_quote
 * during the list-item sub-parse, the registry's pool hands the
 * inner call a different parser instance for the same exclusion key,
 * so the outer parse state is not corrupted.
 *
 * Lazy continuation is deliberately not supported. Every quote line
 * must begin with `>` at column 0; the first non-`>` line ends the
 * quote. This matches the policy GfmListblock enforces for lists —
 * markers required on every line. Trade-off: a few CommonMark
 * blockquote spec examples that rely on lazy continuation stay red,
 * but the parser stays single-pass and predictable.
 *
 * Rendering shape depends on syntax preference. Under MD-preferred
 * (`md`, `md+dw`) the sub-parser's paragraph wrapping survives:
 * a quote with one paragraph emits `<blockquote><p>...</p></blockquote>`.
 * Under DW-preferred (`dw`, `dw+md`) a post-pass flattens
 * paragraph wrapping into explicit `linebreak` calls so existing DW
 * pages keep their `<blockquote>...line1<br/>line2...</blockquote>`
 * rendering. Same `quote_open` / `quote_close` instructions in both
 * modes — no renderer change required.
 */
class GfmQuote extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 220;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        $this->registry->registerBlockEolMode('gfm_quote');
    }

    /**
     * Capture an entire blockquote in one match.
     *
     * The pattern requires a column-0 `>` on every line. The first
     * non-`>` line ends the capture (no lazy continuation). A bare `>`
     * with no body is valid — it represents an empty paragraph break
     * inside the quote (spec 240) or an empty quote (spec 239).
     *
     * The first line uses (?:^|\n)> rather than \n> so the blockquote
     * can take over when a preceding block mode (a table or a list)
     * consumed the boundary \n on its way out. Those modes' exit
     * patterns are \n by structural necessity: at the boundary there
     * is no leading unmatched content for a zero-width lookahead exit
     * to attach to, and a pure-lookahead exit would trip the lexer's
     * no-advance safety check. Accepting either a literal \n or a line
     * start (^ in PCRE multiline mode, which also matches the position
     * immediately after a consumed \n) lets the blockquote start
     * regardless. Subsequent quote lines still anchor on \n> because
     * the previous line consumed up to but not including the \n, so
     * it is always available for them.
     *
     * @param string $mode the lexer state name to wire the pattern into
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('(?:^|\n)>[^\n]*(?:\n>[^\n]*)*', $mode, 'gfm_quote');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $stripped = ltrim($match, "\n");
        $cursor = strlen($match) - strlen($stripped);

        $parsed = [];
        foreach (explode("\n", $stripped) as $line) {
            $parsed[] = $this->parseLine($line, $pos + $cursor);
            $cursor += strlen($line) + 1; // +1 for the \n consumed by explode
        }

        $currentDepth = 0;
        $buffer = [];
        $segmentStart = $pos;

        foreach ($parsed as $p) {
            if ($p['depth'] !== $currentDepth) {
                if ($buffer) {
                    $this->emitBody($handler, $segmentStart, implode("\n", $buffer));
                    $buffer = [];
                }
                while ($currentDepth < $p['depth']) {
                    $handler->addCall('quote_open', [], $pos);
                    $currentDepth++;
                }
                while ($currentDepth > $p['depth']) {
                    $handler->addCall('quote_close', [], $pos);
                    $currentDepth--;
                }
            }
            if (!$buffer) $segmentStart = $p['offset'];
            $buffer[] = $p['content'];
        }

        if ($buffer) {
            $this->emitBody($handler, $segmentStart, implode("\n", $buffer));
        }
        while ($currentDepth > 0) {
            $handler->addCall('quote_close', [], $pos + strlen($match));
            $currentDepth--;
        }

        return true;
    }

    /**
     * Parse one captured line into depth, content, and content offset.
     *
     * Counts leading `>` characters (each consuming one optional
     * trailing space) to compute the depth. The remainder of the line
     * is the content for that depth. The returned `offset` is the
     * absolute byte position of the content's first character within
     * the source (`$lineStart` plus the length of the consumed marker
     * prefix).
     *
     * `> > foo` → depth 2, content `foo`. `>>foo` → depth 2, content
     * `foo`. `>` alone → depth 1, content empty.
     *
     * @param string $line one line of captured blockquote text, with
     *     no surrounding newlines
     * @param int $lineStart absolute byte offset of the line's first
     *     character within the source
     * @return array{depth: int, content: string, offset: int}
     */
    protected function parseLine(string $line, int $lineStart): array
    {
        $depth = 0;
        $i = 0;
        $len = strlen($line);
        while ($i < $len && $line[$i] === '>') {
            $depth++;
            $i++;
            if ($i < $len && $line[$i] === ' ') $i++;
        }
        return [
            'depth'   => $depth,
            'content' => substr($line, $i),
            'offset'  => $lineStart + $i,
        ];
    }

    /**
     * Sub-parse a body segment and emit its calls inside a Nest.
     *
     * Drops `document_start` / `document_end` from the sub-parser
     * output. Under DW-preferred syntax, also runs the linebreak
     * post-pass so paragraph wrapping is flattened into explicit
     * `linebreak` calls. Empty bodies emit nothing.
     *
     * `$segmentStart` is the absolute byte offset of the segment's
     * first content character within the source. Sub-handler positions
     * are relative to the sub-parsed body, which begins at the first
     * line of the segment, so adding `$segmentStart` to each
     * sub-handler position lands the call back on the right byte in
     * the source. Lines after the first drift slightly because the
     * `>[ ]?` prefix between source lines collapses to a single `\n`
     * in the sub-parsed body — drift is bounded by the prefix length
     * (one or two bytes per line skipped).
     *
     * @param Handler $handler outer handler to emit calls on
     * @param int $segmentStart absolute byte offset of the segment's
     *     first content character within the source
     * @param string $body concatenated content of the buffered lines,
     *     separated by `\n`
     */
    protected function emitBody(Handler $handler, int $segmentStart, string $body): void
    {
        $registry = $this->registry;
        $calls = $registry->withSubParser(
            [ModeRegistry::CATEGORY_BASEONLY],
            ['gfm_quote'],
            static function ($subParser) use ($body) {
                $subParser->getHandler()->reset();
                $subParser->parse($body);
                return $subParser->getHandler()->calls;
            }
        );

        if ($calls && $calls[0][0] === 'document_start') array_shift($calls);
        if ($calls && end($calls)[0] === 'document_end') array_pop($calls);

        if ($registry->isDwPreferred()) {
            $calls = $this->flattenForDwRendering($calls);
        }

        if (!$calls) return;

        $outer = $handler->getCallWriter();
        $nest = new Nest($outer);
        $handler->setCallWriter($nest);
        foreach ($calls as $call) {
            $handler->addCall($call[0], $call[1], $segmentStart + $call[2]);
        }
        $handler->setCallWriter($nest->process());
    }

    /**
     * Flatten paragraph structure into linebreak-separated cdata.
     *
     * DW Quote historically rendered each `>`-line as a separate visible
     * line via an explicit `<br/>` between same-depth markers. To
     * preserve that rendering for DW-preferred installs, this pass:
     *
     *   1. Replaces every `p_open` and `p_close` with a `linebreak`
     *      call. After this, paragraph boundaries become two adjacent
     *      linebreaks (the close-of-prev plus the open-of-next), which
     *      matches the DW two-`<br/>`-for-blank-line shape.
     *   2. Drops the first and last `linebreak` calls so the run starts
     *      and ends with content, not break markers.
     *   3. Splits any `cdata` containing `\n` into multiple `cdata`
     *      calls separated by `linebreak` — sub-parsed paragraphs may
     *      contain soft breaks that a renderer would otherwise collapse
     *      to a single space.
     *
     * Block-level calls inside the body (list_open from a list inside
     * a quote, code, etc.) are passed through unchanged.
     *
     * @param array $calls sub-parsed call list to flatten
     * @return array the flattened call list
     */
    protected function flattenForDwRendering(array $calls): array
    {
        $stage = [];
        foreach ($calls as $call) {
            if ($call[0] === 'p_open' || $call[0] === 'p_close') {
                $stage[] = ['linebreak', [], $call[2]];
            } else {
                $stage[] = $call;
            }
        }

        while ($stage && $stage[0][0] === 'linebreak') array_shift($stage);
        while ($stage && end($stage)[0] === 'linebreak') array_pop($stage);

        $out = [];
        foreach ($stage as $call) {
            if ($call[0] === 'cdata' && str_contains($call[1][0], "\n")) {
                $parts = explode("\n", $call[1][0]);
                foreach ($parts as $i => $part) {
                    if ($i > 0) $out[] = ['linebreak', [], $call[2]];
                    if ($part !== '') $out[] = ['cdata', [$part], $call[2]];
                }
            } else {
                $out[] = $call;
            }
        }

        return $out;
    }
}
