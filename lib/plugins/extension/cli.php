<?php

use dokuwiki\Extension\CLIPlugin;
use dokuwiki\plugin\extension\Exception as ExtensionException;
use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Installer;
use dokuwiki\plugin\extension\Local;
use dokuwiki\plugin\extension\Notice;
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
            "   i - installed                    " . Notice::symbol(Notice::SECURITY) . " - security issue\n" .
            "   b - bundled with DokuWiki        " . Notice::symbol(Notice::ERROR) . " - extension error\n" .
            "   g - installed via git            " . Notice::symbol(Notice::WARNING) . " - extension warning\n" .
            "   d - disabled                     " . Notice::symbol(Notice::INFO) . " - extension info\n" .
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
        $options->registerOption('git-overwrite', 'Do not skip git-controlled extensions', 'g', false, 'upgrade');

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
            $this->warning($e->getMessage());
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
                $ret = $this->cmdUpgrade($options->getOpt('git-overwrite', false));
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
    protected function cmdUpgrade($gitOverwrite)
    {
        $local = new Local();
        $extensions = [];
        foreach ($local->getExtensions() as $ext) {
            if ($ext->isGitControlled() && !$gitOverwrite) continue; // skip git controlled extensions
            if ($ext->isUpdateAvailable()) $extensions[] = $ext->getID();
        }
        return $this->cmdInstall($extensions);
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
        $ok = 0;
        foreach ($extensions as $extname) {
            $ext = Extension::createFromId($extname);

            try {
                if ($set) {
                    $ext->enable();
                    $msg = 'msg_enabled';
                } else {
                    $ext->disable();
                    $msg = 'msg_disabled';
                }
                $this->success(sprintf($this->getLang($msg), $ext->getID()));
            } catch (ExtensionException $e) {
                $this->error($e->getMessage());
                ++$ok;
                continue;
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
        $installer = new Installer();

        $ok = 0;
        foreach ($extensions as $extname) {
            $ext = Extension::createFromId($extname);

            try {
                $installer->uninstall($ext);
                $this->success(sprintf($this->getLang('msg_delete_success'), $ext->getID()));
            } catch (ExtensionException $e) {
                $this->debug($e->getTraceAsString());
                $this->error($e->getMessage());
                $ok++; // error code is number of failed uninstalls
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
        $ok = 0;
        foreach ($extensions as $extname) {
            $installer = new Installer(true);

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

            $processed = $installer->getProcessed();
            foreach ($processed as $id => $status) {
                if ($status == Installer::STATUS_INSTALLED) {
                    $this->success(sprintf($this->getLang('msg_install_success'), $id));
                } elseif ($status == Installer::STATUS_UPDATED) {
                    $this->success(sprintf($this->getLang('msg_update_success'), $id));
                }
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
        $extensions = (new Local())->getExtensions();
        // initialize remote data in one go
        Repository::getInstance()->initExtensions(array_keys($extensions));

        $this->listExtensions($extensions, $showdetails, $filter);
        return 0;
    }

    /**
     * List the given extensions
     *
     * @param Extension[] $list
     * @param bool $details display details
     * @param string $filter filter for this status
     * @throws Exception
     * @todo break into smaller methods
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

            $notices = Notice::list($ext);
            if ($notices[Notice::SECURITY]) $status .= Notice::symbol(Notice::SECURITY);
            if ($notices[Notice::ERROR]) $status .= Notice::symbol(Notice::ERROR);
            if ($notices[Notice::WARNING]) $status .= Notice::symbol(Notice::WARNING);
            if ($notices[Notice::INFO]) $status .= Notice::symbol(Notice::INFO);

            echo $tr->format(
                [20, 5, 12, '*'],
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
                [7, '*'],
                ['', $ext->getDescription()],
                [null, Colors::C_CYAN]
            );
            foreach ($notices as $type => $msgs) {
                if (!$msgs) continue;
                foreach ($msgs as $msg) {
                    echo $tr->format(
                        [7, '*'],
                        ['', Notice::symbol($type) . ' ' . $msg],
                        [null, Colors::C_LIGHTBLUE]
                    );
                }
            }
        }
    }
}
