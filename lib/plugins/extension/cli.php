<?php

use splitbrain\phpcli\Exception;
use dokuwiki\Extension\CLIPlugin;
use splitbrain\phpcli\Options;
use splitbrain\phpcli\TableFormatter;
use splitbrain\phpcli\Colors;

/**
 * Class cli_plugin_extension
 *
 * Command Line component for the extension manager
 *
 * @license GPL2
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class cli_plugin_extension extends CLIPlugin
{
    /** @inheritdoc */
    protected function setup(Options $options)
    {
        // general setup
        $options->setHelp(
            "Manage plugins and templates for this DokuWiki instance\n\n" .
            "Status codes:\n" .
            "   i - installed\n" .
            "   b - bundled with DokuWiki\n" .
            "   g - installed via git\n" .
            "   d - disabled\n" .
            "   u - update available\n"
        );

        // search
        $options->registerCommand('search', 'Search for an extension');
        $options->registerOption('max', 'Maximum number of results (default 10)', 'm', 'number', 'search');
        $options->registerOption('verbose', 'Show detailed extension information', 'v', false, 'search');
        $options->registerArgument('query', 'The keyword(s) to search for', true, 'search');

        // list
        $options->registerCommand('list', 'List installed extensions');
        $options->registerOption('verbose', 'Show detailed extension information', 'v', false, 'list');
        $options->registerOption('filter', 'Filter by this status', 'f', 'status', 'list');

        // upgrade
        $options->registerCommand('upgrade', 'Update all installed extensions to their latest versions');

        // install
        $options->registerCommand('install', 'Install or upgrade extensions');
        $options->registerArgument(
            'extensions...',
            'One or more extensions to install. Either by name or download URL',
            true,
            'install'
        );

        // uninstall
        $options->registerCommand('uninstall', 'Uninstall a new extension');
        $options->registerArgument('extensions...', 'One or more extensions to install', true, 'uninstall');

        // enable
        $options->registerCommand('enable', 'Enable installed extensions');
        $options->registerArgument('extensions...', 'One or more extensions to enable', true, 'enable');

        // disable
        $options->registerCommand('disable', 'Disable installed extensions');
        $options->registerArgument('extensions...', 'One or more extensions to disable', true, 'disable');
    }

    /** @inheritdoc */
    protected function main(Options $options)
    {
        /** @var helper_plugin_extension_repository $repo */
        $repo = plugin_load('helper', 'extension_repository');
        if (!$repo->hasAccess(false)) {
            $this->warning('Extension Repository API is not accessible, no remote info available!');
        }

        switch ($options->getCmd()) {
            case 'list':
                $ret = $this->cmdList($options->getOpt('verbose'), $options->getOpt('filter', ''));
                break;
            case 'search':
                $ret = $this->cmdSearch(
                    implode(' ', $options->getArgs()),
                    $options->getOpt('verbose'),
                    (int)$options->getOpt('max', 10)
                );
                break;
            case 'install':
                $ret = $this->cmdInstall($options->getArgs());
                break;
            case 'uninstall':
                $ret = $this->cmdUnInstall($options->getArgs());
                break;
            case 'enable':
                $ret = $this->cmdEnable(true, $options->getArgs());
                break;
            case 'disable':
                $ret = $this->cmdEnable(false, $options->getArgs());
                break;
            case 'upgrade':
                $ret = $this->cmdUpgrade();
                break;
            default:
                echo $options->help();
                $ret = 0;
        }

        exit($ret);
    }

    /**
     * Upgrade all extensions
     *
     * @return int
     */
    protected function cmdUpgrade()
    {
        /* @var helper_plugin_extension_extension $ext */
        $ext = $this->loadHelper('extension_extension');
        $list = $this->getInstalledExtensions();

        $ok = 0;
        foreach ($list as $extname) {
            $ext->setExtension($extname);
            $date = $ext->getInstalledVersion();
            $avail = $ext->getLastUpdate();
            if ($avail && $avail > $date && !$ext->isBundled()) {
                $ok += $this->cmdInstall([$extname]);
            }
        }

        return $ok;
    }

    /**
     * Enable or disable one or more extensions
     *
     * @param bool $set
     * @param string[] $extensions
     * @return int
     */
    protected function cmdEnable($set, $extensions)
    {
        /* @var helper_plugin_extension_extension $ext */
        $ext = $this->loadHelper('extension_extension');

        $ok = 0;
        foreach ($extensions as $extname) {
            $ext->setExtension($extname);
            if (!$ext->isInstalled()) {
                $this->error(sprintf('Extension %s is not installed', $ext->getID()));
                ++$ok;
                continue;
            }

            if ($set) {
                $status = $ext->enable();
                $msg = 'msg_enabled';
            } else {
                $status = $ext->disable();
                $msg = 'msg_disabled';
            }

            if ($status !== true) {
                $this->error($status);
                ++$ok;
                continue;
            } else {
                $this->success(sprintf($this->getLang($msg), $ext->getID()));
            }
        }

        return $ok;
    }

    /**
     * Uninstall one or more extensions
     *
     * @param string[] $extensions
     * @return int
     */
    protected function cmdUnInstall($extensions)
    {
        /* @var helper_plugin_extension_extension $ext */
        $ext = $this->loadHelper('extension_extension');

        $ok = 0;
        foreach ($extensions as $extname) {
            $ext->setExtension($extname);
            if (!$ext->isInstalled()) {
                $this->error(sprintf('Extension %s is not installed', $ext->getID()));
                ++$ok;
                continue;
            }

            $status = $ext->uninstall();
            if ($status) {
                $this->success(sprintf($this->getLang('msg_delete_success'), $ext->getID()));
            } else {
                $this->error(sprintf($this->getLang('msg_delete_failed'), hsc($ext->getID())));
                $ok = 1;
            }
        }

        return $ok;
    }

    /**
     * Install one or more extensions
     *
     * @param string[] $extensions
     * @return int
     */
    protected function cmdInstall($extensions)
    {
        /* @var helper_plugin_extension_extension $ext */
        $ext = $this->loadHelper('extension_extension');

        $ok = 0;
        foreach ($extensions as $extname) {
            $installed = [];

            if (preg_match("/^https?:\/\//i", $extname)) {
                try {
                    $installed = $ext->installFromURL($extname, true);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    ++$ok;
                }
            } else {
                $ext->setExtension($extname);

                if (!$ext->getDownloadURL()) {
                    ++$ok;
                    $this->error(
                        sprintf('Could not find download for %s', $ext->getID())
                    );
                    continue;
                }

                try {
                    $installed = $ext->installOrUpdate();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    ++$ok;
                }
            }

            foreach ($installed as $info) {
                $this->success(
                    sprintf(
                        $this->getLang('msg_' . $info['type'] . '_' . $info['action'] . '_success'),
                        $info['base']
                    )
                );
            }
        }
        return $ok;
    }

    /**
     * Search for an extension
     *
     * @param string $query
     * @param bool $showdetails
     * @param int $max
     * @return int
     * @throws Exception
     */
    protected function cmdSearch($query, $showdetails, $max)
    {
        /** @var helper_plugin_extension_repository $repository */
        $repository = $this->loadHelper('extension_repository');
        $result = $repository->search($query);
        if ($max) {
            $result = array_slice($result, 0, $max);
        }

        $this->listExtensions($result, $showdetails);
        return 0;
    }

    /**
     * @param bool $showdetails
     * @param string $filter
     * @return int
     * @throws Exception
     */
    protected function cmdList($showdetails, $filter)
    {
        $list = $this->getInstalledExtensions();
        $this->listExtensions($list, $showdetails, $filter);

        return 0;
    }

    /**
     * Get all installed extensions
     *
     * @return array
     */
    protected function getInstalledExtensions()
    {
        /** @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        $pluginlist = $plugin_controller->getList('', true);
        $tpllist = glob(DOKU_INC . 'lib/tpl/*', GLOB_ONLYDIR);
        $tpllist = array_map(static fn($path) => 'template:' . basename($path), $tpllist);

        $list = array_merge($pluginlist, $tpllist);
        sort($list);
        return $list;
    }

    /**
     * List the given extensions
     *
     * @param string[] $list
     * @param bool $details display details
     * @param string $filter filter for this status
     * @throws Exception
     */
    protected function listExtensions($list, $details, $filter = '')
    {
        /** @var helper_plugin_extension_extension $ext */
        $ext = $this->loadHelper('extension_extension');
        $tr = new TableFormatter($this->colors);


        foreach ($list as $name) {
            $ext->setExtension($name);

            $status = '';
            if ($ext->isInstalled()) {
                $date = $ext->getInstalledVersion();
                $avail = $ext->getLastUpdate();
                $status = 'i';
                if ($avail && $avail > $date) {
                    $vcolor = Colors::C_RED;
                    $status .= 'u';
                } else {
                    $vcolor = Colors::C_GREEN;
                }
                if ($ext->isGitControlled()) $status = 'g';
                if ($ext->isBundled()) $status = 'b';
                if ($ext->isEnabled()) {
                    $ecolor = Colors::C_BROWN;
                } else {
                    $ecolor = Colors::C_DARKGRAY;
                    $status .= 'd';
                }
            } else {
                $ecolor = null;
                $date = $ext->getLastUpdate();
                $vcolor = null;
            }

            if ($filter && strpos($status, $filter) === false) {
                continue;
            }

            echo $tr->format(
                [20, 3, 12, '*'],
                [
                    $ext->getID(),
                    $status,
                    $date,
                    strip_tags(sprintf(
                        $this->getLang('extensionby'),
                        $ext->getDisplayName(),
                        $this->colors->wrap($ext->getAuthor(), Colors::C_PURPLE)
                    ))
                ],
                [
                    $ecolor,
                    Colors::C_YELLOW,
                    $vcolor,
                    null,
                ]
            );

            if (!$details) continue;

            echo $tr->format(
                [5, '*'],
                ['', $ext->getDescription()],
                [null, Colors::C_CYAN]
            );
        }
    }
}
