<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\MailUtils;
use dokuwiki\Parsing\Handler;

class Emaillink extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 340;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('<' . MailUtils::PREG_PATTERN_VALID_EMAIL . '>', $mode, 'emaillink');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $email = preg_replace(['/^</', '/>$/'], '', $match);
        $handler->addCall('emaillink', [$email, null], $pos);
        return true;
    }
}
