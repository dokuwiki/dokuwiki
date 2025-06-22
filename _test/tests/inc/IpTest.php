<?php

namespace dokuwiki\test;

use dokuwiki\Input\Input;
use dokuwiki\Ip;

class IpTest extends \DokuWikiTest {

    /**
     * The data provider for ipToNumber() tests.
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function ip_to_number_provider() : array
    {
        $tests = [
            ['127.0.0.1', 4, 0x00000000, 0x7f000001],
            ['::127.0.0.1', 6, 0x00000000, 0x7f000001],
            ['::1', 6, 0x00000000, 0x00000001],
            ['38AF:3033:AA39:CDE3:1A46:094C:44ED:5300', 6, 0x38AF3033AA39CDE3, 0x1A46094C44ED5300],
            ['193.53.125.7', 4, 0x00000000, 0xC1357D07],
        ];

        return $tests;
    }

    /**
     * Test ipToNumber().
     *
     * @dataProvider ip_to_number_provider
     *
     * @param string $ip The IP address to convert.
     * @param int    $version The IP version, either 4 or 6.
     * @param int    $upper   The upper 64 bits of the IP.
     * @param int    $lower   The lower 64 bits of the IP.
     *
     * @return void
     */
    public function test_ip_to_number(string $ip, int $version, int $upper, int $lower): void
    {
        $result = Ip::ipToNumber($ip);

        $this->assertSame($version, $result['version']);
        $this->assertSame($upper, $result['upper']);
        $this->assertSame($lower, $result['lower']);
    }

