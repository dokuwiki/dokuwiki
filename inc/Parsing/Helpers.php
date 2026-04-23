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
     * Parse the attribute block of a code / file tag or fence opener.
     *
     * Accepts the text between `<code` and `>` (DokuWiki) or the info
     * string after a fence opener (GFM). The grammar is the same in both
     * places: an optional `[key=value,...]` bracket block appears
     * anywhere in the string and contains highlight options; what
     * remains, whitespace-split, is language then filename.
     *
     * Conventions carried over from DokuWiki's Code mode:
     *   - `-` as the language means "no language" (returned as null);
     *   - `html` is aliased to GeSHi's `html4strict` identifier.
     *
     * @param string $attr raw attribute text (no `<code`/`>` or fence chars)
     * @return array{0: ?string, 1: ?string, 2: ?array} [language, filename, options]
     */
    public static function parseCodeAttributes(string $attr): array
    {
        $options = null;
        if (preg_match('/\[.*\]/', $attr, $optMatch)) {
            $attr = str_replace($optMatch[0], '', $attr);
            $options = self::parseHighlightOptions($optMatch[0]);
        }

        $parts = preg_split('/\s+/', trim($attr), 2, PREG_SPLIT_NO_EMPTY);
        $language = $parts[0] ?? null;
        $filename = $parts[1] ?? null;

        if ($language === 'html') $language = 'html4strict';
        if ($language === '-') $language = null;

        return [$language, $filename, $options];
    }

    /**
     * Parse a `[key=value,...]` block of highlight options.
     *
     * Keys without a value are treated as booleans (1). Values may be
     * bare or `"quoted"`; quoted values may contain commas. Only a
     * fixed whitelist of keys is retained (see below); unknown keys are
     * silently dropped.
     *
     * @param string $options the `[...]` string including the brackets
     * @return array|null key/value map, or null if nothing recognised
     */
    public static function parseHighlightOptions(string $options): ?array
    {
        $result = [];
        preg_match_all('/(\w+(?:="[^"]*"))|(\w+(?:=[^\s]*))|(\w+[^=\s\]])(?:\s*)/', $options, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $equal_sign = strpos($match[0], '=');
            if ($equal_sign === false) {
                $key = trim($match[0]);
                $result[$key] = 1;
            } else {
                $key = substr($match[0], 0, $equal_sign);
                $value = substr($match[0], $equal_sign + 1);
                $value = trim($value, '"');
                if ($value !== '') {
                    $result[$key] = $value;
                } else {
                    $result[$key] = 1;
                }
            }
        }

        $result = array_intersect_key(
            $result,
            array_flip([
                'enable_line_numbers',
                'start_line_numbers_at',
                'highlight_lines_extra',
                'enable_keyword_links'
            ])
        );

        if (isset($result['enable_line_numbers'])) {
            if ($result['enable_line_numbers'] === 'false') {
                $result['enable_line_numbers'] = false;
            }
            $result['enable_line_numbers'] = (bool)$result['enable_line_numbers'];
        }
        if (isset($result['highlight_lines_extra'])) {
            $result['highlight_lines_extra'] = array_map(intval(...), explode(',', $result['highlight_lines_extra']));
            $result['highlight_lines_extra'] = array_filter($result['highlight_lines_extra']);
            $result['highlight_lines_extra'] = array_unique($result['highlight_lines_extra']);
        }
        if (isset($result['start_line_numbers_at'])) {
            $result['start_line_numbers_at'] = (int)$result['start_line_numbers_at'];
        }
        if (isset($result['enable_keyword_links'])) {
            if ($result['enable_keyword_links'] === 'false') {
                $result['enable_keyword_links'] = false;
            }
            $result['enable_keyword_links'] = (bool)$result['enable_keyword_links'];
        }
        if (count($result) == 0) {
            return null;
        }

        return $result;
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
