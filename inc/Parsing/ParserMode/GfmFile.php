<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM fenced code block with tilde fences: ~~~...~~~
 *
 * Tildes map to DokuWiki's `<file>` flavor — same rendering pipeline as
 * `code` but carries "this is a downloadable file" semantics. Markdown
 * authors pick the flavor by choosing the fence character.
 *
 * Unlike backtick fences, tilde info strings may contain any non-newline
 * character (spec example 116).
 */
class GfmFile extends GfmCode
{
    /** @inheritdoc */
    protected $type = 'file';

    /** @inheritdoc */
    protected $fenceChar = '~';

    /** @inheritdoc */
    protected $infoClass = '[^\n]*';

    /** @inheritdoc */
    public function getSort()
    {
        return 210;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_file';
    }
}
