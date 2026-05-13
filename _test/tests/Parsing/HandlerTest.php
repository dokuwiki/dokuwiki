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

    function testResetClearsCallsAndStatusAndCurrentMode()
    {
        $handler = new Handler();

        // dirty the handler: append a call, mutate status, and prime
        // currentModeName via a token dispatch.
        $handler->calls[] = ['cdata', ['x'], 0];

        self::setInaccessibleProperty($handler, 'status', [
            'section' => true, 'doublequote' => 3, 'footnote' => true,
        ]);

        $mode = new class extends \dokuwiki\Parsing\ParserMode\AbstractMode {
            public function getSort()
            {
                return 0;
            }

            public function handle($match, $state, $pos, Handler $handler)
            {
                return true;
            }
        };
        $handler->registerModeObject('m', $mode);
        $handler->handleToken('m', 'x', DOKU_LEXER_SPECIAL, 0, 'm');
        $this->assertSame('m', $handler->getModeName());

        $writerBefore = self::getInaccessibleProperty($handler, 'callWriter');

        $handler->reset();

        $this->assertSame([], $handler->calls);
        $this->assertSame('', $handler->getModeName());
        $this->assertSame(
            ['section' => false, 'doublequote' => 0, 'footnote' => false],
            self::getInaccessibleProperty($handler, 'status')
        );
        // reset reinstalls a fresh CallWriter — must be a new instance
        $writerAfter = self::getInaccessibleProperty($handler, 'callWriter');
        $this->assertNotSame($writerBefore, $writerAfter);
        $this->assertInstanceOf(\dokuwiki\Parsing\Handler\CallWriter::class, $writerAfter);
    }

    /**
     * Regression test for #4637
     *
     * handleToken() must route plugin_* modes through plugin() even when
     * the same name is also registered as a mode object.
     *
     * Before the fix, modeObjects was consulted first, which called
     * SyntaxPlugin::handle() directly. That returns data but never
     * emits an instruction via addPluginCall(), so the plugin's rendered
     * output silently disappeared.
     */
    function testPluginModeIsRoutedThroughPluginHandler()
    {
        $handler = new Handler();

        // Plugins register themselves as mode objects under their plugin_* name.
        // This reproduces the conflict the bug depended on.
        $info = plugin_load('syntax', 'info');
        $this->assertNotNull($info, 'info plugin must be available for this test');
        $handler->registerModeObject('plugin_info', $info);

        $handler->handleToken('plugin_info', '~~INFO:datetime~~', DOKU_LEXER_SPECIAL, 0);

        // After the fix, plugin() runs and emits a plugin instruction.
        // With the bug, modeObjects['plugin_info']->handle() ran and emitted nothing.
        $this->assertCount(1, $handler->calls, 'plugin mode must emit exactly one instruction');
        [$name, $args] = $handler->calls[0];
        $this->assertSame('plugin', $name);
        $this->assertSame('info', $args[0]);
        $this->assertSame(['datetime'], $args[1]);
        $this->assertSame('~~INFO:datetime~~', $args[3]);
    }
}
