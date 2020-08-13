#!/usr/bin/env php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Checkout and commit pages from the command line while maintaining the history
 */
class PageCLI extends CLI {

    protected $force = false;
    protected $username = '';

    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options) {
        /* global */
        $options->registerOption(
            'force',
            'force obtaining a lock for the page (generally bad idea)',
            'f'
        );
        $options->registerOption(
            'user',
            'work as this user. defaults to current CLI user',
            'u',
            'username'
        );
        $options->setHelp(
            'Utility to help command line Dokuwiki page editing, allow ' .
            'pages to be checked out for editing then committed after changes'
        );

        /* checkout command */
        $options->registerCommand(
            'checkout',
            'Checks out a file from the repository, using the wiki id and obtaining ' .
            'a lock for the page. ' . "\n" .
            'If a working_file is specified, this is where the page is copied to. ' .
            'Otherwise defaults to the same as the wiki page in the current ' .
            'working directory.'
        );
        $options->registerArgument(
            'wikipage',
            'The wiki page to checkout',
            true,
            'checkout'
        );
        $options->registerArgument(
            'workingfile',
            'How to name the local checkout',
            false,
            'checkout'
        );

        /* commit command */
        $options->registerCommand(
            'commit',
            'Checks in the working_file into the repository using the specified ' .
            'wiki id, archiving the previous version.'
        );
        $options->registerArgument(
            'workingfile',
            'The local file to commit',
            true,
            'commit'
        );
        $options->registerArgument(
            'wikipage',
            'The wiki page to create or update',
            true,
            'commit'
        );
        $options->registerOption(
            'message',
            'Summary describing the change (required)',
            'm',
            'summary',
            'commit'
        );
        $options->registerOption(
            'trivial',
            'minor change',
            't',
            false,
            'commit'
        );

        /* lock command */
        $options->registerCommand(
            'lock',
            'Obtains or updates a lock for a wiki page'
        );
        $options->registerArgument(
            'wikipage',
            'The wiki page to lock',
            true,
            'lock'
        );

        /* unlock command */
        $options->registerCommand(
            'unlock',
            'Removes a lock for a wiki page.'
        );
        $options->registerArgument(
            'wikipage',
            'The wiki page to unlock',
            true,
            'unlock'
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
    protected function main(Options $options) {
        $this->force = $options->getOpt('force', false);
        $this->username = $options->getOpt('user', $this->getUser());

        $command = $options->getCmd();
        $args = $options->getArgs();
        switch($command) {
            case 'checkout':
                $wiki_id = array_shift($args);
                $localfile = array_shift($args);
                $this->commandCheckout($wiki_id, $localfile);
                break;
            case 'commit':
                $localfile = array_shift($args);
                $wiki_id = array_shift($args);
                $this->commandCommit(
                    $localfile,
                    $wiki_id,
                    $options->getOpt('message', ''),
                    $options->getOpt('trivial', false)
                );
                break;
            case 'lock':
                $wiki_id = array_shift($args);
                $this->obtainLock($wiki_id);
                $this->success("$wiki_id locked");
                break;
            case 'unlock':
                $wiki_id = array_shift($args);
                $this->clearLock($wiki_id);
                $this->success("$wiki_id unlocked");
                break;
            default:
                echo $options->help();
        }
    }

    /**
     * Check out a file
     *
     * @param string $wiki_id
     * @param string $localfile
     */
    protected function commandCheckout($wiki_id, $localfile) {
        global $conf;

        $wiki_id = cleanID($wiki_id);
        $wiki_fn = wikiFN($wiki_id);

        if(!file_exists($wiki_fn)) {
            $this->fatal("$wiki_id does not yet exist");
        }

        if(empty($localfile)) {
            $localfile = getcwd() . '/' . \dokuwiki\Utf8\PhpString::basename($wiki_fn);
        }

        if(!file_exists(dirname($localfile))) {
            $this->fatal("Directory " . dirname($localfile) . " does not exist");
        }

        if(stristr(realpath(dirname($localfile)), realpath($conf['datadir'])) !== false) {
            $this->fatal("Attempt to check out file into data directory - not allowed");
        }

        $this->obtainLock($wiki_id);

        if(!copy($wiki_fn, $localfile)) {
            $this->clearLock($wiki_id);
            $this->fatal("Unable to copy $wiki_fn to $localfile");
        }

        $this->success("$wiki_id > $localfile");
    }

    /**
     * Save a file as a new page revision
     *
     * @param string $localfile
     * @param string $wiki_id
     * @param string $message
     * @param bool $minor
     */
    protected function commandCommit($localfile, $wiki_id, $message, $minor) {
        $wiki_id = cleanID($wiki_id);
        $message = trim($message);

        if(!file_exists($localfile)) {
            $this->fatal("$localfile does not exist");
        }

        if(!is_readable($localfile)) {
            $this->fatal("Cannot read from $localfile");
        }

        if(!$message) {
            $this->fatal("Summary message required");
        }

        $this->obtainLock($wiki_id);

        saveWikiText($wiki_id, file_get_contents($localfile), $message, $minor);

        $this->clearLock($wiki_id);

        $this->success("$localfile > $wiki_id");
    }

    /**
     * Lock the given page or exit
     *
     * @param string $wiki_id
     */
    protected function obtainLock($wiki_id) {
        if($this->force) $this->deleteLock($wiki_id);

        $_SERVER['REMOTE_USER'] = $this->username;

        if(checklock($wiki_id)) {
            $this->error("Page $wiki_id is already locked by another user");
            exit(1);
        }

        lock($wiki_id);

        if(checklock($wiki_id)) {
            $this->error("Unable to obtain lock for $wiki_id ");
            var_dump(checklock($wiki_id));
            exit(1);
        }
    }

    /**
     * Clear the lock on the given page
     *
     * @param string $wiki_id
     */
    protected function clearLock($wiki_id) {
        if($this->force) $this->deleteLock($wiki_id);

        $_SERVER['REMOTE_USER'] = $this->username;
        if(checklock($wiki_id)) {
            $this->error("Page $wiki_id is locked by another user");
            exit(1);
        }

        unlock($wiki_id);

        if(file_exists(wikiLockFN($wiki_id))) {
            $this->error("Unable to clear lock for $wiki_id");
            exit(1);
        }
    }

    /**
     * Forcefully remove a lock on the page given
     *
     * @param string $wiki_id
     */
    protected function deleteLock($wiki_id) {
        $wikiLockFN = wikiLockFN($wiki_id);

        if(file_exists($wikiLockFN)) {
            if(!unlink($wikiLockFN)) {
                $this->error("Unable to delete $wikiLockFN");
                exit(1);
            }
        }
    }

    /**
     * Get the current user's username from the environment
     *
     * @return string
     */
    protected function getUser() {
        $user = getenv('USER');
        if(empty ($user)) {
            $user = getenv('USERNAME');
        } else {
            return $user;
        }
        if(empty ($user)) {
            $user = 'admin';
        }
        return $user;
    }
}

// Main
$cli = new PageCLI();
$cli->run();
