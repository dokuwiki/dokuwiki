#!/usr/bin/env php
<?php

use dokuwiki\Extension\CLIPlugin;
use dokuwiki\Extension\PluginController;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Colors;
use splitbrain\phpcli\Options;
use splitbrain\phpcli\TableFormatter;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

class PluginCLI extends CLI
{
    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options)
    {
        $options->setHelp('Excecutes Plugin command line tools');
        $options->registerArgument('plugin', 'The plugin CLI you want to run. Leave off to see list', false);
    }

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param Options $options
     * @return void
     */
    protected function main(Options $options)
    {
        global $argv;
        $argv = $options->getArgs();

        if ($argv) {
            $plugin = $this->loadPlugin($argv[0]);
            if ($plugin instanceof CLIPlugin) {
                $plugin->run();
            } else {
                $this->fatal('Command {cmd} not found.', ['cmd' => $argv[0]]);
            }
        } else {
            echo $options->help();
            $this->listPlugins();
        }
    }

    /**
     * List available plugins
     */
    protected function listPlugins()
    {
        /** @var PluginController $plugin_controller */
        global $plugin_controller;

        echo "\n";
        echo "\n";
        echo $this->colors->wrap('AVAILABLE PLUGINS:', Colors::C_BROWN);
        echo "\n";

        $list = $plugin_controller->getList('cli');
        sort($list);
        if ($list === []) {
            echo $this->colors->wrap("  No plugins providing CLI components available\n", Colors::C_RED);
        } else {
            $tf = new TableFormatter($this->colors);

            foreach ($list as $name) {
                $plugin = $this->loadPlugin($name);
                if (!$plugin instanceof CLIPlugin) continue;
                $info = $plugin->getInfo();

                echo $tf->format(
                    [2, '30%', '*'],
                    ['', $name, $info['desc']],
                    ['', Colors::C_CYAN, '']
                );
            }
        }
    }

    /**
     * Instantiate a CLI plugin
     *
     * @param string $name
     * @return CLIPlugin|null
     */
    protected function loadPlugin($name)
    {
        [$basename] = explode('_', $name); // might be a sub component
        if (plugin_isdisabled($basename)) return null;

        // execute the plugin CLI
        $class = "cli_plugin_$name";
        if (class_exists($class)) {
            return new $class();
        }
        return null;
    }
}

// Main
$cli = new PluginCLI();
$cli->run();
