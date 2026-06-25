<?php

namespace dokuwiki\Parsing\Helpers;

/**
 * Pure helpers for parsing code / file attribute blocks.
 *
 * Side-effect-free: returns data and leaves handler emission to the
 * caller. Shared by DokuWiki's Code / File modes and GfmCode / GfmFile.
 */
class Code
{
    /**
     * Parse the attribute block of a code / file tag or fence opener.
     *
     * Accepts the text between <code and > (DokuWiki) or the info
     * string after a fence opener (GFM). The grammar is the same in both
     * places: an optional [key=value,...] bracket block appears
     * anywhere in the string and contains highlight options; what
     * remains, whitespace-split, is language then filename.
     *
     * Conventions carried over from DokuWiki's Code mode:
     *   - "-" as the language means "no language" (returned as null);
     *   - "html" is aliased to GeSHi's "html4strict" identifier.
     *
     * @param string $attr raw attribute text (no <code/> or fence chars)
     * @return array{0: ?string, 1: ?string, 2: ?array} [language, filename, options]
     */
    public static function parseAttributes(string $attr): array
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
     * Parse a [key=value,...] block of highlight options.
     *
     * Keys without a value are treated as booleans (1). Values may be
     * bare or "quoted"; quoted values may contain commas. Only a
     * fixed whitelist of keys is retained (see below); unknown keys are
     * silently dropped.
     *
     * @param string $options the [...] string including the brackets
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
}
