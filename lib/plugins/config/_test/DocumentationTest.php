<?php

namespace dokuwiki\plugin\config\test;

use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\plugin\config\core\Configuration;
use dokuwiki\plugin\config\core\Setting\SettingFieldset;
use dokuwiki\plugin\config\core\Setting\SettingHidden;

/**
 * Ensure config options have documentation at dokuwiki.org
 *
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 * @group internet
 */
class DocumentationTest extends \DokuWikiTest
{
    protected $http;

    /**
     * @return \Generator|array[]
     */
    public function provideSettings()
    {
        $configuration = new Configuration();

        foreach ($configuration->getSettings() as $setting) {
            if (is_a($setting, SettingHidden::class)) continue;
            if (is_a($setting, SettingFieldset::class)) continue;

            $key = $setting->getKey();
            $pretty = $setting->getPrettyKey();
            if (!preg_match('/ href="(.*?)"/', $pretty, $m)) continue;
            $url = $m[1];

            yield [$key, $url];
        }
    }

    /**
     * @dataProvider provideSettings
     * @param string $key Settingskey
     * @param string $url Documentation URL
     */
    public function testDocs($key, $url)
    {
        if ($this->http === null) {
            $this->http = new DokuHTTPClient();
            $this->http->timeout = 15;
        }

        echo ini_get('max_execution_time');

        $check = $this->http->get($url);
        $fail = (bool)strpos($check, 'topic does not exist');
        $msg = "Setting '$key' should have documentation at $url.";
        $this->assertFalse($fail, $msg . ' ' . $this->http->status . ' ' . $this->http->error);
    }
}
