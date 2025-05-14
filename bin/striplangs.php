#!/usr/bin/env php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Remove unwanted languages from a DokuWiki install
 */
class StripLangsCLI extends CLI
{
    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options)
    {

        $options->setHelp(
            'Remove all languages from the installation, besides the ones specified. English language ' .
            'is never removed!'
        );

        $options->registerOption(
            'keep',
            'Comma separated list of languages to keep in addition to English.',
            'k',
            'langcodes'
        );
        $options->registerOption(
            'english-only',
            'Remove all languages except English',
            'e'
        );
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
        if ($options->getOpt('keep')) {
            $keep = explode(',', $options->getOpt('keep'));
            if (!in_array('en', $keep)) $keep[] = 'en';
        } elseif ($options->getOpt('english-only')) {
            $keep = ['en'];
        } else {
            echo $options->help();
            exit(0);
        }

        // Kill all language directories in /inc/lang and /lib/plugins besides those in $langs array
        $this->stripDirLangs(realpath(__DIR__ . '/../inc/lang'), $keep);
        $this->processExtensions(realpath(__DIR__ . '/../lib/plugins'), $keep);
        $this->processExtensions(realpath(__DIR__ . '/../lib/tpl'), $keep);
    }

    /**
     * Strip languages from extensions
     *
     * @param string $path path to plugin or template dir
     * @param array $keep_langs languages to keep
     */
    protected function processExtensions($path, $keep_langs)
    {
        if (is_dir($path)) {
            $entries = scandir($path);

            foreach ($entries as $entry) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir($path . '/' . $entry)) {
                        $plugin_langs = $path . '/' . $entry . '/lang';

                        if (is_dir($plugin_langs)) {
                            $this->stripDirLangs($plugin_langs, $keep_langs);
                        }
                    }
                }
            }
        }
    }

    /**
     * Strip languages from path
     *
     * @param string $path path to lang dir
     * @param array $keep_langs languages to keep
     */
    protected function stripDirLangs($path, $keep_langs)
    {
        $dir = dir($path);

        while (($cur_dir = $dir->read()) !== false) {
            if ($cur_dir != '.' && $cur_dir != '..' && is_dir($path . '/' . $cur_dir)) {
                if (!in_array($cur_dir, $keep_langs, true)) {
                    io_rmdir($path . '/' . $cur_dir, true);
                }
            }
        }
        $dir->close();
    }
}

$cli = new StripLangsCLI();
$cli->run();
