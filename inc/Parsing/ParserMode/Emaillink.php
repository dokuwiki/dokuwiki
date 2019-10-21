<?php

namespace dokuwiki\Parsing\ParserMode;

class Emaillink extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // pattern below is defined in inc/mail.php
        $this->Lexer->addSpecialPattern('<'.PREG_PATTERN_VALID_EMAIL.'>', $mode, 'emaillink');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 340;
    }
}
