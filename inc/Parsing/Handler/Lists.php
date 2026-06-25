<?php

namespace dokuwiki\Parsing\Handler;

/**
 * CallWriter rewriter for DokuWiki lists.
 *
 * DokuWiki's list syntax requires 2 spaces of indent per nesting level and
 * uses `*` for unordered, `-` for ordered. Ordered lists do not carry a
 * start number. The state machine lives in {@see AbstractListsRewriter};
 * this class supplies only the marker parser.
 */
class Lists extends AbstractListsRewriter
{
    /** @inheritdoc */
    protected function interpretSyntax(string $match): array
    {
        $type = str_ends_with($match, '*') ? 'u' : 'o';
        $depth = substr_count(str_replace("\t", '  ', $match), '  ') + 1;
        return ['depth' => $depth, 'type' => $type];
    }
}
