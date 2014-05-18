#!/usr/bin/php
<?php
if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Easily update plugin versions
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class VersionFixCLI extends DokuCLI {

    protected $dokuwiki_user = '';
    protected $dokuwiki_pass = '';
    protected $github_user = '';
    protected $github_key = '';

    /**
     * Register options and arguments on the given $options object
     *
     * @param DokuCLI_Options $options
     * @return void
     */
    protected function setup(DokuCLI_Options $options) {
        $options->setHelp(
            "Update the version of a plugin or template"
        );

        $options->registerArgument(
            'extension',
            'The name of the extension to update. Templates have to be prefixed with \'template:\'',
            true
        );
    }

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param DokuCLI_Options $options
     * @return void
     */
    protected function main(DokuCLI_Options $options) {
        $this->loadCredentials();

        $extension = array_shift($options->args);

        $repoinfo = $this->getRepoInfo($extension);
        $this->info('dokuwiki.org version: ' . $repoinfo['repoversion']);
        $repoinfo['txtversion'] = $this->fetchInfoTxtVersion($repoinfo['github_user'], $repoinfo['github_repo'], $repoinfo['is_template']);
        $this->info('github.com version:   ' . $repoinfo['txtversion']);
        $repoinfo['commitversion'] = $this->fetchLastCommit($repoinfo['github_user'], $repoinfo['github_repo']);
        $this->info('last real commit:     ' . $repoinfo['commitversion']);

        if($repoinfo['repoversion'] == $repoinfo['txtversion'] && $repoinfo['txtversion'] == $repoinfo['commitversion']) {
            $this->info('versions match, no update needed.');
            exit (0);
        }

        $target = max($repoinfo['txtversion'], $repoinfo['commitversion']);
        $this->info('target version:       ' . $target);

        if($target != $repoinfo['txtversion']) {
            $this->updateGithub($repoinfo['github_user'], $repoinfo['github_repo'], $repoinfo['is_template'], $repoinfo['txtversion'], $target);
        } else {
            $this->info('info.txt is uptodate already.');
        }

        if($target != $repoinfo['repoversion']) {
            $dwpage = $this->fetchAndAdjustPage($repoinfo['page'], $repoinfo['repoversion'], $target);
            $this->updatePage($repoinfo['page'], $dwpage);
        } else {
            $this->info('extension page is uptodate already.');
        }
    }

    /**
     * Load credentials from config file
     */
    protected function loadCredentials() {
        $home = getenv("HOME");
        $conf = "$home/.dwversionfix.conf";
        $this->info('Searching for credentials in '.$conf);
        if(!file_exists($conf)){
            io_saveFile($conf,
            "## Uncomment the following lines and set correct credentials\n".
            "#dokuwiki_user  <your user at dokuwiki.org>\n".
            "#dokuwiki_pass  <your password at dokuwiki.org>\n".
            "#github_user    <your user at github.com>\n".
            "#github_key     <your API key at github.com>\n"
            );
            @chmod($conf, 0600);
            $this->fatal('Please edit the credentials in '.$conf);
        }
        $creds = confToHash($conf);

        $this->dokuwiki_user = $creds['dokuwiki_user'];
        $this->dokuwiki_pass = $creds['dokuwiki_pass'];
        $this->github_user   = $creds['github_user'];
        $this->github_key    = $creds['github_key'];

        if(
            empty($this->dokuwiki_user) ||
            empty($this->dokuwiki_pass) ||
            empty($this->github_user) ||
            empty($this->github_key)
        ) $this->fatal('Please edit the credentials in '.$conf);
    }

    /**
     * Load info about the extension from the plugin repository
     *
     * @param string $extension name of extension
     * @return array
     */
    protected function getRepoInfo($extension) {
        /** @var helper_plugin_extension_extension $ext */
        $ext = plugin_load('helper', 'extension_extension');
        $ext->setExtension($extension);

        // get plugin repository info
        $repourl     = $ext->getSourcerepoURL();
        $repoversion = $ext->getLastUpdate();
        if(!$repoversion) $this->fatal('No current version of this extension in plugin repository. Make sure it\'s listed.');
        if(!$repourl) $this->fatal('No source info found in plugin repository.');

        // match github repo
        if(!preg_match('/github\.com\/([^\/]+)\/([^\/]+)/i', $repourl, $m)) {
            $this->fatal('No github repository for this extensions. This tool only works with github.');
        }

        $page = $ext->getBase();
        if(!$ext->isTemplate()) $page = 'plugin:' . $page;

        return array(
            'page'        => $page,
            'repoversion' => $repoversion,
            'is_template' => $ext->isTemplate(),
            'github_user' => $m[1],
            'github_repo' => $m[2]
        );
    }

    /**
     * Get the date from the *info.txt file at github
     *
     * @param string $user The github user owning the git repository (might be a organization)
     * @param string $repo The git repositor name at github
     * @param bool $is_template Is this a template? Plugin otherwise
     * @return string
     */
    protected function fetchInfoTxtVersion($user, $repo, $is_template) {
        $http = new HTTPClient();
        $http->headers['Accept'] = 'application/vnd.github.v3+json';
        $http->user = $this->github_user;
        $http->pass = $this->github_key;
        $json = new JSON(JSON_LOOSE_TYPE);

        $infotxt = 'plugin.info.txt';
        if($is_template) $infotxt = 'template.info.txt';
        $url = 'https://api.github.com/repos/'.$user.'/'.$repo.'/contents/'.$infotxt;

        // get the current file
        $response = $http->get($url);
        if(!$response){
            $this->error($http->error);
            $this->error($http->resp_body);
            $this->fatal('Failed to talk to github API for fetching file.');
        }
        $response = $json->decode($response);
        $infotxt = base64_decode($response['content']);

        $infotxt = linesToHash(explode("\n", $infotxt));

        if(empty($infotxt['date'])) $this->fatal('info.txt has no date field');
        return $infotxt['date'];
    }

    /**
     * Load the current page from dokuwiki.org and adjust the date
     *
     * @param string $page The extension's page name
     * @param string $search The date to replace
     * @param string $date The new date to set
     * @return string The adjusted page content
     */
    protected function fetchAndAdjustPage($page, $search, $date) {
        $http = new DokuHTTPClient();
        $data = $http->get('https://www.dokuwiki.org/' . $page . '?do=export_raw');
        if(!$data) $this->fatal('Failed to fetch extension page from dokuwiki.org');

        $search  = preg_quote($search, '/');
        $newdata = preg_replace('/(\n---- (?:plugin|template) ----.*?(?:lastupdate *: *))' . $search . '/s', '${1}' . $date, $data);
        if($data == $newdata) {
            $this->fatal('Failed to adjust date in dokuwiki page. Date might differ from cached repo data.');
        }
        return $newdata;
    }

    /**
     * Save the extension's page at dokuwiki.org
     *
     * @param string $page The extension's page name
     * @param string $content The new content
     */
    protected function updatePage($page, $content) {
        $http = new DokuHTTPClient();

        $data     = array(
            'id' => $page,
            'do' => 'edit',
            'u'  => $this->dokuwiki_user,
            'p'  => $this->dokuwiki_pass
        );
        $response = $http->post('https://www.dokuwiki.org/doku.php', $data);

        if(!preg_match('/<input type="hidden" name="sectok" value="([0-9a-f]{32})" \/>/', $response, $m)) {
            $this->fatal('Failed to open extension page for editing. Might be locked or your credentials are wrong.');
        }
        $sectoken = $m[1];

        $data = array(
            'id'       => $page,
            'prefix'   => '',
            'suffix'   => '',
            'wikitext' => $content,
            'summary'  => 'version upped',
            'sectok'   => $sectoken,
            'do'       => 'save',
        );

        $response    = $http->post('https://www.dokuwiki.org/doku.php', $data);

        if(preg_match('/<div class="error">(.*?)(<\/div>)/', $response, $m)) {
            $this->error('Seems like something went wrong on editing the page ' . $page . '. Error was: ' . $m[1]);
        } else {
            $this->success('Updated ' . $page . ' at dokuwiki.org.');
        }
    }

    /**
     * Update the *info.txt file at github
     *
     * @param string $user The github user owning the git repository (might be a organization)
     * @param string $repo The git repositor name at github
     * @param bool $is_template Is this a template? Plugin otherwise
     * @param string $search The date to replace
     * @param string $date The new date to set
     */
    protected function updateGithub($user, $repo, $is_template, $search, $date) {
        $http = new HTTPClient();
        $http->headers['Accept'] = 'application/vnd.github.v3+json';
        $http->user = $this->github_user;
        $http->pass = $this->github_key;
        $json = new JSON(JSON_LOOSE_TYPE);

        $infotxt = 'plugin.info.txt';
        if($is_template) $infotxt = 'template.info.txt';
        $url = 'https://api.github.com/repos/'.$user.'/'.$repo.'/contents/'.$infotxt;
        $this->info($url);

        // get the current file
        $response = $http->get($url);
        if(!$response){
            $this->error($http->error);
            $this->error($http->resp_body);
            $this->fatal('Failed to talk to github API for fetching file.');
        }
        $response = $json->decode($response);
        $sha = $response['sha'];

        // replace the date
        $content = base64_decode($response['content']);
        $newcontent = preg_replace('/(\n[ \t]*date[ \t]+)'.$search.'/s', '${1}'.$date, $content);
        if($content == $newcontent) {
            $this->fatal('Failed to update version in info.txt');
        }

        // prepare new data
        $data = array (
            'sha' => $sha,
            'message' => 'Version upped',
            'content' => base64_encode($newcontent)
        );
        $data = $json->encode($data);

        // update at github
        $http->headers['Content-Type'] = 'application/json';
        $http->headers['Content-Length'] = strlen($data);
        $response = $http->sendRequest($url, $data, 'PUT');
        if(!$response){
            $this->error($http->error);
            $this->error($http->resp_body);
            $this->fatal('Failed to talk to github API for updating file.');
        }
        $this->success('Updated '.$infotxt.' at github.');
    }

    /**
     * Return the date of the last significant commit
     *
     * @param string $user The github user owning the git repository (might be a organization)
     * @param string $repo The git repositor name at github
     * @return string
     */
    protected function fetchLastCommit($user, $repo) {
        $http = new HTTPClient();
        $http->headers['Accept'] = 'application/vnd.github.v3+json';
        $http->user = $this->github_user;
        $http->pass = $this->github_key;
        $json = new JSON(JSON_LOOSE_TYPE);

        $url = 'https://api.github.com/repos/'.$user.'/'.$repo.'/commits?per_page=100';
        $commits = $http->get($url);
        if(!$commits) {
            $this->error($http->error);
            $this->error($http->resp_body);
            $this->fatal('Failed to talk to github API for fetching commits.');
        }

        $commits = $json->decode($commits);
        foreach($commits as $commit) {
            if(preg_match('/^Merge/i', $commit['commit']['message'])) continue; // skip merges;
            if($commit['commit']['committer']['email'] == 'translate@dokuwiki.org') continue; //skip translations

            return substr($commit['commit']['author']['date'], 0, 10);
        }

        return date('Y-m-d');
    }
}

// Main
$cli = new VersionFixCLI();
$cli->run();