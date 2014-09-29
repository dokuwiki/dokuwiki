#!/usr/bin/php
<?php

if('cli' != php_sapi_name()) die();
ini_set('memory_limit', '128M');
if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__).'/../').'/');
define('NOSESSION', 1);
require_once(DOKU_INC.'inc/init.php');

$GitToolCLI = new GitToolCLI();

array_shift($argv);
$command = array_shift($argv);

switch($command) {
    case '':
    case 'help':
        $GitToolCLI->cmd_help();
        break;
    case 'clone':
        $GitToolCLI->cmd_clone($argv);
        break;
    case 'install':
        $GitToolCLI->cmd_install($argv);
        break;
    case 'repo':
    case 'repos':
        $GitToolCLI->cmd_repos();
        break;
    default:
        $GitToolCLI->cmd_git($command, $argv);
}

/**
 * Easily manage DokuWiki git repositories
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class GitToolCLI {
    private $color = true;

    public function cmd_help() {
        echo <<<EOF
Usage: gittool.php <command> [parameters]

Manage git repositories for DokuWiki and its plugins and templates.

EXAMPLE

$> ./bin/gittool.php clone gallery template:ach
$> ./bin/gittool.php repos
$> ./bin/gittool.php origin -v

COMMANDS

help
    This help screen

clone <extensions>
    Tries to install a known plugin or template (prefix with template:) via
    git. Uses the DokuWiki.org plugin repository to find the proper git
    repository. Multiple extensions can be given as parameters

install <extensions>
    The same as clone, but when no git source repository can be found, the
    extension is installed via download

repos
    Lists all git repositories found in this DokuWiki installation

<any>
    Any unknown commands are assumed to be arguments to git and will be
    executed in all repositories found within this DokuWiki installation

EOF;
    }

    /**
     * Tries to install the given extensions using git clone
     *
     * @param       $extensions
     */
    public function cmd_clone($extensions) {
        $errors    = array();
        $succeeded = array();

        foreach($extensions as $ext) {
            $repo = $this->getSourceRepo($ext);

            if(!$repo) {
                $this->msg_error("could not find a repository for $ext");
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
        if($succeeded) $this->msg_success('successfully cloned the following extensions: '.join(', ', $succeeded));
        if($errors) $this->msg_error('failed to clone the following extensions: '.join(', ', $errors));
    }

    /**
     * Tries to install the given extensions using git clone with fallback to install
     *
     * @param       $extensions
     */
    public function cmd_install($extensions) {
        $errors    = array();
        $succeeded = array();

        foreach($extensions as $ext) {
            $repo = $this->getSourceRepo($ext);

            if(!$repo) {
                $this->msg_info("could not find a repository for $ext");
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
        if($succeeded) $this->msg_success('successfully installed the following extensions: '.join(', ', $succeeded));
        if($errors) $this->msg_error('failed to install the following extensions: '.join(', ', $errors));
    }

    /**
     * Executes the given git command in every repository
     *
     * @param $cmd
     * @param $arg
     */
    public function cmd_git($cmd, $arg) {
        $repos = $this->findRepos();

        $shell = array_merge(array('git', $cmd), $arg);
        $shell = array_map('escapeshellarg', $shell);
        $shell = join(' ', $shell);

        foreach($repos as $repo) {
            if(!@chdir($repo)) {
                $this->msg_error("Could not change into $repo");
                continue;
            }

            echo "\n";
            $this->msg_info("executing $shell in $repo");
            $ret = 0;
            system($shell, $ret);

            if($ret == 0) {
                $this->msg_success("git succeeded in $repo");
            } else {
                $this->msg_error("git failed in $repo");
            }
        }
    }

    /**
     * Simply lists the repositories
     */
    public function cmd_repos() {
        $repos = $this->findRepos();
        foreach($repos as $repo) {
            echo "$repo\n";
        }
    }

    /**
     * Install extension from the given download URL
     *
     * @param string $ext
     * @return bool
     */
    private function downloadExtension($ext) {
        /** @var helper_plugin_extension_extension $plugin */
        $plugin = plugin_load('helper', 'extension_extension');
        if(!$ext) die("extension plugin not available, can't continue");
        $plugin->setExtension($ext);

        $url = $plugin->getDownloadURL();
        if(!$url) {
            $this->msg_error("no download URL for $ext");
            return false;
        }

        $ok = false;
        try {
            $this->msg_info("installing $ext via download from $url");
            $ok = $plugin->installFromURL($url);
        } catch(Exception $e) {
            $this->msg_error($e->getMessage());
        }

        if($ok) {
            $this->msg_success("installed $ext via download");
            return true;
        } else {
            $this->msg_success("failed to install $ext via download");
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
            $target = fullpath(tpl_incdir().'../'.substr($ext, 9));
        } else {
            $target = DOKU_PLUGIN.$ext;
        }

        $this->msg_info("cloning $ext from $repo to $target");
        $ret = 0;
        system("git clone $repo $target", $ret);
        if($ret === 0) {
            $this->msg_success("cloning of $ext succeeded");
            return true;
        } else {
            $this->msg_error("cloning of $ext failed");
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
        $this->msg_info('Looking for .git directories');
        $data = array_merge(
            glob(DOKU_INC.'.git', GLOB_ONLYDIR),
            glob(DOKU_PLUGIN.'*/.git', GLOB_ONLYDIR),
            glob(fullpath(tpl_incdir().'../').'/*/.git', GLOB_ONLYDIR)
        );

        if(!$data) {
            $this->msg_error('Found no .git directories');
        } else {
            $this->msg_success('Found '.count($data).' .git directories');
        }
        $data = array_map('fullpath', array_map('dirname', $data));
        return $data;
    }

    /**
     * Returns the repository for the given extension
     *
     * @param $extension
     * @return bool|string
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
            return 'https://github.com/'.$user.'/'.$repo.'.git';
        }

        // match gitorious repos
        if(preg_match('/gitorious.org\/([^\/]+)\/([^\/]+)?/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            if(!$repo) $repo = $user;

            return 'https://git.gitorious.org/'.$user.'/'.$repo.'.git';
        }

        // match bitbucket repos - most people seem to use mercurial there though
        if(preg_match('/bitbucket\.org\/([^\/]+)\/([^\/]+)/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            return 'https://bitbucket.org/'.$user.'/'.$repo.'.git';
        }

        return false;
    }

    /**
     * Print an error message
     *
     * @param $string
     */
    private function msg_error($string) {
        if($this->color) echo "\033[31m"; // red
        echo "E: $string\n";
        if($this->color) echo "\033[37m"; // reset
    }

    /**
     * Print a success message
     *
     * @param $string
     */
    private function msg_success($string) {
        if($this->color) echo "\033[32m"; // green
        echo "S: $string\n";
        if($this->color) echo "\033[37m"; // reset
    }

    /**
     * Print an info message
     *
     * @param $string
     */
    private function msg_info($string) {
        if($this->color) echo "\033[36m"; // cyan
        echo "I: $string\n";
        if($this->color) echo "\033[37m"; // reset
    }
}