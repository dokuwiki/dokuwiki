<?php

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../');
require_once(DOKU_INC . 'vendor/autoload.php');
require_once DOKU_INC . 'inc/load.php';

/**
 * Command Line utility to gather and check data for building a release
 */
class Release extends splitbrain\phpcli\CLI
{
    const TYPES = ['stable', 'hotfix', 'rc'];

    // base URL to fetch raw files from the stable branch
    protected $BASERAW = 'https://raw.githubusercontent.com/dokuwiki/dokuwiki/stable/';

    /** @inheritdoc */
    public function __construct($autocatch = true)
    {
        parent::__construct($autocatch);

        // when running on a clone, use the correct base URL
        $repo = getenv('GITHUB_REPOSITORY');
        if ($repo) {
            $this->BASERAW = 'https://raw.githubusercontent.com/' . $repo . '/stable/';
        }
    }


    protected function setup(\splitbrain\phpcli\Options $options)
    {
        $options->setHelp('This tool is used to gather and check data for building a release');

        $options->registerCommand('new', 'Get environment for creating a new release');
        $options->registerOption('type', 'The type of release to build', null, join('|', self::TYPES), 'new');
        $options->registerOption('date', 'The date to use for the version. Defaults to today', null, 'YYYY-MM-DD', 'new');
        $options->registerOption('name', 'The codename to use for the version. Defaults to the last used one', null, 'codename', 'new');

        $options->registerCommand('current', 'Get environment of the current release');
    }

    protected function main(\splitbrain\phpcli\Options $options)
    {
        switch ($options->getCmd()) {
            case 'new':
                $this->prepareNewEnvironment($options);
                break;
            case 'current':
                $this->prepareCurrentEnvironment($options);
                break;
            default:
                echo $options->help();
        }
    }

    /**
     * Prepare environment for the current branch
     */
    protected function prepareCurrentEnvironment(\splitbrain\phpcli\Options $options)
    {
        $current = $this->getLocalVersion();
        // we name files like the string in the VERSION file, with rc at the front
        $current['file'] = ($current['type'] === 'rc' ? 'rc' : '') . $current['date'] . $current['hotfix'];

        // output to be piped into GITHUB_ENV
        foreach ($current as $k => $v) {
            echo "current_$k=$v\n";
        }
    }

    /**
     * Prepare environment for creating a new release
     */
    protected function prepareNewEnvironment(\splitbrain\phpcli\Options $options)
    {
        $current = $this->getUpstreamVersion();

        // continue if we want to create a new release
        $next = [
            'type' => $options->getOpt('type'),
            'date' => $options->getOpt('date'),
            'codename' => $options->getOpt('name'),
            'hotfix' => '',
        ];
        if (!$next['type']) $next['type'] = 'stable';
        if (!$next['date']) $next['date'] = date('Y-m-d');
        if (!$next['codename']) $next['codename'] = $current['codename'];
        $next['codename'] = ucwords(strtolower($next['codename']));

        if (!in_array($next['type'], self::TYPES)) {
            throw new \splitbrain\phpcli\Exception('Invalid release type. Use one of ' . join(', ', self::TYPES));
        }

        if ($next['type'] === 'hotfix') {
            $next['update'] = floatval($current['update']) + 0.1;
            $next['codename'] = $current['codename'];
            $next['date'] = $current['date'];
            $next['hotfix'] = $this->increaseHotfix($current['hotfix']);
        } else {
            $next['update'] = intval($current['update']) + 1;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $next['date'])) {
            throw new \splitbrain\phpcli\Exception('Invalid date format, use YYYY-MM-DD');
        }

        if ($current['date'] > $next['date']) {
            throw new \splitbrain\phpcli\Exception('Date must be equal or later than the last release');
        }

        if ($current['type'] === 'rc' && $next['type'] === 'hotfix') {
            throw new \splitbrain\phpcli\Exception(
                'Cannot create hotfixes for release candidates, create a new RC instead'
            );
        }

        if ($current['type'] === 'stable' && $next['type'] !== 'hotfix' && $current['codename'] === $next['codename']) {
            throw new \splitbrain\phpcli\Exception('Codename must be different from the last release');
        }

        $next['version'] = $next['date'] . ($next['type'] === 'rc' ? 'rc' : $next['hotfix']);
        $next['raw'] = ($next['type'] === 'rc' ? 'rc' : '') .
            $next['date'] .
            $next['hotfix'] .
            ' "' . $next['codename'] . '"';

        // output to be piped into GITHUB_ENV
        foreach ($current as $k => $v) {
            echo "current_$k=$v\n";
        }
        foreach ($next as $k => $v) {
            echo "next_$k=$v\n";
        }
    }

    /**
     * Get current version info from local VERSION file
     *
     * @return string[]
     */
    protected function getLocalVersion()
    {
        $versioninfo = \dokuwiki\Info::parseVersionString(trim(file_get_contents('VERSION')));
        $doku = file_get_contents('doku.php');
        if (!preg_match('/\$updateVersion = "(\d+(\.\d+)?)";/', $doku, $m)) {
            throw new \Exception('Could not find $updateVersion in doku.php');
        }
        $versioninfo['update'] = floatval($m[1]);
        return $versioninfo;
    }

    /**
     * Get current version info from stable branch
     *
     * @return string[]
     * @throws Exception
     */
    protected function getUpstreamVersion()
    {
        // basic version info
        $versioninfo = \dokuwiki\Info::parseVersionString(trim(file_get_contents($this->BASERAW . 'VERSION')));

        // update version grepped from the doku.php file
        $doku = file_get_contents($this->BASERAW . 'doku.php');
        if (!preg_match('/\$updateVersion = "(\d+(\.\d+)?)";/', $doku, $m)) {
            throw new \Exception('Could not find $updateVersion in doku.php');
        }
        $versioninfo['update'] = floatval($m[1]);

        return $versioninfo;
    }

    /**
     * Increase the hotfix letter
     *
     * (max 26 hotfixes)
     *
     * @param string $hotfix
     * @return string
     */
    protected function increaseHotfix($hotfix)
    {
        if (empty($hotfix)) return 'a';
        return substr($hotfix, 0, -1) . chr(ord($hotfix) + 1);
    }
}

(new Release())->run();
