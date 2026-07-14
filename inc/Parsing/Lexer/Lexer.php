<?php

/**
 * Lexer adapted from Simple Test: http://sourceforge.net/projects/simpletest/
 * For an intro to the Lexer see:
 * https://web.archive.org/web/20120125041816/http://www.phppatterns.com/docs/develop/simple_test_lexer_notes
 *
 * @author Marcus Baker http://www.lastcraft.com
 */

namespace dokuwiki\Parsing\Lexer;

use dokuwiki\Parsing\Handler;

/**
 * Accepts text and breaks it into tokens.
 *
 * Some optimisation to make the sure the content is only scanned by the PHP regex
 * parser once. Lexer modes must not start with leading underscores.
 */
class Lexer
{
    /** @var string Signal for leaving a mode */
    public const MODE_EXIT = '__exit';
    /** @var string Prefix marking special (enter-and-exit) patterns */
    public const MODE_SPECIAL_PREFIX = '_';
    /**
     * Pattern matching a paragraph break: a blank line — two newlines
     * possibly separated by horizontal whitespace. The usual boundary
     * for addCloserPattern().
     *
     * @var string
     */
    public const PARA_BREAK = '\n[ \t]*\n';

    /** @var ParallelRegex[] */
    protected $regexes = [];
    /** @var Handler */
    protected $handler;
    /** @var StateStack */
    protected $modeStack;
    /** @var array mode "rewrites" */
    protected $mode_handlers = [];
    /** @var CloserPattern[] closer-existence checks, keyed by the mode they guard */
    protected $closerPatterns = [];
    /** @var bool case sensitive? */
    protected $case;

    /**
     * Sets up the lexer in case insensitive matching by default.
     *
     * @param Handler $handler  Handling strategy by reference.
     * @param string $start            Starting handler.
     * @param boolean $case            True for case sensitive.
     */
    public function __construct($handler, $start = "accept", $case = false)
    {
        $this->case = $case;
        $this->handler = $handler;
        $this->modeStack = new StateStack($start);
    }

