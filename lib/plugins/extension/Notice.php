<?php

namespace dokuwiki\plugin\extension;

class Notice
{
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const SECURITY = 'security';

    protected const ICONS = [
        self::INFO => 'I',
        self::WARNING => 'W',
        self::ERROR => 'E',
        self::SECURITY => 'S',
    ];

    protected $notices = [
        self::INFO => [],
        self::WARNING => [],
        self::ERROR => [],
        self::SECURITY => [],
    ];

    /** @var \helper_plugin_extension */
    protected $helper;

    /** @var Extension */
    protected Extension $extension;

    /**
     * Not public, use list() instead
     * @param Extension $extension
     */
    protected function __construct(Extension $extension)
    {
        $this->helper = plugin_load('helper', 'extension');
        $this->extension = $extension;

        $this->checkSecurity();
        $this->checkURLChange();
        $this->checkFolder();
        $this->checkPHPVersion();
        $this->checkDependencies();
        $this->checkConflicts();
        $this->checkUpdateMessage();
        $this->checkPermissions();
        $this->checkUnusedAuth();
        $this->checkGit();
    }

    /**
     * Get all notices for the extension
     *
     * @return string[][] array of notices grouped by type
     */
    public static function list(Extension $extension): array
    {
        $self = new self($extension);
        return $self->notices;
    }

    /**
     * Return the icon path for a notice type
     *
     * @param string $type The notice type constant
     * @return string
     */
    public static function icon($type): string
    {
        if (!isset(self::ICONS[$type])) throw new \RuntimeException('Unknown notice type: ' . $type);
        return __DIR__ . '/images/' . $type . '.svg';
    }

    /**
     * Return the character symbol for a notice type used on CLI
     *
     * @param string $type The notice type constant
     * @return string
     */
    public static function symbol($type): string
    {
        if (!isset(self::ICONS[$type])) throw new \RuntimeException('Unknown notice type: ' . $type);
        return self::ICONS[$type][0] ?? '';
    }

    /**
     * Access a language string
     *
     * @param string $msg
     * @return string
     */
    protected function getLang($msg)
    {
        return $this->helper->getLang($msg);
    }

    /**
     * Check that all dependencies are met
     * @return void
     */
    protected function checkDependencies()
    {
        if (!$this->extension->isInstalled()) return;

        $dependencies = $this->extension->getDependencyList();
        $missing = [];
        foreach ($dependencies as $dependency) {
            $dep = Extension::createFromId($dependency);
            if (!$dep->isInstalled()) $missing[] = $dep;
        }
        if (!$missing) return;

        $this->notices[self::ERROR][] = sprintf(
            $this->getLang('missing_dependency'),
            implode(', ', array_map(static fn(Extension $dep) => $dep->getId(true), $missing))
        );
    }

    /**
     * Check if installed dependencies are conflicting
     * @return void
     */
    protected function checkConflicts()
    {
        $conflicts = $this->extension->getConflictList();
        $found = [];
        foreach ($conflicts as $conflict) {
            $dep = Extension::createFromId($conflict);
            if ($dep->isInstalled()) $found[] = $dep;
        }
        if (!$found) return;

        $this->notices[self::WARNING][] = sprintf(
            $this->getLang('found_conflict'),
            implode(', ', array_map(static fn(Extension $dep) => $dep->getId(true), $found))
        );
    }

    /**
     * Check for security issues
     * @return void
     */
    protected function checkSecurity()
    {
        if ($issue = $this->extension->getSecurityIssue()) {
            $this->notices[self::SECURITY][] = sprintf($this->getLang('security_issue'), $issue);
        }
        if ($issue = $this->extension->getSecurityWarning()) {
            $this->notices[self::SECURITY][] = sprintf($this->getLang('security_warning'), $issue);
        }
    }

    /**
     * Check if the extension is installed in correct folder
     * @return void
     */
    protected function checkFolder()
    {
        if (!$this->extension->isInWrongFolder()) return;

        $this->notices[self::ERROR][] = sprintf(
            $this->getLang('wrong_folder'),
            basename($this->extension->getCurrentDir()),
            basename($this->extension->getInstallDir())
        );
    }

    /**
     * Check PHP requirements
     * @return void
     */
    protected function checkPHPVersion()
    {
        try {
            Installer::ensurePhpCompatibility($this->extension);
        } catch (\Exception $e) {
            $this->notices[self::ERROR][] = $e->getMessage();
        }
    }

    /**
     * Check for update message
     * @return void
     */
    protected function checkUpdateMessage()
    {
        // only display this for installed extensions
        if (!$this->extension->isInstalled()) return;
        if ($msg = $this->extension->getUpdateMessage()) {
            $this->notices[self::WARNING][] = sprintf($this->getLang('update_message'), $msg);
        }
    }

    /**
     * Check for URL changes
     * @return void
     */
    protected function checkURLChange()
    {
        if (!$this->extension->hasChangedURL()) return;
        $this->notices[self::WARNING][] = sprintf(
            $this->getLang('url_change'),
            $this->extension->getDownloadURL(),
            $this->extension->getManager()->getDownloadURL()
        );
    }

    /**
     * Check if the extension dir has the correct permissions to change
     *
     * @return void
     */
    protected function checkPermissions()
    {
        try {
            Installer::ensurePermissions($this->extension);
        } catch (\Exception $e) {
            $this->notices[self::ERROR][] = $e->getMessage();
        }
    }

    /**
     * Hint about unused auth plugins
     *
     * @return void
     */
    protected function checkUnusedAuth()
    {
        global $conf;
        if (
            $this->extension->isEnabled() &&
            in_array('Auth', $this->extension->getComponentTypes()) &&
            $conf['authtype'] != $this->extension->getID()
        ) {
            $this->notices[self::INFO][] = $this->getLang('auth');
        }
    }

    /**
     * Hint about installations by git
     *
     * @return void
     */
    protected function checkGit()
    {
        if ($this->extension->isGitControlled()) {
            $this->notices[self::INFO][] = $this->getLang('git');
        }
    }
}
