<?php

/**
 * DokuWiki IP address functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Zebra North <mrzebra@mrzebra.co.uk>
 */

namespace dokuwiki;

use dokuwiki\Input\Input;
use Exception;

class Ip
{
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
     * @param string $needle The IP to test, either IPv4 in dotted decimal
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
    public static function ipInRange(string $needle, string $haystack): bool
    {
        $range = explode('/', $haystack);
        $networkIp = Ip::ipToNumber($range[0]);
        $maskLength = $range[1];

        // For an IPv4 address the top 96 bits must be zero.
        if ($networkIp['version'] === 4) {
            $maskLength += 96;
        }

        if ($maskLength > 128) {
            throw new Exception('Invalid IP range mask: ' . $haystack);
        }


        $needle = Ip::ipToNumber($needle);

        $maskLengthUpper = min($maskLength, 64);
        $maskLengthLower = max(0, $maskLength - 64);

        if (PHP_INT_SIZE == 4) {
            $needle_up = Ip::bitmask64_32($needle['upper'],    $maskLengthUpper);
            $net_up    = Ip::bitmask64_32($networkIp['upper'], $maskLengthUpper);
            $needle_lo = Ip::bitmask64_32($needle['lower'],    $maskLengthLower);
            $net_lo    = Ip::bitmask64_32($networkIp['lower'], $maskLengthLower);
        } else {
            $maskUpper = ~0 << intval(64 - $maskLengthUpper);
            $maskLower = ~0 << intval(64 - $maskLengthLower);

            $needle_up = $needle['upper'] & $maskUpper;
            $net_up    = $networkIp['upper'] & $maskUpper;
            $needle_lo = $needle['lower'] & $maskLower;
            $net_lo    = $networkIp['lower'] & $maskLower;
        }

        return $needle_up === $net_up && $needle_lo === $net_lo;
    }

