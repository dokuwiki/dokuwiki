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
    // Tabs (§2.2) — DokuWiki's tab handling is binary: a leading tab
    // (matching `\n\t` directly after the newline) is the indented-code
    // trigger; otherwise tabs are ordinary characters. CommonMark
    // instead advances each tab to the next 4-column stop and uses the
    // resulting column count to drive list-continuation, list-nesting,
    // blockquote-interior, and 4-column indented-code decisions. The
    // column arithmetic is what's missing.
    //
    // Examples #1, #3, #8, #10 are not listed: they happen to render
    // correctly because a leading tab matches `\n\t`, four leading
    // spaces match the `md`-mode 4-space code trigger, and GfmHeader
    // accepts a tab as the post-`#` separator.
    // --------------------------------------------------------------------
    2 => 'tab indented-code: 2 spaces then tab. The 4-space trigger needs'
        . ' 4 spaces; the `\n\t` trigger needs the tab directly after the'
        . ' newline. Neither fires. CommonMark counts the tab as advancing'
        . ' to column 4 → code block; DokuWiki does no such arithmetic.',
    4 => 'tab as 4-column lazy-continuation indent inside a list item.'
        . ' DokuWiki treats a leading tab as the indented-code trigger,'
        . ' not as list continuation. Resolving requires column arithmetic'
        . ' against the list\'s content column.',
    5 => 'two tabs (8 columns) inside a list item → code block inside list.'
        . ' Requires column arithmetic to subtract the list\'s content'
        . ' column from the indent and route the residue into a nested'
        . ' code block.',
    6 => 'tabs after blockquote marker → indented code inside blockquote.'
        . ' Requires column arithmetic for the blockquote interior;'
        . ' DokuWiki treats the tab as a top-level code trigger instead.',
    7 => 'tabs after list marker → indented code inside list item.'
        . ' Requires column arithmetic for the list interior; DokuWiki'
        . ' treats the tab as a top-level code trigger instead.',
    9 => 'tab as 4-column indent for list nesting. DokuWiki treats a'
        . ' leading tab as the indented-code trigger, never as list'
        . ' nesting indent.',
    11 => '`*\t*\t*\t` thematic break with tab separators. Strict-bare-run'
        . ' HR policy rejects internal whitespace (same family as #21-23);'
        . ' the tab form is the same case.',

    // --------------------------------------------------------------------
    // Thematic breaks (GfmHr) — strict-only HR is intentional. The
    // delimiter run must be bare: no leading, trailing, or internal
    // whitespace in either DW or GFM flavor. The list-precedence cases
    // additionally need a GfmListblock guard that is out of scope.
    // --------------------------------------------------------------------
    17 => 'thematic break: 0-3 spaces of leading indent. Strict policy:'
        . ' opener must be at column 0 in either flavor.',
    21 => 'thematic break: spaces between delimiter chars (`- - -`).'
        . ' Strict policy: bare run only.',
    22 => 'thematic break: spaces between delimiter chars (`** * **`).'
        . ' Strict policy: bare run only.',
    23 => 'thematic break: spaces between delimiter chars (`-     -`).'
        . ' Strict policy: bare run only.',
    24 => 'thematic break: trailing spaces after the run. Strict policy:'
        . ' bare run only.',
    29 => 'thematic break: Setext heading underline `Foo\n---` should'
        . ' render as `<h2>`. Setext headings are deliberately not'
        . ' supported — `---` collides with DokuWiki HR and `===` would'
        . ' collide with DokuWiki heading syntax.',
    30 => 'thematic break vs. list-item precedence (`* * *` between list'
        . ' items): requires internal-space HR support and a GfmListblock'
        . ' guard so the list refuses to absorb the HR-shaped line. Both'
        . ' out of scope; the line stays a list-item body.',
    31 => 'thematic break inside list with different bullet (`- * * *`):'
        . ' depends on internal-space HR support inside the sub-parsed'
        . ' item body. See example 30.',

    // --------------------------------------------------------------------
    // Setext headings (§4.3) — deliberately not supported across the
    // whole section. The `---` underline collides with DokuWiki\'s HR
    // syntax and `===` would collide with DokuWiki\'s heading delimiter.
    // Same rationale as #29 (thematic break vs. Setext underline),
    // #111 (fence after Setext), and #212 (Setext after blockquote).
    //
    // Examples #62, #64, #67, #68, #69, #71, #74 are NOT listed: those
    // are cases where Setext is deliberately NOT triggered (blockquote /
    // list / paragraph wins, or blank lines disambiguate), so the spec
    // output matches DokuWiki\'s no-Setext rendering and they pass
    // naturally.
    //
    // #58 and #75 also depend on DokuWiki\'s strict-bare-run HR rule
    // (`--- -` and `* * *` need internal-space HR, see #21-23) — they
    // sit in the Setext section because the spec uses them to
    // illustrate Setext-underline edge cases.
    // --------------------------------------------------------------------
    50 => 'Setext heading (`Foo *bar*\n=====` / `\n-----`): Setext'
        . ' headings deliberately not supported — `---`/`===` underlines'
        . ' collide with DokuWiki HR / heading syntax.',
    51 => 'Setext heading with multi-line content: Setext headings'
        . ' deliberately not supported (see #50).',
    52 => 'Setext heading with indented multi-line content: Setext'
        . ' headings deliberately not supported (see #50).',
    53 => 'Setext heading with any-length underline: Setext headings'
        . ' deliberately not supported (see #50).',
    54 => 'Setext heading with 3-space-indented content / underline:'
        . ' Setext headings deliberately not supported (see #50).',
    55 => 'Setext heading: 4-space-indented content forms code block,'
        . ' then `---` HR. Setext headings deliberately not supported'
        . ' (see #50).',
    56 => 'Setext heading: underline indented up to 3 spaces with'
        . ' trailing spaces. Setext headings deliberately not'
        . ' supported (see #50).',
    57 => 'Setext heading vs. 4-space-indented underline (paragraph'
        . ' wins). Setext headings deliberately not supported (see #50).',
    58 => 'Setext heading: underline cannot contain internal spaces'
        . ' (`= =` / `--- -`). Setext headings deliberately not supported'
        . ' (see #50); also depends on internal-space HR support DokuWiki'
        . ' lacks (see #21-23).',
    59 => 'Setext heading: trailing spaces in content do not cause a'
        . ' line break. Setext headings deliberately not supported (see'
        . ' #50).',
    60 => 'Setext heading: trailing backslash in content. Setext'
        . ' headings deliberately not supported (see #50).',
    61 => 'Setext heading: block-structure precedence over inline.'
        . ' Setext headings deliberately not supported (see #50).',
    63 => 'Setext heading: underline cannot be a lazy continuation in'
        . ' a blockquote. Setext headings deliberately not supported'
        . ' (see #50).',
    65 => 'Setext heading: preceding paragraph becomes part of heading'
        . ' content. Setext headings deliberately not supported (see #50).',
    66 => 'Setext heading: no blank line required before/after. Setext'
        . ' headings deliberately not supported (see #50).',
    70 => 'Setext heading: 4-space-indented content forms code block,'
        . ' then `---` HR. Setext headings deliberately not supported'
        . ' (see #50).',
    72 => 'Setext heading with backslash-escaped marker `\\> foo`.'
        . ' Setext headings deliberately not supported (see #50).',
    73 => 'Setext heading: blank-line-separated paragraph + heading +'
        . ' paragraph. Setext headings deliberately not supported (see'
        . ' #50).',
    75 => 'Setext heading boundary: `* * *` should be HR (cannot count'
        . ' as Setext underline). Setext headings deliberately not'
        . ' supported (see #50); also depends on internal-space HR'
        . ' support DokuWiki lacks (see #21-23).',
    76 => 'Setext heading: backslash-escaped underline `\\---` keeps'
        . ' content as paragraph. Setext headings deliberately not'
        . ' supported (see #50).',

    // --------------------------------------------------------------------
    // Indented code blocks (§4.4) vs. paragraph continuation — the
    // single-pass lexer cannot carry paragraph-open state across modes,
    // so DokuWiki\'s `Preformatted` triggers on every `\n    ` and exits
    // on every `\n`. Two CommonMark rules consequently cannot be
    // expressed:
    //
    //   - The 4-space indent must NOT open a code block on a
    //     paragraph-continuation line — GFM treats it as lazy paragraph
    //     text. We have no `paragraph-open` flag to consult.
    //   - An indented code block MAY span blank lines as long as the
    //     next non-blank line is still 4-space indented. Our exit-on-any-
    //     blank-line behavior splits the block.
    //
    // List-interior indented code (#79, #80, #193) additionally needs the
    // column arithmetic that the §2.2 tabs family already documents as
    // out of scope (see #4-9).
    // --------------------------------------------------------------------
    19 => 'thematic break preceded by paragraph: `Foo\n    ***` should be'
        . ' a paragraph continuation followed by an HR — DokuWiki\'s'
        . ' `Preformatted` mode triggers on the 4-space indent regardless'
        . ' of paragraph-open state. Single-pass lexer cannot carry block'
        . ' context across modes.',
    79 => 'list item containing indented code: requires column arithmetic'
        . ' for list interior plus paragraph-context-aware indent trigger'
        . ' (see #4-9 for the column-arithmetic rationale).',
    80 => 'list item with indented code after content: same as #79 — list'
        . ' interior column arithmetic plus paragraph-context-aware indent'
        . ' trigger.',
    81 => 'indented code block spanning blank lines: GFM keeps the run open'
        . ' as long as the next non-blank line is also 4-space indented;'
        . ' DokuWiki\'s `Preformatted` exits on any `\n`. Same single-pass'
        . ' lexer limit as #19.',
    83 => 'indented code trigger mid-paragraph: 4-space indent on a'
        . ' paragraph-continuation line should be lazy paragraph text in'
        . ' GFM, not a code block. Same root cause as #19.',
    85 => 'indented code trigger mid-paragraph (variant): see #83 / #19.',
    87 => 'indented code block spanning blank lines (variant): see #81.',
    193 => 'list item with indented code: same family as #79 / #80 — list'
         . ' interior column arithmetic plus paragraph-context-aware'
         . ' indent trigger.',

    // --------------------------------------------------------------------
    // Fenced code blocks (GfmCode / GfmFile) — deliberate simplifications
    // versus strict GFM. All of these are consequences of lexer constraints
    // (no regex backreferences) or the deliberate column-0-only policy.
    // --------------------------------------------------------------------
    94  => 'fenced code: closing fence must be ≥ opening length — DokuWiki'
         . ' accepts any 3+ run as a closer (no regex backreferences for'
         . ' length pairing). Deliberate relaxation.',
    95  => 'fenced code (tilde variant): closing fence must be ≥ opening'
         . ' length — see example 94.',
    96  => 'fenced code: unclosed fence — DokuWiki convention requires a'
         . ' closer (matches DW <code> tag), so unclosed fences stay'
         . ' literal rather than consuming to EOF. GFM\'s "close at end"'
         . ' rule is really "close at any container boundary" in'
         . ' CommonMark\'s two-pass block parser, which our single-pass'
         . ' lexer cannot implement.',
    97  => 'fenced code: unclosed fence with intervening short run — stays'
         . ' literal, see example 96.',
    98  => 'fenced code inside blockquote: GFM closes the fence at the'
         . ' blockquote\'s end, but DokuWiki requires an explicit closing'
         . ' fence and the single-pass lexer has no notion of container'
         . ' boundaries to close at. Same root cause as example 96 —'
         . ' unclosed fences stay literal.',
    101 => 'fenced code: opener indented 1 space — DokuWiki requires'
         . ' column-0 fences. Indent tolerance + per-line body dedent out'
         . ' of scope.',
    102 => 'fenced code: opener indented 2 spaces — see example 101.',
    103 => 'fenced code: opener indented 3 spaces — see example 101.',
    105 => 'fenced code: closer indented 2 spaces — column-0-only policy,'
         . ' see example 101.',
    106 => 'fenced code: indented opener with less-indented closer —'
         . ' column-0-only policy, see example 101.',
    107 => 'fenced code: 4-space-indented closer — with column-0-only'
         . ' policy there is no valid closer, so the fence stays literal'
         . ' (see example 96).',
    109 => 'fenced code: malformed closer `~~~ ~~` (space-broken run) —'
         . ' with no valid closer the fence stays literal (see example 96).',
    108 => 'fenced code: `` `` is not a valid fence; GFM falls back to an'
         . ' inline code span of length 3. Inline spans with n≥3 not'
         . ' implemented (GfmBacktickSingle/Double cover only n=1, n=2).',
    111 => 'fenced code interrupting Setext heading (`foo\n---`): Setext'
         . ' headings are deliberately not supported — the `---` underline'
         . ' collides with DokuWiki\'s horizontal rule and `===` would'
         . ' collide with DokuWiki heading syntax.',
    115 => 'fenced code: `` `` backtick-fence-with-backticks-in-info-string'
         . ' is invalid; GFM falls back to n=3 inline span — inline spans'
         . ' with n≥3 not implemented. See example 108.',

    // --------------------------------------------------------------------
    // HTML blocks (§4.6) — raw HTML pass-through is not supported
    // --------------------------------------------------------------------
    118 => 'raw HTML block (script/pre/style/textarea group): raw HTML pass-through not supported — DokuWiki escapes `<` as `&lt;`',
    119 => 'raw HTML block: raw HTML pass-through not supported',
    120 => 'raw HTML block: raw HTML pass-through not supported',
    121 => 'raw HTML block: raw HTML pass-through not supported',
    122 => 'raw HTML block (comment): raw HTML pass-through not supported',
    123 => 'raw HTML block (processing instruction): raw HTML pass-through not supported',
    124 => 'raw HTML block (declaration): raw HTML pass-through not supported',
    125 => 'raw HTML block (CDATA): raw HTML pass-through not supported',
    126 => 'raw HTML block (block-level tag group): raw HTML pass-through not supported',
    127 => 'raw HTML block: raw HTML pass-through not supported',
    128 => 'raw HTML block: raw HTML pass-through not supported',
    129 => 'raw HTML block: raw HTML pass-through not supported',
    130 => 'raw HTML block: raw HTML pass-through not supported',
    131 => 'raw HTML block: raw HTML pass-through not supported',
    132 => 'raw HTML block: raw HTML pass-through not supported',
    133 => 'raw HTML block: raw HTML pass-through not supported',
    134 => 'raw HTML block: raw HTML pass-through not supported',
    135 => 'raw HTML block: raw HTML pass-through not supported',
    136 => 'raw HTML block (any-tag group): raw HTML pass-through not supported',
    137 => 'raw HTML block: raw HTML pass-through not supported',
    138 => 'raw HTML block: raw HTML pass-through not supported',
    139 => 'raw HTML block: raw HTML pass-through not supported',
    140 => 'raw HTML block: raw HTML pass-through not supported',
    141 => 'raw HTML block: raw HTML pass-through not supported',
    142 => 'raw HTML block: raw HTML pass-through not supported',
    143 => 'raw HTML block: raw HTML pass-through not supported',
    144 => 'raw HTML block: raw HTML pass-through not supported',
    145 => 'raw HTML block: raw HTML pass-through not supported',
    146 => 'raw HTML block: raw HTML pass-through not supported',
    147 => 'raw HTML block: raw HTML pass-through not supported',
    148 => 'raw HTML block: raw HTML pass-through not supported',
    149 => 'raw HTML block: raw HTML pass-through not supported',
    150 => 'raw HTML block: raw HTML pass-through not supported',
    151 => 'raw HTML block: raw HTML pass-through not supported',
    152 => 'raw HTML block: raw HTML pass-through not supported',
    153 => 'raw HTML block: raw HTML pass-through not supported',
    154 => 'raw HTML block: raw HTML pass-through not supported',
    155 => 'raw HTML block: raw HTML pass-through not supported',
    156 => 'raw HTML block: raw HTML pass-through not supported',
    157 => 'raw HTML block: raw HTML pass-through not supported',
    158 => 'raw HTML block: raw HTML pass-through not supported',
    159 => 'raw HTML block: raw HTML pass-through not supported',
    160 => 'raw HTML block: raw HTML pass-through not supported',

    // --------------------------------------------------------------------
    // Link reference definitions (§4.7) — single-pass lexer cannot resolve
    // forward references, so the `[foo]: /url` definition lines are not
    // recognised and the matching `[foo]` references stay literal. Same
    // rationale as the reference-link entries at #535-579.
    // Examples #168, #180-182 are NOT listed: their definitions are
    // invalid (empty URL / inside indented code / inside fenced code /
    // attached to a paragraph), so the spec also expects literal output
    // for the `[foo]` reference, and DW agrees.
    // --------------------------------------------------------------------
    161 => 'link reference definition: forward-reference definitions not supported (single-pass lexer)',
    162 => 'link reference definition: forward-reference definitions not supported (single-pass lexer)',
    163 => 'link reference definition (multi-line title): forward-reference definitions not supported (single-pass lexer)',
    164 => 'link reference definition (case-insensitive label): forward-reference definitions not supported (single-pass lexer)',
    165 => 'link reference definition (Unicode case folding): forward-reference definitions not supported (single-pass lexer)',
    166 => 'link reference definition (whitespace-collapsed label): forward-reference definitions not supported (single-pass lexer)',
    167 => 'link reference definition (no link text used): forward-reference definitions not supported (single-pass lexer)',
    169 => 'link reference definition (pointy-bracket destination): forward-reference definitions not supported (single-pass lexer)',
    170 => 'link reference definition (no title, blank line in between): forward-reference definitions not supported (single-pass lexer)',
    171 => 'link reference definition (title only, no destination): forward-reference definitions not supported (single-pass lexer)',
    172 => 'link reference definition (multiple definitions): forward-reference definitions not supported (single-pass lexer)',
    173 => 'link reference definition (first wins on duplicate label): forward-reference definitions not supported (single-pass lexer)',
    174 => 'link reference definition (label case-insensitive): forward-reference definitions not supported (single-pass lexer)',
    175 => 'link reference definition (used as paragraph delimiter): forward-reference definitions not supported (single-pass lexer)',
    176 => 'link reference definition (no body following): forward-reference definitions not supported (single-pass lexer)',
    177 => 'link reference definition (label with surrounding whitespace): forward-reference definitions not supported (single-pass lexer)',
    178 => 'link reference definition (indented up to 3 spaces): forward-reference definitions not supported (single-pass lexer)',
    179 => 'link reference definition (multi-line definition with title): forward-reference definitions not supported (single-pass lexer)',
    183 => 'link reference definition (does not interrupt paragraph): forward-reference definitions not supported (single-pass lexer)',
    184 => 'link reference definition (between blockquote and paragraph): forward-reference definitions not supported (single-pass lexer)',
    185 => 'link reference definition (lone definition emits nothing): forward-reference definitions not supported (single-pass lexer)',
    186 => 'link reference definition (definition then HR): forward-reference definitions not supported (single-pass lexer)',
    187 => 'link reference definition (multiple defs in a row): forward-reference definitions not supported (single-pass lexer)',
    188 => 'link reference definition (def inside blockquote): forward-reference definitions not supported (single-pass lexer)',
    329 => 'reference link with entity-decoded URL in definition: depends on'
         . ' link reference definitions, which forward-reference definitions'
         . ' are not supported (single-pass lexer)',

    // --------------------------------------------------------------------
    // Code-span edge cases that collide with project-wide decisions
    // (no raw HTML, no GFM angle-bracket autolinks, typography on by
    // default) or with the single-pass lexer's limits.
    // --------------------------------------------------------------------
    351 => 'code span vs. emphasis: cross-positional precedence would require'
         . ' a pre-scan pass — the single-pass lexer matches leftmost-first'
         . ' and cannot reject an earlier emphasis opener because a later'
         . ' backtick span would consume its closer',
    352 => 'code span vs. link `[not a `link](/foo`)`: the link opener is'
         . ' leftmost but a backtick span inside its label should consume'
         . ' the closing `]` and `)` — single-pass lexer matches'
         . ' leftmost-first and cannot reorder spans (see #351).',
    327 => 'raw HTML tag with entity in attribute: raw HTML pass-through not supported',
    354 => 'raw HTML tag pass-through: raw HTML pass-through not supported',
    605 => 'angle-bracket autolink with `MAILTO:` scheme: Externallink'
         . ' builds one regex per scheme listed in `conf/scheme.conf`, and'
         . ' `mailto` is not in the default allow-list. The brackets fall'
         . ' through to default escaping and the URL is emitted as literal'
         . ' text — same security policy as DokuWiki\'s bare-URL detection.',
    606 => 'angle-bracket autolink with `a+b+c` scheme: scheme is not in'
         . ' DokuWiki\'s `conf/scheme.conf` allow-list (see #605).',
    607 => 'angle-bracket autolink with `made-up-scheme`: scheme is not in'
         . ' DokuWiki\'s `conf/scheme.conf` allow-list (see #605).',
    609 => 'angle-bracket autolink with `localhost:5001/foo`: `localhost` is'
         . ' not in DokuWiki\'s `conf/scheme.conf` allow-list (see #605).',

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

    // Emphasis vs. angle-bracket autolink: same root cause as #351 (the
    // single-pass lexer matches leftmost-first and cannot reject an
    // earlier `**`/`__` opener because a later `<URL>` autolink would
    // consume its closer).
    489 => 'emphasis vs. angle-bracket autolink `**a<http://...?q=**>`:'
         . ' leftmost-match cannot reorder spans — see #351 for the'
         . ' single-pass-lexer rationale.',
    490 => 'emphasis vs. angle-bracket autolink `__a<http://...?q=__>`:'
         . ' leftmost-match cannot reorder spans — see #351.',

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
    328 => 'link with entity-decoded URL and title: URL side decodes correctly,'
         . ' but the title attribute is discarded — DokuWiki link instructions'
         . ' have no title slot.',
    493 => 'link title attribute: GfmLink parses but discards — DokuWiki link instructions have no title slot',
    513 => 'link title attribute (three quoting styles): discarded by GfmLink',
    514 => 'link title with HTML-entity escape `"title \\"&quot;"`: title slot not supported (see #493)',
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
    512 => 'link destination that parses as a title: edge case not supported',
    337 => 'entity-decoded `&quot;` inside link URL slot: spec rejects the'
         . ' link because the decoded `"` would split URL from title, but'
         . ' GfmLink uses a permissive `[^)\n]+` URL slot and accepts the'
         . ' whole run as the URL — strict GFM URL rejection not implemented',
    497 => 'unquoted whitespace in URL slot `[link](/my uri)`: GfmLink truncates'
         . ' at the first space and discards the remainder as a (would-be)'
         . ' title; spec rejects the whole construct and emits literal text —'
         . ' strict GFM URL rejection not implemented',

    // Inherent single-pass-lexer limits for link text containing nested
    // structures. These cannot be resolved inside one mode.
    520 => 'link label with literal nested brackets `[link [foo [bar]]](/uri)`:'
         . ' GfmLink label class forbids `[`/`]`, so the outer match fails —'
         . ' same family as #522/#526',
    522 => 'nested bracket forms inner link, outer falls back to literal',
    523 => 'link label with backslash-escaped bracket `[link \\[bar](/uri)`:'
         . ' GfmLink label class forbids `[` even when escaped — same family'
         . ' as #522/#526',
    524 => 'inline formatting inside link label `[link *foo **bar** `#`*](/uri)`:'
         . ' GfmLink takes the label as a flat string and does not re-tokenize'
         . ' inline spans — same family as #428/#442',
    526 => 'nested links: inner is a link, outer falls back to literal',
    527 => 'nested links inside emphasis: not supported',
    529 => 'link text grouping vs. emphasis: leftmost-match cannot override',
    530 => 'emphasis/bracket crossing: leftmost-match cannot override',
    482 => 'emphasis/bracket crossing `*[bar*](/url)`: opener `*` precedes the'
         . ' link, closer `*` falls inside the link label — GFM flanking'
         . ' rejects the pair; DW takes the leftmost `*` as an emphasis'
         . ' opener and never finds a closer (same family as #529/#530)',
    483 => 'emphasis/bracket crossing `_foo [bar_](/url)`: closer `_` falls'
         . ' inside link label — same family as #482/#529/#530',
    428 => 'emphasis inside link label `*foo [*bar*](/url)*`: GfmLink takes'
         . ' the label as a flat string (DW link instructions have no'
         . ' re-parsed-inline label slot), so inner `*bar*` stays literal',
    442 => 'emphasis inside link label `**foo [*bar*](/url)**`: same as #428'
         . ' — link label is a flat string and inner `*bar*` is not'
         . ' re-tokenized as emphasis',
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

    528 => 'image-as-alt with nested link `![[[foo](uri1)](uri2)](uri3)`: alt'
         . ' class forbids brackets so the outer image match fails; the inner'
         . ' `[foo](uri1)` matches as a regular link and the outer falls back'
         . ' to literal — same family as #582/#583/#598',
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
    600 => 'image-via-reference fallback `!\[foo]` with `[foo]: /url`: forward-reference definitions not supported (single-pass lexer)',
    601 => 'image-via-reference fallback `\![foo]` with `[foo]: /url`: forward-reference definitions not supported (single-pass lexer)',

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

    // --------------------------------------------------------------------
    // List items / Lists — list features GfmListblock deliberately does
    // not implement. The simplifications are by design: indentation uses
    // a fixed 2-space-multiple step starting at 0, lazy continuation is
    // not supported, and the rewriter groups items by 'u'/'o' type only.
    // The buckets are:
    //
    //  A. Extra spaces after the marker. CommonMark rolls them (up to
    //     4) into the content column; we dedent at `marker_width + 1`,
    //     collapsing the extras.
    //  B. 1- or 3-space indent for nesting (we round down to nearest 2).
    //  C. Lazy continuation (column-0 paragraph wrap inside an item).
    //  D. Strict CommonMark loose/tight classification (every blank line
    //     between items / inside items reclassifies; we use a simpler
    //     single-paragraph-tight, multi-paragraph-loose rule).
    //  E. Marker-character-change splits ordered lists ('.' vs ')') or
    //     unordered ('-' vs '+' vs '*'). Our rewriter groups by 'u' / 'o'
    //     type only, not by marker character.
    //  F. List interrupting a paragraph without a blank line — requires a
    //     multi-pass block parser to revisit prior text.
    //
    // Examples that depend on a pending mode (GfmQuote, GfmEscape, …) are
    // intentionally NOT skipped — they remain visible failing tests until
    // the mode lands.
    // --------------------------------------------------------------------
    // --------------------------------------------------------------------
    // Block quotes — deliberate scope reductions vs. strict GFM. The
    // unified GfmQuote mode (replacing DW Quote) covers `>` blockquotes
    // for both DW and MD pages, but several CommonMark blockquote rules
    // are out of scope:
    //
    // - 1-3 space indent before `>` (column-0-only policy, consistent
    //   with GfmCode / GfmFile / GfmHeader).
    // - Lazy continuation (paragraph text without `>` on continuation
    //   lines). Same policy as GfmListblock — markers required on
    //   every line.
    // - Headers inside quotes — sub-parser excludes BASEONLY so header
    //   instructions don't drive TOC/section-edit anchors that don't
    //   compose with `<blockquote>`. Same rationale as GfmListblock's
    //   header exclusion inside list items.
    // - Setext-style block constructs (the `---` underline collides
    //   with DW's HR rule).
    //
    // Examples that depend on still-pending modes (GfmHr) are
    // intentionally NOT skipped — they stay visible until those modes
    // land.
    // --------------------------------------------------------------------
    206 => 'block quotes: header inside quote — sub-parser excludes'
         . ' BASEONLY (TOC / section-edit anchors do not compose with'
         . ' `<blockquote>`). Same policy as GfmListblock for `<li>`.',
    207 => 'block quotes: header inside quote with no space after `>` —'
         . ' see #206 for the BASEONLY exclusion rationale.',
    208 => 'block quotes: leading-space `>` (1-3 spaces of indent) —'
         . ' column-0-only policy, consistent with GfmCode / GfmFile.',
    210 => 'block quotes: lazy continuation `> # Foo\n> bar\nbaz` —'
         . ' every quote line must begin with `>` at column 0. Same'
         . ' policy as GfmListblock.',
    211 => 'block quotes: lazy continuation `> bar\nbaz\n> foo` —'
         . ' see #210.',
    212 => 'block quotes: Setext heading underline `---` after `> foo`'
         . ' — no Setext headings (the `---` collides with DW HR syntax).',
    215 => 'block quotes: fenced code block split across blockquote'
         . ' boundary — fence inside quote followed by non-`>` lines'
         . ' depends on the same lazy-continuation rule we do not'
         . ' implement (see #210).',
    216 => 'block quotes: lazy continuation `> foo\n    - bar` — see #210.',
    225 => 'block quotes: lazy continuation `> bar\nbaz` — see #210.',
    227 => 'block quotes: lazy continuation `> bar\n>\nbaz` — see #210.',
    228 => 'block quotes: lazy continuation in nested quote'
         . ' `> > > foo\nbar` — see #210.',
    229 => 'block quotes: lazy continuation across nested levels'
         . ' `>>> foo\n> bar\n>>baz` — see #210.',

    232 => 'list items: marker-width content-column alignment (A)',
    235 => 'list items: marker-width content-column alignment (A)',
    237 => 'list items: ordered list nested in `>>` with 3-space leading'
         . ' indent and marker-width content column (B+A; see #208 for'
         . ' the leading-`>` indent policy).',
    238 => 'list items: bullet inside `>>` followed by leading-space'
         . ' `  >  > two` continuation — column-0-only `>` policy plus'
         . ' interior space inside the nested quote (B; see #208).',
    241 => 'list items: marker-width content column for `1.  foo` with'
         . ' fenced code, paragraph and blockquote at content column 4'
         . ' (A; sub-blocks would also need to open at non-zero column).',
    242 => 'list items: marker-width content column + indented code must'
         . ' span multiple internal blank lines (A; the multi-blank'
         . ' indented-code rule is a separate gap).',
    249 => 'list items: marker-width-driven content-column alignment for `10. foo` (A)',
    254 => 'list items: marker-width content-column alignment edge case (A)',
    257 => 'list items: empty bullet line then content on the next line —'
         . ' content column derived from next non-blank line\'s indent'
         . ' (A sub-case).',
    258 => 'list items: marker-width content-column for `1.  foo` (A)',
    263 => 'list items: indent ambiguity at column 0/1/2 (B)',
    264 => 'list items: 1-space-indent variation (B)',
    265 => 'list items: marker-width with multi-line continuation (A)',
    266 => 'list items: marker-width with multi-line continuation (A)',
    267 => 'list items: lazy continuation (C)',
    268 => 'list items: lazy continuation (C)',
    270 => 'list items: lazy continuation across blank line (C+D)',
    271 => 'list items: lazy continuation in nested quote-list-quote'
         . ' (`> 1. > Blockquote` then `> continued here.`) (C; see #210).',
    273 => 'list items: list interrupting a paragraph without blank line (F)',
    376 => 'lone `*` on the line after `*foo bar` is taken as an empty list'
         . ' marker by GfmListblock, breaking the paragraph; GFM keeps the'
         . ' whole input as one paragraph because the trailing `*` does not'
         . ' pair as emphasis. List-interrupts-paragraph (F), same family'
         . ' as #273 / #284.',
    275 => 'list items: 3-space indent rounds to 2 — sub-list under previous item (B)',
    276 => 'list items: marker-width content-column with mixed types (A+E)',
    277 => 'list items: nested markers on a single line (A)',
    278 => 'list items: marker-character switch splits the list (E)',
    281 => 'lists: marker-character change splits unordered list `-` -> `+` (E)',
    282 => 'lists: ordered delimiter switch splits list `.` -> `)` (E)',
    284 => 'lists: list interrupting paragraph without blank line (F)',
    286 => 'lists: marker-width content-column alignment for ordered list (A)',
    287 => 'lists: triple blank line + indented continuation in deeply nested item (D)',
    288 => 'lists: marker-character change at deeper level (E)',
    289 => 'lists: marker-character change with type switch (E)',
    290 => 'lists: 1-space-indent variations of items, all stay top-level (B)',
    291 => 'lists: 1-space-indent variations on ordered list (B)',
    292 => 'lists: marker-character change splits inside nested list (E)',
    293 => 'lists: marker-character change with mixed indent (E+B)',
    294 => 'lists: lazy continuation across types (C+E)',
    295 => 'lists: lazy continuation in nested list (C)',
    296 => 'lists: lazy continuation across blank line (C+D)',
    297 => 'lists: blank-line classification for loose/tight in nested list (D)',
    298 => 'lists: blank-line classification (D)',
    300 => 'lists: blank-line classification with marker change (D+E)',
    301 => 'lists: blank-line classification + marker-width alignment (D+A)',
    304 => 'lists: blank line between sub-list items affects loose/tight (D)',
    305 => 'lists: blank line between deeply nested items (D)',
    306 => 'lists: blank line at the end of a loose list affects classification (D)',

    // --------------------------------------------------------------------
    // Backslash-escape examples (§6.1) that fail for reasons unrelated to
    // GfmEscape itself: renderer divergences, typography conversion, and
    // already-skipped GFM features (autolinks, raw HTML, reference links,
    // discarded link titles). The escape mechanic itself works.
    // --------------------------------------------------------------------
    316 => 'backslash escapes inside angle-bracket autolinks: GFM autolink'
         . ' `<URL>` form not implemented (see example 356)',
    317 => 'backslash escapes inside raw HTML: raw HTML pass-through is not'
         . ' supported by default (see example 354)',
    318 => 'backslash escapes in link title: title attribute is discarded — DW'
         . ' link instructions have no title slot',
    319 => 'backslash escapes in reference-link definition: link reference'
         . ' definitions not supported (single-pass lexer cannot resolve'
         . ' forward references)',

    // --------------------------------------------------------------------
    // Raw HTML (§6.6) — inline raw HTML pass-through. Same project-wide
    // decision as HTML blocks (#118-160): DokuWiki escapes `<` as `&lt;`
    // by default; the `<html>` block is the opt-in. Examples #637 and
    // #640 are intentionally NOT listed — the spec there expects literal
    // `&lt;...&gt;` escaping for malformed tags, which DW also produces,
    // so they pass naturally.
    // --------------------------------------------------------------------
    632 => 'raw HTML inline (open tag): raw HTML pass-through not supported',
    633 => 'raw HTML inline (closing tag): raw HTML pass-through not supported',
    634 => 'raw HTML inline (multi-line attributes): raw HTML pass-through not supported',
    635 => 'raw HTML inline (line breaks in attributes): raw HTML pass-through not supported',
    636 => 'raw HTML inline (custom tags / attribute syntax): raw HTML pass-through not supported',
    638 => 'raw HTML inline (illegal attribute names): raw HTML pass-through not supported',
    639 => 'raw HTML inline (illegal attribute values): raw HTML pass-through not supported',
    641 => 'raw HTML inline (open and closing tags): raw HTML pass-through not supported',
    642 => 'raw HTML inline (HTML comment): raw HTML pass-through not supported',
    643 => 'raw HTML inline (invalid comment): raw HTML pass-through not supported',
    644 => 'raw HTML inline (processing instruction): raw HTML pass-through not supported',
    645 => 'raw HTML inline (declaration): raw HTML pass-through not supported',
    646 => 'raw HTML inline (declaration single-letter name): raw HTML pass-through not supported',
    647 => 'raw HTML inline (declaration EMPTY): raw HTML pass-through not supported',
    648 => 'raw HTML inline (CDATA section): raw HTML pass-through not supported',
    649 => 'raw HTML inline (entity reference inside attribute): raw HTML pass-through not supported',
    650 => 'raw HTML inline (backslash escape inside attribute): raw HTML pass-through not supported',
    651 => 'raw HTML inline (entity-escaped quote inside attribute): raw HTML pass-through not supported',
    652 => 'Disallowed Raw HTML (extension) is a filter on top of raw HTML'
         . ' pass-through; DokuWiki escapes raw HTML by policy (see #118-160),'
         . ' so the filter has no input to operate on.',
    484 => 'raw HTML inline `<img …/>` adjacent to `*`: raw HTML pass-through not supported',
    485 => 'raw HTML inline `<a href="**">` adjacent to `**`: raw HTML pass-through not supported',
    486 => 'raw HTML inline `<a href="__">` adjacent to `__`: raw HTML pass-through not supported',

    // --------------------------------------------------------------------
    // Hard line breaks (GfmLinebreak) — both delimiter forms (two trailing
    // spaces and `\` before newline) work in paragraphs, emphasis, and
    // other inline containers. The skipped cases sit inside raw HTML tags,
    // which DokuWiki does not pass through by default.
    // --------------------------------------------------------------------
    662 => 'hard line break inside a raw HTML tag: raw HTML pass-through not supported',
    663 => 'hard line break (backslash form) inside a raw HTML tag — see'
         . ' #662. Raw HTML out of scope.',
];
