<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\GfmLists;
use dokuwiki\Parsing\Handler\Nest;
use dokuwiki\Parsing\ModeRegistry;

/**
 * GFM list block.
 *
 * Captures an entire list block atomically (one addSpecialPattern match) and
 * walks the captured text in handle(), grouping lines into items. A pooled
 * sub-parser is acquired from the ModeRegistry for the duration of the per-item
 * loop so each item's body is dedented to its content column and parsed by it,
 * and block content - paragraphs, fenced code, blockquotes, plugin blocks -
 * works inside items uniformly without depending on column-0 anchoring of
 * nested mode patterns. If any nested mode requests a sub-parser with the
 * same exclusion key while ours is in use, the registry's pool hands them a
 * different slot so their reset() does not corrupt our state.
 *
 * Sub-parser mode set: every active mode except CATEGORY_BASEONLY (i.e. no
 * Header inside list items, since `<h1>`-`<h6>` inside `<li>` is never
 * desirable and section nesting must not span into items) and gfm_listblock
 * itself (defensive guard against lexer re-entry on pathological inputs;
 * normal nested lists are caught by the outer pattern instead).
 *
 * Each item's sub-parsed calls are wrapped in a nest instruction (see
 * Handler\Nest) before they reach the outer handler. The sub-parse has
 * already run its own finalize - the call-writer rewriters plus the
 * Block paragraph pass - producing a complete instruction sequence for
 * the item. The main handler finalizes the whole document afterwards;
 * wrapping the item in nest keeps it opaque to that second pass (Block
 * copies a nest call verbatim instead of descending into it), so the
 * item's block structure is preserved and replayed as-is by the renderer
 * base class. This is the isolation Footnote has always relied on nest
 * for, so nested block content is not re-stitched by the outer rewriters.
 * Nest also merges adjacent cdata and drops stray eol as it buffers,
 * sealing the item boundary.
 *
 * Indentation rule: depth = (indent / 2) + 1. Tabs become two spaces. 1- and
 * 3-space indents round down. Marker characters: -, *, + (unordered) and
 * digits followed by . or ) (ordered). Nested lists are caught by the
 * outer pattern (each marker at any 2-space-multiple indent is its own
 * item at the corresponding depth) and stitched back into nested HTML by
 * the GfmLists rewriter.
 */
class GfmListblock extends AbstractMode
{
    /**
     * Regex fragment matching one list marker.
     *
     * Either an unordered marker (`-`, `*`, `+`) or an ordered marker
     * (1-9 digits followed by `.` or `)`). Used by the entry pattern in
     * connectTo() and by the per-line classifier in parseItems().
     */
    protected const MARKER = '(?:[-*+]|\d{1,9}[.)])';

    /** @inheritdoc */
    public function getSort()
    {
        return 10;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        $this->registry->registerBlockEolMode('gfm_listblock');
    }

    /**
     * Register the special pattern that captures a whole list block.
     *
     * The pattern starts on a marker line (any indent) and then loops over
     * four alternatives until none matches:
     *
     *   1. A subsequent marker line at any indent.
     *   2. An indented continuation line (>= 2 leading spaces with content).
     *   3. A blank line followed by indented content (any number of
     *      intervening blank lines tolerated via the lookahead).
     *   4. A blank line followed by a next marker (same multi-blank
     *      tolerance as alt 3).
     *
     * The block ends naturally when none of the alternatives match — for
     * example a column-0 non-marker line, or two-or-more blank lines
     * followed by non-list content.
     *
     * @inheritdoc
     */
    public function connectTo($mode)
    {
        $pattern =
            '\n[ \t]*' . self::MARKER . '(?:[ \t][^\n]*|(?=\n))' .
            '(?:' .
                '\n[ \t]*' . self::MARKER . '(?:[ \t][^\n]*|(?=\n))' .
            '|' . '\n[ \t]{2,}\S[^\n]*' .
            '|' . '\n[ \t]*(?=(?:\n[ \t]*)*\n[ \t]{2,}\S)' .
            '|' . '\n[ \t]*(?=(?:\n[ \t]*)*\n[ \t]*' . self::MARKER . ')' .
            ')*';
        $this->Lexer->addSpecialPattern($pattern, $mode, 'gfm_listblock');
    }

    /**
     * Convert the captured block into handler calls.
     *
     * Sequence:
     *   1. parseItems() splits the captured text into per-item records.
     *   2. Install GfmLists as a CallWriter rewriter on the main handler.
     *   3. Emit list_open carrying the first item's marker — the rewriter's
     *      handleListOpen opens the `<ul>`/`<ol>` and the first `<li>`.
     *   4. For each item:
     *        - If not the first, emit list_item (closes the previous `<li>`
     *          and opens a new one in the rewriter).
     *        - Sub-parse the dedented item body via the cached sub-parser.
     *        - Filter document_start/end and the outer p_open/p_close pair
     *          for tight items (single paragraph).
     *        - Wrap the filtered calls in a Nest so the main handler's
     *          Block rewriter treats them as opaque.
     *   5. Emit list_close and finalise the GfmLists rewriter.
     *
     * @inheritdoc
     */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $items = $this->parseItems($match);
        if (empty($items)) {
            $handler->addCall('cdata', [$match], $pos);
            return true;
        }

        $handler->setCallWriter(new GfmLists($handler->getCallWriter()));
        $handler->addCall('list_open', [$items[0]['markerMatch']], $pos);