    /**
     * The data provider for test_ip_in_range().
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function ip_in_range_provider(): array
    {
        $tests = [
            ['192.168.11.2', '192.168.0.0/16', true],
            ['192.168.11.2', '192.168.64.1/16', true],
            ['192.168.11.2', '192.168.64.1/18', false],
            ['192.168.11.2', '192.168.11.0/20', true],
            ['127.0.0.1', '127.0.0.0/7', true],
            ['127.0.0.1', '127.0.0.0/8', true],
            ['127.0.0.1', '127.200.0.0/8', true],
            ['127.0.0.1', '127.200.0.0/9', false],
            ['127.0.0.1', '127.0.0.0/31', true],
            ['127.0.0.1', '127.0.0.0/32', false],
            ['127.0.0.1', '127.0.0.1/32', true],
            ['1111:2222:3333:4444:5555:6666:7777:8888', '1110::/12', true],
            ['1110:2222:3333:4444:5555:6666:7777:8888', '1110::/12', true],
            ['1100:2222:3333:4444:5555:6666:7777:8888', '1110::/12', false],
            ['1111:2222:3333:4444:5555:6666:7777:8888', '1111:2222:3300::/40', true],
            ['1111:2222:3333:4444:5555:6666:7777:8888', '1111:2222:3200::/40', false],
            ['1111:2222:3333:4444:5555:6666:7777:8888', '1111:2222:3333:4444:5555:6666:7777:8889/127', true],
            ['1111:2222:3333:4444:5555:6666:7777:8888', '1111:2222:3333:4444:5555:6666:7777:8889/128', false],
            ['1111:2222:3333:4444:5555:6666:7777:8889', '1111:2222:3333:4444:5555:6666:7777:8889/128', true],
            ['abcd:ef0a:bcde:f0ab:cdef:0abc:def0:abcd', 'abcd:ef0a:bcde:f0ab:cdef:0abc:def0:abcd/128', true],
            ['abcd:ef0a:bcde:f0ab:cdef:0abc:def0:abce', 'abcd:ef0a:bcde:f0ab:cdef:0abc:def0:abcd/128', false],
        ];

        return $tests;
    }

    /**
     * Test ipInRange().
     *
     * @dataProvider ip_in_range_provider
     *
     * @param string $ip The IP to test.
     * @param string $range The IP range to test against.
     * @param bool $expected The expected result from ipInRange().
     *
     * @return void
     */
    public function test_ip_in_range(string $ip, string $range, bool $expected): void
    {
        $result = Ip::ipInRange($ip, $range);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_ip_matches().
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function ip_matches_provider(): array
    {
        // Tests for a CIDR range.
        $rangeTests = $this->ip_in_range_provider();

        // Tests for an exact IP match.
        $exactTests = [
            ['127.0.0.1', '127.0.0.1', true],
            ['127.0.0.1', '127.0.0.0', false],
            ['aaaa:bbbb:cccc:dddd:eeee::', 'aaaa:bbbb:cccc:dddd:eeee:0000:0000:0000', true],
            ['aaaa:bbbb:cccc:dddd:eeee:0000:0000:0000', 'aaaa:bbbb:cccc:dddd:eeee::', true],
            ['aaaa:bbbb:0000:0000:0000:0000:0000:0001', 'aaaa:bbbb::1', true],
            ['aaaa:bbbb::0001', 'aaaa:bbbb::1', true],
            ['aaaa:bbbb::0001', 'aaaa:bbbb::', false],
            ['::ffff:127.0.0.1', '127.0.0.1', false],
            ['::ffff:127.0.0.1', '::0:ffff:127.0.0.1', true],
        ];


        return array_merge($rangeTests, $exactTests);
    }

    /**
     * Test ipMatches().
     *
     * @dataProvider ip_matches_provider
     *
     * @param string $ip        The IP to test.
     * @param string $ipOrRange The IP or IP range to test against.
     * @param bool   $expected  The expeced result from ipMatches().
     *
     * @return void
     */
    public function test_ip_matches(string $ip, string $ipOrRange, bool $expected): void
    {
        $result = Ip::ipMatches($ip, $ipOrRange);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for proxyIsTrusted().
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function proxy_is_trusted_provider(): array
    {
        // The new default configuration value.
        $default = ['::1', 'fe80::/10', '127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'];

        // Adding some custom trusted proxies.
        $custom = array_merge($default, ['1.2.3.4', '1122::', '3.0.0.1/8', '1111:2222::/32']);

        $tests = [
            // Empty configuration.
            ['', '127.0.0.1', false],

            // Configuration with an array of  IPs/CIDRs.
            [$default, '127.0.0.1', true],
            [$default, '127.1.2.3', true],
            [$default, '10.1.2.3', true],
            [$default, '11.1.2.3', false],
            [$default, '172.16.0.1', true],
            [$default, '172.160.0.1', false],
            [$default, '172.31.255.255', true],
            [$default, '172.32.0.0', false],
            [$default, '172.200.0.0', false],
            [$default, '192.168.2.3', true],
            [$default, '192.169.1.2', false],
            [$default, '::1', true],
            [$default, '0000:0000:0000:0000:0000:0000:0000:0001', true],

            // With custom proxies set.
            [$custom, '127.0.0.1', true],
            [$custom, '1.2.3.4', true],
            [$custom, '3.0.1.2', true],
            [$custom, '1122::', true],
            [$custom, '1122:0000:0000:0000:0000:0000:0000:0000', true],
            [$custom, '1111:2223::', false],
            [$custom, '1111:2222::', true],
            [$custom, '1111:2222:3333::', true],
            [$custom, '1111:2222:3333::1', true],
        ];

        return $tests;
    }

    /**
     * Test proxyIsTrusted().
     *
     * @dataProvider proxy_is_trusted_provider
     *
     * @param string|string[] $config   The value for $conf[trustedproxies].
     * @param string          $ip       The proxy IP to test.
     * @param bool            $expected The expected result from proxyIsTrusted().
     */
    public function test_proxy_is_trusted($config, string $ip, bool $expected): void
    {
        global $conf;
        $conf['trustedproxies'] = $config;

        $result = Ip::proxyIsTrusted($ip);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_forwarded_for().
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function forwarded_for_provider(): array
    {
        // The new default configuration value.
        $default = ['::1', 'fe80::/10', '127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'];

        // Adding some custom trusted proxies.
        $custom = array_merge($default, ['1.2.3.4', '1122::', '3.0.0.1/8', '1111:2222::/32']);

        $tests = [
            // Empty config value should always return empty array.
            [[], '', '127.0.0.1', []],
            [[], '127.0.0.1', '127.0.0.1', []],

            // The new default configuration.
            [$default, '', '127.0.0.1', []],
            [$default, '1.2.3.4', '127.0.0.1', ['1.2.3.4', '127.0.0.1']],
            [$default, '1.2.3.4', '192.168.1.1', ['1.2.3.4', '192.168.1.1']],
            [$default, '1.2.3.4,172.16.0.1', '192.168.1.1', ['1.2.3.4', '172.16.0.1', '192.168.1.1']],
            [$default, '1.2.3.4,172.16.0.1', '::1', ['1.2.3.4', '172.16.0.1', '::1']],
            [$default, '1.2.3.4,172.16.0.1', '::0001', ['1.2.3.4', '172.16.0.1', '::0001']],

            // Directly from an untrusted proxy.
            [$default, '', '127.0.0.1', []],
            [$default, '1.2.3.4', '11.22.33.44', []],
            [$default, '::1', '11.22.33.44', []],
            [$default, '::1', '::2', []],

            // From a trusted proxy, but via an untrusted proxy.
            [$default, '1.2.3.4,11.22.33.44,172.16.0.1', '192.168.1.1', []],
            [$default, '1.2.3.4,::2,172.16.0.1', '::1', []],

            // A custom configuration.
            [$custom, '', '127.0.0.1', []],
            [$custom, '1.2.3.4', '127.0.0.1', ['1.2.3.4', '127.0.0.1']],
            [$custom, '1.2.3.4', '192.168.1.1', ['1.2.3.4', '192.168.1.1']],
            [$custom, '1.2.3.4,172.16.0.1', '192.168.1.1', ['1.2.3.4', '172.16.0.1', '192.168.1.1']],
            [$custom, '1.2.3.4,172.16.0.1', '::1', ['1.2.3.4', '172.16.0.1', '::1']],
            [$custom, '1.2.3.4,172.16.0.1', '::0001', ['1.2.3.4', '172.16.0.1', '::0001']],

            // Directly from an untrusted proxy.
            [$custom, '', '127.0.0.1', []],
            [$custom, '1.2.3.4', '11.22.33.44', []],
            [$custom, '::1', '11.22.33.44', []],
            [$custom, '::1', '::2', []],

            // From a trusted proxy, but via an untrusted proxy.
            [$custom, '1.2.3.4,11.22.33.44,172.16.0.1', '192.168.1.1', []],
            [$custom, '1.2.3.4,::2,172.16.0.1', '::1', []],

            // Via a custom proxy.
            [$custom, '11.2.3.4,3.1.2.3,172.16.0.1', '192.168.1.1', ['11.2.3.4', '3.1.2.3', '172.16.0.1', '192.168.1.1']],
            [$custom, '11.2.3.4,1122::,172.16.0.1', '3.0.0.1', ['11.2.3.4', '1122::', '172.16.0.1', '3.0.0.1']],
            [$custom, '11.2.3.4,1122::,172.16.0.1', '1111:2222:3333::', ['11.2.3.4', '1122::', '172.16.0.1', '1111:2222:3333::']],
        ];

        return $tests;
    }

    /**
     * Test forwardedFor().
     *
     * @dataProvider forwarded_for_provider
     *
     * @param string|string[] $config     The trustedproxies config value.
     * @param string          $header     The X-Forwarded-For header value.
     * @param string          $remoteAddr The TCP/IP peer address.
     * @param array           $expected   The expected result from forwardedFor().
     *
     * @return void
     */
    public function test_forwarded_for($config, string $header, string $remoteAddr, array $expected): void
    {
        /* @var Input $INPUT */
        global $INPUT, $conf;

        $conf['trustedproxies'] = $config;
        $INPUT->server->set('HTTP_X_FORWARDED_FOR', $header);
        $INPUT->server->set('REMOTE_ADDR', $remoteAddr);

        $result = Ip::forwardedFor();

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_is_ssl().
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function is_ssl_provider(): array
    {
        // The new default configuration value.
        $default = ['::1', 'fe80::/10', '127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'];

        $tests = [
            // Running behind an SSL proxy, HTTP between server and proxy
            // Proxy (REMOTE_ADDR) is matched by trustedproxies config
            // HTTPS not set, HTTP_X_FORWARDED_PROTO set to https
            [$default, '127.0.0.1', '', 'https', true],

            // Running behind an SSL proxy, HTTP between server and proxy
            // Proxy (REMOTE_ADDR) is not matched by trustedproxies config
            // HTTPS not set, HTTP_X_FORWARDED_PROTO set to https
            [[], '8.8.8.8', '', 'https', false],

            // Running behind a plain HTTP proxy, HTTP between server and proxy
            // HTTPS not set, HTTP_X_FORWARDED_PROTO set to http
            [$default, '127.0.0.1', '', 'http', false],

            // Running behind an SSL proxy, HTTP between server and proxy
            // HTTPS set to off, HTTP_X_FORWARDED_PROTO set to https
            [$default, '127.0.0.1', 'off', 'https', true],

            // Not running behind a proxy, HTTPS server
            // HTTPS set to on, HTTP_X_FORWARDED_PROTO not set
            [[], '8.8.8.8', 'on', '', true],

            // Not running behind a proxy, plain HTTP server
            // HTTPS not set, HTTP_X_FORWARDED_PROTO not set
            [[], '8.8.8.8', '', '', false],

            // Not running behind a proxy, plain HTTP server
            // HTTPS set to off, HTTP_X_FORWARDED_PROTO not set
            [[], '8.8.8.8', 'off', '', false],

            // Running behind an SSL proxy, SSL between proxy and HTTP server
            // HTTPS set to on, HTTP_X_FORWARDED_PROTO set to https
            [$default, '127.0.0.1', 'on', 'https', true],
        ];

        return $tests;
    }

    /**
     * Test isSsl().
     *
     * @dataProvider is_ssl_provider
     *
     * @param string|string[] $config           The trustedproxies config value.
     * @param string          $remoteAddr       The REMOTE_ADDR value.
     * @param string          $https            The HTTPS value.
     * @param string          $forwardedProto   The HTTP_X_FORWARDED_PROTO value.
     * @param bool            $expected         The expected result from isSsl().
     *
     * @return void
     */
    public function test_is_ssl($config, string $remoteAddr, string $https, string $forwardedProto, bool $expected): void
    {
        /* @var Input $INPUT */
        global $INPUT, $conf;

        $conf['trustedproxies'] = $config;
        $INPUT->server->set('REMOTE_ADDR', $remoteAddr);
        $INPUT->server->set('HTTPS', $https);
        $INPUT->server->set('HTTP_X_FORWARDED_PROTO', $forwardedProto);

        $result = Ip::isSsl();

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_host_name().
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function host_name_provider(): array
    {
        // The new default configuration value.
        $default = ['::1', 'fe80::/10', '127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'];

        $tests = [
            // X-Forwarded-Host with trusted proxy
            [$default, '127.0.0.1', 'proxy.example.com', 'www.example.com', 'server.local', 'proxy.example.com'],

            // X-Forwarded-Host with untrusted proxy (should fall back to HTTP_HOST)
            [[], '8.8.8.8', 'proxy.example.com', 'www.example.com', 'server.local', 'www.example.com'],

            // No X-Forwarded-Host, use HTTP_HOST
            [$default, '127.0.0.1', '', 'www.example.com', 'server.local', 'www.example.com'],

            // No X-Forwarded-Host or HTTP_HOST, use SERVER_NAME
            [$default, '127.0.0.1', '', '', 'server.local', 'server.local'],

            // No headers set, should fall back to system hostname
            [$default, '127.0.0.1', '', '', '', php_uname('n')],
        ];

        return $tests;
    }

    /**
     * Test hostName().
     *
     * @dataProvider host_name_provider
     *
     * @param string|string[] $config           The trustedproxies config value.
     * @param string          $remoteAddr       The REMOTE_ADDR value.
     * @param string          $forwardedHost    The HTTP_X_FORWARDED_HOST value.
     * @param string          $httpHost         The HTTP_HOST value.
     * @param string          $serverName       The SERVER_NAME value.
     * @param string          $expected         The expected result from hostName().
     *
     * @return void
     */
    public function test_host_name($config, string $remoteAddr, string $forwardedHost, string $httpHost, string $serverName, string $expected): void
    {
        /* @var Input $INPUT */
        global $INPUT, $conf;

        $conf['trustedproxies'] = $config;
        $INPUT->server->set('REMOTE_ADDR', $remoteAddr);
        $INPUT->server->set('HTTP_X_FORWARDED_HOST', $forwardedHost);
        $INPUT->server->set('HTTP_HOST', $httpHost);
        $INPUT->server->set('SERVER_NAME', $serverName);

        $result = Ip::hostName();

        $this->assertSame($expected, $result);
    }
}
