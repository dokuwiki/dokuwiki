<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

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
