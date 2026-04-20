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
];