        $registry = $this->registry;
        $excludeCats = [ModeRegistry::CATEGORY_BASEONLY];
        $excludeModes = ['gfm_listblock'];
        $subParser = $registry->acquireSubParser($excludeCats, $excludeModes);
        $subHandler = $subParser->getHandler();

        foreach ($items as $i => $item) {
            $itemPos = $pos + $item['offset'];
            if ($i > 0) {
                $handler->addCall('list_item', [$item['markerMatch']], $itemPos);
            }

            $subHandler->reset();
            $subParser->parse($item['body']);
            $itemCalls = $this->filterSubCalls($subHandler->calls);
            if (empty($itemCalls)) continue; // empty item — nothing to emit

            // Wrap the item content in a Nest so the outer handler's
            // finalize (its rewriters and Block pass) does not re-process
            // this already-finalized sub-tree: Block copies a nest call
            // verbatim and the renderer base class replays it as-is.
            $outer = $handler->getCallWriter();
            $nest = new Nest($outer);
            $handler->setCallWriter($nest);
            foreach ($itemCalls as $call) {
                // sub-handler positions are relative to the item body; offset
                // them back into the source so section-edit anchors work.
                $handler->addCall($call[0], $call[1], $itemPos + $call[2]);
            }
            $handler->setCallWriter($nest->process());
        }

        $registry->releaseSubParser($excludeCats, $excludeModes);

        $handler->addCall('list_close', [], $pos + strlen($match));
        $reWriter = $handler->getCallWriter();
        $handler->setCallWriter($reWriter->process());

        return true;
    }

    /**
     * Walk the captured block, grouping lines into items.
     *
     * Each returned item describes one list_item: its marker (in the form
     * "\n{indent}{marker}" so GfmLists::interpretSyntax can parse it), the
     * dedented body, dedent column, and absolute offset within $match.
     *
     * Lines are classified as marker / continuation / blank. A marker line
     * starts a new item; continuation and blank lines accumulate into the
     * current item's body. Continuation lines are dedented by up to
     * indent + marker_width + 1 leading spaces (the item's content column
     * for single-space-after-marker cases). Blank lines are kept as empty
     * body lines while they're in the middle of the body and stripped
     * from the trailing edge by joinBody() so single-paragraph items
     * parse tight.
     *
     * @param string $match the raw special-pattern match (starts with \n)
     * @return array<int, array{markerMatch: string, dedent: int, body: string, offset: int}>
     */
    protected function parseItems($match)
    {
        $stripped = ltrim($match, "\n");
        $offsetBase = strlen($match) - strlen($stripped);
        $lines = explode("\n", $stripped);

        $items = [];
        $current = null;
        $bodyLines = [];
        $cursor = $offsetBase;

        foreach ($lines as $line) {
            $isMarker = preg_match(
                '/^([ \t]*)(' . self::MARKER . ')(?:[ \t](.*)|$)/',
                $line,
                $m
            );

            if ($isMarker) {
                if ($current !== null) {
                    $current['body'] = $this->joinBody($bodyLines);
                    $items[] = $current;
                }
                $indent = str_replace("\t", "  ", $m[1]);
                $marker = $m[2];
                $firstLine = $m[3] ?? '';
                $current = [
                    'markerMatch' => "\n" . $indent . $marker,
                    'dedent' => strlen($indent) + strlen($marker) + 1,
                    'offset' => $cursor,
                ];
                $bodyLines = [$firstLine];
            } elseif ($current !== null) {
                if (trim($line) === '') {
                    $bodyLines[] = '';
                } else {
                    $expanded = str_replace("\t", "  ", $line);
                    $available = strlen($expanded) - strlen(ltrim($expanded, ' '));
                    $strip = min($current['dedent'], $available);
                    $bodyLines[] = substr($expanded, $strip);
                }
            }

            $cursor += strlen($line) + 1; // +1 for the \n consumed by explode
        }

        if ($current !== null) {
            $current['body'] = $this->joinBody($bodyLines);
            $items[] = $current;
        }

        return $items;
    }

    /**
     * Join body lines into a string, trimming trailing blank lines.
     *
     * Trailing blanks would reach the sub-parser and cause Block to wrap
     * the otherwise-single paragraph content in `p_open`/`p_close`,
     * forcing a tight item into loose-item shape. Stripping them here
     * preserves the tight rendering for items that look tight in source.
     *
     * @param string[] $lines
     */
    protected function joinBody(array $lines): string
    {
        return rtrim(implode("\n", $lines), "\n");
    }

    /**
     * Filter the sub-parser's flat call list before nest-wrapping it.
     *
     * Drops `document_start` / `document_end` (always emitted by
     * Handler::finalize), and strips the outer `p_open` / `p_close` pair
     * for tight items so their content sits inline inside `<li>`. Loose
     * items (multiple paragraphs, more than one `p_open`) keep their
     * inner pairs untouched. The filtered calls are then wrapped in a
     * Nest by handle() before they reach the GfmLists rewriter.
     *
     * @param array $calls
     * @return array
     */
    protected function filterSubCalls(array $calls)
    {
        if ($calls && $calls[0][0] === 'document_start') array_shift($calls);
        if ($calls && end($calls)[0] === 'document_end') array_pop($calls);

        $pCount = 0;
        foreach ($calls as $c) {
            if ($c[0] === 'p_open') $pCount++;
        }

        if (
            $pCount === 1
            && $calls[0][0] === 'p_open'
            && end($calls)[0] === 'p_close'
        ) {
            array_shift($calls);
            array_pop($calls);
        }

        return $calls;
    }
}
