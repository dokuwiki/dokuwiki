<?php

namespace dokuwiki\Parsing\Handler;

/**
 * CallWriter rewriter for GFM lists.
 *
 * GFM accepts `-`, `*`, or `+` for unordered markers and `\d{1,9}[.)]` for
 * ordered markers, with the first ordered item's number carried through as
 * the `start` attribute. Indentation is a multiple of 2 starting at 0;
 * depth = (spaces / 2) + 1, odd indents round down, tabs become two spaces.
 *
 * The state machine lives in {@see AbstractListsRewriter}; this class
 * supplies only the marker parser.
 */
class GfmLists extends AbstractListsRewriter
{
    /** @inheritdoc */
    protected function interpretSyntax(string $match): array
    {
        $stripped = str_replace("\t", '  ', ltrim($match, "\n"));
        $indent = strspn($stripped, ' ');
        $digitLen = strspn($stripped, '0123456789', $indent);

        return [
            'depth' => intdiv($indent, 2) + 1,
            'type' => $digitLen > 0 ? 'o' : 'u',
            'start' => $digitLen > 0 ? (int) substr($stripped, $indent, $digitLen) : 1,
        ];
    }
}
