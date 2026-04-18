<?php

namespace dokuwiki\Parsing\ParserMode;

class Emphasis extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 80;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'emphasis';
    }

    /**
     * @inheritdoc
     * @see https://github.com/dokuwiki/dokuwiki/issues/384
     * @see https://github.com/dokuwiki/dokuwiki/issues/763
     * @see https://github.com/dokuwiki/dokuwiki/issues/1468
     */
    protected function getEntryPattern(): string
    {
        return '//(?=[^\x00]*[^:])';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '//';
    }
}
