<?php

use dokuwiki\Logger;

require_once DOKU_PLUGIN . 'authad/auth.php';

/**
 * authad subclass with a controllable directory handle.
 */
class auth_plugin_authad_failovertest extends auth_plugin_authad
{
    public $stub = null;
    public $useStub = false;

    /** @inheritDoc */
    protected function initAdLdap($domain)
    {
        if ($this->useStub) {
            return $this->stub;
        }
        return parent::initAdLdap($domain);
    }

    /**
     * @param string|null $domain
     * @return adLDAP|bool
     */
    public function callInit($domain)
    {
        return $this->initAdLdap($domain);
    }

    /**
     * @param array $values
     * @return void
     */
    public function configure(array $values)
    {
        foreach ($values as $key => $value) {
            $this->conf[$key] = $value;
        }
    }
}

/**
 * Directory double used only by the plugin-boundary tests.
 */
class authad_failover_directory_double
{
    public $mode = 'reject';

    public function authenticate($user, $pass, $preventRebind = false)
    {
        if ($this->mode === 'throw') {
            throw new adLDAPException('directory unavailable');
        }
        return false;
    }
}

/**
 * Domain-controller failover for the bundled adLDAP client and authad.
 */
class authad_failover_test extends DokuWikiTest
{
    private const DC_A = 'dc-a.corp.test';
    private const DC_B = 'dc-b.corp.test';
    private const DC_C = 'dc-c.corp.test';
    private const USER_DN = 'alice@corp.test';
    private const ADMIN_DN = 'svc@corp.test';

    /** @var array|null */
    private static $report = null;

