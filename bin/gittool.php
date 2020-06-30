#!/usr/bin/env php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Easily manage DokuWiki git repositories
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class GitToolCLI extends CLI {

    /**
     * Register options and arguments on the given $options object
     *
     * @param Options $options
     * @return void
     */
    protected function setup(Options $options) {
        $options->setHelp(
            "Manage git repositories for DokuWiki and its plugins and templates.\n\n" .
            "$> ./bin/gittool.php clone gallery template:ach\n" .
            "$> ./bin/gittool.php repos\n" .
            "$> ./bin/gittool.php origin -v"
        );

        $options->registerArgument(
            'command',
            'Command to execute. See below',
            true
        );

        $options->registerCommand(
            'clone',
            'Tries to install a known plugin or template (prefix with template:) via git. Uses the DokuWiki.org ' .
            'plugin repository to find the proper git repository. Multiple extensions can be given as parameters'
        );
        $options->registerArgument(
            'extension',
            'name of the extension to install, prefix with \'template:\' for templates',
            true,
            'clone'
        );

        $options->registerCommand(
            'install',
            'The same as clone, but when no git source repository can be found, the extension is installed via ' .
            'download'
        );
        $options->registerArgument(
            'extension',
            'name of the extension to install, prefix with \'template:\' for templates',
            true,
            'install'
        );

        $options->registerCommand(
            'repos',
            'Lists all git repositories found in this DokuWiki installation'
        );

        $options->registerCommand(
            '*',
            'Any unknown commands are assumed to be arguments to git and will be executed in all repositories ' .
            'found within this DokuWiki installation'
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
        $command = $options->getCmd();
        $args = $options->getArgs();
        if(!$command) $command = array_shift($args);

        switch($command) {
            case '':
                echo $options->help();
                break;
            case 'clone':
                $this->cmdClone($args);
                break;
            case 'install':
                $this->cmdInstall($args);
                break;
            case 'repo':
            case 'repos':
                $this->cmdRepos();
                break;
            default:
                $this->cmdGit($command, $args);
        }
    }

    /**
     * Tries to install the given extensions using git clone
     *
     * @param array $extensions
     */
    public function cmdClone($extensions) {
        $errors = array();
        $succeeded = array();

        foreach($extensions as $ext) {
            $repo = $this->getSourceRepo($ext);

            if(!$repo) {
                $this->error("could not find a repository for $ext");
                $errors[] = $ext;
            } else {
                if($this->cloneExtension($ext, $repo)) {
                    $succeeded[] = $ext;
                } else {
                    $errors[] = $ext;
                }
            }
        }

        echo "\n";
        if($succeeded) $this->success('successfully cloned the following extensions: ' . join(', ', $succeeded));
        if($errors) $this->error('failed to clone the following extensions: ' . join(', ', $errors));
    }

    /**
     * Tries to install the given extensions using git clone with fallback to install
     *
     * @param array $extensions
     */
    public function cmdInstall($extensions) {
        $errors = array();
        $succeeded = array();

        foreach($extensions as $ext) {
            $repo = $this->getSourceRepo($ext);

            if(!$repo) {
                $this->info("could not find a repository for $ext");
                if($this->downloadExtension($ext)) {
                    $succeeded[] = $ext;
                } else {
                    $errors[] = $ext;
                }
            } else {
                if($this->cloneExtension($ext, $repo)) {
                    $succeeded[] = $ext;
                } else {
                    $errors[] = $ext;
                }
            }
        }

        echo "\n";
        if($succeeded) $this->success('successfully installed the following extensions: ' . join(', ', $succeeded));
        if($errors) $this->error('failed to install the following extensions: ' . join(', ', $errors));
    }

    /**
     * Executes the given git command in every repository
     *
     * @param $cmd
     * @param $arg
     */
    public function cmdGit($cmd, $arg) {
        $repos = $this->findRepos();

        $shell = array_merge(array('git', $cmd), $arg);
        $shell = array_map('escapeshellarg', $shell);
        $shell = join(' ', $shell);

        foreach($repos as $repo) {
            if(!@chdir($repo)) {
                $this->error("Could not change into $repo");
                continue;
            }

            $this->info("executing $shell in $repo");
            $ret = 0;
            system($shell, $ret);

            if($ret == 0) {
                $this->success("git succeeded in $repo");
            } else {
                $this->error("git failed in $repo");
            }
        }
    }

    /**
     * Simply lists the repositories
     */
    public function cmdRepos() {
        $repos = $this->findRepos();
        foreach($repos as $repo) {
            echo "$repo\n";
        }
    }

    /**
     * Install extension from the given download URL
     *
     * @param string $ext
     * @return bool|null
     */
    private function downloadExtension($ext) {
        /** @var helper_plugin_extension_extension $plugin */
        $plugin = plugin_load('helper', 'extension_extension');
        if(!$ext) die("extension plugin not available, can't continue");

        $plugin->setExtension($ext);

        $url = $plugin->getDownloadURL();
        if(!$url) {
            $this->error("no download URL for $ext");
            return false;
        }

        $ok = false;
        try {
            $this->info("installing $ext via download from $url");
            $ok = $plugin->installFromURL($url);
        } catch(Exception $e) {
            $this->error($e->getMessage());
        }

        if($ok) {
            $this->success("installed $ext via download");
            return true;
        } else {
            $this->success("failed to install $ext via download");
            return false;
        }
    }

    /**
     * Clones the extension from the given repository
     *
     * @param string $ext
     * @param string $repo
     * @return bool
     */
    private function cloneExtension($ext, $repo) {
        if(substr($ext, 0, 9) == 'template:') {
            $target = fullpath(tpl_incdir() . '../' . substr($ext, 9));
        } else {
            $target = DOKU_PLUGIN . $ext;
        }

        $this->info("cloning $ext from $repo to $target");
        $ret = 0;
        system("git clone $repo $target", $ret);
        if($ret === 0) {
            $this->success("cloning of $ext succeeded");
            return true;
        } else {
            $this->error("cloning of $ext failed");
            return false;
        }
    }

    /**
     * Returns all git repositories in this DokuWiki install
     *
     * Looks in root, template and plugin directories only.
     *
     * @return array
     */
    private function findRepos() {
        $this->info('Looking for .git directories');
        $data = array_merge(
            glob(DOKU_INC . '.git', GLOB_ONLYDIR),
            glob(DOKU_PLUGIN . '*/.git', GLOB_ONLYDIR),
            glob(fullpath(tpl_incdir() . '../') . '/*/.git', GLOB_ONLYDIR)
        );

        if(!$data) {
            $this->error('Found no .git directories');
        } else {
            $this->success('Found ' . count($data) . ' .git directories');
        }
        $data = array_map('fullpath', array_map('dirname', $data));
        return $data;
    }

    /**
     * Returns the repository for the given extension
     *
     * @param $extension
     * @return false|string
     */
    private function getSourceRepo($extension) {
        /** @var helper_plugin_extension_extension $ext */
        $ext = plugin_load('helper', 'extension_extension');
        if(!$ext) die("extension plugin not available, can't continue");

        $ext->setExtension($extension);

        $repourl = $ext->getSourcerepoURL();
        if(!$repourl) return false;

        // match github repos
        if(preg_match('/github\.com\/([^\/]+)\/([^\/]+)/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            return 'https://github.com/' . $user . '/' . $repo . '.git';
        }

        // match gitorious repos
        if(preg_match('/gitorious.org\/([^\/]+)\/([^\/]+)?/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            if(!$repo) $repo = $user;

            return 'https://git.gitorious.org/' . $user . '/' . $repo . '.git';
        }

        // match bitbucket repos - most people seem to use mercurial there though
        if(preg_match('/bitbucket\.org\/([^\/]+)\/([^\/]+)/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            return 'https://bitbucket.org/' . $user . '/' . $repo . '.git';
        }

        return false;
    }
}

// Main
$cli = new GitToolCLI();
$cli->run();
