<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\Parser;

/**
 * Base class for parser mode tests
 *
 * Sets up a fresh Parser and Handler for each test. Provides assertion helpers
 * for comparing handler call sequences.
 */
abstract class ParserTestBase extends \DokuWikiTest
{
    /** @var Parser parser instance for the current test */
    protected Parser $P;
    /** @var Handler handler instance that records calls made by the parser */
    protected Handler $H;
    /** @var ModeRegistry registry attached to $P, injected into modes on addMode() */
    protected ModeRegistry $registry;

    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();
        $this->buildParser();
    }

    /**
     * (Re)build $P/$H/$registry for the current $conf['syntax'].
     *
     * Modes a test adds via $this->P->addMode() are injected with this
     * registry, so its syntax must already be the one under test — use
     * setSyntax() to switch.
     */
    protected function buildParser(): void
    {
        global $conf;
        $this->registry = new ModeRegistry($conf['syntax']);
        $this->H = new Handler($this->registry);
        $this->P = new Parser($this->H, $this->registry);
    }

    /**
     * Switch the syntax flavour under test and rebuild the parser so a
     * fresh registry carries it. Call before adding modes.
     */
    protected function setSyntax(string $syntax): void
    {
        global $conf;
        $conf['syntax'] = $syntax;
        $this->buildParser();
    }

    /** @inheritdoc */
    public function tearDown(): void
    {
        unset($this->P, $this->H);
        parent::tearDown();
    }

    /**
     * Assert that handler calls match the expected calls, ignoring byte index positions
     *
     * The byte index (element [2] in each call) is stripped before comparison because
     * it depends on internal parser state and is not relevant for most tests.
     *
     * @param array $expected the expected call sequence
     * @param array $actual the actual handler calls (typically $this->H->calls)
     * @param string $message optional failure message
     */
    protected function assertCalls(array $expected, array $actual, string $message = ''): void
    {
        $this->assertEquals($expected, array_map($this->stripByteIndex(...), $actual), $message);
    }

    /**
     * Remove the byte index from a single handler call
     *
     * Recursively processes nested calls (e.g. footnotes).
     *
     * @param array $call a single handler call [method, args, byteindex]
     * @return array the call with the byte index removed
     */
    private function stripByteIndex(array $call): array
    {
        unset($call[2]);
        if ($call[0] === 'nest') {
            $call[1][0] = array_map($this->stripByteIndex(...), $call[1][0]);
        }
        return $call;
    }
}