    /**
     * modeling bitshift like  ~0 << $pow for 32-bit arch
     * @param pow power of 2 for mask
     * @return 64-char string of 1 and 0s
     * pow=1
     * 1111111111111111111111111111111111111111111111111111111111111110
     * pow=63
     * 1000000000000000000000000000000000000000000000000000000000000000
     * pow=64
     * 0000000000000000000000000000000000000000000000000000000000000000
     */
    private static function make_bitmask_32(int $pow) : string {
        $pow = $pow < 0 ? 64 - $pow : $pow;
        $mask = sprintf("%064d",0);
        for ($i=0; $i<64; $i++) {
            if ($i >= $pow) {
                $mask[63 - $i] = '1';
            }
        }
        return $mask;
    }
    /**
     * slow and ugly bitwise_and for 32bit arch
     * @param $u64 unsigned 64bit integer as string
     *            likely from ipv6_upper_lower_32
     * @param $pow 0-64 power of 2 for bitmask
     */
    private static function bitmask64_32(string $u64, int $pow) : string {
        //$u64 = sprintf("%.0f", $u65);
        $b32 = '4294967296';
        $bin = sprintf("%032b%032b",
                bcdiv($u64, $b32, 0),
                bcmod($u64, $b32));

        $mask = Ip::make_bitmask_32(64-$pow);

        // most right is lowest bit
        $res='0';
        for ($i=0; $i<64; $i++){
            if (bcmul($bin[$i], $mask[$i]) == 1) {
                $res = bcadd($res, bcpow(2, 63-$i));
            }
        }
        return $res;
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
     * @param string $ip The IPv4 or IPv6 address.
     *
     * @return int[] Returns an array of 'version', 'upper', 'lower'.
     *
     * @throws Exception Thrown if the IP is not valid.
     */
    public static function ipToNumber(string $ip): array
    {
        $binary = inet_pton($ip);

        if ($binary === false) {
            throw new Exception('Invalid IP: ' . $ip);
        }

        if (strlen($binary) === 4) {
            // IPv4.
            return [
                'version' => 4,
                'upper' => 0,
                'lower' => unpack('Nip', $binary)['ip'],
            ];
        } else {
            // IPv6. strlen==16
            if(PHP_INT_SIZE == 8) { // 64-bit arch
               $result = unpack('Jupper/Jlower', $binary);
            } else { // 32-bit
               $result = Ip::ipv6_upper_lower_32($binary);
            }
            $result['version'] = 6;
            return $result;
        }
    }

    /**
     * conversion of inet_pton ipv6 into 64-bit upper and lower
     * bcmath version for 32-bit architecture
     * w/o no unpack('J') - unsigned long long (always 64 bit, big endian byte order)
     *
     * results match unpack('Jupper/Jlower', $binary)
     *
     * @param string $binary inet_pton's ipv6 16 element binary
     *
     * @return int[] upper 64 and lower 64 for ipToNumber
     */
    public static function ipv6_upper_lower_32(string $binary) {
       // unpack into four 32-bit unsigned ints to recombine as 2 64-bit
       $b32 = 4294967296; // bcpow(2, 32)
       $parts = unpack('N4', $binary);
       $upper = bcadd(bcmul($parts[1], $b32),
                      $parts[2]);
       $lower = bcadd(bcmul($parts[3], $b32),
                      $parts[4]);
       // ISSUE:
       // unpack('J2') on 64bit is stored as 2 signed int (even if J is unsigned)
       // here upper and lower have to be strings. numbers wont fit in 32-bit
       return ['upper' => $upper, 'lower' => $lower];
    }

    /**
     * Determine if an IP address is equal to another IP or within an IP range.
     * IPv4 and IPv6 are supported.
     *
     * @param string $ip The address to test.
     * @param string $ipOrRange An IP address or CIDR range.
     *
     * @return bool Returns true if the IP matches, false if not.
     */
    public static function ipMatches(string $ip, string $ipOrRange): bool
    {
        try {
            // If it's not a range, compare the addresses directly.
            // Addresses are converted to numbers because the same address may be
            // represented by different strings, e.g. "::1" and "::0001".
            if (strpos($ipOrRange, '/') === false) {
                return Ip::ipToNumber($ip) === Ip::ipToNumber($ipOrRange);
            }

            return Ip::ipInRange($ip, $ipOrRange);
        } catch (Exception $ex) {
            // The IP address was invalid.
            return false;
        }
    }

    /**
     * Given the IP address of a proxy server, determine whether it is
     * a known and trusted server.
     *
     * This test is performed using the config value `trustedproxies`.
     *
     * @param string $ip The IP address of the proxy.
     *
     * @return bool Returns true if the IP is trusted as a proxy.
     */
    public static function proxyIsTrusted(string $ip): bool
    {
        global $conf;

        // If the configuration is empty then no proxies are trusted.
        if (empty($conf['trustedproxies'])) {
            return false;
        }

        foreach ((array)$conf['trustedproxies'] as $trusted) {
            if (Ip::ipMatches($ip, $trusted)) {
                return true; // The given IP matches one of the trusted proxies.
            }
        }

        return false; // none of the proxies matched
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
    public static function forwardedFor(): array
    {
        /* @var Input $INPUT */
        global $INPUT, $conf;

        $forwardedFor = $INPUT->server->str('HTTP_X_FORWARDED_FOR');

        if (empty($conf['trustedproxies']) || !$forwardedFor) {
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
            if (!Ip::proxyIsTrusted($proxy)) {
                return [];
            }
        }

        // Add the client address before the list of proxies.
        return array_merge([$clientAddress], $proxies);
    }

    /**
     * Return the IP of the client.
     *
     * The IP is sourced from, in order of preference:
     *
     *   - The X-Real-IP header if $conf[realip] is true.
     *   - The X-Forwarded-For header if all the proxies are trusted by $conf[trustedproxy].
     *   - The TCP/IP connection remote address.
     *   - 0.0.0.0 if all else fails.
     *
     * The 'realip' config value should only be set to true if the X-Real-IP header
     * is being added by the web server, otherwise it may be spoofed by the client.
     *
     * The 'trustedproxy' setting must not allow any IP, otherwise the X-Forwarded-For
     * may be spoofed by the client.
     *
     * @return string Returns an IPv4 or IPv6 address.
     */
    public static function clientIp(): string
    {
        return Ip::clientIps()[0];
    }

    /**
     * Return the IP of the client and the proxies through which the connection has passed.
     *
     * The IPs are sourced from, in order of preference:
     *
     *   - The X-Real-IP header if $conf[realip] is true.
     *   - The X-Forwarded-For header if all the proxies are trusted by $conf[trustedproxies].
     *   - The TCP/IP connection remote address.
     *   - 0.0.0.0 if all else fails.
     *
     * @return string[] Returns an array of IPv4 or IPv6 addresses.
     */
    public static function clientIps(): array
    {
        /* @var Input $INPUT */
        global $INPUT, $conf;

        // IPs in order of most to least preferred.
        $ips = [];

        // Use the X-Real-IP header if it is enabled by the configuration.
        if (!empty($conf['realip']) && $INPUT->server->str('HTTP_X_REAL_IP')) {
            $ips[] = $INPUT->server->str('HTTP_X_REAL_IP');
        }

        // Add the X-Forwarded-For addresses if all proxies are trusted.
        $ips = array_merge($ips, Ip::forwardedFor());

        // Add the TCP/IP connection endpoint.
        $ips[] = $INPUT->server->str('REMOTE_ADDR');

        // Remove invalid IPs.
        $ips = array_filter($ips, static fn($ip) => filter_var($ip, FILTER_VALIDATE_IP));

        // Remove duplicated IPs.
        $ips = array_values(array_unique($ips));

        // Add a fallback if for some reason there were no valid IPs.
        if (!$ips) {
            $ips[] = '0.0.0.0';
        }

        return $ips;
    }
}
