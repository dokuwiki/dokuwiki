<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Code as CodeHelper;

class Code extends AbstractMode
{
    /** @var string The call type used in addCall ('code' or 'file') */
    protected $type = 'code';

    /** @inheritdoc */
    public function getSort()
    {
        return 200;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<code\b(?=.*</code>)', $mode, 'code');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</code>', 'code');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        if ($state !== DOKU_LEXER_UNMATCHED) return true;

        // split "language filename [options]>content" at the first >
        [$attr, $content] = sexplode('>', $match, 2, '');
        [$language, $filename, $options] = CodeHelper::parseAttributes($attr);

        $param = [$content, $language, $filename];
        if ($options !== null) $param[] = $options;
        $handler->addCall($this->type, $param, $pos);

        return true;
    }
}
