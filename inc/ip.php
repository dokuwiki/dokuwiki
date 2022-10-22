<?php
/**
 * DokuWiki IP address functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Zebra North <mrzebra@mrzebra.co.uk>
 */

namespace dokuwiki;

use Exception;

/**
 * Determine whether an IP address is within a given CIDR range.
 * The needle and haystack may be either IPv4 or IPv6.
 *
 * Example:
 *
 * ipInRange('192.168.11.123', '192.168.0.0/16') === true
 * ipInRange('192.168.11.123', '::192.168.0.0/80') === true
 * ipInRange('::192.168.11.123', '192.168.0.0/16') === true
 * ipInRange('::192.168.11.123', '::192.168.0.0/80') === true
 *
 * @param string $needle   The IP to test, either IPv4 in doted decimal
 *                         notation or IPv6 in colon notation.
 * @param string $haystack The CIDR range as an IP followed by a forward
 *                         slash and the number of significant bits.
 *
 * @return bool Returns true if $needle is within the range specified
 *              by $haystack, false if it is outside the range.
 *
 * @throws Exception Thrown if $needle is not a valid IP address.
 * @throws Exception Thrown if $haystack is not a valid IP range.
 */
function ipInRange(string $needle, string $haystack): bool
{
    $range = explode('/', $haystack);
    $networkIp = ipToNumber($range[0]);
    $maskLength = $range[1];

    // For an IPv4 address the top 96 bits must be zero.
    if ($networkIp['version'] === 4) {
        $maskLength += 96;
    }

    if ($maskLength > 128) {
        throw new Exception('Invalid IP range mask: ' . $haystack);
    }

    $maskLengthUpper = min($maskLength, 64);
    $maskLengthLower = max(0, $maskLength - 64);

    $maskUpper = ~0 << intval(64 - $maskLengthUpper);
    $maskLower = ~0 << intval(64 - $maskLengthLower);

    $needle = ipToNumber($needle);

    return ($needle['upper'] & $maskUpper) === ($networkIp['upper'] & $maskUpper) &&
        ($needle['lower'] & $maskLower) === ($networkIp['lower'] & $maskLower);
}

/**
 * Convert an IP address from a string to a number.
 *
 * This splits 128 bit IP addresses into the upper and lower 64 bits, and
 * also returns whether the IP given was IPv4 or IPv6.
 *
 * The returned array contains:
 *
 *  - version: Either '4' or '6'.
 *  - upper: The upper 64 bits of the IP.
 *  - lower: The lower 64 bits of the IP.
 *
 * For an IPv4 address, 'upper' will always be zero.
 *
 * @param string The IPv4 or IPv6 address.
 *
 * @return int[] Returns an array of 'version', 'upper', 'lower'.
 *
 * @throws Exception Thrown if the IP is not valid.
 */
function ipToNumber(string $ip): array
{
    $binary = inet_pton($ip);

    if ($binary === false) {
        throw new Exception('Invalid IP: ' . $ip);
    }

    if (strlen($binary) === 4) {
        // IPv4.
        return [
            'version' => 4,
            'upper'   => 0,
            'lower'   => unpack('Nip', $binary)['ip'],
        ];
    } else {
        // IPv6.
        $result = unpack('Jupper/Jlower', $binary);
        $result['version'] = 6;
        return $result;
    }
}

/**
 * Determine if an IP address is equal to another IP or within an IP range.
 * IPv4 and IPv6 are supported.
 *
 * @param string $ip        The address to test.
 * @param string $ipOrRange An IP address or CIDR range.
 *
 * @return bool Returns true if the IP matches, false if not.
 */
function ipMatches(string $ip, string $ipOrRange): bool
{
    try {
        // If it's not a range, compare the addresses directly.
        // Addresses are converted to numbers because the same address may be
        // represented by different strings, e.g. "::1" and "::0001".
        if (strpos($ipOrRange, '/') === false) {
            return ipToNumber($ip) === ipToNumber($ipOrRange);
        }

        return ipInRange($ip, $ipOrRange);
    } catch (Exception $ex) {
        // The IP address was invalid.
        return false;
    }
}

/**
 * Given the IP address of a proxy server, determine whether it is
 * a known and trusted server.
 *
 * This test is performed using the config value `trustedproxy`.
 *
 * @param string $ip The IP address of the proxy.
 *
 * @return bool Returns true if the IP is trusted as a proxy.
 */
function proxyIsTrusted(string $ip): bool
{
    global $conf;

    // If the configuration is empty then no proxies are trusted.
    if (empty($conf['trustedproxy'])) {
        return false;
    }

    if (is_string($conf['trustedproxy'])) {
        // If the configuration is a string then treat it as a regex.
        return preg_match('/' . $conf['trustedproxy'] . '/', $ip);
    } elseif (is_array($conf['trustedproxy'])) {
        // If the configuration is an array, then at least one must match.
        foreach ($conf['trustedproxy'] as $trusted) {
            if (ipMatches($ip, $trusted)) {
                return true;
            }
        }

        return false;
    }

    throw new Exception('Invalid value for $conf[trustedproxy]');
}

/**
 * Get the originating IP address and the address of every proxy that the
 * request has passed through, according to the X-Forwarded-For header.
 *
 * To prevent spoofing of the client IP, every proxy listed in the
 * X-Forwarded-For header must be trusted, as well as the TCP/IP endpoint
 * from which the connection was received (i.e. the final proxy).
 *
 * If the header is not present or contains an untrusted proxy then
 * an empty array is returned.
 *
 * The client IP is the first entry in the returned list, followed by the
 * proxies.
 *
 * @return string[] Returns an array of IP addresses.
 */
function forwardedFor(): array
{
    /* @var Input $INPUT */
    global $INPUT, $conf;

    $forwardedFor = $INPUT->server->str('HTTP_X_FORWARDED_FOR');

    if (empty($conf['trustedproxy']) || !$forwardedFor) {
        return [];
    }

    // This is the address from which the header was received.
    $remoteAddr = $INPUT->server->str('REMOTE_ADDR');

    // Get the client address from the X-Forwarded-For header.
    // X-Forwarded-For: <client> [, <proxy>]...
    $forwardedFor = explode(',', str_replace(' ', '', $forwardedFor));

    // The client address is the first item, remove it from the list.
    $clientAddress = array_shift($forwardedFor);

    // The remaining items are the proxies through which the X-Forwarded-For
    // header has passed.  The final proxy is the connection's remote address.
    $proxies = $forwardedFor;
    $proxies[] = $remoteAddr;

    // Ensure that every proxy is trusted.
    foreach ($proxies as $proxy) {
        if (!proxyIsTrusted($proxy)) {
            return [];
        }
    }

    // Add the client address before the list of proxies.
    return array_merge([$clientAddress], $proxies);
}
