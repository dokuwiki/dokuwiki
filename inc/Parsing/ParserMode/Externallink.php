<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\HtmlEntity;
use dokuwiki\Parsing\ModeRegistry;

/**
 * Parser mode for external links (URLs).
 *
 * This mode is responsible for recognizing and handling external links in the text. It uses regular expressions
 * to identify URLs based on common schemes and patterns, and it can handle both standard URLs and Markdown-style
 * angle-bracket autolinks.
 */
class Externallink extends AbstractMode
{
    protected $schemes = [];
    protected $patterns = [];

    /** @inheritdoc */
    public function getSort()
    {
        return 330;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        if (count($this->patterns)) return;

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-\[\]';
        $punc = '.:?\-;,';
        $tail = '';

        // GFM autolink extension (Markdown-only):
        //   - Parentheses are allowed inside URLs; trailing unbalanced `)` are trimmed in handle().
        //   - A trailing entity-reference-like sequence (e.g. `&copy;`, `&hl;`) is consumed by the URL regex
        //     and then stripped in handle(); decodeOne() expands valid named/numeric refs to their Unicode
        //     character (`&copy;` -> `©`) while unknown names round-trip as literal text.
        if (ModeRegistry::getInstance()->isMdPreferred()) {
            $gunk .= '()';
            $tail = '(?:' . HtmlEntity::PATTERN . ')?';
        }

        $host = $ltrs . $punc;
        $any  = $ltrs . $gunk . $punc;

        $this->schemes = getSchemes();
        foreach ($this->schemes as $scheme) {
            $this->patterns[] = '\b(?i)' . $scheme . '(?-i)://[' . $any . ']+?' . $tail .
                '(?=[' . $punc . ']*[^' . $any . '])';
        }

        $this->patterns[] = '(?<![/\\\\])\b(?i)www?(?-i)\.[' . $host . ']+?\.' .
                            '[' . $host . ']+?[' . $any . ']+?' . $tail .
                            '(?=[' . $punc . ']*[^' . $any . '])';
        $this->patterns[] = '(?<![/\\\\])\b(?i)ftp?(?-i)\.[' . $host . ']+?\.' .
                            '[' . $host . ']+?[' . $any . ']+?' . $tail .
                            '(?=[' . $punc . ']*[^' . $any . '])';

        // Markdown-only: angle-bracket autolinks per CommonMark §6.5. One per-scheme pattern that captures the whole
        // envelope; handle() decides at match time whether to emit a link or literal cdata based on whether the content
        // contains whitespace (which disqualifies the autolink).
        // Angle brackets with white space are basically a simple way to write a URL without triggering autolinking
        if (ModeRegistry::getInstance()->isMdPreferred()) {
            foreach ($this->schemes as $scheme) {
                $this->patterns[] = '<[ \t]*(?i)' . $scheme . '(?-i)://[^<>\n]*>';
            }
        }
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {

        foreach ($this->patterns as $pattern) {
            $this->Lexer->addSpecialPattern($pattern, $mode, 'externallink');
        }
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        if (str_starts_with($match, '<') && str_ends_with($match, '>')) {
            $this->handleAngleAutolink($match, $pos, $handler);
        } else {
            $this->handleBareUrl($match, $pos, $handler);
        }
        return true;
    }

    /**
     * Emit a Markdown angle-bracket autolink (CommonMark §6.5).
     *
     * Whitespace inside the brackets disqualifies the autolink; in that case the literal envelope is
     * preserved as cdata so the brackets remain visible.
     */
    protected function handleAngleAutolink(string $match, int $pos, Handler $handler): void
    {
        if (preg_match('/\s/', $match)) {
            $handler->addCall('cdata', [$match], $pos);
            return;
        }
        $url = substr($match, 1, -1);
        $handler->addCall('externallink', [$url, $url], $pos);
    }

    /**
     * Emit a bare-URL autolink, optionally preceded by the GFM-extension trim step.
     *
     * In Markdown-preferred mode, peelGfmTail() removes characters the URL regex over-consumed
     * (trailing entity references, unbalanced closing parens) and returns them as a cdata suffix.
     */
    protected function handleBareUrl(string $match, int $pos, Handler $handler): void
    {
        $url = $match;
        $trailing = '';

        if (ModeRegistry::getInstance()->isMdPreferred()) {
            $trailing = $this->peelGfmTail($url);
        }

        $title = $this->addProtocolPrefix($url);

        $handler->addCall('externallink', [$url, $title], $pos);
        if ($trailing !== '') {
            $handler->addCall('cdata', [$trailing], $pos);
        }
    }

    /**
     * Peel GFM-extension trailing chars off a URL.
     *
     * The URL regex deliberately over-consumes parentheses and entity references so this method can decide
     * what really belongs to the URL. It peels one of two things at a time, repeating until neither applies:
     *
     *  - A trailing entity reference (e.g. &copy;): decoded via HtmlEntity::decodeOne so valid named or
     *    numeric refs become their Unicode character and unknown ones round-trip as literal text.
     *  - A trailing ) that has no matching ( earlier in the URL.
     *
     * Peels prepend to the trailing string so the final order matches the original source.
     *
     * @param string $url Mutated in place to the trimmed URL
     * @return string The peeled-off chars, in original source order, ready to emit as cdata after the link
     */
    protected function peelGfmTail(string &$url): string
    {
        $trailing = '';
        while (true) {
            if (preg_match('/' . HtmlEntity::PATTERN . '$/', $url, $m)) {
                $trailing = HtmlEntity::decodeOne($m[0]) . $trailing;
                $url = substr($url, 0, -strlen($m[0]));
            } elseif (str_ends_with($url, ')') && substr_count($url, ')') > substr_count($url, '(')) {
                $trailing = ')' . $trailing;
                $url = substr($url, 0, -1);
            } else {
                break;
            }
        }
        return $trailing;
    }

    /**
     * Add the implicit protocol on www./ftp. URLs and return the visible label.
     *
     * For scheme URLs (http://, ftp://, ...) the label is null, signalling the renderer to display the
     * href verbatim. For www./ftp. shortcuts the label is the original unprefixed form.
     *
     * @param string $url Mutated in place to include the protocol prefix when one was added
     * @return string|null The visible label, or null to use the prefixed URL as its own label
     */
    protected function addProtocolPrefix(string &$url): ?string
    {
        $title = null;
        if (str_starts_with($url, 'ftp') && !str_starts_with($url, 'ftp://')) {
            $title = $url;
            $url = 'ftp://' . $url;
        }
        if (str_starts_with($url, 'www')) {
            $title = $url;
            $url = 'http://' . $url;
        }
        return $title;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }
}