    public function test_service_account_initialization_fails_over()
    {
        $scenario = $this->scenario('service_failover');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['bound_after_connect']);
        $this->assertTrue($scenario['connected_after_connect']);
        $binds = $this->events($scenario, 'bind');
        $this->assertCount(2, $binds);
        $this->assertSame(-1, $binds[0]['errno']);
        $this->assertSame(0, $binds[1]['errno']);
        $this->assertSame(self::ADMIN_DN, $binds[0]['dn']);
        $this->assertSame(self::ADMIN_DN, $binds[1]['dn']);
        $this->assertNotSame($binds[0]['id'], $binds[1]['id']);
        $this->assertTransportFailover($scenario, 'bind', [self::DC_A, self::DC_B]);
    }

    public function test_deferred_user_bind_fails_over_without_counting_lazy_connect()
    {
        $scenario = $this->scenario('user_failover');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['connected_after_connect']);
        $this->assertFalse($scenario['bound_after_connect']);
        $this->assertTrue($scenario['result']);
        $this->assertTrue($scenario['bound']);
        $binds = $this->events($scenario, 'bind');
        $this->assertCount(2, $binds);
        $this->assertSame(-1, $binds[0]['errno']);
        $this->assertSame(0, $binds[1]['errno']);
        $this->assertSame(self::USER_DN, $binds[0]['dn']);
        $this->assertSame(self::USER_DN, $binds[1]['dn']);
        $this->assertNotSame($binds[0]['id'], $binds[1]['id']);
        $this->assertSame([], $this->events($scenario, 'read'));
        $this->assertTransportFailover($scenario, 'bind', [self::DC_A, self::DC_B]);
    }

    public function test_user_transport_error_is_captured_before_admin_rebind()
    {
        $scenario = $this->scenario('user_rebind_capture');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['result']);
        $this->assertSame(2, $this->bindCount($scenario, self::USER_DN));
        $this->assertSame(2, $this->bindCount($scenario, self::ADMIN_DN));
        $userBinds = $this->bindsFor($scenario, self::USER_DN);
        $this->assertSame(-1, $userBinds[0]['errno']);
        $this->assertSame(0, $userBinds[1]['errno']);
        $this->assertNotSame($userBinds[0]['id'], $userBinds[1]['id']);
        $this->assertCount(2, $this->events($scenario, 'connect'));
        $this->assertTransportFailover($scenario, 'bind', [self::DC_A, self::DC_B]);
    }

    public function test_all_controllers_are_tried_once_when_initializing()
    {
        $scenario = $this->scenario('all_down');
        $this->assertFalse($scenario['constructed']);
        $this->assertNull($scenario['result']);
        $message = $this->assertOperational($scenario, 'Active Directory is unavailable');
        $this->assertStringContainsString('[redacted]', $message);
        $this->assertStringNotContainsString('Bind to Active Directory failed', $message);
        $this->assertTriedOnce($scenario, [self::DC_A, self::DC_B, self::DC_C], 'bind');
    }

    public function test_all_controllers_are_tried_once_during_user_authentication()
    {
        $scenario = $this->scenario('user_all_down');
        $this->assertTrue($scenario['constructed']);
        $this->assertFalse($scenario['bound_after_connect']);
        $this->assertNull($scenario['result']);
        $message = $this->assertOperational($scenario, 'Active Directory is unavailable');
        $this->assertStringContainsString('[redacted]', $message);
        $this->assertTriedOnce($scenario, [self::DC_A, self::DC_B], 'bind');
        $this->assertSame(0, $this->bindCount($scenario, self::ADMIN_DN));
    }

    public function test_single_controller_outage_does_not_retry()
    {
        $scenario = $this->scenario('single_down');
        $this->assertFalse($scenario['constructed']);
        $message = $this->assertOperational($scenario, 'Active Directory is unavailable');
        $this->assertStringContainsString('[redacted]', $message);
        $this->assertTriedOnce($scenario, [self::DC_A], 'bind');
    }

    public function test_empty_controller_list_fails_without_a_connection()
    {
        $scenario = $this->scenario('empty_controllers');
        $this->assertFalse($scenario['constructed']);
        $message = $this->assertOperational($scenario, 'No domain controllers configured');
        $this->assertStringContainsString('Active Directory is unavailable', $message);
        $this->assertSame([], $scenario['trace']);
        $this->assertSame(0, $scenario['open_handles']);
    }

    public function test_failed_ldap_connect_tries_each_controller_once()
    {
        $scenario = $this->scenario('connect_fails');
        $this->assertFalse($scenario['constructed']);
        $message = $this->assertOperational($scenario, 'ldap_connect failed');
        $this->assertStringContainsString('Active Directory is unavailable', $message);
        $connects = $this->events($scenario, 'connect');
        $this->assertCount(2, $connects);
        foreach ($connects as $event) {
            $this->assertFalse($event['ok']);
            $this->assertNull($event['id']);
        }
        $this->assertSame([], $this->events($scenario, 'bind'));
        $this->assertSame([], $this->events($scenario, 'close'));
        $this->assertSame(0, $scenario['open_handles']);
        $this->assertEqualsCanonicalizing(
            [self::DC_A, self::DC_B],
            array_column($connects, 'host')
        );
    }

    public function test_invalid_credentials_are_not_replayed()
    {
        $scenario = $this->scenario('invalid_credentials');
        $this->assertRejection($scenario);
        $binds = $this->events($scenario, 'bind');
        $this->assertCount(1, $binds);
        $this->assertSame(49, $binds[0]['errno']);
        $this->assertSame(self::USER_DN, $binds[0]['dn']);
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertFalse($scenario['bound_after_connect']);
    }

    public function test_invalid_credentials_restore_admin_without_another_user_bind()
    {
        $scenario = $this->scenario('invalid_with_admin');
        $this->assertRejection($scenario);
        $this->assertSame(1, $this->bindCount($scenario, self::USER_DN));
        $this->assertSame(2, $this->bindCount($scenario, self::ADMIN_DN));
        $userBinds = $this->bindsFor($scenario, self::USER_DN);
        $this->assertSame(49, $userBinds[0]['errno']);
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertTrue($scenario['bound_after_connect']);
    }

    public function test_empty_username_or_password_does_not_bind()
    {
        $scenario = $this->scenario('empty_secrets');
        $this->assertSame([false, false, false, false, false], $scenario['results']);
        $this->assertSame([], $this->events($scenario, 'bind'));
        $this->assertSame([], $this->events($scenario, 'sasl'));
        $this->assertTrue($scenario['connected_after_connect']);
        $this->assertFalse($scenario['bound_after_connect']);
        $this->assertNull($scenario['exception']);
        $encoded = json_encode($scenario);
        $this->assertStringNotContainsString('user-secret', $encoded);
        $this->assertSettled($scenario);
    }

    public function test_transport_codes_retry_and_other_codes_do_not()
    {
        $cases = $this->scenario('transport_matrix');
        $this->assertCount(8, $cases);
        foreach ($cases as $case) {
            $binds = $this->events($case, 'bind');
            $this->assertSame($case['errno'], $binds[0]['errno']);
            $this->assertSame(self::USER_DN, $binds[0]['dn']);
            $this->assertNull($case['exception']);
            $this->assertFalse($case['bound_after_connect']);
            $this->assertSettled($case);
            $this->assertNoAnonymous($case);
            $this->assertTimeoutBeforeNetwork($case);
            if ($case['expect_retry']) {
                $this->assertTrue($case['result']);
                $this->assertCount(2, $binds);
                $this->assertSame(0, $binds[1]['errno']);
                $this->assertNotSame($binds[0]['id'], $binds[1]['id']);
                $this->assertCount(2, $this->events($case, 'connect'));
                $this->assertFailedOpClosedOnce($case, 'bind');
            } else {
                $this->assertFalse($case['result']);
                $this->assertCount(1, $binds);
                $this->assertCount(1, $this->events($case, 'connect'));
            }
        }
    }

    public function test_starttls_failure_never_binds_in_plaintext()
    {
        $scenario = $this->scenario('starttls');
        $this->assertFalse($scenario['constructed']);
        $message = $this->assertOperational($scenario, 'StartTLS to Active Directory failed');
        $this->assertStringContainsString('tls failed', $message);
        $this->assertStringContainsString('[redacted]', $message);
        $this->assertStringNotContainsString('Bind to Active Directory failed', $message);
        $this->assertCount(1, $this->events($scenario, 'start_tls'));
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertSame([], $this->events($scenario, 'bind'));
        $this->assertTimeoutBeforeNetwork($scenario);
        $this->assertSettled($scenario);
    }

    public function test_ldaps_url_and_configured_port_are_retained()
    {
        $scenario = $this->scenario('ldaps');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['bound_after_connect']);
        $connects = $this->events($scenario, 'connect');
        $this->assertCount(1, $connects);
        $this->assertSame('ldaps://' . self::DC_A, $connects[0]['host']);
        $this->assertSame(1636, $connects[0]['port']);
        $this->assertSame(1, $this->bindCount($scenario, self::ADMIN_DN));
        $this->assertTimeoutBeforeNetwork($scenario);
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
    }

    public function test_sasl_connection_fails_over_before_base_dn_discovery()
    {
        $scenario = $this->scenario('sasl_connect');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['bound_after_connect']);
        $this->assertNull($scenario['base_dn']);
        $this->assertSame([], $this->events($scenario, 'read'));
        $this->assertSame([], $this->events($scenario, 'bind'));
        $sasl = $this->events($scenario, 'sasl');
        $this->assertCount(2, $sasl);
        $this->assertSame(-1, $sasl[0]['errno']);
        $this->assertSame(0, $sasl[1]['errno']);
        $this->assertNotSame($sasl[0]['id'], $sasl[1]['id']);
        foreach ($sasl as $event) {
            $this->assertSame('GSSAPI', $event['mech']);
            $this->assertTrue($event['env_ok']);
        }
        $this->assertTransportFailover($scenario, 'sasl', [self::DC_A, self::DC_B]);
    }

    public function test_sasl_authentication_owns_failover_after_a_lazy_connect()
    {
        $scenario = $this->scenario('sasl_auth');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['connected_after_connect']);
        $this->assertFalse($scenario['bound_after_connect']);
        $this->assertTrue($scenario['result']);
        $this->assertSame([], $this->events($scenario, 'bind'));
        $this->assertSame([], $this->events($scenario, 'read'));
        $sasl = $this->events($scenario, 'sasl');
        $this->assertCount(2, $sasl);
        $this->assertSame(-1, $sasl[0]['errno']);
        $this->assertSame(0, $sasl[1]['errno']);
        foreach ($sasl as $event) {
            $this->assertSame('GSSAPI', $event['mech']);
            $this->assertTrue($event['env_ok']);
        }
        $this->assertTransportFailover($scenario, 'sasl', [self::DC_A, self::DC_B]);
    }

    public function test_service_account_rejection_is_not_retried()
    {
        $scenario = $this->scenario('service_rejected');
        $this->assertFalse($scenario['constructed']);
        $message = $this->assertOperational($scenario, 'Bind to Active Directory failed');
        $this->assertStringContainsString('[redacted]', $message);
        $this->assertStringNotContainsString('unavailable', $message);
        $binds = $this->events($scenario, 'bind');
        $this->assertCount(1, $binds);
        $this->assertSame(49, $binds[0]['errno']);
        $this->assertSame(self::ADMIN_DN, $binds[0]['dn']);
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
    }

    public function test_admin_restoration_failure_is_not_a_successful_login()
    {
        $scenario = $this->scenario('admin_restore_fails');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['result']);
        $this->assertFalse($scenario['bound']);
        $message = $this->assertOperational($scenario, 'Rebind to Active Directory failed');
        $this->assertStringContainsString('[redacted]', $message);
        $this->assertSame(1, $this->bindCount($scenario, self::USER_DN));
        $this->assertSame(2, $this->bindCount($scenario, self::ADMIN_DN));
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
    }

    public function test_prevent_rebind_skips_admin_restoration()
    {
        $scenario = $this->scenario('prevent_rebind');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertTrue($scenario['result']);
        $this->assertTrue($scenario['bound']);
        $this->assertSame(1, $this->bindCount($scenario, self::USER_DN));
        $this->assertSame(1, $this->bindCount($scenario, self::ADMIN_DN));
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
        $this->assertTimeoutBeforeNetwork($scenario);
    }

    public function test_base_dn_is_discovered_on_the_winning_connection()
    {
        $scenario = $this->scenario('base_dn');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertSame('DC=example,DC=com', $scenario['base_dn']);
        $binds = $this->events($scenario, 'bind');
        $this->assertCount(2, $binds);
        $this->assertSame(-1, $binds[0]['errno']);
        $this->assertSame(0, $binds[1]['errno']);
        $reads = $this->events($scenario, 'read');
        $this->assertCount(1, $reads);
        $this->assertSame($binds[1]['id'], $reads[0]['id']);
        $this->assertNotSame($binds[0]['id'], $reads[0]['id']);
        $readAt = $this->eventIndex($scenario, $reads[0]);
        $successAt = $this->eventIndex($scenario, $binds[1]);
        $this->assertGreaterThan($successAt, $readAt);
        $this->assertTransportFailover($scenario, 'bind', [self::DC_A, self::DC_B]);
    }

    public function test_close_is_idempotent()
    {
        $scenario = $this->scenario('close_twice');
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertFalse($scenario['connection_after_close']);
        $this->assertCount(1, $this->events($scenario, 'close'));
        $this->assertCount(1, $this->events($scenario, 'connect'));
        $this->assertSame([], $this->events($scenario, 'bind'));
        $this->assertSettled($scenario);
        $this->assertTimeoutOnOpenHandles($scenario);
    }

    public function test_rejected_password_leaves_the_backend_healthy()
    {
        $plugin = $this->plugin();
        $plugin->useStub = true;
        $plugin->stub = new authad_failover_directory_double();
        $plugin->success = true;
        $before = $this->logText();
        $this->assertFalse($plugin->checkPass('alice', 'user-secret'));
        $this->assertTrue($plugin->success);
        $this->assertSame($before, $this->logText());
    }

    public function test_operational_exception_marks_the_backend_failed()
    {
        $plugin = $this->plugin();
        $plugin->useStub = true;
        $directory = new authad_failover_directory_double();
        $directory->mode = 'throw';
        $plugin->stub = $directory;
        $plugin->success = true;
        $this->expectLogMessage('directory unavailable');
        $this->assertFalse($plugin->checkPass('alice', 'user-secret'));
        $this->assertFalse($plugin->success);
        $log = $this->logText();
        $this->assertStringContainsString('directory unavailable', $log);
        $this->assertStringNotContainsString('user-secret', $log);
    }

    public function test_cached_null_is_not_reconstructed()
    {
        $plugin = $this->plugin();
        $plugin->configure([
            'admin_password' => 'admin-secret',
            'base_dn' => 'DC=corp,DC=test',
            'domain_controllers' => '',
        ]);
        $plugin->success = false;
        $this->setInaccessibleProperty($plugin, 'adldap', ['' => null]);
        $before = $this->logText();
        $this->assertFalse($plugin->callInit(null));
        $this->assertFalse($plugin->success);
        $cache = $this->getInaccessibleProperty($plugin, 'adldap');
        $this->assertArrayHasKey('', $cache);
        $this->assertNull($cache['']);
        $this->assertSame($before, $this->logText());
    }

    public function test_cached_connection_clears_a_stale_failure()
    {
        $plugin = $this->plugin();
        $connection = new stdClass();
        $plugin->success = false;
        $this->setInaccessibleProperty($plugin, 'adldap', ['' => $connection]);
        $before = $this->logText();
        $this->assertSame($connection, $plugin->callInit(null));
        $this->assertTrue($plugin->success);
        $this->assertSame($before, $this->logText());
    }

    public function test_real_initialization_failure_is_cached()
    {
        $plugin = $this->plugin();
        $plugin->configure([
            'admin_password' => 'admin-secret',
            'admin_username' => '',
            'base_dn' => 'DC=corp,DC=test',
            'domain_controllers' => '',
            'debug' => 0,
        ]);
        if (!function_exists('ldap_connect')) {
            $this->expectLogMessage('No LDAP support');
        } else {
            $this->expectLogMessage('Active Directory is unavailable');
        }
        $this->assertFalse($plugin->callInit(null));
        $this->assertFalse($plugin->success);
        $cache = $this->getInaccessibleProperty($plugin, 'adldap');
        $this->assertArrayHasKey('', $cache);
        $this->assertNull($cache['']);
        $logged = $this->logText();
        $this->assertStringNotContainsString('admin-secret', $logged);
        if (function_exists('ldap_connect')) {
            $this->assertStringContainsString('No domain controllers configured', $logged);
        }
        $this->assertFalse($plugin->callInit(null));
        $this->assertFalse($plugin->success);
        $this->assertSame($logged, $this->logText());
        $this->assertNull($this->getInaccessibleProperty($plugin, 'adldap')['']);
    }

    /**
     * @param string $name
     * @return array
     */
    private function scenario($name)
    {
        $report = $this->loadReport();
        if (!empty($report['skip'])) {
            $this->markTestSkipped($report['skip']);
        }
        $this->assertArrayHasKey($name, $report['scenarios']);
        return $report['scenarios'][$name];
    }

    /**
     * @return array
     */
    private function loadReport()
    {
        if (self::$report !== null) {
            return self::$report;
        }

        $probe = $this->runProcess([PHP_BINARY, '-n', '-r', 'exit(function_exists("ldap_connect") ? 2 : 0);']);
        if ($probe['exit'] === 2) {
            self::$report = [
                'skip' => 'ext-ldap is compiled into PHP; LDAP fixtures were not installed and live functions were not redefined',
            ];
            return self::$report;
        }

        $run = $this->runProcess([PHP_BINARY, '-n', __DIR__ . '/ldap_fixture.php']);
        if ($run['exit'] === 2) {
            self::$report = [
                'skip' => trim($run['stderr']) !== ''
                    ? trim($run['stderr'])
                    : 'ext-ldap is compiled into PHP; LDAP fixtures were not installed and live functions were not redefined',
            ];
            return self::$report;
        }

        $this->assertSame(0, $run['exit'], $run['stderr']);
        $this->assertSame('', $run['stderr']);
        foreach (['admin-secret', 'user-secret', 'krb5cc_fixture'] as $secret) {
            $this->assertStringNotContainsString($secret, $run['stdout'] . $run['stderr']);
        }
        $decoded = json_decode($run['stdout'], true);
        $this->assertIsArray($decoded);
        self::$report = $decoded;
        return self::$report;
    }

    /**
     * @param array $command
     * @param int $timeoutSeconds
     * @return array{exit:int,stdout:string,stderr:string}
     */
    private function runProcess(array $command, $timeoutSeconds = 20)
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptors, $pipes);
        $this->assertTrue(is_resource($process));
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $exitCode = null;
        $deadline = microtime(true) + $timeoutSeconds;
        while (microtime(true) < $deadline) {
            $stdout .= (string) stream_get_contents($pipes[1]);
            $stderr .= (string) stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if (!$status['running']) {
                $exitCode = $status['exitcode'];
                break;
            }
            usleep(20000);
        }
        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if ($exitCode === null) {
            proc_terminate($process);
            proc_close($process);
            $this->fail("timed out:\n" . $stderr);
        }
        proc_close($process);
        return ['exit' => $exitCode, 'stdout' => $stdout, 'stderr' => $stderr];
    }

    /**
     * @return auth_plugin_authad_failovertest
     */
    private function plugin()
    {
        global $INPUT;
        unset($_SERVER['REMOTE_USER'], $_SERVER['KRB5CCNAME']);
        $INPUT->server->remove('REMOTE_USER');
        return new auth_plugin_authad_failovertest();
    }

    /**
     * @return string
     */
    private function logText()
    {
        $file = Logger::getInstance(Logger::LOG_ERROR)->getLogfile();
        if (!is_file($file)) {
            return '';
        }
        return (string) file_get_contents($file);
    }

    /**
     * @param array $scenario
     * @param string $op
     * @return array
     */
    private function events(array $scenario, $op)
    {
        $events = [];
        foreach ($scenario['trace'] as $event) {
            if (($event['op'] ?? null) === $op) {
                $events[] = $event;
            }
        }
        return $events;
    }

    /**
     * @param array $scenario
     * @return array
     */
    private function connectHosts(array $scenario)
    {
        $hosts = [];
        foreach ($this->events($scenario, 'connect') as $event) {
            if (!empty($event['ok'])) {
                $hosts[] = $event['host'];
            }
        }
        return $hosts;
    }

    /**
     * @param array $scenario
     * @param string $dn
     * @return array
     */
    private function bindsFor(array $scenario, $dn)
    {
        $binds = [];
        foreach ($this->events($scenario, 'bind') as $event) {
            if ($event['dn'] === $dn) {
                $binds[] = $event;
            }
        }
        return $binds;
    }

    /**
     * @param array $scenario
     * @param string $dn
     * @return int
     */
    private function bindCount(array $scenario, $dn)
    {
        return count($this->bindsFor($scenario, $dn));
    }

    /**
     * @param array $scenario
     * @param array $event
     * @return int
     */
    private function eventIndex(array $scenario, array $event)
    {
        foreach ($scenario['trace'] as $index => $candidate) {
            if ($candidate === $event) {
                return $index;
            }
        }
        $this->fail('event missing from trace');
    }

    /**
     * @param array $scenario
     * @param string $fragment
     * @return string
     */
    private function assertOperational(array $scenario, $fragment)
    {
        $message = (string) $scenario['exception'];
        $this->assertSame('adLDAPException', $scenario['exception_class']);
        $this->assertStringContainsString($fragment, $message);
        $this->assertStringNotContainsString('admin-secret', $message);
        $this->assertStringNotContainsString('user-secret', $message);
        $this->assertStringNotContainsString('krb5cc_fixture', $message);
        $this->assertStringNotContainsString('CLOSED', $message);
        return $message;
    }

    /**
     * @param array $scenario
     * @return void
     */
    private function assertRejection(array $scenario)
    {
        $this->assertTrue($scenario['constructed']);
        $this->assertNull($scenario['exception']);
        $this->assertFalse($scenario['result']);
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
        $this->assertTimeoutBeforeNetwork($scenario);
    }

    /**
     * @param array $scenario
     * @param string $op
     * @param array $hosts
     * @return void
     */
    private function assertTransportFailover(array $scenario, $op, array $hosts)
    {
        $this->assertEqualsCanonicalizing($hosts, $this->connectHosts($scenario));
        $this->assertTimeoutBeforeNetwork($scenario);
        $this->assertFailedOpClosedOnce($scenario, $op);
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
    }

    /**
     * @param array $scenario
     * @param array $hosts
     * @param string $op
     * @return void
     */
    private function assertTriedOnce(array $scenario, array $hosts, $op)
    {
        $this->assertEqualsCanonicalizing($hosts, $this->connectHosts($scenario));
        $operations = $this->events($scenario, $op);
        $this->assertCount(count($hosts), $operations);
        foreach ($operations as $event) {
            $this->assertNotSame(0, $event['errno']);
        }
        $this->assertTimeoutBeforeNetwork($scenario);
        $this->assertSettled($scenario);
        $this->assertNoAnonymous($scenario);
    }

    /**
     * @param array $scenario
     * @param string $op
     * @return void
     */
    private function assertFailedOpClosedOnce(array $scenario, $op)
    {
        $failed = [];
        $succeeded = 0;
        $closed = [];
        foreach ($scenario['trace'] as $event) {
            $id = $event['id'] ?? null;
            if ($event['op'] === 'close') {
                $closed[$id] = ($closed[$id] ?? 0) + 1;
            }
            if ($event['op'] === 'connect' && !empty($event['ok'])) {
                foreach ($failed as $failedId) {
                    $this->assertSame(1, $closed[$failedId] ?? 0);
                }
            }
            if ($event['op'] === $op) {
                if (($event['errno'] ?? 0) === 0) {
                    $succeeded++;
                } else {
                    $failed[] = $id;
                }
            }
        }
        $this->assertNotEmpty($failed);
        $this->assertGreaterThan(0, $succeeded);
        foreach ($failed as $failedId) {
            $this->assertSame(1, $closed[$failedId] ?? 0);
        }
    }

    /**
     * @param array $scenario
     * @return void
     */
    private function assertNoAnonymous(array $scenario)
    {
        foreach ($this->events($scenario, 'bind') as $event) {
            $this->assertFalse($event['empty_password']);
            $this->assertNotSame('', $event['dn']);
        }
    }

    /**
     * @param array $scenario
     * @return void
     */
    private function assertSettled(array $scenario)
    {
        $this->assertSame(0, $scenario['open_handles']);
        $closed = [];
        foreach ($this->events($scenario, 'close') as $event) {
            $closed[$event['id']] = ($closed[$event['id']] ?? 0) + 1;
        }
        foreach ($closed as $count) {
            $this->assertSame(1, $count);
        }
        foreach ($this->events($scenario, 'connect') as $event) {
            if (empty($event['ok']) || $event['id'] === null) {
                continue;
            }
            $this->assertSame(1, $closed[$event['id']] ?? 0);
        }
    }

    /**
     * @param array $scenario
     * @return void
     */
    private function assertTimeoutBeforeNetwork(array $scenario)
    {
        $this->assertTimeoutOnOpenHandles($scenario);
        $byId = [];
        foreach ($scenario['trace'] as $index => $event) {
            if (!isset($event['id']) || $event['id'] === null) {
                continue;
            }
            $event['_index'] = $index;
            $byId[$event['id']][] = $event;
        }
        $checked = 0;
        foreach ($byId as $events) {
            $timeoutAt = null;
            $networkAt = null;
            foreach ($events as $event) {
                if ($timeoutAt === null && ($event['name'] ?? '') === 'network_timeout') {
                    $this->assertSame(2, $event['value']);
                    $timeoutAt = $event['_index'];
                }
                if ($networkAt === null && in_array($event['op'], ['bind', 'sasl', 'start_tls'], true)) {
                    $networkAt = $event['_index'];
                }
            }
            if ($networkAt !== null) {
                $this->assertNotNull($timeoutAt);
                $this->assertLessThan($networkAt, $timeoutAt);
                $checked++;
            }
        }
        $this->assertGreaterThan(0, $checked);
    }

    /**
     * @param array $scenario
     * @return void
     */
    private function assertTimeoutOnOpenHandles(array $scenario)
    {
        $needs = [];
        foreach ($this->events($scenario, 'connect') as $event) {
            if (!empty($event['ok'])) {
                $needs[$event['id']] = false;
            }
        }
        foreach ($scenario['trace'] as $event) {
            if (($event['name'] ?? '') !== 'network_timeout') {
                continue;
            }
            $this->assertSame(2, $event['value']);
            if (isset($needs[$event['id']])) {
                $needs[$event['id']] = true;
            }
        }
        foreach ($needs as $id => $seen) {
            $this->assertTrue($seen, 'missing network timeout on handle ' . $id);
        }
    }
}
