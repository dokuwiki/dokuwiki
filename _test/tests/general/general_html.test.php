<?php

/**
 * Check some page output for validity
 *
 * @group internet
 */
class general_html_test extends DokuWikiTest
{
    /** @var string[] we consider these hits shortcomings in the validator and not errors */
    protected $allowedErrors = [
        'The string “ugc” is not a registered keyword.',
    ];

    /**
     * List of requests to check for validity
     *
     * @return array
     */
    public function requestProvider()
    {
        return [
            ['/doku.php', 'GET', []],
            ['/doku.php', 'GET', ['do' => 'recent']],
            ['/doku.php', 'GET', ['do' => 'index']],
            ['/doku.php', 'GET', ['do' => 'login']],
            ['/doku.php', 'GET', ['do' => 'search', 'q' => 'wiki']],
            ['/doku.php', 'GET', ['id' => 'wiki:syntax']],
            ['/doku.php', 'GET', ['id' => 'wiki:syntax', 'ns' => 'wiki', 'image' => 'wiki:dokuwiki-128.png', 'do' => 'media']],
            ['/lib/exe/detail.php', 'GET', ['id' => 'wiki:syntax', 'media' => 'wiki:dokuwiki-128.png']],
        ];
    }

    /**
     * Sends the given HTML to the validator and returns the result
     *
     * @param string $html
     * @return array
     * @throws Exception when communication failed
     */
    protected function validate($html)
    {
        $http = new \dokuwiki\HTTP\DokuHTTPClient();
        $http->headers['Content-Type'] = 'text/html; charset=utf-8';
        $result = $http->post('https://validator.w3.org/nu/?out=json&level=error', $html);

        if ($result === false) {
            throw new \Exception($http->error);
        }

        $result = json_decode($result, true);
        if ($result === null) {
            throw new \Exception('could not decode JSON');
        }

        return $result;
    }

    /**
     * Reformat the errors for nicer display in output
     *
     * @param array $result
     * @return string[]
     */
    protected function listErrors($result)
    {
        $errors = [];
        foreach ($result['messages'] as $msg) {
            if ($this->isAllowedError($msg['message'])) continue;
            $errors[] = "☛ " . $msg['message'] . "\n" . $msg['extract'] . "\n";
        }
        return $errors;
    }

    /**
     * Is the given string an allowed error that should be skipped?
     *
     * @param string $string
     * @return bool
     */
    protected function isAllowedError($string)
    {
        $re = join('|', array_map('preg_quote_cb', $this->allowedErrors));
        return (bool)preg_match("/$re/", $string);
    }

    /**
     * @dataProvider requestProvider
     * @param string $url
     * @param string $method
     * @param array $data
     * @group internet
     */
    public function test_Validity($url, $method, $data)
    {
        $request = new TestRequest();
        if ($method == 'GET') {
            $response = $request->get($data, $url);
        } elseif ($method == 'POST') {
            $response = $request->post($data, $url);
        } else {
            throw new \RuntimeException("unknown method given: $method");
        }

        $html = $response->getContent();
        try {
            $result = $this->validate($html);
        } catch (\Exception $e) {
            $this->markTestSkipped($e->getMessage());
            return;
        }

        $errors = $this->listErrors($result);
        $info = "Invalid HTML found:\n" . join("\n", $errors);

        $this->assertEquals(0, count($errors), $info);
    }
}
