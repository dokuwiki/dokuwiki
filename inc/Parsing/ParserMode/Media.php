<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

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
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\{\{(?:[^\}]|(?:\}[^\}]))+\}\}", $mode, 'media');
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

        //split into src and parameters (using the very last questionmark)
        $pos = strrpos($link[0], '?');
        if ($pos !== false) {
            $src = substr($link[0], 0, $pos);
            $param = substr($link[0], $pos + 1);
        } else {
            $src = $link[0];
            $param = '';
        }

        //parse width and height
        if (preg_match('#(\d+)(x(\d+))?#i', $param, $size)) {
            $w = empty($size[1]) ? null : $size[1];
            $h = empty($size[3]) ? null : $size[3];
        } else {
            $w = null;
            $h = null;
        }

        //get linking command
        if (preg_match('/nolink/i', $param)) {
            $linking = 'nolink';
        } elseif (preg_match('/direct/i', $param)) {
            $linking = 'direct';
        } elseif (preg_match('/linkonly/i', $param)) {
            $linking = 'linkonly';
        } else {
            $linking = 'details';
        }

        //get caching command
        if (preg_match('/(nocache|recache)/i', $param, $cachemode)) {
            $cache = $cachemode[1];
        } else {
            $cache = 'cache';
        }

        // Check whether this is a local or remote image or interwiki
        if (media_isexternal($src) || link_isinterwiki($src)) {
            $call = 'externalmedia';
        } else {
            $call = 'internalmedia';
        }

        return [
            'type' => $call,
            'src' => $src,
            'title' => $link[1],
            'align' => $align,
            'width' => $w,
            'height' => $h,
            'cache' => $cache,
            'linking' => $linking
        ];
    }
}
