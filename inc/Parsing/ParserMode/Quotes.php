<?php

namespace dokuwiki\Parsing\ParserMode;

class Quotes extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        global $conf;

        $ws   =  '\s/\#~:+=&%@\-\x28\x29\]\[{}><"\'';   // whitespace
        $punc =  ';,\.?!';

        if ($conf['typography'] == 2) {
            $this->Lexer->addSpecialPattern(
                "(?<=^|[$ws])'(?=[^$ws$punc])",
                $mode,
                'singlequoteopening'
            );
            $this->Lexer->addSpecialPattern(
                "(?<=^|[^$ws]|[$punc])'(?=$|[$ws$punc])",
                $mode,
                'singlequoteclosing'
            );
            $this->Lexer->addSpecialPattern(
                "(?<=^|[^$ws$punc])'(?=$|[^$ws$punc])",
                $mode,
                'apostrophe'
            );
        }

        $this->Lexer->addSpecialPattern(
            "(?<=^|[$ws])\"(?=[^$ws$punc])",
            $mode,
            'doublequoteopening'
        );
        $this->Lexer->addSpecialPattern(
            "\"",
            $mode,
            'doublequoteclosing'
        );
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 280;
    }
}
