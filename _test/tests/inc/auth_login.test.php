<?php

use dokuwiki\Extension\AuthPlugin;

/**
 * Minimal backend for auth_login() message selection.
 */
class auth_login_backend_double extends AuthPlugin
{
    /** @var bool */
    private $accept;

    /**
     * @param bool $accept
     * @param bool $healthy
     */
    public function __construct($accept, $healthy)
    {
        parent::__construct();
        $this->accept = $accept;
        $this->success = $healthy;
    }

    /** @inheritDoc */
    public function checkPass($user, $pass)
    {
        return $this->accept;
    }

    /** @inheritDoc */
    public function getUserData($user, $requireGroups = true)
    {
        return [
            'name' => 'Alice Tester',
            'mail' => 'alice@example.com',
            'grps' => ['user'],
        ];
    }
}

/**
 * auth_login() reports a directory outage separately from a bad password.
 */
class auth_login_test extends DokuWikiTest
{
    /** @var string */
    private $loginOutput = '';

    public function setUp(): void
    {
        parent::setUp();
        global $MSG, $USERINFO, $INPUT;
        $MSG = [];
        $USERINFO = [];
        unset($_SERVER['REMOTE_USER']);
        $INPUT->server->remove('REMOTE_USER');
        unset($_SESSION[DOKU_COOKIE], $_COOKIE[DOKU_COOKIE]);
        if (!headers_sent()) {
            header_remove('Status');
            http_response_code(200);
        }
    }

    public function test_bad_password_reports_badlogin_and_logs_off()
    {
        global $auth, $lang;
        $this->presetSession();
        $auth = new auth_login_backend_double(false, true);
        $this->assertFalse($this->attemptLogin('alice', 'wrong', false, false));
        $messages = $this->renderedMessages();
        $this->assertStringContainsString($lang['badlogin'], $messages);
        $this->assertStringNotContainsString($lang['authtempfail'], $messages);
        $this->assertLoggedOff();
        $this->assertFailureStatus();
    }

    public function test_backend_outage_reports_authtempfail_and_logs_off()
    {
        global $auth, $lang;
        $this->presetSession();
        $auth = new auth_login_backend_double(false, false);
        $this->assertFalse($this->attemptLogin('alice', 'wrong', false, false));
        $messages = $this->renderedMessages();
        $this->assertStringContainsString($lang['authtempfail'], $messages);
        $this->assertStringNotContainsString($lang['badlogin'], $messages);
        $this->assertLoggedOff();
        $this->assertFailureStatus();
    }

    public function test_silent_failure_suppresses_messages_and_logs_off()
    {
        global $auth, $MSG, $lang;
        foreach ([true, false] as $healthy) {
            $this->presetSession();
            $MSG = [];
            $status = http_response_code();
            $auth = new auth_login_backend_double(false, $healthy);
            $this->assertFalse($this->attemptLogin('alice', 'wrong', false, true));
            $this->assertStringNotContainsString($lang['badlogin'], $this->renderedMessages());
            $this->assertStringNotContainsString($lang['authtempfail'], $this->renderedMessages());
            $this->assertLoggedOff();
            $this->assertSame($status, http_response_code());
        }
    }

    public function test_successful_login_sets_session_and_cookie()
    {
        global $auth, $INPUT, $lang;
        $auth = new auth_login_backend_double(true, true);
        $headersBefore = $this->headerList();
        $this->assertTrue($this->attemptLogin('alice', 'secret', true, false));
        $this->assertSame('alice', $INPUT->server->str('REMOTE_USER'));
        $this->assertStringNotContainsString($lang['badlogin'], $this->renderedMessages());
        $this->assertStringNotContainsString($lang['authtempfail'], $this->renderedMessages());

        $session = $_SESSION[DOKU_COOKIE]['auth'];
        $this->assertSame('alice', $session['user']);
        $this->assertSame('Alice Tester', $session['info']['name']);
        $this->assertSame('alice@example.com', $session['info']['mail']);
        $this->assertSame(['user'], $session['info']['grps']);
        $this->assertNotSame('', $session['buid']);
        $this->assertGreaterThan(0, $session['time']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $session['pass']);
        $this->assertNotSame(sha1('secret'), $session['pass']);

        $added = array_diff($this->headerList(), $headersBefore);
        $cookie = '';
        foreach ($added as $header) {
            if (stripos($header, 'Set-Cookie:') === 0 && str_contains($header, DOKU_COOKIE . '=')) {
                $cookie = urldecode($header);
            }
        }
        // The CLI SAPI only lists sent headers through xdebug, so the cookie is checked when visible.
        if ($cookie !== '') {
            $this->assertStringContainsString(base64_encode('alice'), $cookie);
        }
    }

    /**
     * Login while this process may already have emitted bootstrap output.
     *
     * @param string $user
     * @param string $pass
     * @param bool $sticky
     * @param bool $silent
     * @return bool
     */
    private function attemptLogin($user, $pass, $sticky, $silent)
    {
        $this->loginOutput = '';
        ob_start();
        set_error_handler(static function ($severity, $message) {
            if ($severity === E_WARNING && str_contains($message, 'Cannot modify header information')) {
                return true;
            }
            return false;
        });
        try {
            return auth_login($user, $pass, $sticky, $silent);
        } finally {
            restore_error_handler();
            $this->loginOutput = (string) ob_get_clean();
        }
    }

    /**
     * @return void
     */
    private function assertFailureStatus()
    {
        if (headers_sent()) {
            return;
        }
        $this->assertSame(403, http_response_code());
    }

    /**
     * @return array
     */
    private function headerList()
    {
        if (function_exists('xdebug_get_headers')) {
            return xdebug_get_headers();
        }
        return headers_list();
    }

    /**
     * @return void
     */
    private function presetSession()
    {
        global $INPUT;
        $INPUT->server->set('REMOTE_USER', 'alice');
        $_SESSION[DOKU_COOKIE]['auth']['user'] = 'alice';
        $_SESSION[DOKU_COOKIE]['auth']['pass'] = 'hash';
        $_SESSION[DOKU_COOKIE]['auth']['info'] = ['name' => 'Alice Tester'];
    }

    /**
     * @return void
     */
    private function assertLoggedOff()
    {
        global $INPUT;
        $session = $_SESSION[DOKU_COOKIE]['auth'] ?? [];
        $this->assertArrayNotHasKey('user', $session);
        $this->assertArrayNotHasKey('pass', $session);
        $this->assertArrayNotHasKey('info', $session);
        $this->assertSame('', $INPUT->server->str('REMOTE_USER'));
    }

    /**
     * Messages kept in $MSG, or already rendered because headers were sent.
     *
     * @return string
     */
    private function renderedMessages()
    {
        global $MSG;
        $text = $this->loginOutput;
        foreach ((array) $MSG as $item) {
            if (is_array($item) && isset($item['msg'])) {
                $text .= "\n" . $item['msg'];
            }
        }
        return $text;
    }
}
