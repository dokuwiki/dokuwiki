<?php

namespace dokuwiki\Parsing\Lexer;

/**
 * A closer-existence check guarding a lexer mode.
 *
 * Holds one mode's closer and boundary patterns, the scan regex compiled
 * from them, and the memoized verdicts of earlier scans. The Lexer calls
 * position() to ask whether a valid closer exists ahead of an entry match;
 * memoizing the verdict keeps that check linear over a whole parse.
 *
 * See Lexer::addCloserPattern() for the contract the patterns follow.
 */
class CloserPattern
{
    /** @var string pattern matching a valid closer, in plain PCRE syntax */
    protected string $closer;
    /** @var string|null pattern the closer must occur before, in plain PCRE syntax */
    protected ?string $boundary;
    /** @var string|null scan regex built by compile(), null until then */
    protected ?string $scan = null;
    /** @var int|null start of the range proven to see the memoized closer */
    protected ?int $successFrom = null;
    /** @var int|null memoized closer position, valid from successFrom on */
    protected ?int $closerPos = null;
    /** @var int|null start of the range proven closer-free */
    protected ?int $failFrom = null;
    /** @var int|null exclusive end of the range proven closer-free */
    protected ?int $failUntil = null;

    /**
     * Translates both patterns from the lexer convention into plain PCRE
     * syntax. Building the scan regex is deferred to compile().
     *
     * @param string $pattern regex fragment matching the closing delimiter,
     *                        flanking context expressed as lookarounds
     * @param string|null $boundary regex fragment the closer must occur
     *                              before; null to scan to the end of the
     *                              subject
     */
    public function __construct(string $pattern, ?string $boundary = null)
    {
        $this->closer = ParallelRegex::escapePattern($pattern);
        $this->boundary = $boundary === null ? null : ParallelRegex::escapePattern($boundary);
    }

    /**
     * Whether compile() has built the scan regex yet.
     *
     * @return bool
     */
    public function isCompiled(): bool
    {
        return $this->scan !== null;
    }

    /**
     * Builds the scan regex from the mode's patterns and the opaque spans
     * supplied by the Lexer.
     *
     * It matches the earliest of three things ahead: the boundary (named
     * group "bound"), the closer (named group "closer"), or an opaque span
     * — a stretch the lexer consumes atomically, so a closer lookalike
     * inside it can never really close the mode (see Lexer::opaqueSpans()).
     * Alternative order breaks same-position ties: the boundary wins over a
     * span starting at the same byte, and the closer is never swallowed by
     * one. position() interprets the matches.
     *
     * @param string[] $opaqueSpans regex fragments in plain PCRE syntax,
     *                              each matching one whole span the scan
     *                              must not look into
     * @param bool $case true for case sensitive matching
     * @return void
     */
    public function compile(array $opaqueSpans, bool $case): void
    {
        $alternatives = [];
        if ($this->boundary !== null) {
            $alternatives[] = '(?<bound>' . $this->boundary . ')';
        }
        $alternatives[] = '(?<closer>' . $this->closer . ')';
        $alternatives = array_merge($alternatives, $opaqueSpans);

        $flags = $case ? 'ms' : 'msi';
        $this->scan = '/' . implode('|', $alternatives) . '/' . $flags;
    }

    /**
     * Byte position where the first valid closer at or after $from starts,
     * or null if none occurs before the boundary (or the end of subject).
     * Requires compile() to have run.
     *
     * Answers from the memo when possible, otherwise runs the scan (see
     * compile()), hopping over opaque spans, and memoizes the verdict: on
     * success the range from $from up to the closer, on failure the
     * closer-free range up to the boundary (or end of subject). A verdict
     * holds for any position in its range because the leftmost scan proves
     * no closer lies earlier; positions inside opaque spans are never
     * queried, since the lexer consumes those without matching entry
     * patterns in them.
     *
     * @param string $subject the full subject being lexed
     * @param int $from byte position just after the entry pattern match
     * @return int|null
     */
    public function position(string $subject, int $from): ?int
    {
        if ($this->successFrom !== null && $from >= $this->successFrom && $from <= $this->closerPos) {
            return $this->closerPos;
        }
        if ($this->failFrom !== null && $from >= $this->failFrom && $from < $this->failUntil) {
            return null;
        }

        $pos = $from;
        while (preg_match($this->scan, $subject, $match, PREG_OFFSET_CAPTURE, $pos) === 1) {
            if (isset($match['bound']) && $match['bound'][1] !== -1) {
                $this->failFrom = $from;
                $this->failUntil = $match['bound'][1] + 1;
                return null;
            }
            if (isset($match['closer']) && $match['closer'][1] !== -1) {
                $this->successFrom = $from;
                $this->closerPos = $match['closer'][1];
                return $this->closerPos;
            }
            // an opaque span: resume behind it
            $pos = max($match[0][1] + strlen($match[0][0]), $pos + 1);
        }

        $this->failFrom = $from;
        $this->failUntil = strlen($subject) + 1;
        return null;
    }

    /**
     * Forgets all memoized scan verdicts, which only hold for the subject
     * they were computed on. The compiled scan regex is kept — it depends
     * only on the connected patterns, not on the subject.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->successFrom = null;
        $this->closerPos = null;
        $this->failFrom = null;
        $this->failUntil = null;
    }
}
