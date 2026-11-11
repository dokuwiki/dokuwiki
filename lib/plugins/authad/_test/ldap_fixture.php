<?php

/**
 * Scripted LDAP doubles for adLDAP failover coverage.
 *
 * Run with PHP_BINARY -n so ext-ldap is not loaded. When the extension is
 * compiled in, this process exits 2 without defining any LDAP functions.
 */

if (function_exists('ldap_connect')) {
    fwrite(STDERR, "ext-ldap is statically compiled; cannot install LDAP fixtures\n");
    exit(2);
}

ini_set('zend.exception_ignore_args', '1');
error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

define('LDAP_OPT_PROTOCOL_VERSION', 17);
define('LDAP_OPT_REFERRALS', 8);
define('LDAP_OPT_NETWORK_TIMEOUT', 20485);
define('FIX_DC_A', 'dc-a.corp.test');
define('FIX_DC_B', 'dc-b.corp.test');
define('FIX_DC_C', 'dc-c.corp.test');

class FakeLdapLink
{
    public $id;
    public $host;
    public $port;
    public $closed = false;

    public function __construct($id, $host, $port)
    {
        $this->id = $id;
        $this->host = $host;
        $this->port = $port;
    }
}

class LdapFake
{
    public static $trace = [];
    public static $conns = [];
    public static $nextId = 0;
    public static $errno = [];
    public static $error = [];
    public static $bindSeq = [];
    public static $bindDefault = -1;
    public static $saslSeq = [];
    public static $saslDefault = -1;
    public static $tlsSeq = [];
    public static $tlsDefault = 0;
    public static $connectFails = false;
    public static $failureText = '';
    public static $calls = 0;

    public static function reset(array $script = [])
    {
        self::$trace = [];
        self::$conns = [];
        self::$nextId = 0;
        self::$errno = [];
        self::$error = [];
        self::$bindSeq = $script['bind'] ?? [];
        self::$bindDefault = $script['bind_default'] ?? -1;
        self::$saslSeq = $script['sasl'] ?? [];
        self::$saslDefault = $script['sasl_default'] ?? -1;
        self::$tlsSeq = $script['tls'] ?? [];
        self::$tlsDefault = $script['tls_default'] ?? 0;
        self::$connectFails = !empty($script['connect_fail']);
        self::$failureText = $script['failure_text'] ?? '';
        self::$calls = 0;
    }

    public static function guard()
    {
        self::$calls++;
        if (self::$calls > 40) {
            throw new RuntimeException('runaway LDAP call volume');
        }
    }

    public static function shift(&$seq, $default)
    {
        if ($seq) {
            return array_shift($seq);
        }
        return $default;
    }

    public static function openCount()
    {
        $open = 0;
        foreach (self::$conns as $conn) {
            if (!$conn->closed) {
                $open++;
            }
        }
        return $open;
    }
}

