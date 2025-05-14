<?php

class common_clientIP_test extends DokuWikiTest {

    /**
     * @var mixed[] $configs Possible values for $conf['trustedproxies'].
     */
    private $configs = [
        ['::1', 'fe80::/10', '127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'],
    ];

    /**
     * The data provider for clientIP() tests.
     *
     * @return mixed[][] Returns an array of test cases.
     */
    public function client_ip_all_provider() : array {
        // Malicious code in a header.
        $bad = '<?php die("hacked"); ?>';

        // Letters A, B, C, D, E will be substitued with an IPv4 or IPv6 address.
        $tests = [
            // A single IP with no other headers.
            ['A', false, '', '', false, 'A'],
            ['A', true, '', '', false, 'A'],
            ['A', false, '', '', true, 'A'],
            ['A', true, '', '', true, 'A'],

            // A X-Real-IP header.
            ['A', false, 'B', '', false, 'A'],
            ['A', true, 'B', '', false, 'B,A'],
            ['A', false, 'B', '', true, 'A'],
            ['A', true, 'B', '', true, 'B'],

            // An X-Forwarded-For header from an untrusted proxy.
            ['A', false, 'B', 'C', false, 'A'],
            ['A', true, 'B', 'C', false, 'B,A'],
            ['A', false, 'B', 'C', true, 'A'],
            ['A', true, 'B', 'C', true, 'B'],

            // An X-Forwarded-For header from a trusted proxy.
            ['D', false, 'B', 'C', false, 'C,D'],
            ['D', true, 'B', 'C', false, 'B,C,D'],
            ['D', false, 'B', 'C', true, 'C'],
            ['D', true, 'B', 'C', true, 'B'],

            // An X-Forwarded-For header with proxies from an untrusted proxy.
            ['A', false, 'B', 'C,E', false, 'A'],
            ['A', true, 'B', 'C,E', false, 'B,A'],
            ['A', false, 'B', 'C,E', true, 'A'],
            ['A', true, 'B', 'C,E', true, 'B'],

            // An X-Forwarded-For header with untrusted proxies from a trusted proxy.
            ['D', false, 'B', 'C,E', false, 'D'],
            ['D', true, 'B', 'C,E', false, 'B,D'],
            ['D', false, 'B', 'C,E', true, 'D'],
            ['D', true, 'B', 'C,E', true, 'B'],

            // An X-Forwarded-For header with an invalid proxy from a trusted proxy.
            ['D', false, 'B', 'C,invalid,E', false, 'D'],
            ['D', true, 'B', 'C,invalid,E', false, 'B,D'],
            ['D', false, 'B', 'C,invalid,E', true, 'D'],
            ['D', true, 'B', 'C,invalid,E', true, 'B'],

            // Malicious X-Real-IP and X-Forwarded-For headers.
            ['A', false, $bad, $bad, false, 'A'],
            ['A', true, $bad, $bad, false, 'A'],
            ['A', false, $bad, $bad, true, 'A'],
            ['A', true, $bad, $bad, true, 'A'],

            // Malicious remote address, X-Real-IP and X-Forwarded-For headers.
            [$bad, false, $bad, $bad, false, '0.0.0.0'],
            [$bad, true, $bad, $bad, false, '0.0.0.0'],
            [$bad, false, $bad, $bad, true, '0.0.0.0'],
            [$bad, true, $bad, $bad, true, '0.0.0.0'],
        ];

        return $tests;
    }

    /**
     * Test clientIP() with IPv6 addresses.
     *
     * @dataProvider client_ip_all_provider
     *
     * @param string $remoteAddr   The TCP/IP remote IP address.
     * @param bool   $useRealIp    True if using the X-Real-IP header is enabled in the config.
     * @param string $realIp       The X-Real-IP header.
     * @param string $forwardedFor The X-Forwarded-For header.
     * @param bool   $single       True to return the most likely client IP, false to return all candidates.
     * @param string $expected     The expected function result.
     *
     * @return void
     */
    public function test_client_ip_v4(string $remoteAddr, bool $useRealIp, string $realIp, string $forwardedFor, bool $single, string $expected) : void {
        global $conf;

        $addresses = [
            'A' => '123.123.123.123',
            'B' => '22.22.22.22',
            'C' => '33.33.33.33',
            'D' => '192.168.11.1',
            'E' => '44.44.44.44',
        ];

        $_SERVER['REMOTE_ADDR']          = str_replace(array_keys($addresses), array_values($addresses), $remoteAddr);
        $_SERVER['HTTP_X_REAL_IP']       = str_replace(array_keys($addresses), array_values($addresses), $realIp);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = str_replace(array_keys($addresses), array_values($addresses), $forwardedFor);
        $conf['realip'] = $useRealIp;

        foreach ($this->configs as $config) {
            $conf['trustedproxies'] = $config;
            $this->assertEquals(str_replace(array_keys($addresses), array_values($addresses), $expected), clientIP($single));
        }
    }

    /**
     * Test clientIP() with IPv6 addresses.
     *
     * @dataProvider client_ip_all_provider
     *
     * @param string $remoteAddr   The TCP/IP remote IP address.
     * @param bool   $useRealIp    True if using the X-Real-IP header is enabled in the config.
     * @param string $realIp       The X-Real-IP header.
     * @param string $forwardedFor The X-Forwarded-For header.
     * @param bool   $single       True to return the most likely client IP, false to return all candidates.
     * @param string $expected     The expected function result.
     *
     * @return void
     */
    public function test_client_ip_v6(string $remoteAddr, bool $useRealIp, string $realIp, string $forwardedFor, bool $single, string $expected) : void {
        global $conf;

        $addresses = [
            'A' => '1234:1234:1234:1234:1234:1234:1234:1234',
            'B' => '22:aa:22:bb:22:cc:22:dd',
            'C' => '33:aa:33:bb:33:cc:33:dd',
            'D' => '::1',
            'E' => '44:aa:44:bb:44:cc:44:dd',
        ];

        $_SERVER['REMOTE_ADDR']          = str_replace(array_keys($addresses), array_values($addresses), $remoteAddr);
        $_SERVER['HTTP_X_REAL_IP']       = str_replace(array_keys($addresses), array_values($addresses), $realIp);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = str_replace(array_keys($addresses), array_values($addresses), $forwardedFor);
        $conf['realip'] = $useRealIp;

        foreach ($this->configs as $config) {
            $conf['trustedproxies'] = $config;
            $this->assertEquals(str_replace(array_keys($addresses), array_values($addresses), $expected), clientIP($single));
        }
    }
}
