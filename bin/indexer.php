#!/usr/bin/env php
<?php

use dokuwiki\Logger;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Update the Search Index from command line
 */
class IndexerCLI extends CLI
{
    private $quiet = false;
    private $clear = false;

    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options)
    {
        $options->setHelp(
            'Updates the searchindex by indexing all new or changed pages. When the -c option is ' .
            'given the index is cleared first.'
        );

        $options->registerOption(
            'clear',
            'clear the index before updating',
            'c'
        );
        $options->registerOption(
            'quiet',
            'DEPRECATED',
            'q'
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
        $this->clear = $options->getOpt('clear');
        $this->quiet = $options->getOpt('quiet');

        if ($this->quiet) {
            Logger::deprecated('Calling bin/indexer.php with -q/--quiet is deprecated. Use --loglevel instead.');
            $this->setLogLevel('emergency');
        }

        if ($this->clear) $this->clearindex();

        $this->update();
    }

    /**
     * Update the index
     */
    protected function update()
    {
        global $conf;
        $data = [];
        $this->notice('Searching pages...');
        search($data, $conf['datadir'], 'search_allpages', ['skipacl' => true]);
        $this->info(count($data) . ' pages found.');

        foreach ($data as $val) {
            $this->index($val['id']);
        }
    }

    /**
     * Index the given page
     *
     * @param string $id
     */
    protected function index($id)
    {
        $this->notice("$id indexing...");
        try {
            if (idx_addPage($id, isset($this->loglevel['info']), $this->clear)) {
                $this->success("$id indexed.");
            } else {
                $this->info("$id index not updated.");
            }
        } catch (Throwable $e) {
            $this->error("$id indexing error: " . $e->getMessage());
            $this->debug($e->getTraceAsString());
            return;
        }
    }

    /**
     * Clear all index files
     */
    protected function clearindex()
    {
        $this->notice('Clearing index...');
        idx_get_indexer()->clear();
        $this->success('Index cleared.');
    }
}

// Main
$cli = new IndexerCLI();
$cli->run();
