<?php

/**
 * GFM spec examples that GfmSpecTest should skip, keyed by example number (as numbered in spec.txt).
 *
 * All entries skipped here are things that are explicitly not supported by DokuWiki's Markdown mode, for reasons
 * documented in the skip reason (mostly limitations of the single-pass regex lexer).
 *
 * Most of the skipped examples are edge cases that are rarely used in practice.
 *
 * Entries are ordered by example number, when the underlying spec.txt is updated, its example numbers may shift,
 * so the keys here may need to be updated to match the new example numbers.
 */

return [

    // -----------------------------------------------------------------------------------------------------------------
    // Tabs (§2.2)
    //
    // DokuWiki's tab handling is binary: a leading tab (matching \n\t directly after the newline) is the indented-code
    // trigger, otherwise tabs are ordinary characters.
    //
    // CommonMark instead advances each tab to the next 4-column stop and uses the resulting column count to drive
    // list-continuation, list-nesting, blockquote-interior, and 4-column indented-code decisions. The column arithmetic
    // is what we don't support.
    // -----------------------------------------------------------------------------------------------------------------

    2   => 'Tabs (§2.2): 2 spaces then tab as code trigger - no column arithmetic to advance tab to column 4',
    4   => 'Tabs (§2.2): tab as 4-column lazy continuation in list - list-interior column arithmetic not implemented',
    5   => 'Tabs (§2.2): two tabs (8 columns) inside list item - list-interior column arithmetic not implemented',
    6   => 'Tabs (§2.2): tabs after blockquote marker for code - quote-interior column arithmetic not implemented',
    7   => 'Tabs (§2.2): tabs after list marker for indented code - list-interior column arithmetic not implemented',
    9   => 'Tabs (§2.2): tab as 4-column indent for list nesting - leading tab is code trigger, never a nesting indent',
    11  => 'Tabs (§2.2): thematic break with tab separators - strict bare-run HR rejects internal whitespace',

    // -----------------------------------------------------------------------------------------------------------------
    // Thematic breaks aka. HRs (§4.1)
    //
    // We only support horizontal rules siting at column 0 with no leading, trailing, or internal whitespace.
    // -----------------------------------------------------------------------------------------------------------------

    17  => 'Thematic breaks (§4.1): HR with 0-3 spaces of leading indent - column-0-only opener required',
    19  => 'Thematic breaks (§4.1): HR after paragraph with 4-space indent - Preformatted triggers regardless of state',
    21  => 'Thematic breaks (§4.1): HR with spaces between delimiters (- - -) - no whitespace allowed',
    22  => 'Thematic breaks (§4.1): HR with spaces between delimiters (** * **) - no whitespace allowed',
    23  => 'Thematic breaks (§4.1): HR with spaces between delimiters (-     -) - no whitespace allowed',
    24  => 'Thematic breaks (§4.1): HR with internal spaces and trailing whitespace - no whitespace allowed',
    29  => 'Thematic breaks (§4.1): Setext underline disambiguation - Setext headers not supported',
    30  => 'Thematic breaks (§4.1): HR between list items (* * *) - no whitespace allowed',
    31  => 'Thematic breaks (§4.1): HR shape inside list item - HRs not supported inside lists',

    // -----------------------------------------------------------------------------------------------------------------
    // ATX headings (§4.2)
    //
    // We only support headers sitting at column 0 with no leading whitespace.
    // -----------------------------------------------------------------------------------------------------------------

    38  => 'ATX headings (§4.2): heading with 0-3 spaces of leading indent - column-0-only opener required',
    40  => 'ATX headings (§4.2): 4-space indent after paragraph as lazy text - no paragraph-open state in lexer',
    41  => 'ATX headings (§4.2): heading with 2-space leading indent - column-0-only opener required',
    49  => 'ATX headings (§4.2): empty heading - XHTML renderer skips blank headings via blank() guard',

    // -----------------------------------------------------------------------------------------------------------------
    // Setext (§4.3)
    //
    // Setext headings are not supported. They are hard to pass and nobody uses them anymore.
    // -----------------------------------------------------------------------------------------------------------------

    50  => 'Setext (§4.3): heading with emphasis and underline - Setext headings not supported',
    51  => 'Setext (§4.3): heading with multi-line content - Setext headings not supported',
    52  => 'Setext (§4.3): heading with indented multi-line content - Setext headings not supported',
    53  => 'Setext (§4.3): heading with any-length underline - Setext headings not supported',
    54  => 'Setext (§4.3): heading with 3-space-indented content/underline - Setext headings not supported',
    55  => 'Setext (§4.3): 4-space-indented content forms code then HR - Setext headings not supported',
    56  => 'Setext (§4.3): underline indented up to 3 spaces with trailing - Setext headings not supported',
    57  => 'Setext (§4.3): 4-space-indented underline (paragraph wins) - Setext headings not supported',
    58  => 'Setext (§4.3): underline with internal spaces (= = / --- -) - Setext and internal-space HR not supported',
    59  => 'Setext (§4.3): trailing spaces in content do not break line - Setext headings not supported',
    60  => 'Setext (§4.3): trailing backslash in content - Setext headings not supported',
    61  => 'Setext (§4.3): block-structure precedence over inline - Setext headings not supported',
    63  => 'Setext (§4.3): underline cannot be lazy continuation in quote - Setext headings not supported',
    65  => 'Setext (§4.3): preceding paragraph becomes heading content - Setext headings not supported',
    66  => 'Setext (§4.3): no blank line required before/after heading - Setext headings not supported',
    70  => 'Setext (§4.3): 4-space-indented content forms code then --- HR - Setext headings not supported',
    72  => 'Setext (§4.3): heading with backslash-escaped marker - Setext headings not supported',
    73  => 'Setext (§4.3): paragraph + heading + paragraph blank-separated - Setext headings not supported',
    75  => 'Setext (§4.3): * * * boundary should be HR not underline - Setext and internal-space HR not supported',
    76  => 'Setext (§4.3): backslash-escaped underline keeps as paragraph - Setext headings not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Indented code (§4.4)
    //
    // DokuWiki's single-pass lexer cannot carry paragraph-open state across modes, so Preformatted triggers on every \n
    // followed by 4-space indent and exits on every blank line. Two CommonMark rules consequently cannot be expressed:
    //
    //   - The 4-space indent must NOT open a code block on a paragraph-continuation line — GFM treats it as lazy
    //     paragraph text. There is no paragraph-open flag to consult.
    //   - An indented code block MAY span blank lines as long as the next non-blank line is still 4-space indented.
    //     DokuWiki's exit-on-any-blank-line behavior splits the block.
    //
    // List-interior indented code (79, 80) additionally needs the column arithmetic that the §2.2 tabs family already
    // documents as out of scope.
    // -----------------------------------------------------------------------------------------------------------------

    79  => 'Indented code (§4.4): inside list item - list-interior column arithmetic plus paragraph trigger',
    80  => 'Indented code (§4.4): after content in list item - list-interior column arithmetic plus paragraph trigger',
    81  => 'Indented code (§4.4): code block spanning blank lines - fully blank lines exit the block',
    83  => 'Indented code (§4.4): 4-space indent on paragraph continuation - no paragraph-open state in lexer',
    85  => 'Indented code (§4.4): 4-space indent mid-paragraph variant - no paragraph-open state in lexer',

    // -----------------------------------------------------------------------------------------------------------------
    // Fenced code (§4.5)
    //
    // Our parser does not support backreferences in regexes, so we cannot enforce the fence-length pairing rules that
    // CommonMark requires for closing a fenced code block.
    //
    // We require fence runs to sit at column 0, no leading spaces allowed.
    //
    // Unclosed fences stay literal because the single-pass lexer has no notion of container boundaries to close at,
    // unlike CommonMark's two-pass block parser.
    // -----------------------------------------------------------------------------------------------------------------

    94  => 'Fenced code (§4.5): closer must be >= opener length - no regex backreferences, any 3+ run closes',
    95  => 'Fenced code (§4.5): closer length pairing for tilde fence - no regex backreferences for length pairing',
    96  => 'Fenced code (§4.5): unclosed fence consuming to EOF - no container boundaries in lexer',
    97  => 'Fenced code (§4.5): unclosed fence with intervening short run - no container boundaries in lexer',
    98  => 'Fenced code (§4.5): fence inside blockquote closes at quote end - no container boundaries in lexer',
    101 => 'Fenced code (§4.5): opener indented 1 space - column-0-only policy, indent tolerance out of scope',
    102 => 'Fenced code (§4.5): opener indented 2 spaces - column-0-only policy',
    103 => 'Fenced code (§4.5): opener indented 3 spaces - column-0-only policy',
    105 => 'Fenced code (§4.5): closer indented 2 spaces - column-0-only policy',
    106 => 'Fenced code (§4.5): indented opener with less-indented closer - column-0-only policy',
    107 => 'Fenced code (§4.5): 4-space-indented closer - column-0-only policy, no valid closer, fence stays literal',
    108 => 'Fenced code (§4.5): three-backtick fallback to inline span - backtick spans with n>=3 not implemented',
    109 => 'Fenced code (§4.5): malformed closer with space-broken run - no valid closer, fence stays literal',
    111 => 'Fenced code (§4.5): fence interrupting Setext heading - Setext headings not supported',
    115 => 'Fenced code (§4.5): single-line ``` x ``` - 3+ backtick inline code spans are not implemented',

    // -----------------------------------------------------------------------------------------------------------------
    // HTML blocks (§4.6)
    //
    // Raw HTML pass-through is not supported. DokuWiki therefore renders it as escaped text.
    // -----------------------------------------------------------------------------------------------------------------

    118 => 'HTML blocks (§4.6): script/pre/style/textarea tag group - raw HTML pass-through not supported',
    119 => 'HTML blocks (§4.6): table with following content - raw HTML pass-through not supported',
    120 => 'HTML blocks (§4.6): div with leading-space indent - raw HTML pass-through not supported',
    121 => 'HTML blocks (§4.6): closing div tag opens block - raw HTML pass-through not supported',
    122 => 'HTML blocks (§4.6): uppercase DIV with attributes - raw HTML pass-through not supported',
    123 => 'HTML blocks (§4.6): div with id on continuation line - raw HTML pass-through not supported',
    124 => 'HTML blocks (§4.6): div with multi-line attribute - raw HTML pass-through not supported',
    125 => 'HTML blocks (§4.6): div opening - raw HTML pass-through not supported',
    126 => 'HTML blocks (§4.6): div with id attribute - raw HTML pass-through not supported',
    127 => 'HTML blocks (§4.6): div with class attribute spread - raw HTML pass-through not supported',
    128 => 'HTML blocks (§4.6): div with garbage attribute syntax - raw HTML pass-through not supported',
    129 => 'HTML blocks (§4.6): div with anchor and literal asterisks - raw HTML pass-through not supported',
    130 => 'HTML blocks (§4.6): table with closing td/tr/table - raw HTML pass-through not supported',
    131 => 'HTML blocks (§4.6): self-closing div on single line - raw HTML pass-through not supported',
    132 => 'HTML blocks (§4.6): anchor as block-level element - raw HTML pass-through not supported',
    133 => 'HTML blocks (§4.6): unknown tag falls into any-tag group - raw HTML pass-through not supported',
    134 => 'HTML blocks (§4.6): inline tag (i) treated as block - raw HTML pass-through not supported',
    135 => 'HTML blocks (§4.6): closing ins tag opens block - raw HTML pass-through not supported',
    136 => 'HTML blocks (§4.6): del with paragraph break inside - raw HTML pass-through not supported',
    137 => 'HTML blocks (§4.6): del without blank line breaks - raw HTML pass-through not supported',
    138 => 'HTML blocks (§4.6): del with literal asterisks inline - raw HTML pass-through not supported',
    139 => 'HTML blocks (§4.6): pre with language and code child - raw HTML pass-through not supported',
    140 => 'HTML blocks (§4.6): script tag - raw HTML pass-through not supported',
    141 => 'HTML blocks (§4.6): style tag opening - raw HTML pass-through not supported',
    142 => 'HTML blocks (§4.6): unclosed style consuming rest of input - raw HTML pass-through not supported',
    143 => 'HTML blocks (§4.6): style inside blockquote - raw HTML pass-through not supported',
    144 => 'HTML blocks (§4.6): style inside list item - raw HTML pass-through not supported',
    145 => 'HTML blocks (§4.6): style followed by inline content - raw HTML pass-through not supported',
    146 => 'HTML blocks (§4.6): comment with following inline content - raw HTML pass-through not supported',
    147 => 'HTML blocks (§4.6): script tag with body content - raw HTML pass-through not supported',
    148 => 'HTML blocks (§4.6): comment - raw HTML pass-through not supported',
    149 => 'HTML blocks (§4.6): processing instruction - raw HTML pass-through not supported',
    150 => 'HTML blocks (§4.6): DOCTYPE declaration - raw HTML pass-through not supported',
    151 => 'HTML blocks (§4.6): CDATA - raw HTML pass-through not supported',
    152 => 'HTML blocks (§4.6): comment with leading whitespace - raw HTML pass-through not supported',
    153 => 'HTML blocks (§4.6): div with leading whitespace - raw HTML pass-through not supported',
    154 => 'HTML blocks (§4.6): paragraph followed by div - raw HTML pass-through not supported',
    155 => 'HTML blocks (§4.6): div followed by paragraph - raw HTML pass-through not supported',
    156 => 'HTML blocks (§4.6): paragraph then div (any-tag group) - raw HTML pass-through not supported',
    157 => 'HTML blocks (§4.6): div continuation across blank line - raw HTML pass-through not supported',
    158 => 'HTML blocks (§4.6): nested divs with content between - raw HTML pass-through not supported',
    159 => 'HTML blocks (§4.6): table followed by paragraph - raw HTML pass-through not supported',
    160 => 'HTML blocks (§4.6): table with markdown content - raw HTML pass-through not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Link refs (§4.7)
    //
    // The single-pass lexer cannot resolve forward references, so links by reference are not supported.
    // -----------------------------------------------------------------------------------------------------------------

    161 => 'Link refs (§4.7): basic definition with title - forward-reference definitions not supported',
    162 => 'Link refs (§4.7): definition with leading indent across lines - forward references not supported',
    163 => 'Link refs (§4.7): definition with multi-line title - forward-reference definitions not supported',
    164 => 'Link refs (§4.7): definition with multi-line label - forward-reference definitions not supported',
    165 => 'Link refs (§4.7): single-quote title across newline - forward-reference definitions not supported',
    166 => 'Link refs (§4.7): definition with whitespace-collapsed label - forward-reference definitions not supported',
    167 => 'Link refs (§4.7): definition without matching reference use - forward-reference definitions not supported',
    169 => 'Link refs (§4.7): pointy-bracket destination - forward-reference definitions not supported',
    170 => 'Link refs (§4.7): destination then blank line then title - forward-reference definitions not supported',
    171 => 'Link refs (§4.7): title-only without destination - forward-reference definitions not supported',
    172 => 'Link refs (§4.7): backslash escapes in destination and title - forward-reference definitions not supported',
    173 => 'Link refs (§4.7): definition with reference use following - forward-reference definitions not supported',
    174 => 'Link refs (§4.7): case-insensitive label match - forward-reference definitions not supported',
    175 => 'Link refs (§4.7): Unicode case folding for label - forward-reference definitions not supported',
    176 => 'Link refs (§4.7): single definition with no body - forward-reference definitions not supported',
    177 => 'Link refs (§4.7): definition with surrounding whitespace - forward-reference definitions not supported',
    178 => 'Link refs (§4.7): definition indented up to 3 spaces - forward-reference definitions not supported',
    179 => 'Link refs (§4.7): multi-line with title on next line - forward-reference definitions not supported',
    183 => 'Link refs (§4.7): definition does not interrupt paragraph - forward-reference definitions not supported',
    184 => 'Link refs (§4.7): between blockquote and paragraph - forward-reference definitions not supported',
    185 => 'Link refs (§4.7): lone definition emits nothing - forward-reference definitions not supported',
    186 => 'Link refs (§4.7): definition followed by HR - forward-reference definitions not supported',
    187 => 'Link refs (§4.7): multiple definitions in a row - forward-reference definitions not supported',
    188 => 'Link refs (§4.7): definition inside blockquote - forward-reference definitions not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Paragraphs (§4.8)
    //
    // We do not support lazy continuation of paragraphs
    // -----------------------------------------------------------------------------------------------------------------

    193 => 'Paragraphs (§4.8): leading whitespace on continuation lines - no paragraph-open state to suppress indent',

    // -----------------------------------------------------------------------------------------------------------------
    // Block quotes (§5.1)
    //
    // We only support block quotes that sit at column 0 with no leading spaces before the > marker.
    //
    // Lazy continuation of block quotes is not supported - every line of a block quote must start with > at column 0.
    //
    // Headers are not supported inside block quotes to not mess up TOC and section edit mechanisms.
    // -----------------------------------------------------------------------------------------------------------------

    206 => 'Block quotes (§5.1): header inside - no headers in quotes',
    207 => 'Block quotes (§5.1): header inside with no space after > - no headers in quotes',
    208 => 'Block quotes (§5.1): leading-space > with 1-3 spaces of indent - column-0-only policy',
    210 => 'Block quotes (§5.1): lazy continuation without > on next line - every quote line needs > at column 0',
    211 => 'Block quotes (§5.1): lazy continuation followed by quoted line - every quote line needs > at column 0',
    212 => 'Block quotes (§5.1): Setext underline --- after > foo - Setext headings not supported',
    215 => 'Block quotes (§5.1): fenced code split across boundary - depends on lazy continuation, not implemented',
    216 => 'Block quotes (§5.1): lazy continuation with indented sub-list - every quote line needs > at column 0',
    225 => 'Block quotes (§5.1): lazy continuation across paragraph - every quote line needs > at column 0',
    227 => 'Block quotes (§5.1): lazy continuation with empty line then text - every quote line needs > at column 0',
    228 => 'Block quotes (§5.1): lazy continuation in nested level - every quote line needs > at column 0',
    229 => 'Block quotes (§5.1): lazy continuation across nested levels - every quote line needs > at column 0',

    // -----------------------------------------------------------------------------------------------------------------
    // List items (§5.2), Task list (§5.3), Lists (§5.4)
    //
    // List indentation uses a fixed 2-space-multiple step starting at 0, lazy continuation is not supported.
    //
    // We do not separate into tight and loose lists, but blank lines inside lists are still allowed and do not
    // interrupt the list - they just don't trigger the extra-paragraph wrapping that CommonMark applies to loose lists.
    //
    // The rewriter groups items by 'u'/'o' type only, not by marker character.
    //
    // The task-list extension is not supported
    // -----------------------------------------------------------------------------------------------------------------

    232 => 'List items (§5.2): marker-width content-column for indented body - extra spaces after marker collapsed',
    235 => 'List items (§5.2): marker-width content-column with continuation - extra spaces after marker collapsed',
    237 => 'List items (§5.2): ordered nested in >> with 3-space indent - leading-> indent plus marker-width column',
    238 => 'List items (§5.2): bullet inside >> with leading-space continuation - column-0-only > plus interior space',
    241 => 'List items (§5.2): marker-width column with code/paragraph/quote - extra spaces after marker collapsed',
    242 => 'List items (§5.2): marker-width column plus indented code blanks - extra spaces plus blank-spanning code',
    249 => 'List items (§5.2): marker-width content-column for 10. foo - extra spaces after marker collapsed',
    254 => 'List items (§5.2): marker-width content-column edge case - extra spaces after marker collapsed',
    257 => 'List items (§5.2): empty bullet line then content next line - content column from next line not derived',
    258 => 'List items (§5.2): marker-width content-column for 1.  foo - extra spaces after marker collapsed',
    263 => 'List items (§5.2): indent ambiguity at column 0/1/2 - 1- or 3-space indent rounded to nearest 2',
    264 => 'List items (§5.2): 1-space-indent nesting variation - 1- or 3-space indent rounded to nearest 2',
    265 => 'List items (§5.2): marker-width with multi-line continuation - extra spaces after marker collapsed',
    266 => 'List items (§5.2): marker-width with multi-line continuation variant - extra spaces after marker collapsed',
    267 => 'List items (§5.2): lazy continuation inside list item - column-0 paragraph wrap not supported',
    268 => 'List items (§5.2): lazy continuation across line break - column-0 paragraph wrap not supported',
    270 => 'List items (§5.2): lazy continuation across blank line - lazy plus loose/tight rules not implemented',
    271 => 'List items (§5.2): lazy continuation in nested quote-list-quote - column-0 paragraph wrap not supported',
    273 => 'List items (§5.2): interrupting paragraph without blank line - requires multi-pass parser to revisit text',
    275 => 'List items (§5.2): 3-space indent rounds to 2 for sub-list - 1- or 3-space indent rounded to nearest 2',
    276 => 'List items (§5.2): marker-width column with mixed types - extra spaces plus marker-character splits',
    277 => 'List items (§5.2): nested markers on a single line - extra spaces after marker collapsed',
    278 => 'List items (§5.2): marker-character switch splits the run - rewriter groups by u/o type, not character',
    279 => 'Task list (§5.3): basic checkbox marker - extension not implemented, literal [ ]/[x] stays as content',
    280 => 'Task list (§5.3): nested checkbox markers - extension not implemented, literal brackets stay as content',
    281 => 'Lists (§5.4): marker-character change unordered (- to +) - rewriter groups by u/o type, not character',
    282 => 'Lists (§5.4): ordered delimiter switch (. to )) - rewriter groups by u/o type, not character',
    284 => 'Lists (§5.4): interrupting paragraph without blank line - requires multi-pass parser',
    286 => 'Lists (§5.4): marker-width column for ordered list - extra spaces after marker collapsed',
    287 => 'Lists (§5.4): triple blank plus indented continuation - loose/tight classification not implemented',
    288 => 'Lists (§5.4): marker-character change at deeper level - rewriter groups by u/o type only',
    289 => 'Lists (§5.4): marker-character change with type switch - rewriter groups by u/o type only',
    290 => 'Lists (§5.4): 1-space-indent variations at top level - nesting indent rounded to nearest 2',
    291 => 'Lists (§5.4): 1-space-indent variations on ordered list - nesting indent rounded to nearest 2',
    292 => 'Lists (§5.4): marker-character change inside nested list - rewriter groups by u/o type only',
    293 => 'Lists (§5.4): marker-character change with mixed indent - u/o-only grouping plus indent rounding',
    294 => 'Lists (§5.4): lazy continuation across types - column-0 wrap plus marker splits not implemented',
    295 => 'Lists (§5.4): lazy continuation in nested list - column-0 paragraph wrap not supported',
    296 => 'Lists (§5.4): lazy continuation across blank line - lazy continuation plus loose/tight not implemented',
    297 => 'Lists (§5.4): blank-line classification in nested list - strict loose/tight rules not implemented',
    298 => 'Lists (§5.4): blank-line classification - strict loose/tight rules not implemented',
    300 => 'Lists (§5.4): blank-line class with marker change - loose/tight plus marker splits not implemented',
    301 => 'Lists (§5.4): blank-line classification plus marker-width - loose/tight plus extra-spaces-after-marker',
    304 => 'Lists (§5.4): blank line between sub-list items - strict loose/tight rules not implemented',
    305 => 'Lists (§5.4): blank line between deeply nested items - strict loose/tight rules not implemented',
    306 => 'Lists (§5.4): blank line at end of loose list - strict loose/tight rules not implemented',

    // -----------------------------------------------------------------------------------------------------------------
    // Backslash escapes (§6.1)
    //
    // These examples exercise escape behavior inside features DokuWiki we do not to support, such as
    // raw HTML pass-through, link titles, reference-link definitions.
    // -----------------------------------------------------------------------------------------------------------------

    317 => 'Backslash escapes (§6.1): escapes inside raw HTML - raw HTML pass-through not supported',
    318 => 'Backslash escapes (§6.1): escapes in link title - title attribute discarded, no DW link slot',
    319 => 'Backslash escapes (§6.1): in reference-link definition - forward-reference definitions not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Entity refs (§6.2)
    //
    // These examples cross into raw HTML pass-through, link-title slots, strict URL rejection, or reference definitions
    // — all features that DokuWiki does not implement for reasons documented in their own sections.
    // -----------------------------------------------------------------------------------------------------------------

    327 => 'Entity refs (§6.2): raw HTML tag with entity in attribute - raw HTML pass-through not supported',
    328 => 'Entity refs (§6.2): link with entity-decoded URL and title - title attribute discarded, no DW link slot',
    329 => 'Entity refs (§6.2): reference link with entity-decoded URL - forward-reference definitions not supported',
    337 => 'Entity refs (§6.2): decoded quote inside link URL - permissive URL slot, strict GFM rejection absent',

    // -----------------------------------------------------------------------------------------------------------------
    // Code spans (§6.3)
    //
    // Cross-positional precedence between code spans and emphasis/links would need a pre-scan pass - our single-pass
    // lexer matches leftmost-first and cannot reject an earlier opener because a later backtick span would consume its
    // closer.
    // -----------------------------------------------------------------------------------------------------------------

    351 => 'Code spans (§6.3): cross-position precedence vs. emphasis - leftmost-match in lexer, no pre-scan',
    352 => 'Code spans (§6.3): span inside link label takes precedence - leftmost-match in lexer, no pre-scan',
    354 => 'Code spans (§6.3): raw HTML tag pass-through - raw HTML pass-through not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Emphasis (§6.4)
    //
    // DokuWiki's regex lexer uses leftmost-match and cannot apply CommonMark's left/right-flanking rules that
    // distinguish word-chars, whitespace, and punctuation for * / _ delimiters, the multiple-of-3 rule for overlapping
    // runs, the excess-drop logic for long delimiter runs, or balanced-pair analysis across nested delimiters.
    // -----------------------------------------------------------------------------------------------------------------

    362 => 'Emphasis (§6.4): punctuation-adjacent * left/right flanking - flanking-delimiter analysis not implemented',
    363 => 'Emphasis (§6.4): Unicode whitespace (U+00A0) flanking - lexer is ASCII-only, u-flag-aware regex needed',
    368 => 'Emphasis (§6.4): punctuation-adjacent _ flanking - flanking-delimiter analysis not implemented',
    372 => 'Emphasis (§6.4): intraword _ with punctuation inside - flanking-delimiter analysis not implemented',
    376 => 'Emphasis (§6.4): bare * on next line should not pair - lone * taken as empty list marker, breaks paragraph',
    377 => 'Emphasis (§6.4): * followed by ( as punctuation-adjacent - flanking-delimiter analysis not implemented',
    378 => 'Emphasis (§6.4): nested *(*foo*)* with punctuation - flanking plus balanced-pair analysis not implemented',
    382 => 'Emphasis (§6.4): nested _(_foo_)_ with punctuation - flanking plus balanced-pair analysis not implemented',
    389 => 'Emphasis (§6.4): punctuation-adjacent ** flanking - flanking-delimiter analysis not implemented',
    394 => 'Emphasis (§6.4): punctuation-adjacent __ flanking - flanking-delimiter analysis not implemented',
    395 => 'Emphasis (§6.4): intraword __ left/right flanking - flanking-delimiter analysis not implemented',
    396 => 'Emphasis (§6.4): intraword __ across digits (5__6__78) - flanking-delimiter analysis not implemented',
    397 => 'Emphasis (§6.4): intraword __ with Cyrillic - flanking-delimiter analysis not implemented',
    398 => 'Emphasis (§6.4): __foo, __bar__, baz__ flanking with pairing - flanking-delimiter analysis not implemented',
    401 => 'Emphasis (§6.4): ** followed by ( as punctuation-adjacent - flanking-delimiter analysis not implemented',
    404 => 'Emphasis (§6.4): nested *bar* inside **foo ... foo** - flanking-delimiter analysis not implemented',
    407 => 'Emphasis (§6.4): __ followed by ( as punctuation-adjacent - flanking-delimiter analysis not implemented',
    409 => 'Emphasis (§6.4): __foo__bar intraword close - flanking-delimiter analysis not implemented',
    410 => 'Emphasis (§6.4): intraword __ with leading Cyrillic - flanking-delimiter analysis not implemented',
    411 => 'Emphasis (§6.4): __foo__bar__baz__ multiple pairs - flanking-delimiter analysis not implemented',
    412 => 'Emphasis (§6.4): __(bar)__ punctuation-adjacent - flanking-delimiter analysis not implemented',
    416 => 'Emphasis (§6.4): overlapping _foo _bar_ baz_ - CommonMark rule 9 multiple-of-3 not implemented',
    417 => 'Emphasis (§6.4): overlapping _ / __ with flanking - flanking plus multiple-of-3 not implemented',
    418 => 'Emphasis (§6.4): overlapping *foo *bar** - multiple-of-3 rule not implemented',
    419 => 'Emphasis (§6.4): nested *foo **bar** baz* - balanced-pair analysis not implemented',
    421 => 'Emphasis (§6.4): overlapping *foo**bar* - multiple-of-3 rule not implemented',
    422 => 'Emphasis (§6.4): nested ***foo** bar* - triple-delimiter analysis not implemented',
    423 => 'Emphasis (§6.4): nested *foo **bar*** - triple-delimiter analysis not implemented',
    424 => 'Emphasis (§6.4): nested *foo**bar*** - triple-delimiter analysis not implemented',
    425 => 'Emphasis (§6.4): triple foo***bar***baz - triple-delimiter analysis not implemented',
    426 => 'Emphasis (§6.4): long delimiter runs of mixed lengths - excess-drop logic not implemented',
    427 => 'Emphasis (§6.4): deeply nested *foo **bar *baz* bim** bop* - balanced-pair analysis not implemented',
    428 => 'Emphasis (§6.4): inside link label *foo [*bar*](/url)* - link label is flat string, not re-tokenized',
    434 => 'Emphasis (§6.4): overlapping __foo __bar__ baz__ - multiple-of-3 rule not implemented',
    435 => 'Emphasis (§6.4): ____foo__ bar__ leading long run - excess-drop logic not implemented',
    436 => 'Emphasis (§6.4): **foo **bar**** trailing long run - excess-drop logic not implemented',
    439 => 'Emphasis (§6.4): nested ***foo* bar** - triple-delimiter analysis not implemented',
    440 => 'Emphasis (§6.4): nested **foo *bar*** - triple-delimiter analysis not implemented',
    441 => 'Emphasis (§6.4): deeply nested **foo *bar **baz** bim* bop** - balanced-pair analysis not implemented',
    442 => 'Emphasis (§6.4): inside link label **foo [*bar*](/url)** - link label is flat string, not re-tokenized',
    463 => 'Emphasis (§6.4): __foo_ mixed _ / __ pairing - flanking-delimiter analysis not implemented',
    464 => 'Emphasis (§6.4): _foo__ mixed _ / __ pairing - flanking-delimiter analysis not implemented',
    465 => 'Emphasis (§6.4): ___foo__ run-length analysis - flanking plus run-length analysis not implemented',
    466 => 'Emphasis (§6.4): ____foo_ run-length analysis - flanking plus run-length analysis not implemented',
    467 => 'Emphasis (§6.4): __foo___ run-length analysis - flanking plus run-length analysis not implemented',
    468 => 'Emphasis (§6.4): _foo____ run-length analysis - flanking plus run-length analysis not implemented',
    470 => 'Emphasis (§6.4): nested *_foo_* - balanced-pair analysis not implemented',
    472 => 'Emphasis (§6.4): nested _*foo*_ - balanced-pair analysis not implemented',
    473 => 'Emphasis (§6.4): ****foo**** excess-drop (4+4 to strong) - excess-drop logic not implemented',
    474 => 'Emphasis (§6.4): ____foo____ excess-drop (4+4 to strong) - excess-drop logic not implemented',
    475 => 'Emphasis (§6.4): ******foo****** excess-drop (6+6 to strong) - excess-drop logic not implemented',
    477 => 'Emphasis (§6.4): _____foo_____ excess-drop (5+5 to em+strong) - excess-drop logic not implemented',
    478 => 'Emphasis (§6.4): *foo _bar* baz_ overlapping different - flanking plus balanced-pair not implemented',
    479 => 'Emphasis (§6.4): *foo __bar *baz bim__ bam* crossing - flanking plus balanced-pair not implemented',
    480 => 'Emphasis (§6.4): **foo **bar baz** overlapping same - flanking plus balanced-pair not implemented',
    482 => 'Emphasis (§6.4): crossing link boundary *[bar*](/url) - leftmost-match cannot reject opener crossing link',
    483 => 'Emphasis (§6.4): crossing link _foo [bar_](/url) - leftmost-match cannot reject opener crossing link',
    484 => 'Emphasis (§6.4): raw HTML <img/> adjacent to * - raw HTML pass-through not supported',
    485 => 'Emphasis (§6.4): raw HTML <a href="**"> adjacent to ** - raw HTML pass-through not supported',
    486 => 'Emphasis (§6.4): raw HTML <a href="__"> adjacent to __ - raw HTML pass-through not supported',
    489 => 'Emphasis (§6.4): vs angle-bracket autolink with ** inside URL - leftmost-match cannot reorder spans',
    490 => 'Emphasis (§6.4): vs angle-bracket autolink with __ inside URL - leftmost-match cannot reorder spans',

    // -----------------------------------------------------------------------------------------------------------------
    // Links (§6.6)
    //
    // GfmLink deliberately does not implement several link features:
    //
    //   - Title attribute ("title" / 'title' / (title) after the URL). Parses cleanly but is discarded — DokuWiki link
    //     instructions have no title slot, and plumbing one through every renderer is out of scope.
    //   - Pointy-bracket destinations <...>. Rarely used; regex cost and interaction with raw-HTML detection outweigh
    //     the benefit.
    //   - Balanced parentheses inside URL destinations.
    //   - Strict GFM URL rejection (e.g. unquoted whitespace, decoded quote inside URL). GfmLink uses a permissive URL
    //     slot.
    //   - Nested brackets in link labels — single-pass lexer cannot resolve, label class forbids brackets so outer
    //     match fails.
    //   - Inline formatting inside link labels — label is taken as a flat string and not re-tokenized.
    //   - Reference links ([text][id], [text][], [foo] with matching definition). Forward references would require a
    //     two-pass parse; only inline links [text](url) are supported.
    // -----------------------------------------------------------------------------------------------------------------

    493 => 'Links (§6.6): link with title attribute - title slot not supported by DW link instructions',
    495 => 'Links (§6.6): empty URL destination [link]() - GfmLink pattern requires non-empty URL',
    496 => 'Links (§6.6): pointy-bracket link destination - not supported, regex cost outweighs benefit',
    497 => 'Links (§6.6): unquoted whitespace in URL slot - strict GFM URL rejection not implemented',
    498 => 'Links (§6.6): pointy-bracket destination with spaces - not supported',
    500 => 'Links (§6.6): pointy-bracket destination with newline - not supported',
    501 => 'Links (§6.6): pointy-bracket destination containing ) - not supported',
    502 => 'Links (§6.6): pointy-bracket destination with trailing backslash - not supported',
    503 => 'Links (§6.6): malformed pointy-bracket destinations - not supported',
    505 => 'Links (§6.6): balanced parens inside URL destination - regex single-level only',
    507 => 'Links (§6.6): pointy-bracket wrapping unbalanced parens - not supported',
    512 => 'Links (§6.6): destination that parses as title - edge case not supported',
    513 => 'Links (§6.6): three quoting styles for link title - title slot not supported',
    514 => 'Links (§6.6): title with HTML-entity escape - title slot not supported',
    515 => 'Links (§6.6): title separated by non-breaking space - title slot not supported',
    516 => 'Links (§6.6): title with nested balanced quotes - Markdown.pl quirk, not supported',
    517 => 'Links (§6.6): title with different inner quote type - title slot not supported',
    518 => 'Links (§6.6): multi-line link title - title slot not supported',
    520 => 'Links (§6.6): label with literal nested brackets - label class forbids brackets, outer match fails',
    522 => 'Links (§6.6): nested bracket forms inner link only - lexer cannot resolve nested labels',
    524 => 'Links (§6.6): inline formatting inside link label - label is flat string, not re-tokenized',
    526 => 'Links (§6.6): nested links - inner link matches, outer falls back to literal',
    527 => 'Links (§6.6): nested links inside emphasis - leftmost-match cannot resolve nesting',
    528 => 'Links (§6.6): image-as-alt with nested link - alt class forbids brackets, outer image fails',
    529 => 'Links (§6.6): link text grouping vs emphasis - leftmost-match cannot override',
    530 => 'Links (§6.6): emphasis/bracket crossing - leftmost-match cannot override',
    532 => 'Links (§6.6): raw HTML inside link text - raw HTML pass-through not supported',
    533 => 'Links (§6.6): code span inside link text - requires pre-scan pass for cross-position precedence',
    534 => 'Links (§6.6): autolink inside link text - angle-bracket autolinks not supported',
    535 => 'Links (§6.6): reference link [foo][bar] - forward-reference definitions not supported',
    536 => 'Links (§6.6): reference link with emphasis in text - forward-reference definitions not supported',
    537 => 'Links (§6.6): reference link with multi-line text - forward-reference definitions not supported',
    538 => 'Links (§6.6): reference link case-insensitive label - forward-reference definitions not supported',
    539 => 'Links (§6.6): reference link whitespace-collapsed label - forward-reference definitions not supported',
    540 => 'Links (§6.6): reference link with multi-word label - forward-reference definitions not supported',
    541 => 'Links (§6.6): reference link case-insensitive Unicode - forward-reference definitions not supported',
    542 => 'Links (§6.6): reference link with whitespace before label - forward-reference definitions not supported',
    543 => 'Links (§6.6): reference link with whitespace inside brackets - forward-reference definitions not supported',
    544 => 'Links (§6.6): reference link no whitespace before label - forward-reference definitions not supported',
    545 => 'Links (§6.6): reference link first def wins on duplicate - forward-reference definitions not supported',
    546 => 'Links (§6.6): reference link with backslash in label - forward-reference definitions not supported',
    547 => 'Links (§6.6): reference link inline content not parsed - forward-reference definitions not supported',
    548 => 'Links (§6.6): reference link does not interrupt sentences - forward-reference definitions not supported',
    549 => 'Links (§6.6): reference link with empty inner label - forward-reference definitions not supported',
    550 => 'Links (§6.6): reference link [foo][] collapsed form - forward-reference definitions not supported',
    551 => 'Links (§6.6): reference link with bracketed text - forward-reference definitions not supported',
    552 => 'Links (§6.6): reference link with whitespace at boundary - forward-reference definitions not supported',
    553 => 'Links (§6.6): reference link with newline between - forward-reference definitions not supported',
    557 => 'Links (§6.6): reference link with newline-separated label - forward-reference definitions not supported',
    558 => 'Links (§6.6): reference link newline-separated variant - forward-reference definitions not supported',
    560 => 'Links (§6.6): reference link with literal [] - forward-reference definitions not supported',
    561 => 'Links (§6.6): collapsed reference link [foo][] - forward-reference definitions not supported',
    562 => 'Links (§6.6): collapsed reference link with emphasis - forward-reference definitions not supported',
    563 => 'Links (§6.6): collapsed reference link case-insensitive - forward-reference definitions not supported',
    564 => 'Links (§6.6): collapsed reference link with newline - forward-reference definitions not supported',
    565 => 'Links (§6.6): shortcut reference link [foo] - forward-reference definitions not supported',
    566 => 'Links (§6.6): shortcut reference link with emphasis - forward-reference definitions not supported',
    567 => 'Links (§6.6): shortcut reference link with whitespace label - forward-reference definitions not supported',
    568 => 'Links (§6.6): shortcut reference link with multi-word label - forward-reference definitions not supported',
    569 => 'Links (§6.6): shortcut reference link case-insensitive - forward-reference definitions not supported',
    570 => 'Links (§6.6): shortcut reference link with whitespace - forward-reference definitions not supported',
    571 => 'Links (§6.6): shortcut reference link with backslash escape - forward-reference definitions not supported',
    572 => 'Links (§6.6): shortcut reference link with emphasis in label - forward-reference definitions not supported',
    573 => 'Links (§6.6): reference link with literal [foo] - forward-reference definitions not supported',
    574 => 'Links (§6.6): reference link with double bracket [[foo]] - forward-reference definitions not supported',
    575 => 'Links (§6.6): reference link inside paragraph - forward-reference definitions not supported',
    576 => 'Links (§6.6): reference link multi-instance - forward-reference definitions not supported',
    577 => 'Links (§6.6): reference link collapsed-form fallback - forward-reference definitions not supported',
    578 => 'Links (§6.6): reference link shortcut-form fallback - forward-reference definitions not supported',
    579 => 'Links (§6.6): reference link full-form fallback - forward-reference definitions not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Images (§6.7)
    //
    // We don't have a title slot or support forward references
    // -----------------------------------------------------------------------------------------------------------------

    580 => 'Images (§6.7): image with title attribute - title slot not supported by DW link instructions',
    581 => 'Images (§6.7): reference-style image - forward-reference definitions not supported',
    582 => 'Images (§6.7): nested image-in-image alt - alt forbids brackets, leftmost-match cannot reorder',
    583 => 'Images (§6.7): link inside image alt - alt forbids brackets, leftmost-match cannot reorder',
    584 => 'Images (§6.7): collapsed reference-style image - forward-reference definitions not supported',
    585 => 'Images (§6.7): full reference-style image - forward-reference definitions not supported',
    587 => 'Images (§6.7): image with title attribute variant - title slot not supported',
    588 => 'Images (§6.7): pointy-bracket image destination - not supported, same as link pointy-brackets',
    590 => 'Images (§6.7): reference-style image with label match - forward-reference definitions not supported',
    591 => 'Images (§6.7): reference-style image case-insensitive - forward-reference definitions not supported',
    592 => 'Images (§6.7): collapsed reference-style image ![foo][] - forward-reference definitions not supported',
    593 => 'Images (§6.7): collapsed reference-style image with emphasis - forward-reference definitions not supported',
    594 => 'Images (§6.7): collapsed reference-style case-insensitive - forward-reference definitions not supported',
    595 => 'Images (§6.7): reference-style image with whitespace - forward-reference definitions not supported',
    596 => 'Images (§6.7): shortcut reference-style image ![foo] - forward-reference definitions not supported',
    597 => 'Images (§6.7): shortcut reference-style image with emphasis - forward-reference definitions not supported',
    598 => 'Images (§6.7): image with unescaped nested brackets - literal-fallback behavior not supported',
    599 => 'Images (§6.7): shortcut reference-style case-insensitive - forward-reference definitions not supported',
    600 => 'Images (§6.7): image-via-reference fallback !\[foo] - forward-reference definitions not supported',
    601 => 'Images (§6.7): image-via-reference fallback \![foo] - forward-reference definitions not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Autolinks (§6.8)
    //
    // Externallink builds one regex per scheme listed in conf/scheme.conf, and only the schemes in that allow-list are
    // recognised as bare-URL autolinks. Schemes outside the allow-list (mailto, made-up schemes, localhost) fall
    // through to default escaping and the URL is emitted as literal text
    // -----------------------------------------------------------------------------------------------------------------

    605 => 'Autolinks (§6.8): angle-bracket autolink with MAILTO: - mailto not in conf/scheme.conf default allow-list',
    606 => 'Autolinks (§6.8): angle-bracket autolink with a+b+c scheme - scheme not in conf/scheme.conf allow-list',
    607 => 'Autolinks (§6.8): angle-bracket autolink with made-up scheme - scheme not in conf/scheme.conf allow-list',
    609 => 'Autolinks (§6.8): angle-bracket with localhost:5001 - localhost not in conf/scheme.conf allow-list',

    // -----------------------------------------------------------------------------------------------------------------
    // Raw HTML (§6.10)
    //
    // We do not support raw HTML pass-through at all.
    // -----------------------------------------------------------------------------------------------------------------

    632 => 'Raw HTML (§6.10): open tag - pass-through not supported',
    633 => 'Raw HTML (§6.10): closing tag - pass-through not supported',
    634 => 'Raw HTML (§6.10): tag with multi-line attributes - pass-through not supported',
    635 => 'Raw HTML (§6.10): tag with line breaks in attributes - pass-through not supported',
    636 => 'Raw HTML (§6.10): tag with custom name and attributes - pass-through not supported',
    638 => 'Raw HTML (§6.10): tag with illegal attribute names - pass-through not supported',
    639 => 'Raw HTML (§6.10): tag with illegal attribute values - pass-through not supported',
    641 => 'Raw HTML (§6.10): open and closing tags pair - pass-through not supported',
    642 => 'Raw HTML (§6.10): HTML comment - pass-through not supported',
    643 => 'Raw HTML (§6.10): invalid comment - pass-through not supported',
    644 => 'Raw HTML (§6.10): processing instruction - pass-through not supported',
    645 => 'Raw HTML (§6.10): declaration - pass-through not supported',
    646 => 'Raw HTML (§6.10): declaration with single-letter name - pass-through not supported',
    647 => 'Raw HTML (§6.10): EMPTY declaration - pass-through not supported',
    648 => 'Raw HTML (§6.10): CDATA section - pass-through not supported',
    649 => 'Raw HTML (§6.10): tag with entity ref in attribute - pass-through not supported',
    650 => 'Raw HTML (§6.10): tag with backslash in attribute - pass-through not supported',
    651 => 'Raw HTML (§6.10): tag with entity quote in attribute - pass-through not supported',

    // -----------------------------------------------------------------------------------------------------------------
    // Raw HTML (§6.11)
    //
    // The Disallowed Raw HTML extension is a filter on top of raw HTML pass-through. DokuWiki escapes raw HTML by
    // policy, so the filter has no input to operate on.
    // -----------------------------------------------------------------------------------------------------------------

    652 => 'Raw HTML (§6.11): disallowed-tag filter on output - DW escapes raw HTML by policy, filter has no input',

    // -----------------------------------------------------------------------------------------------------------------
    // Hard breaks (§6.12)
    //
    // The skipped cases sit inside raw HTML tags, which DokuWiki does not pass through by default.
    // -----------------------------------------------------------------------------------------------------------------

    662 => 'Hard breaks (§6.12): inside raw HTML tag - raw HTML pass-through not supported',
    663 => 'Hard breaks (§6.12): backslash form inside raw HTML tag - raw HTML pass-through not supported',

];
