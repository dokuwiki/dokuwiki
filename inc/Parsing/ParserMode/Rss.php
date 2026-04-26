<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Rss extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 310;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern("\{\{rss>[^\}]+\}\}", $mode, 'rss');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $link = preg_replace(['/^\{\{rss>/', '/\}\}$/'], '', $match);

        // get params
        [$link, $params] = sexplode(' ', $link, 2, '');

        $p = [];
        if (preg_match('/\b(\d+)\b/', $params, $m)) {
            $p['max'] = $m[1];
        } else {
            $p['max'] = 8;
        }
        $p['reverse'] = (preg_match('/rev/', $params));
        $p['author'] = (preg_match('/\b(by|author)/', $params));
        $p['date'] = (preg_match('/\b(date)/', $params));
        $p['details'] = (preg_match('/\b(desc|detail)/', $params));
        $p['nosort'] = (preg_match('/\b(nosort)\b/', $params));

        if (preg_match('/\b(\d+)([dhm])\b/', $params, $m)) {
            $period = ['d' => 86400, 'h' => 3600, 'm' => 60];
            $p['refresh'] = max(600, $m[1] * $period[$m[2]]);  // n * period in seconds, minimum 10 minutes
        } else {
            $p['refresh'] = 14400;   // default to 4 hours
        }

        $handler->addCall('rss', [$link, $p], $pos);
        return true;
    }
}
