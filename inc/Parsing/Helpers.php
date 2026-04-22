<?php

namespace dokuwiki\Parsing;

/**
 * Shared pure-function helpers for parser modes.
 *
 * Both methods here are deliberately side-effect-free: they return data and
 * leave handler emission to the caller. That keeps the mode classes thin,
 * makes the helpers trivially unit-testable without a Handler, and lets
 * symmetric pairs of DW / GFM modes share implementation without a mixin.
 */
class Helpers
{
    /**
     * Classify a link target and return the handler call that would emit it.
     *
     * Classification order: interwiki prefix, then Windows share, then
     * protocol scheme, then email, then local anchor, then internal page
     * as the default. The order is load-bearing — e.g. a URL with an
     * interwiki prefix that also matches an email pattern is still
     * dispatched as interwiki.
     *
     * @param string $url raw link target
     * @param string|array|null $label display label, or null; for
     *     Internallink this may be a parsed media array
     * @return array{0: string, 1: array} tuple of [handler call name, args]
     *     — caller invokes $handler->addCall($name, $args, $pos)
     */
    public static function classifyLink(string $url, $label): array
    {
        if (link_isinterwiki($url)) {
            $iw = sexplode('>', $url, 2, '');
            return ['interwikilink', [$url, $label, strtolower($iw[0]), $iw[1]]];
        }
        if (preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u', $url)) {
            return ['windowssharelink', [$url, $label]];
        }
        if (preg_match('#^([a-z0-9\-\.+]+?)://#i', $url)) {
            return ['externallink', [$url, $label]];
        }
        if (preg_match('<' . PREG_PATTERN_VALID_EMAIL . '>', $url)) {
            return ['emaillink', [$url, $label]];
        }
        if (preg_match('!^#.+!', $url)) {
            return ['locallink', [substr($url, 1), $label]];
        }
        return ['internallink', [$url, $label]];
    }

    /**
     * Split a media source into src and trailing parameter block.
     *
     * DokuWiki media syntax encodes width/height, cache, linking, and
     * alignment directives as URL-style parameters after the last `?`.
     * Using the last `?` means URLs that already carry a query string
     * survive untouched — e.g. https://example.com/img?v=2?100x200&right
     * has src https://example.com/img?v=2 and params 100x200&right.
     *
     * Shared by DW's Media mode ({{...}}) and GfmMedia (![alt](url)).
     * GfmMedia relies on the `left`/`right`/`center` keywords for
     * alignment because GFM has no equivalent of DW's whitespace-inside-
     * braces alignment trick.
     *
     * @param string $src Raw media source with optional ?params suffix
     * @return array{src: string, width: ?string, height: ?string, cache: string, linking: string, align: ?string}
     */
    public static function parseMediaParameters(string $src): array
    {
        $pos = strrpos($src, '?');
        if ($pos !== false) {
            $out = substr($src, 0, $pos);
            $param = substr($src, $pos + 1);
        } else {
            $out = $src;
            $param = '';
        }

        $w = $h = null;
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
