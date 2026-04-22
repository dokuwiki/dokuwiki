<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * Shared URL-classification ladder for link ParserModes.
 *
 * DokuWiki has six distinct link handler instructions (internallink,
 * externallink, interwikilink, emaillink, windowssharelink, locallink)
 * but only one classifier that decides which one to emit for a given
 * raw target string. Both Internallink (for `[[...]]`) and GfmLink
 * (for `[text](url)`) need that decision, so it lives here.
 */
trait LinkDispatch
{
    /**
     * Classify $url and emit the matching handler call.
     *
     * Classification order: interwiki prefix, then Windows share, then
     * protocol scheme, then email, then local anchor, then internal
     * page as the default. The order is load-bearing — e.g. a URL with
     * an interwiki prefix that also matches an email pattern is still
     * dispatched as interwiki.
     *
     * @param string $url raw link target
     * @param string|array|null $label display label, or null; for
     *     Internallink this may be a parsed media array
     * @param int $pos byte offset of the match, forwarded to addCall
     * @param Handler $handler handler that receives the emitted call
     */
    protected function dispatchLink(string $url, $label, int $pos, Handler $handler): void
    {
        if (link_isinterwiki($url)) {
            $interwiki = sexplode('>', $url, 2, '');
            $handler->addCall(
                'interwikilink',
                [$url, $label, strtolower($interwiki[0]), $interwiki[1]],
                $pos
            );
        } elseif (preg_match('/^\\\\\\\\[^\\\\]+?\\\\/u', $url)) {
            $handler->addCall('windowssharelink', [$url, $label], $pos);
        } elseif (preg_match('#^([a-z0-9\-\.+]+?)://#i', $url)) {
            $handler->addCall('externallink', [$url, $label], $pos);
        } elseif (preg_match('<' . PREG_PATTERN_VALID_EMAIL . '>', $url)) {
            $handler->addCall('emaillink', [$url, $label], $pos);
        } elseif (preg_match('!^#.+!', $url)) {
            $handler->addCall('locallink', [substr($url, 1), $label], $pos);
        } else {
            $handler->addCall('internallink', [$url, $label], $pos);
        }
    }
}