eval(<<<'PHP'
function ldap_connect($host = null, $port = 389)
{
    LdapFake::guard();
    if (LdapFake::$connectFails) {
        LdapFake::$trace[] = ['op' => 'connect', 'id' => null, 'host' => $host, 'port' => $port, 'ok' => false];
        return false;
    }
    $id = ++LdapFake::$nextId;
    $link = new FakeLdapLink($id, $host, $port);
    LdapFake::$conns[$id] = $link;
    LdapFake::$trace[] = ['op' => 'connect', 'id' => $id, 'host' => $host, 'port' => $port, 'ok' => true];
    return $link;
}

function ldap_set_option($conn, $option, $value)
{
    LdapFake::guard();
    $id = is_object($conn) ? $conn->id : null;
    $event = ['op' => 'option', 'id' => $id, 'option' => $option, 'value' => $value];
    if ($option === LDAP_OPT_NETWORK_TIMEOUT) {
        $event['name'] = 'network_timeout';
    }
    LdapFake::$trace[] = $event;
    return true;
}

function ldap_start_tls($conn)
{
    LdapFake::guard();
    $errno = LdapFake::shift(LdapFake::$tlsSeq, LdapFake::$tlsDefault);
    if ($errno === 0) {
        LdapFake::$errno[$conn->id] = 0;
        LdapFake::$error[$conn->id] = 'Success';
        LdapFake::$trace[] = ['op' => 'start_tls', 'id' => $conn->id, 'errno' => 0];
        return true;
    }
    LdapFake::$errno[$conn->id] = $errno;
    LdapFake::$error[$conn->id] = LdapFake::$failureText !== '' ? LdapFake::$failureText : ('tls error ' . $errno);
    LdapFake::$trace[] = ['op' => 'start_tls', 'id' => $conn->id, 'errno' => $errno];
    return false;
}

function ldap_bind($conn, $dn = null, $password = null)
{
    LdapFake::guard();
    $errno = LdapFake::shift(LdapFake::$bindSeq, LdapFake::$bindDefault);
    $empty = ($password === null || $password === '');
    if ($errno === 0) {
        LdapFake::$errno[$conn->id] = 0;
        LdapFake::$error[$conn->id] = 'Success';
        LdapFake::$trace[] = ['op' => 'bind', 'id' => $conn->id, 'dn' => $dn, 'empty_password' => $empty, 'errno' => 0];
        return true;
    }
    LdapFake::$errno[$conn->id] = $errno;
    LdapFake::$error[$conn->id] = LdapFake::$failureText !== '' ? LdapFake::$failureText : ('error ' . $errno);
    LdapFake::$trace[] = ['op' => 'bind', 'id' => $conn->id, 'dn' => $dn, 'empty_password' => $empty, 'errno' => $errno];
    return false;
}

function ldap_sasl_bind($conn, $dn = null, $password = null, $mech = null)
{
    LdapFake::guard();
    $errno = LdapFake::shift(LdapFake::$saslSeq, LdapFake::$saslDefault);
    $env = getenv('KRB5CCNAME');
    $expected = $_SERVER['KRB5CCNAME'] ?? null;
    $envOk = is_string($env) && $env !== '' && $env === $expected;
    if ($errno === 0) {
        LdapFake::$errno[$conn->id] = 0;
        LdapFake::$error[$conn->id] = 'Success';
    } else {
        LdapFake::$errno[$conn->id] = $errno;
        LdapFake::$error[$conn->id] = LdapFake::$failureText !== '' ? LdapFake::$failureText : ('sasl error ' . $errno);
    }
    LdapFake::$trace[] = [
        'op' => 'sasl',
        'id' => $conn->id,
        'mech' => $mech,
        'env_ok' => $envOk,
        'errno' => $errno,
    ];
    return $errno === 0;
}

function ldap_read($conn, $base, $filter, $attributes = [])
{
    LdapFake::guard();
    LdapFake::$trace[] = ['op' => 'read', 'id' => $conn->id];
    return 'sr-' . $conn->id;
}

function ldap_get_entries($conn, $result)
{
    LdapFake::guard();
    return [
        'count' => 1,
        0 => [
            'defaultnamingcontext' => [
                'count' => 1,
                0 => 'DC=example,DC=com',
            ],
        ],
    ];
}

function ldap_errno($conn)
{
    LdapFake::guard();
    if (!is_object($conn) || $conn->closed) {
        return 0;
    }
    return LdapFake::$errno[$conn->id] ?? 0;
}

function ldap_error($conn)
{
    LdapFake::guard();
    if (!is_object($conn) || $conn->closed) {
        return 'CLOSED';
    }
    return LdapFake::$error[$conn->id] ?? '';
}

function ldap_close($conn)
{
    LdapFake::guard();
    if (!is_object($conn)) {
        return false;
    }
    $conn->closed = true;
    LdapFake::$errno[$conn->id] = 0;
    LdapFake::$error[$conn->id] = 'CLOSED';
    LdapFake::$trace[] = ['op' => 'close', 'id' => $conn->id];
    return true;
}
PHP);

require dirname(__DIR__) . '/adLDAP/adLDAP.php';

function clear_identity()
{
    putenv('KRB5CCNAME');
    unset($_SERVER['KRB5CCNAME'], $_SERVER['REMOTE_USER']);
}

function ad_opts(array $extra = [])
{
    return array_merge([
        'account_suffix' => '@corp.test',
        'base_dn' => 'DC=corp,DC=test',
        'domain_controllers' => [FIX_DC_A, FIX_DC_B],
    ], $extra);
}

