<?php
/**
 * Helper class to provide basic functionality for tests
 */
abstract class DokuWikiTest extends PHPUnit_Framework_TestCase {
    /**
     * Reset the DokuWiki environment before each test run
     *
     * Makes sure loaded config, language and plugins are correct
     */
    function setUp() {
        // reload config
        global $conf, $config_cascade;
        $conf = array();
        foreach (array('default','local','protected') as $config_group) {
            if (empty($config_cascade['main'][$config_group])) continue;
            foreach ($config_cascade['main'][$config_group] as $config_file) {
                if (@file_exists($config_file)) {
                    include($config_file);
                }
            }
        }

        // reload license config
        global $license;
        $license = array();

        // load the license file(s)
        foreach (array('default','local') as $config_group) {
            if (empty($config_cascade['license'][$config_group])) continue;
            foreach ($config_cascade['license'][$config_group] as $config_file) {
                if(@file_exists($config_file)){
                    include($config_file);
                }
            }
        }

        // make real paths and check them
        init_paths();
        init_files();

        // reset loaded plugins
        global $plugin_controller_class, $plugin_controller;
        $plugin_controller = new $plugin_controller_class();
        global $EVENT_HANDLER;
        $EVENT_HANDLER = new Doku_Event_Handler();

        // reload language
        $local = $conf['lang'];
        trigger_event('INIT_LANG_LOAD', $local, 'init_lang', true);
    }
}