    /**
     * Adds a token search pattern for a particular parsing mode.
     *
     * The pattern does not change the current mode.
     *
     * @param string $pattern      Perl style regex, but ( and )
     *                             lose the usual meaning.
     * @param string $mode         Should only apply this
     *                             pattern when dealing with
     *                             this type of input.
     */
    public function addPattern($pattern, $mode = "accept")
    {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern);
    }

    /**
     * Adds a pattern that will enter a new parsing mode.
     *
     * Useful for entering parenthesis, strings, tags, etc.
     *
     * @param string $pattern      Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode         Should only apply this pattern when dealing with this type of input.
     * @param string $new_mode     Change parsing to this new nested mode.
     */
    public function addEntryPattern($pattern, $mode, $new_mode)
    {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern, $new_mode);
    }

    /**
     * Requires a closer to exist ahead before any entry pattern may enter
     * the given mode.
     *
     * Whenever an entry pattern for $mode matches, the subject is scanned
     * from the end of that match for $pattern — before the next $boundary
     * match if a boundary is given, anywhere ahead otherwise. If no closer
     * is found the entry is rejected and its delimiter stays literal text;
     * see reduce() for how the rejected match is discarded.
     *
     * Keeping the check out of the entry pattern is what makes it
     * affordable. A closer lookahead written into the entry pattern is
     * re-evaluated for every candidate the regex engine tries — quadratic
     * on input dense with delimiters that never close. Here the scan runs
     * once per position and its verdict is memoized, so together with the
     * lexer consuming each entered span the whole parse stays linear; see
     * CloserPattern for the scan and its memo.
     *
     * The pattern must match where the closing delimiter starts, with
     * flanking requirements expressed as lookarounds (the convention exit
     * patterns follow, e.g. (?<=[^\s])\*\* for strong). A pattern that
     * consumed flanking context instead would skew the closer positions
     * canEnter() compares across modes, and could not see a closer whose
     * delimiter directly follows the opener, since the scan starts only
     * after the entry match.
     *
     * The closer must not be able to match where the boundary matches,
     * since the scan stops unconditionally at the boundary. It also does
     * not look inside content the lexer consumes atomically; see
     * opaqueSpans().
     *
     * A mode has exactly one closer check, shared by all its entry
     * patterns; registering another replaces it. The memo is reset at the
     * start of every parse() run.
     *
     * @param string $pattern regex fragment matching the closing delimiter,
     *                        flanking context expressed as lookarounds
     * @param string $mode    the mode entered by the guarded entry patterns
     * @param string|null $boundary regex fragment the closer must occur
     *                              before (usually self::PARA_BREAK); null
     *                              to scan to the end of the subject
     * @return void
     */
    public function addCloserPattern($pattern, $mode, $boundary = null)
    {
        $this->closerPatterns[$mode] = new CloserPattern($pattern, $boundary);
    }

    /**
     * Adds a pattern that will exit the current mode and re-enter the previous one.
     *
     * @param string $pattern      Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode         Mode to leave.
     */
    public function addExitPattern($pattern, $mode)
    {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern, self::MODE_EXIT);
    }

    /**
     * Adds a pattern that has a special mode.
     *
     * Acts as an entry and exit pattern in one go, effectively calling a special
     * parser handler for this token only.
     *
     * @param string $pattern      Perl style regex, but ( and ) lose the usual meaning.
     * @param string $mode         Should only apply this pattern when dealing with this type of input.
     * @param string $special      Use this mode for this one token.
     */
    public function addSpecialPattern($pattern, $mode, $special)
    {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern, self::MODE_SPECIAL_PREFIX . $special);
    }

    /**
     * Adds a mapping from a mode to another handler.
     *
     * @param string $mode        Mode to be remapped.
     * @param string $handler     New target handler.
     */
    public function mapHandler($mode, $handler)
    {
        $this->mode_handlers[$mode] = $handler;
    }

    /**
     * Splits the page text into tokens.
     *
     * Will fail if the handlers report an error or if no content is consumed. If successful then each
     * unparsed and parsed token invokes a call to the held listener.
     *
     * @param string $raw        Raw HTML text.
     * @return boolean           True on success, else false.
     */
    public function parse($raw)
    {
        if (! isset($this->handler)) {
            return false;
        }
        $this->resetCloserMemos();
        $offset = 0;
        while (is_array($parsed = $this->reduce($raw, $offset))) {
            [$unmatched, $matched, $mode] = $parsed;
            $matchPos = $offset + strlen($unmatched);
            if (! $this->dispatchTokens($unmatched, $matched, $mode, $offset, $matchPos)) {
                return false;
            }
            $newOffset = $matchPos + strlen($matched);
            if ($newOffset === $offset && $mode !== self::MODE_EXIT) {
                // No byte was consumed. For an ordinary match this means the
                // pattern set cannot advance and we must stop to avoid an
                // infinite loop. A zero-width EXIT (a lookahead-only exit
                // pattern such as Preformatted's (?=\n[^ \t\n])) is the
                // exception: it makes progress by popping the mode stack,
                // leaving the boundary byte for the parent mode to consume on
                // the next iteration. The stack strictly shrinks on each such
                // exit, so this cannot loop forever.
                return false;
            }
            $offset = $newOffset;
        }
        if (!$parsed) {
            return false;
        }
        return $this->invokeHandler(substr($raw, $offset), DOKU_LEXER_UNMATCHED, $offset);
    }

    /**
     * Gives plugins access to the mode stack
     *
     * @return StateStack
     */
    public function getModeStack()
    {
        return $this->modeStack;
    }

    /**
     * Sends the matched token and any leading unmatched
     * text to the parser changing the lexer to a new
     * mode if one is listed.
     *
     * @param string $unmatched Unmatched leading portion.
     * @param string $matched Actual token match.
     * @param bool|string $mode Mode after match. A boolean false mode causes no change.
     * @param int $initialPos
     * @param int $matchPos Current byte index location in raw doc thats being parsed
     * @return boolean             False if there was any error from the parser.
     */
    protected function dispatchTokens($unmatched, $matched, $mode, $initialPos, $matchPos)
    {
        if (! $this->invokeHandler($unmatched, DOKU_LEXER_UNMATCHED, $initialPos)) {
            return false;
        }
        if ($this->isModeEnd($mode)) {
            if (! $this->invokeHandler($matched, DOKU_LEXER_EXIT, $matchPos)) {
                return false;
            }
            return $this->modeStack->leave();
        }
        if ($this->isSpecialMode($mode)) {
            $this->modeStack->enter($this->decodeSpecial($mode));
            if (! $this->invokeHandler($matched, DOKU_LEXER_SPECIAL, $matchPos)) {
                return false;
            }
            return $this->modeStack->leave();
        }
        if (is_string($mode)) {
            $this->modeStack->enter($mode);
            return $this->invokeHandler($matched, DOKU_LEXER_ENTER, $matchPos);
        }
        return $this->invokeHandler($matched, DOKU_LEXER_MATCHED, $matchPos);
    }

    /**
     * Tests to see if the new mode is actually to leave the current mode and pop an item from the matching
     * mode stack.
     *
     * @param string $mode    Mode to test.
     * @return boolean        True if this is the exit mode.
     */
    protected function isModeEnd($mode)
    {
        return ($mode === self::MODE_EXIT);
    }

    /**
     * Test to see if the mode is one where this mode is entered for this token only and automatically
     * leaves immediately afterwoods.
     *
     * @param string $mode    Mode to test.
     * @return boolean        True if this is the exit mode.
     */
    protected function isSpecialMode($mode)
    {
        return str_starts_with($mode, self::MODE_SPECIAL_PREFIX);
    }

    /**
     * Strips the magic underscore marking single token modes.
     *
     * @param string $mode    Mode to decode.
     * @return string         Underlying mode name.
     */
    protected function decodeSpecial($mode)
    {
        return substr($mode, strlen(self::MODE_SPECIAL_PREFIX));
    }

    /**
     * Dispatches a token to the handler.
     *
     * Resolves mode name aliases (e.g. unformattedalt → unformatted) and
     * delegates all dispatch logic to Handler::handleToken().
     *
     * @param string $content Text parsed.
     * @param int $state One of the DOKU_LEXER_* constants identifying the
     *                   lexer event (ENTER / MATCHED / UNMATCHED / EXIT /
     *                   SPECIAL).
     * @param int $pos Current byte index location in raw doc
     *                             thats being parsed
     * @return bool
     */
    protected function invokeHandler($content, $state, $pos)
    {
        if ($content === false) {
            return true;
        }
        // Empty content is a no-op for every state EXCEPT EXIT: a zero-width
        // exit pattern (lookahead-only) must still fire the mode's exit
        // handler so cleanup like restoring a buffered call writer happens.
        // Skipping it would pop the mode stack but leave the handler-side
        // state stale.
        if ($content === '' && $state !== DOKU_LEXER_EXIT) {
            return true;
        }
        $originalName = $this->modeStack->getCurrent();
        $modeName = $this->mode_handlers[$originalName] ?? $originalName;

        return $this->handler->handleToken($modeName, $content, $state, $pos, $originalName);
    }

    /**
     * Tries to match the next token starting at `$offset` in `$raw`.
     *
     * The full subject is passed to the regex engine (rather than a
     * truncated tail) so that lookbehind assertions in the registered
     * patterns can see characters before the current offset. Empty
     * subjects (offset past end) will not be matched.
     *
     * A matched entry pattern for a guarded mode (one with a closer
     * pattern) is discarded when canEnter() rejects it: its delimiter
     * stays unparsed text and matching resumes one byte on, so a shorter
     * delimiter overlapping the rejected one still gets its turn.
     * Resuming past the delimiter also skips any rival pattern anchored
     * at that exact byte, which is safe: guarded modes are registered
     * only by the core, and two core delimiters sharing a byte share an
     * equivalent closer, so one rejection implies the other. Plugin
     * patterns are never guarded and so never take this path.
     *
     * @param string $raw     The full subject to parse.
     * @param int    $offset  Byte offset at which to resume matching.
     * @return array|bool     Three item list of unparsed content followed by the
     *                        recognised token and finally the action the parser is to take.
     *                        True if no match, false if there is a parsing error.
     */
    protected function reduce($raw, $offset)
    {
        if (! isset($this->regexes[$this->modeStack->getCurrent()])) {
            return false;
        }
        if ($offset >= strlen($raw)) {
            return true;
        }
        $initialOffset = $offset;
        while ($action = $this->regexes[$this->modeStack->getCurrent()]->split($raw, $split, $offset)) {
            [$unparsed, $match] = $split;
            $matchPos = $offset + strlen($unparsed);

            if (
                is_string($action)
                && isset($this->closerPatterns[$action])
                && !$this->canEnter($action, $raw, $matchPos + strlen($match))
            ) {
                $offset = $matchPos + 1;
                if ($offset >= strlen($raw)) {
                    return true;
                }
                continue;
            }

            return [substr($raw, $initialOffset, $matchPos - $initialOffset), $match, $action];
        }
        return true;
    }

    /**
     * May an entry pattern for the given mode enter at this position?
     *
     * Two conditions must hold. First, a valid closer for the mode must
     * exist ahead, before its boundary — otherwise a delimiter that can
     * never close would stay open forever. Second, when the entry sits
     * inside a guarded mode, that mode's closer must not come first: an
     * inner delimiter whose closer lies beyond the enclosing closer can
     * never close within its parent, so it stays literal rather than span
     * across the parent boundary (e.g. a stray '*' in ''glob/*.conf''
     * pairing with the '*' of a following ''…'' span).
     *
     * The enclosing mode is the nearest guarded ancestor on the stack, not
     * necessarily the immediate parent; see nearestGuardedAncestor().
     *
     * @param string $mode the mode entered by the matched entry pattern
     * @param string $subject the full subject being lexed
     * @param int $from byte position just after the entry pattern match
     * @return bool
     */
    protected function canEnter(string $mode, string $subject, int $from): bool
    {
        $closerPos = $this->closerPosition($mode, $subject, $from);
        if ($closerPos === null) {
            return false;
        }

        $enclosing = $this->nearestGuardedAncestor($mode);
        if ($enclosing !== null) {
            $enclosingCloserPos = $this->closerPosition($enclosing, $subject, $from);
            if ($enclosingCloserPos !== null && $enclosingCloserPos < $closerPos) {
                return false;
            }
        }

        return true;
    }

    /**
     * The nearest mode on the stack that has its own closer and could thus
     * constrain where a delimiter entering $mode may close, or null if none
     * does.
     *
     * The search walks from the immediate parent outward, stepping over
     * unguarded modes — plugins, list items, anything with no closer. Such
     * a mode offers no closer to compare against, yet a guarded ancestor
     * beyond it still constrains the inner delimiter (e.g. ''strong'' around
     * a plugin span holding an ''emphasis'' whose only closer lies further
     * on), so skipping it reaches that ancestor.
     *
     * Only the nearest guarded ancestor matters: when it opened it was
     * validated against its own nearest guarded ancestor, so the
     * "closes before its parent" relation chains up the stack and one level
     * is enough. The walk also stops at $mode itself — a same-mode ancestor
     * shares this candidate's closer pattern, so its closer cannot fall
     * before the candidate's and can never be the rejecting constraint.
     *
     * @param string $mode the mode about to be entered
     * @return string|null the nearest guarded ancestor, or null if none
     */
    protected function nearestGuardedAncestor(string $mode): ?string
    {
        $stack = $this->modeStack->getStack();
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $enclosing = $stack[$i];
            if ($enclosing === $mode) {
                return null;
            }
            if (isset($this->closerPatterns[$enclosing])) {
                return $enclosing;
            }
        }
        return null;
    }

    /**
     * Byte position where the first valid closer for the given mode at or
     * after $from starts, or null if none exists; delegates to the mode's
     * CloserPattern (see position()).
     *
     * The scan regex is compiled on first use, not in addCloserPattern(),
     * because the opaque-span derivation needs the patterns of all connected
     * modes, and other modes' connectTo() calls may run after this mode's
     * postConnect() has registered the closer.
     *
     * @param string $mode the mode whose closer pattern applies
     * @param string $subject the full subject being lexed
     * @param int $from byte position just after the entry pattern match
     * @return int|null
     */
    protected function closerPosition(string $mode, string $subject, int $from): ?int
    {
        $closerPattern = $this->closerPatterns[$mode];
        if (!$closerPattern->isCompiled()) {
            $closerPattern->compile($this->opaqueSpans($mode), $this->case);
        }
        return $closerPattern->position($subject, $from);
    }

    /**
     * Derives the spans within the given mode whose content a closer scan
     * must not look into.
     *
     * Content the lexer consumes without exposing it to the mode's exit
     * pattern can never hold a real closer — a %%..%% span may contain the
     * characters that would close an enclosing bold span, yet the bold exit
     * cannot fire inside it. Such content is identified from the already
     * registered patterns:
     *
     * - A plain or special pattern is consumed in one step, so the pattern
     *   itself describes the span. (Zero-width matches would stall parse(),
     *   so each consumes at least one byte.)
     * - An entry pattern leads into a nested mode. If that mode is verbatim
     *   (see verbatimExit()), the lexer consumes up to its first exit, so
     *   the span is the entry pattern, a lazy body, and the exit. Other
     *   nested modes have no statically known extent — their content is
     *   scanned as plain text, leaving the closer check an approximation
     *   there.
     *
     * @param string $mode the mode whose closer scan needs the spans
     * @return string[] regex fragments, each matching one whole span
     */
    protected function opaqueSpans(string $mode): array
    {
        if (!isset($this->regexes[$mode])) {
            return [];
        }

        $spans = [];
        foreach ($this->regexes[$mode]->getPatterns() as $registered) {
            $label = $registered['label'];
            if ($label === self::MODE_EXIT) {
                continue;
            }
            if ($label === true || $this->isSpecialMode($label)) {
                $spans[] = '(?:' . ParallelRegex::escapePattern($registered['pattern']) . ')';
                continue;
            }
            $exit = $this->verbatimExit($label);
            if ($exit !== null) {
                $spans[] = '(?:' . ParallelRegex::escapePattern($registered['pattern']) . '.*?' . $exit . ')';
            }
        }
        return $spans;
    }

    /**
     * The exit pattern of the given mode if the mode is verbatim, null
     * otherwise.
     *
     * A mode is verbatim when its pattern set consists solely of exit
     * patterns (e.g. nowiki): nothing can match inside it, so it consumes
     * everything up to its first exit match. Several exits are combined
     * into an alternation.
     *
     * @param string $mode the mode entered by an entry pattern
     * @return string|null regex fragment matching the mode's exit
     */
    protected function verbatimExit(string $mode): ?string
    {
        if (!isset($this->regexes[$mode])) {
            return null;
        }

        $exits = [];
        foreach ($this->regexes[$mode]->getPatterns() as $registered) {
            if ($registered['label'] !== self::MODE_EXIT) {
                return null;
            }
            $exits[] = ParallelRegex::escapePattern($registered['pattern']);
        }
        if ($exits === []) {
            return null;
        }
        return '(?:' . implode('|', $exits) . ')';
    }

    /**
     * Forgets all closer scan verdicts. Called at the start of every
     * parse() run, since the memos only hold for the subject they were
     * computed on.
     *
     * @return void
     */
    protected function resetCloserMemos(): void
    {
        foreach ($this->closerPatterns as $closerPattern) {
            $closerPattern->reset();
        }
    }

    /**
     * Escapes regex characters other than (, ) and /
     *
     * @param string $str
     * @return string
     */
    public static function escape($str)
    {
        $chars = [
            '/\\\\/',
            '/\./',
            '/\+/',
            '/\*/',
            '/\?/',
            '/\[/',
            '/\^/',
            '/\]/',
            '/\$/',
            '/\{/',
            '/\}/',
            '/\=/',
            '/\!/',
            '/\</',
            '/\>/',
            '/\|/',
            '/\:/'
        ];

        $escaped = [
            '\\\\\\\\',
            '\.',
            '\+',
            '\*',
            '\?',
            '\[',
            '\^',
            '\]',
            '\$',
            '\{',
            '\}',
            '\=',
            '\!',
            '\<',
            '\>',
            '\|',
            '\:'
        ];

        return preg_replace($chars, $escaped, $str);
    }
}
