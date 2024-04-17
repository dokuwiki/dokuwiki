<?php

use dokuwiki\Extension\CLIPlugin;
use dokuwiki\plugin\extension\Exception as ExtensionException;
use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Installer;
use dokuwiki\plugin\extension\Local;
use dokuwiki\plugin\extension\Repository;
use splitbrain\phpcli\Colors;
use splitbrain\phpcli\Exception;
use splitbrain\phpcli\Options;
use splitbrain\phpcli\TableFormatter;

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
        $options->useCompactHelp();
        $options->setHelp(
            "Manage plugins and templates for this DokuWiki instance\n\n" .
            "Status codes:\n" .
            "   i - installed\n" .
            "   b - bundled with DokuWiki\n" .
            "   g - installed via git\n" .
            "   d - disabled\n" .
            "   u - update available\n" .
            "   ☠ - security issue\n" .
            "   ⚠ - security warning\n" .
            "   ▽ - update message\n"
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
        $repo = Repository::getInstance();
        try {
            $repo->checkAccess();
        } catch (ExtensionException $e) {
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

        $installer = new Installer(true);

        $ok = 0;
        foreach ($extensions as $extname) {
            try {
                if (preg_match("/^https?:\/\//i", $extname)) {
                    $installer->installFromURL($extname, true);
                } else {
                    $installer->installFromId($extname);
                }
            } catch (ExtensionException $e) {
                $this->debug($e->getTraceAsString());
                $this->error($e->getMessage());
                $ok++; // error code is number of failed installs
            }
        }

        $processed = $installer->getProcessed();
        foreach($processed as $id => $status){
            if($status == Installer::STATUS_INSTALLED) {
                $this->success(sprintf($this->getLang('msg_install_success'), $id));
            } else if($status == Installer::STATUS_UPDATED) {
                $this->success(sprintf($this->getLang('msg_update_success'), $id));
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
        $repo = Repository::getInstance();
        $result = $repo->searchExtensions($query);
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
        $this->listExtensions((new Local())->getExtensions(), $showdetails, $filter);
        return 0;
    }

    /**
     * List the given extensions
     *
     * @param Extension[] $list
     * @param bool $details display details
     * @param string $filter filter for this status
     * @throws Exception
     */
    protected function listExtensions($list, $details, $filter = '')
    {
        $tr = new TableFormatter($this->colors);
        foreach ($list as $ext) {

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
                if ($ext->isBundled()) {
                    $status = 'b';
                    $date = '<bundled>';
                    $vcolor = null;
                }
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


            if ($ext->getSecurityIssue()) $status .= '☠';
            if ($ext->getSecurityWarning()) $status .= '⚠';
            if ($ext->getUpdateMessage()) $status .= '▽';

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
            if ($ext->getSecurityWarning()) {
                echo $tr->format(
                    [5, '*'],
                    ['', '⚠ ' . $ext->getSecurityWarning()],
                    [null, Colors::C_YELLOW]
                );
            }
            if ($ext->getSecurityIssue()) {
                echo $tr->format(
                    [5, '*'],
                    ['', '☠ ' . $ext->getSecurityIssue()],
                    [null, Colors::C_LIGHTRED]
                );
            }
            if ($ext->getUpdateMessage()) {
                echo $tr->format(
                    [5, '*'],
                    ['', '▽ ' . $ext->getUpdateMessage()],
                    [null, Colors::C_LIGHTBLUE]
                );
            }
        }
    }
}
