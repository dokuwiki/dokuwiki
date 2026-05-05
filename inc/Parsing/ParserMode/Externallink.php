<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
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
        $host = $ltrs . $punc;
        $any  = $ltrs . $gunk . $punc;

        $this->schemes = getSchemes();
        foreach ($this->schemes as $scheme) {
            $this->patterns[] = '\b(?i)' . $scheme . '(?-i)://[' . $any . ']+?(?=[' . $punc . ']*[^' . $any . '])';
        }

        $this->patterns[] = '(?<![/\\\\])\b(?i)www?(?-i)\.[' . $host . ']+?\.' .
                            '[' . $host . ']+?[' . $any . ']+?(?=[' . $punc . ']*[^' . $any . '])';
        $this->patterns[] = '(?<![/\\\\])\b(?i)ftp?(?-i)\.[' . $host . ']+?\.' .
                            '[' . $host . ']+?[' . $any . ']+?(?=[' . $punc . ']*[^' . $any . '])';

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
        // Angle-bracket autolink (Markdown §6.5).
        if (str_starts_with($match, '<') && str_ends_with($match, '>')) {
            if (preg_match('/\s/', $match)) {
                // Disqualified by internal whitespace — render literally
                $handler->addCall('cdata', [$match], $pos);
                return true;
            }
            $url = substr($match, 1, -1);
            // Pass URL as both href and visible label so the rendered text shows the URL exactly as written
            $handler->addCall('externallink', [$url, $url], $pos);
            return true;
        }

        $url = $match;
        $title = null;

        // add protocol on simple short URLs
        if (str_starts_with($url, 'ftp') && !str_starts_with($url, 'ftp://')) {
            $title = $url;
            $url = 'ftp://' . $url;
        }
        if (str_starts_with($url, 'www')) {
            $title = $url;
            $url = 'http://' . $url;
        }

        $handler->addCall('externallink', [$url, $title], $pos);
        return true;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }
}
