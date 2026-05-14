<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
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

    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();
        $this->H = new Handler();
        $this->P = new Parser($this->H);
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
