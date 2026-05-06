<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Quotes extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 280;
    }

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
    public function postConnect()
    {
        // Map all sub-mode names back to 'quotes' so the Handler
        // dispatches them to this mode object
        $this->Lexer->mapHandler('singlequoteopening', 'quotes');
        $this->Lexer->mapHandler('singlequoteclosing', 'quotes');
        $this->Lexer->mapHandler('apostrophe', 'quotes');
        $this->Lexer->mapHandler('doublequoteopening', 'quotes');
        $this->Lexer->mapHandler('doublequoteclosing', 'quotes');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $call = $handler->getModeName();

        if ($call === 'doublequoteclosing' && $handler->getStatus('doublequote') <= 0) {
            $call = 'doublequoteopening';
        }

        if ($call === 'doublequoteopening') {
            $handler->setStatus('doublequote', $handler->getStatus('doublequote') + 1);
        } elseif ($call === 'doublequoteclosing') {
            $handler->setStatus('doublequote', max(0, $handler->getStatus('doublequote') - 1));
        }

        $handler->addCall($call, [], $pos);
        return true;
    }
}
