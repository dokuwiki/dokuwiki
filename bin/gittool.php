#!/usr/bin/env php
<?php

use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Installer;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Easily manage DokuWiki git repositories
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class GitToolCLI extends CLI
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
        $options->registerOption(
            'prefer-https',
            'Prefer HTTPS over SSH for cloning (default: try SSH first, fallback to HTTPS)',
            false,
            false,
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
        $options->registerOption(
            'prefer-https',
            'Prefer HTTPS over SSH for cloning (default: try SSH first, fallback to HTTPS)',
            false,
            false,
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
    protected function main(Options $options)
    {
        $command = $options->getCmd();
        $args = $options->getArgs();
        if (!$command) $command = array_shift($args);

        switch ($command) {
            case '':
                echo $options->help();
                break;
            case 'clone':
                $this->cmdClone($args, $options->getOpt('prefer-https'));
                break;
            case 'install':
                $this->cmdInstall($args, $options->getOpt('prefer-https'));
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
     * @param bool $preferHttps
     */
    public function cmdClone($extensions, $preferHttps = false)
    {
        $errors = [];
        $succeeded = [];

        foreach ($extensions as $ext) {
            $repo = $this->getSourceRepo($ext);

            if (!$repo) {
                $this->error("could not find a repository for $ext");
                $errors[] = $ext;
            } elseif ($this->cloneExtension($ext, $repo, $preferHttps)) {
                $succeeded[] = $ext;
            } else {
                $errors[] = $ext;
            }
        }

        echo "\n";
        if ($succeeded) $this->success('successfully cloned the following extensions: ' . implode(', ', $succeeded));
        if ($errors) $this->error('failed to clone the following extensions: ' . implode(', ', $errors));
    }

    /**
     * Tries to install the given extensions using git clone with fallback to install
     *
     * @param array $extensions
     * @param bool $preferHttps
     */
    public function cmdInstall($extensions, $preferHttps = false)
    {
        $errors = [];
        $succeeded = [];

        foreach ($extensions as $ext) {
            $repo = $this->getSourceRepo($ext);

            if (!$repo) {
                $this->info("could not find a repository for $ext");

                try {
                    $installer = new Installer();
                    $this->info("installing $ext via download");
                    $installer->installFromId($ext);
                    $this->success("installed $ext via download");
                    $succeeded[] = $ext;
                } catch (\Exception) {
                    $this->error("failed to install $ext via download");
                    $errors[] = $ext;
                }
            } elseif ($this->cloneExtension($ext, $repo, $preferHttps)) {
                $succeeded[] = $ext;
            } else {
                $errors[] = $ext;
            }
        }

        echo "\n";
        if ($succeeded) $this->success('successfully installed the following extensions: ' . implode(', ', $succeeded));
        if ($errors) $this->error('failed to install the following extensions: ' . implode(', ', $errors));
    }

    /**
     * Executes the given git command in every repository
     *
     * @param $cmd
     * @param $arg
     */
    public function cmdGit($cmd, $arg)
    {
        $repos = $this->findRepos();

        $shell = array_merge(['git', $cmd], $arg);
        $shell = array_map(escapeshellarg(...), $shell);
        $shell = implode(' ', $shell);

        foreach ($repos as $repo) {
            if (!@chdir($repo)) {
                $this->error("Could not change into $repo");
                continue;
            }

            $this->info("executing $shell in $repo");
            $ret = 0;
            system($shell, $ret);

            if ($ret == 0) {
                $this->success("git succeeded in $repo");
            } else {
                $this->error("git failed in $repo");
            }
        }
    }

    /**
     * Simply lists the repositories
     */
    public function cmdRepos()
    {
        $repos = $this->findRepos();
        foreach ($repos as $repo) {
            echo "$repo\n";
        }
    }

    /**
     * Clones the extension from the given repository
     *
     * @param string $ext
     * @param string $repo
     * @param bool $preferHttps
     * @return bool
     */
    private function cloneExtension($ext, $repo, $preferHttps = false)
    {
        if (str_starts_with($ext, 'template:')) {
            $target = fullpath(tpl_incdir() . '../' . substr($ext, 9));
        } else {
            $target = DOKU_PLUGIN . $ext;
        }

        $ret = -1;

        // try SSH clone first, unless the user prefers HTTPS
        if (!$preferHttps) {
            $sshUrl = $this->httpsToSshUrl($repo);
            $this->info("cloning $ext from $sshUrl");
            system("git clone $sshUrl $target", $ret);
            if ($ret !== 0) $this->info("SSH clone failed, trying HTTPS: $repo");
        }

        // try HTTPS clone
        if ($ret !== 0) {
            $this->info("cloning $ext from $repo");
            system("git clone $repo $target", $ret);
        }

        if ($ret === 0) {
            $this->success("cloning of $ext succeeded");
            return true;
        } else {
            $this->error("cloning of $ext failed");
            return false;
        }
    }

    /**
     * Convert a HTTPS repo URL to an SSH URL if possible
     *
     * @return string
     */
    private function httpsToSshUrl($url)
    {
        if (preg_match('/(github\.com|bitbucket\.org|gitorious\.org)\/([^\/]+)\/([^\/]+)/i', $url, $m)) {
            $host = $m[1];
            $user = $m[2];
            $repo = $m[3];
            return "git@$host:$user/$repo";
        }
        return $url;
    }

    /**
     * Returns all git repositories in this DokuWiki install
     *
     * Looks in root, template and plugin directories only.
     *
     * @return array
     */
    private function findRepos()
    {
        $this->info('Looking for .git directories');
        $data = array_merge(
            glob(DOKU_INC . '.git', GLOB_ONLYDIR),
            glob(DOKU_PLUGIN . '*/.git', GLOB_ONLYDIR),
            glob(fullpath(tpl_incdir() . '../') . '/*/.git', GLOB_ONLYDIR)
        );

        if (!$data) {
            $this->error('Found no .git directories');
        } else {
            $this->success('Found ' . count($data) . ' .git directories');
        }
        $data = array_map(fullpath(...), array_map(dirname(...), $data));
        return $data;
    }

    /**
     * Returns the repository for the given extension
     *
     * @param string $extensionId
     * @return false|string
     */
    private function getSourceRepo($extensionId)
    {
        $extension = Extension::createFromId($extensionId);

        $repourl = $extension->getSourcerepoURL();
        if (!$repourl) return false;

        // match github repos
        if (preg_match('/github\.com\/([^\/]+)\/([^\/]+)/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            return 'https://github.com/' . $user . '/' . $repo . '.git';
        }

        // match gitorious repos
        if (preg_match('/gitorious.org\/([^\/]+)\/([^\/]+)?/i', $repourl, $m)) {
            $user = $m[1];
            $repo = $m[2];
            if (!$repo) $repo = $user;

            return 'https://git.gitorious.org/' . $user . '/' . $repo . '.git';
        }

        // match bitbucket repos - most people seem to use mercurial there though
        if (preg_match('/bitbucket\.org\/([^\/]+)\/([^\/]+)/i', $repourl, $m)) {
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
