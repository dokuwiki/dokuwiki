<?php

/**
 * GFM spec examples that GfmSpecTest should skip, keyed by example number
 * (as numbered in spec.txt / the rendered spec).
 *
 * Add entries here ONLY for behavior DokuWiki has explicitly decided not to
 * implement — not for features that are merely pending. Unimplemented
 * features should show as real failures so they remain visible TODOs on
 * the branch.
 *
 * Each value is a short human-readable reason that will appear in phpunit's
 * skip output.
 */

return [
    // --------------------------------------------------------------------
    // Code-span edge cases that collide with project-wide decisions
    // (no raw HTML, no GFM angle-bracket autolinks, typography on by
    // default) or with the single-pass lexer's limits.
    // --------------------------------------------------------------------
    351 => 'code span vs. emphasis: cross-positional precedence would require'
         . ' a pre-scan pass — the single-pass lexer matches leftmost-first'
         . ' and cannot reject an earlier emphasis opener because a later'
         . ' backtick span would consume its closer',
    353 => 'code span: the trailing `"` outside the span is converted to a'
         . ' curly quote by DokuWiki typography, diverging from the spec HTML',
    354 => 'raw HTML tag pass-through: DokuWiki does not render raw HTML by'
         . ' default; `<html>` blocks are the opt-in',
    356 => 'GFM angle-bracket autolink `<http://…>`: not implemented — we'
         . ' rely on DokuWiki\'s existing bare-URL detection, which does not'
         . ' parse `<URL>` form',

    // --------------------------------------------------------------------
    // CommonMark §6.2 flanking-delimiter analysis — deliberately not
    // implemented. DokuWiki's regex lexer uses leftmost-match and cannot
    // apply CommonMark's left/right-flanking rules that distinguish
    // word-chars, whitespace, and punctuation for `*`/`_` delimiters, or
    // the "multiple-of-3" rule for overlapping runs. These examples all
    // rely on that machinery.
    // --------------------------------------------------------------------

    // Unicode whitespace in flanking context. Our `\s` is ASCII-only
    // because the lexer doesn't set the PCRE `u` flag.
    363 => 'Unicode whitespace (U+00A0) flanking — requires u-flag-aware regex',

    // Punctuation-adjacent flanking for `*` / `_` / `**` / `__`
    362 => 'flanking: punctuation-adjacent `*` (left-flanking vs. right-flanking)',
    368 => 'flanking: punctuation-adjacent `_`',
    372 => 'flanking: intraword `_` with punctuation inside',
    377 => 'flanking: `*` followed by `(` requires punctuation-aware flanking',
    378 => 'flanking: nested `*(*foo*)*` requires flanking + balanced-pair analysis',
    382 => 'flanking: nested `_(_foo_)_` requires flanking + balanced-pair analysis',
    389 => 'flanking: punctuation-adjacent `**`',
    394 => 'flanking: punctuation-adjacent `__`',
    401 => 'flanking: `**` followed by `(`',
    404 => 'flanking: nested `*bar*` inside `**foo ... foo**` with punctuation',
    407 => 'flanking: `__` followed by `(`',
    470 => 'flanking: nested `*_foo_*` requires balanced-pair analysis',
    472 => 'flanking: nested `_*foo*_` requires balanced-pair analysis',

    // Intraword `__` strong (even multibyte) — flanking rule for `_` requires
    // examining whether the delimiter run is word-boundary-flanking, which our
    // simple lookbehind/lookahead approximation doesn't fully match.
    395 => 'flanking: intraword `__` (`foo__bar__`) — left-flanking vs right-flanking',
    396 => 'flanking: intraword `__` across digits (`5__6__78`)',
    397 => 'flanking: intraword `__` with Cyrillic',
    398 => 'flanking: `__foo, __bar__, baz__` — flanking + balanced pairing',
    409 => 'flanking: `__foo__bar` — intraword close',
    410 => 'flanking: intraword `__` with Cyrillic (leading)',
    411 => 'flanking: `__foo__bar__baz__` — multiple `__` pairs with flanking',
    412 => 'flanking: `__(bar)__.` — punctuation-adjacent',

    // Overlapping / multiple-of-3 rule for runs
    416 => 'CommonMark rule 9 (overlapping same-delimiter `_foo _bar_ baz_`)',
    417 => 'CommonMark overlapping `_` / `__` with flanking',
    418 => 'CommonMark overlapping `*foo *bar**` — multiple-of-3 rule',
    419 => 'CommonMark nested `*foo **bar** baz*` — balanced-pair analysis',
    421 => 'CommonMark overlapping `*foo**bar*` — multiple-of-3',
    422 => 'CommonMark nested `***foo** bar*` — triple-delimiter analysis',
    423 => 'CommonMark nested `*foo **bar***` — triple-delimiter analysis',
    424 => 'CommonMark nested `*foo**bar***` — triple-delimiter analysis',
    425 => 'CommonMark triple `foo***bar***baz` — triple-delimiter analysis',
    426 => 'CommonMark long delimiter runs `foo******bar*********baz`',
    427 => 'CommonMark deeply nested `*foo **bar *baz* bim** bop*`',
    434 => 'CommonMark overlapping `__foo __bar__ baz__` — multiple-of-3',
    435 => 'CommonMark `____foo__ bar__` — leading long delimiter run',
    436 => 'CommonMark `**foo **bar****` — trailing long delimiter run',
    439 => 'CommonMark nested `***foo* bar**` — triple-delimiter',
    440 => 'CommonMark nested `**foo *bar***` — triple-delimiter',
    441 => 'CommonMark deeply nested `**foo *bar **baz** bim* bop**`',

    // `__foo_` / `_foo__` — mixing `_` and `__` requires flanking to decide
    // which delimiter pairs open/close.
    463 => 'flanking: `__foo_` — mixed `_`/`__` pairing',
    464 => 'flanking: `_foo__` — mixed `_`/`__` pairing',
    465 => 'flanking: `___foo__` — delimiter-run length analysis',
    466 => 'flanking: `____foo_` — delimiter-run length analysis',
    467 => 'flanking: `__foo___` — delimiter-run length analysis',
    468 => 'flanking: `_foo____` — delimiter-run length analysis',

    // Long delimiter runs require excess-drop logic (2 outer chars dropped
    // from each side). Stack-based pairing needed — out of scope.
    473 => 'CommonMark `****foo****` — excess-drop (4+4 → strong only)',
    474 => 'CommonMark `____foo____` — excess-drop (4+4 → strong only)',
    475 => 'CommonMark `******foo******` — excess-drop (6+6 → strong only)',
    477 => 'CommonMark `_____foo_____` — excess-drop (5+5 → em+strong, 2 dropped each side)',

    // Overlapping / crossing delimiters
    478 => 'CommonMark `*foo _bar* baz_` — overlapping different delimiters',
    479 => 'CommonMark `*foo __bar *baz bim__ bam*` — crossing delimiters',
    480 => 'CommonMark `**foo **bar baz**` — overlapping same delimiter',

    // --------------------------------------------------------------------
    // Inline link `[text](url)` — features GfmLink deliberately does not
    // implement. Either rarely-used syntax paid for with disproportionate
    // regex complexity, or single-pass-lexer limits that can't be worked
    // around inside one mode.
    // --------------------------------------------------------------------

    // GFM link title attribute (`"title"` / `'title'` / `(title)` after
    // the URL). Parses cleanly but is discarded: DokuWiki's link handler
    // instructions have no title-attribute slot, and plumbing one through
    // every renderer is out of scope for GfmLink.
    493 => 'link title attribute: GfmLink parses but discards — DokuWiki link instructions have no title slot',
    513 => 'link title attribute (three quoting styles): discarded by GfmLink',
    515 => 'link title separated by non-breaking space: title slot not supported',
    516 => 'link title with nested balanced quotes: Markdown.pl quirk, not supported',
    517 => 'link title with different quote type for inner quotes: title slot not supported',
    518 => 'multi-line link title: title slot not supported',

    // Pointy-bracket link destinations `<...>`. Rarely used; regex cost
    // and interaction with raw-HTML detection outweigh the benefit.
    496 => 'pointy-bracket link destination `<>`: not supported',
    498 => 'pointy-bracket destination with spaces `<...>`: not supported',
    500 => 'pointy-bracket destination with newline: not supported',
    501 => 'pointy-bracket destination containing `)`: not supported',
    502 => 'pointy-bracket destination with trailing backslash: not supported',
    503 => 'malformed pointy-bracket destinations: renderer output differs',
    507 => 'pointy-bracket destination wrapping unbalanced parens: not supported',

    // Balanced-parens inside URL destinations.
    505 => 'balanced-parens in URL destination: not supported (regex single-level)',

    // Other URL-level edges.
    495 => 'empty URL destination `[link]()`: pattern requires non-empty URL',
    510 => 'backslash in URL destination: URL-encoding differs from spec',
    511 => 'HTML entity / percent-encoding in URL: renderer normalization differs',
    512 => 'link destination that parses as a title: edge case not supported',

    // Inherent single-pass-lexer limits for link text containing nested
    // structures. These cannot be resolved inside one mode.
    522 => 'nested bracket forms inner link, outer falls back to literal',
    526 => 'nested links: inner is a link, outer falls back to literal',
    527 => 'nested links inside emphasis: not supported',
    529 => 'link text grouping vs. emphasis: leftmost-match cannot override',
    530 => 'emphasis/bracket crossing: leftmost-match cannot override',
    532 => 'raw HTML inside link text: project-wide "no raw HTML" limit',
    533 => 'code span inside link text: requires pre-scan pass (see #351)',
    534 => 'autolink inside link text: raw `<URL>` autolinks not supported (see #356)',

    // Reference links (`[text][id]`, `[text][]`, `[foo]` with matching
    // `[foo]: url` definition). Not implemented: resolving forward
    // references would require a two-pass parse, but DokuWiki's lexer is
    // single-pass. Inline links `[text](url)` are the only supported
    // form.
    535 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    536 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    537 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    538 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    539 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    540 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    541 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    542 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    543 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    544 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    545 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    546 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    547 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    548 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    549 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    550 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    551 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    552 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    553 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    557 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    558 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    560 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    561 => 'collapsed reference link: forward-reference definitions not supported (single-pass lexer)',
    562 => 'collapsed reference link: forward-reference definitions not supported (single-pass lexer)',
    563 => 'collapsed reference link: forward-reference definitions not supported (single-pass lexer)',
    564 => 'collapsed reference link: forward-reference definitions not supported (single-pass lexer)',
    565 => 'shortcut reference link: forward-reference definitions not supported (single-pass lexer)',
    566 => 'shortcut reference link: forward-reference definitions not supported (single-pass lexer)',
    567 => 'shortcut reference link: forward-reference definitions not supported (single-pass lexer)',
    568 => 'shortcut reference link: forward-reference definitions not supported (single-pass lexer)',
    569 => 'shortcut reference link: forward-reference definitions not supported (single-pass lexer)',
    570 => 'shortcut reference link: forward-reference definitions not supported (single-pass lexer)',
    571 => 'shortcut reference link with escape: forward-reference definitions not supported (single-pass lexer)',
    572 => 'shortcut reference link with emphasis: forward-reference definitions not supported (single-pass lexer)',
    573 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    574 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    575 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    576 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    577 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    578 => 'reference link: forward-reference definitions not supported (single-pass lexer)',
    579 => 'reference link: forward-reference definitions not supported (single-pass lexer)',

    // --------------------------------------------------------------------
    // Inline image `![alt](url)`. The XHTML renderer's default media
    // rendering diverges from GFM's bare <img> (it wraps in a details <a>
    // with fetch.php/detail.php proxy URLs) — GfmSpecTest uses
    // SpecCompatRenderer to emit spec-shape bare <img>, so only the
    // parser-level or feature-level gaps remain as skips: title attribute
    // (no DW slot), reference images, pointy-bracket destinations, nested
    // brackets, and escape-dependent cases.
    // --------------------------------------------------------------------

    580 => 'image with title attribute: GfmMedia discards titles (no DW slot)',
    581 => 'reference-style image: forward-reference definitions not supported (single-pass lexer)',
    582 => 'nested image-in-image `![foo ![bar](x)](y)`: alt class forbids brackets;'
         . ' leftmost-match cannot reorder — outer falls back to literal (see #526)',
    583 => 'link-in-image alt `![foo [bar](x)](y)`: alt class forbids brackets;'
         . ' leftmost-match cannot reorder — outer falls back to literal (see #526)',
    584 => 'collapsed reference-style image: forward-reference definitions not supported',
    585 => 'full reference-style image: forward-reference definitions not supported',
    587 => 'image with title attribute: title discarded (no DW slot)',
    588 => 'pointy-bracket image destination `![alt](<url>)`: not supported (see GfmLink #496)',
    590 => 'reference-style image: forward-reference definitions not supported',
    591 => 'reference-style image (case-insensitive label): forward-reference definitions not supported',
    592 => 'collapsed reference-style image `![foo][]`: forward-reference definitions not supported',
    593 => 'collapsed reference-style image with emphasis in label: forward-reference definitions not supported',
    594 => 'collapsed reference-style image (case-insensitive): forward-reference definitions not supported',
    595 => 'reference-style image with intervening whitespace: forward-reference definitions not supported',
    596 => 'shortcut reference-style image `![foo]`: forward-reference definitions not supported',
    597 => 'shortcut reference-style image with emphasis: forward-reference definitions not supported',
    598 => 'image with unescaped nested brackets `![[foo]]`: literal-fallback behavior not supported',
    599 => 'shortcut reference-style image (case-insensitive): forward-reference definitions not supported',
    600 => 'escape in image syntax `!\[foo]`: depends on GfmEscape (pending)',
    601 => 'backslash-escape of `!` before link: depends on GfmEscape (pending)',

    // --------------------------------------------------------------------
    // ATX heading collisions with DokuWiki-specific behavior.
    // --------------------------------------------------------------------
    38 => 'ATX heading with leading spaces: GFM tolerates 0-3 spaces of'
        . ' indent before the opener; we require the `#` at column 0.'
        . ' Indent tolerance collides with DokuWiki\'s 2-space-indent'
        . ' preformatted block and isn\'t worth untangling',
    39 => 'indented code block: DokuWiki uses 2-space indent for'
        . ' preformatted; GFM 4-space indented code blocks are not'
        . ' implemented',
    40 => 'indented code block: 4-space indent after a paragraph is a'
        . ' continuation in GFM but preformatted in DokuWiki — not'
        . ' implemented',
    41 => 'ATX heading with leading spaces: second heading is indented'
        . ' by 2 spaces; we require the `#` at column 0',
    49 => 'empty ATX heading: DokuWiki\'s XHTML renderer deliberately'
        . ' skips blank headings (blank() guard in Doku_Renderer_xhtml::header)',
];
