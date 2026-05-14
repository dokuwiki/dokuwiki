<?php

namespace dokuwiki\Parsing\Helpers;

/**
 * Pure helper for classifying link targets.
 *
 * Side-effect-free: returns data and leaves handler emission to the
 * caller. Shared by DokuWiki's Internallink mode and GfmLink.
 */
class Link
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
    public static function classify(string $url, $label): array
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
}
