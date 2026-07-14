<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Code as CodeHelper;
use dokuwiki\Parsing\Helpers\Escape;
use dokuwiki\Parsing\Helpers\HtmlEntity;

/**
 * GFM fenced code block with backtick fences: ```...```
 *
 * Emits the same `code` handler instruction DokuWiki's `<code>` mode
 * emits, so renderers, indexing, and syntax highlighting reuse the
 * existing pipeline.
 *
 * The info string after the opening fence accepts DokuWiki's full
 * code-tag attribute vocabulary — language, optional filename, and
 * optional [key=value,...] highlight options — parsed via
 * Helpers\Code::parseAttributes. Markdown authors pasting to GitHub
 * will see the extras render as part of the language class; the
 * divergence is intentional, for feature parity with DokuWiki's
 * <code>...</code> blocks.
 *
 * Column-0 fences only (no indent tolerance, no body dedent). The close
 * fence is any run of 3+ fence chars at column 0 with only trailing
 * whitespace on the line — the opener's length is not paired with the
 * closer's, because ParallelRegex does not support backreferences.
 *
 * Unclosed fences stay literal text. GFM's spec says an unclosed fence
 * runs to end of input (and any enclosing container's end), but that
 * rule is part of CommonMark's two-pass block-then-inline parser where
 * "any container boundary closes" is the uniform termination rule. Our
 * single-pass regex lexer has no notion of container boundaries, so the
 * best we could do is "close at EOF" — a partial implementation that
 * already leaks (spec example 98, fence inside a blockquote, stays red
 * because we can't close at the blockquote boundary). Doing a degraded
 * version of the rule just moves the broken edge case somewhere less
 * obvious.
 *
 * Requiring a closer is also consistent with every other inline GFM
 * mode in this codebase (all of which use entry-pattern lookaheads to
 * verify a matching closer exists) and with DokuWiki's own <code> tag
 * parsing (<code\b(?=.*</code>)>). And it has a safer failure mode: a
 * stray ``` at the top of a document stays as literal text rather than
 * swallowing everything below it into a code block. Spec examples 96
 * and 97 are in skip.php with this rationale.
 *
 * @see GfmFile
 */
class GfmCode extends AbstractMode
{
    /** @var string The call type used in addCall ('code' or 'file') */
    protected $type = 'code';

    /** @var string The fence character (`` ` `` or `~`). */
    protected $fenceChar = '`';

    /**
     * Info-string character class. Backtick fences forbid backticks in
     * the info string (spec example 115); tilde fences allow anything
     * except newline (spec example 116).
     */
    protected $infoClass = '[^\n`]*';

    /** @inheritdoc */
    public function getSort()
    {
        return 200;
    }

    /** The lexer state / mode name. Subclasses override for tildes. */
    protected function getModeName(): string
    {
        return 'gfm_code';
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Entry pattern breakdown (F = fence char, INFO = info-string class):
        //   \n                      — line start (Parser prepends a newline)
        //   F{3,}                   — opener: 3+ fence chars at column 0
        //   INFO                    — info-string (language etc.)
        //   (?=\n)                  — opener line must end at a newline;
        //                             without this anchor `` ``` aa ``` ``
        //                             on one line would parse as a fence
        //   (?:(?!CLOSE).)*+        — body: any char (DOTALL) that isn't
        //                             the start of a close-fence line. The
        //                             tempered dot stops at exactly the first
        //                             CLOSE (the lookahead fails there), so
        //                             no backtracking into the body is ever
        //                             needed once it has stopped. The
        //                             possessive quantifier makes that
        //                             explicit: on an unclosed fence the body
        //                             consumes to EOF, CLOSE fails, and the
        //                             match fails at once instead of
        //                             backtracking byte by byte — which the
        //                             non-JIT PCRE engine does by retaining
        //                             one frame per consumed byte, an
        //                             unbounded memory spike on large fences.
        //   CLOSE = \nF{3,}[ \t]*(?=\n)  — close fence, required.
        //                             No `\z` fallback: unclosed fences stay
        //                             literal (see class docblock)
        $close = '\n' . $this->fenceChar . '{3,}[ \t]*(?=\n)';
        $this->Lexer->addSpecialPattern(
            '\n' . $this->fenceChar . '{3,}' . $this->infoClass . '(?=\n)'
                . '(?:(?!' . $close . ').)*+' . $close,
            $mode,
            $this->getModeName()
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $c = $this->fenceChar;

        // Shed the pattern's leading \n, the opener fence run, and the
        // close-fence run with its trailing whitespace.
        $text = rtrim(ltrim(substr($match, 1), $c), " \t" . $c);

        // The opener ended at a newline (required by the pattern's `(?=\n)`
        // anchor), so an explode split always has two parts.
        [$info, $body] = explode("\n", $text, 2);

        [$language, $filename, $options] = CodeHelper::parseAttributes(
            Escape::unescapeBackslashes(HtmlEntity::decode($info))
        );

        $param = [$body, $language, $filename];
        if ($options !== null) $param[] = $options;
        $handler->addCall($this->type, $param, $pos);
        return true;
    }
}
