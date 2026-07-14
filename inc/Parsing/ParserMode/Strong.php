<?php

namespace dokuwiki\Parsing\ParserMode;

class Strong extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 70;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'strong';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        // Flanking rules (simplified): opener must be followed by non-whitespace
        // non-`*`; closer must be preceded by non-whitespace. This rejects
        // `** foo**`, `**foo **`, and empty pairs `****`.
        return '\*\*(?=[^\s*])';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])\*\*';
    }
}
