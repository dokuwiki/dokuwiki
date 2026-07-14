<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Windowssharelink extends AbstractMode
{
    protected $pattern;

    /** @inheritdoc */
    public function getSort()
    {
        return 350;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        // The path-segment group is possessive: `[\w\-$]+` stops at each
        // backslash, so successive segments never overlap and the group never
        // needs to backtrack. Without it a long `\\host\a\b\c…` run makes the
        // non-JIT PCRE engine retain one backtracking frame per segment.
        $this->pattern = "\\\\\\\\\w+?(?:\\\\[\w\-$]+)++";
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            $this->pattern,
            $mode,
            'windowssharelink'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('windowssharelink', [$match, null], $pos);
        return true;
    }
}
