<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\Media as MediaHelper;

class Media extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 320;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Body is possessive: it can only match up to the first `}}` (neither
        // alternative accepts `}}`), so it never needs to backtrack to let the
        // closing `}}` match. Without the possessive quantifier an unclosed
        // `{{` followed by a large body drives the non-JIT PCRE engine to
        // retain one backtracking frame per byte — an unbounded memory spike.
        $this->Lexer->addSpecialPattern("\{\{(?:[^\}]|(?:\}[^\}]))++\}\}", $mode, 'media');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $p = self::parseMedia($match);

        $handler->addCall(
            $p['type'],
            [$p['src'], $p['title'], $p['align'], $p['width'], $p['height'], $p['cache'], $p['linking']],
            $pos
        );
        return true;
    }

    /**
     * Parse media syntax into its components
     *
     * @param string $match The full media syntax (e.g. {{image.png?200|title}})
     * @return array Parsed media parameters (type, src, title, align, width, height, cache, linking)
     */
    public static function parseMedia($match)
    {
        // Strip the opening and closing markup
        $link = preg_replace(['/^\{\{/', '/\}\}$/u'], '', $match);

        // Split title from URL
        $link = sexplode('|', $link, 2);

        // Check alignment
        $ralign = (bool)preg_match('/^ /', $link[0]);
        $lalign = (bool)preg_match('/ $/', $link[0]);

        // Logic = what's that ;)...
        if ($lalign & $ralign) {
            $align = 'center';
        } elseif ($ralign) {
            $align = 'right';
        } elseif ($lalign) {
            $align = 'left';
        } else {
            $align = null;
        }

        // The title...
        if (!isset($link[1])) {
            $link[1] = null;
        }

        //remove aligning spaces
        $link[0] = trim($link[0]);

        $p = MediaHelper::parseParameters($link[0]);

        // Explicit param-derived alignment (?left/?right/?center) beats
        // the whitespace-derived one — it's unambiguous and visible, and
        // is the only form GfmMedia can express.
        if ($p['align'] !== null) {
            $align = $p['align'];
        }

        if (media_isexternal($p['src']) || link_isinterwiki($p['src'])) {
            $call = 'externalmedia';
        } else {
            $call = 'internalmedia';
        }

        return [
            'type' => $call,
            'src' => $p['src'],
            'title' => $link[1],
            'align' => $align,
            'width' => $p['width'],
            'height' => $p['height'],
            'cache' => $p['cache'],
            'linking' => $p['linking']
        ];
    }
}
