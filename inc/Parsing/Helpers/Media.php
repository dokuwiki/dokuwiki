<?php

namespace dokuwiki\Parsing\Helpers;

/**
 * Pure helper for parsing DokuWiki media parameters.
 *
 * Side-effect-free: returns data and leaves handler emission to the
 * caller. Shared by DokuWiki's Media mode ({{...}}) and GfmMedia
 * (![alt](url)).
 */
class Media
{
    /**
     * Split a media source into src and trailing parameter block.
     *
     * DokuWiki media syntax encodes width/height, cache, linking, and
     * alignment directives as URL-style parameters after the last ?.
     * Using the last ? means URLs that already carry a query string
     * survive untouched — e.g. https://example.com/img?v=2?100x200&right
     * has src https://example.com/img?v=2 and params 100x200&right.
     *
     * GfmMedia relies on the left/right/center keywords for alignment
     * because GFM has no equivalent of DW's whitespace-inside-braces
     * alignment trick.
     *
     * @param string $src Raw media source with optional ?params suffix
     * @return array{src: string, width: ?string, height: ?string, cache: string, linking: string, align: ?string}
     */
    public static function parseParameters(string $src): array
    {
        $pos = strrpos($src, '?');
        if ($pos !== false) {
            $out = substr($src, 0, $pos);
            $param = substr($src, $pos + 1);
        } else {
            $out = $src;
            $param = '';
        }
        $w = null;
        $h = null;
        if (preg_match('#(\d+)(x(\d+))?#i', $param, $size)) {
            $w = empty($size[1]) ? null : $size[1];
            $h = empty($size[3]) ? null : $size[3];
        }

        if (preg_match('/nolink/i', $param)) {
            $linking = 'nolink';
        } elseif (preg_match('/direct/i', $param)) {
            $linking = 'direct';
        } elseif (preg_match('/linkonly/i', $param)) {
            $linking = 'linkonly';
        } else {
            $linking = 'details';
        }

        if (preg_match('/(nocache|recache)/i', $param, $cachemode)) {
            $cache = $cachemode[1];
        } else {
            $cache = 'cache';
        }

        if (preg_match('/(left|right|center)/i', $param, $alignmode)) {
            $align = strtolower($alignmode[1]);
        } else {
            $align = null;
        }

        return [
            'src' => $out,
            'width' => $w,
            'height' => $h,
            'cache' => $cache,
            'linking' => $linking,
            'align' => $align,
        ];
    }
}
