<?php

namespace dokuwiki\test\Parsing;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\ParserMode\ModeInterface;

class HandlerTest extends \DokuWikiTest
{
    function testGetModeNameReturnsOriginalName()
    {
        $handler = new Handler();

        // create a simple mode that records what getModeName() returns
        $mode = new class extends \dokuwiki\Parsing\ParserMode\AbstractMode {
            public $receivedModeName = '';
            public function getSort() { return 0; }
            public function handle($match, $state, $pos, Handler $handler)
            {
                $this->receivedModeName = $handler->getModeName();
                return true;
            }
        };

        $handler->registerModeObject('resolved', $mode);

        // simulate dispatch with a remapped name (original differs from resolved)
        $handler->handleToken('resolved', 'test', DOKU_LEXER_SPECIAL, 0, 'original');

        $this->assertSame('original', $mode->receivedModeName);
    }

    function testGetModeNameFallsBackToModeName()
    {
        $handler = new Handler();

        $mode = new class extends \dokuwiki\Parsing\ParserMode\AbstractMode {
            public $receivedModeName = '';
            public function getSort() { return 0; }
            public function handle($match, $state, $pos, Handler $handler)
            {
                $this->receivedModeName = $handler->getModeName();
                return true;
            }
        };

        $handler->registerModeObject('mymode', $mode);

        // no original name passed — should fall back to the resolved name
        $handler->handleToken('mymode', 'test', DOKU_LEXER_SPECIAL, 0);

        $this->assertSame('mymode', $mode->receivedModeName);
    }
}