function with_admin(array $extra = [])
{
    return ad_opts(array_merge([
        'admin_username' => 'svc',
        'admin_password' => 'admin-secret',
    ], $extra));
}

function blank_result()
{
    return [
        'constructed' => false,
        'exception' => null,
        'exception_class' => null,
        'bound_after_connect' => null,
        'connected_after_connect' => null,
        'base_dn' => null,
        'result' => null,
        'bound' => null,
        'connection_after_close' => null,
        'trace' => [],
        'open_handles' => null,
    ];
}

function boot(array $opt, array $script = [], array $env = [], $auth = null)
{
    clear_identity();
    foreach ($env as $key => $value) {
        $_SERVER[$key] = $value;
    }
    LdapFake::reset($script);
    $out = blank_result();
    try {
        $ad = new adLDAP($opt);
        $out['constructed'] = true;
        $out['bound_after_connect'] = (bool) $ad->getLdapBind();
        $out['connected_after_connect'] = (bool) $ad->getLdapConnection();
        if ($auth !== null) {
            try {
                $out['result'] = $ad->authenticate($auth['user'], $auth['pass'], $auth['prevent'] ?? false);
            } catch (adLDAPException $e) {
                $out['exception'] = $e->getMessage();
                $out['exception_class'] = get_class($e);
                $out['result'] = null;
            }
        }
        if (!empty($script['close_twice'])) {
            $ad->close();
            $ad->close();
            $out['connection_after_close'] = $ad->getLdapConnection();
        }
        $out['base_dn'] = $ad->getBaseDn();
        $out['bound'] = (bool) $ad->getLdapBind();
        unset($ad);
    } catch (adLDAPException $e) {
        if ($out['exception'] === null) {
            $out['exception'] = $e->getMessage();
            $out['exception_class'] = get_class($e);
        }
    }
    $out['trace'] = LdapFake::$trace;
    $out['open_handles'] = LdapFake::openCount();
    return $out;
}

function scenario_empty_secrets()
{
    clear_identity();
    LdapFake::reset(['bind' => [0]]);
    $ad = new adLDAP(ad_opts(['domain_controllers' => [FIX_DC_A]]));
    $out = blank_result();
    $out['constructed'] = true;
    $out['connected_after_connect'] = (bool) $ad->getLdapConnection();
    $out['bound_after_connect'] = (bool) $ad->getLdapBind();
    $out['results'] = [
        $ad->authenticate('', ''),
        $ad->authenticate(null, null),
        $ad->authenticate('alice', ''),
        $ad->authenticate('', 'user-secret'),
        $ad->authenticate('alice', null),
    ];
    unset($ad);
    $out['trace'] = LdapFake::$trace;
    $out['open_handles'] = LdapFake::openCount();
    return $out;
}

function scenario_sasl_auth()
{
    clear_identity();
    $_SERVER['REMOTE_USER'] = 'alice';
    LdapFake::reset(['sasl' => [-1, 0], 'failure_text' => 'sasl transport']);
    $ad = new adLDAP(ad_opts(['sso' => true]));
    $out = blank_result();
    $out['constructed'] = true;
    $out['connected_after_connect'] = (bool) $ad->getLdapConnection();
    $out['bound_after_connect'] = (bool) $ad->getLdapBind();
    $_SERVER['KRB5CCNAME'] = 'FILE:/tmp/krb5cc_fixture';
    try {
        $out['result'] = $ad->authenticate('alice', 'user-secret');
    } catch (adLDAPException $e) {
        $out['exception'] = $e->getMessage();
        $out['exception_class'] = get_class($e);
    }
    unset($ad);
    $out['trace'] = LdapFake::$trace;
    $out['open_handles'] = LdapFake::openCount();
    return $out;
}

function scenario_matrix()
{
    $expect = [
        -5 => true,
        -11 => true,
        81 => true,
        85 => true,
        91 => true,
        2 => false,
        32 => false,
        53 => false,
    ];
    $cases = [];
    foreach ($expect as $errno => $retry) {
        $case = boot(
            ad_opts(),
            ['bind' => [$errno, 0], 'failure_text' => 'classified ' . $errno],
            [],
            ['user' => 'alice', 'pass' => 'user-secret']
        );
        $case['errno'] = $errno;
        $case['expect_retry'] = $retry;
        $cases[] = $case;
    }
    return $cases;
}

try {
    $report = [
        'scenarios' => [
            'service_failover' => boot(
                with_admin(),
                ['bind' => [-1, 0], 'failure_text' => 'Can\'t contact LDAP server admin-secret']
            ),
            'user_failover' => boot(
                ad_opts(),
                ['bind' => [-1, 0], 'failure_text' => 'Can\'t contact LDAP server user-secret'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret']
            ),
            'user_rebind_capture' => boot(
                with_admin(),
                ['bind' => [0, -1, 0, 0], 'failure_text' => 'Can\'t contact LDAP server user-secret'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret']
            ),
            'all_down' => boot(
                with_admin(['domain_controllers' => [FIX_DC_A, FIX_DC_B, FIX_DC_C]]),
                ['bind' => [-1, -1, -1], 'failure_text' => 'Can\'t contact LDAP server admin-secret']
            ),
            'user_all_down' => boot(
                ad_opts(),
                ['bind' => [-1, -1], 'failure_text' => 'Can\'t contact LDAP server user-secret'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret']
            ),
            'single_down' => boot(
                with_admin(['domain_controllers' => [FIX_DC_A]]),
                ['bind' => [-1], 'failure_text' => 'Can\'t contact LDAP server admin-secret']
            ),
            'empty_controllers' => boot(
                with_admin(['domain_controllers' => []]),
                ['failure_text' => 'unused admin-secret']
            ),
            'connect_fails' => boot(
                with_admin(),
                ['connect_fail' => true, 'failure_text' => 'ldap down admin-secret']
            ),
            'invalid_credentials' => boot(
                ad_opts(),
                ['bind' => [49, 0], 'failure_text' => 'Invalid credentials'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret']
            ),
            'invalid_with_admin' => boot(
                with_admin(),
                ['bind' => [0, 49, 0], 'failure_text' => 'Invalid credentials admin-secret'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret']
            ),
            'empty_secrets' => scenario_empty_secrets(),
            'transport_matrix' => scenario_matrix(),
            'starttls' => boot(
                with_admin(['use_tls' => true]),
                ['tls' => [1], 'bind' => [0], 'failure_text' => 'tls failed admin-secret']
            ),
            'ldaps' => boot(
                with_admin([
                    'use_ssl' => true,
                    'ad_port' => 1636,
                    'domain_controllers' => [FIX_DC_A],
                ]),
                ['bind' => [0]]
            ),
            'sasl_connect' => boot(
                ad_opts(['sso' => true, 'base_dn' => null]),
                ['sasl' => [-1, 0], 'failure_text' => 'sasl transport'],
                ['REMOTE_USER' => 'alice', 'KRB5CCNAME' => 'FILE:/tmp/krb5cc_fixture']
            ),
            'sasl_auth' => scenario_sasl_auth(),
            'service_rejected' => boot(
                with_admin(),
                ['bind' => [49, 0], 'failure_text' => 'Invalid credentials admin-secret']
            ),
            'admin_restore_fails' => boot(
                with_admin(['domain_controllers' => [FIX_DC_A]]),
                ['bind' => [0, 0, 49], 'failure_text' => 'rebind failed admin-secret'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret']
            ),
            'prevent_rebind' => boot(
                with_admin(['domain_controllers' => [FIX_DC_A]]),
                ['bind' => [0, 0, 49], 'failure_text' => 'rebind failed admin-secret'],
                [],
                ['user' => 'alice', 'pass' => 'user-secret', 'prevent' => true]
            ),
            'base_dn' => boot(
                with_admin(['base_dn' => null]),
                ['bind' => [-1, 0], 'failure_text' => 'Can\'t contact LDAP server admin-secret']
            ),
            'close_twice' => boot(
                ad_opts(['domain_controllers' => [FIX_DC_A]]),
                ['close_twice' => true]
            ),
        ],
    ];
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n");
    exit(1);
}
