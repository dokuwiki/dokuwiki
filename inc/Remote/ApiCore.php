<?php

namespace dokuwiki\Remote;

use Doku_Renderer_xhtml;
use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Remote\Response\Page;
use dokuwiki\Utf8\Sort;

/**
 * Provides the core methods for the remote API.
 * The methods are ordered in 'wiki.<method>' and 'dokuwiki.<method>' namespaces
 */
class ApiCore
{
    /** @var int Increased whenever the API is changed */
    public const API_VERSION = 11;


    /** @var Api */
    private $api;

    /**
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * Returns details about the core methods
     *
     * @return array
     */
    public function getRemoteInfo()
    {
        return [
            'dokuwiki.getVersion' => new ApiCall('getVersion'),
            'dokuwiki.login' => (new ApiCall([$this, 'login']))
                ->setPublic(),
            'dokuwiki.logoff' => new ApiCall([$this, 'logoff']),
            'dokuwiki.getPagelist' => new ApiCall([$this, 'readNamespace']),
            'dokuwiki.search' => new ApiCall([$this, 'search']),
            'dokuwiki.getTime' => (new ApiCall([$this, 'time'])),
            'dokuwiki.setLocks' => new ApiCall([$this, 'setLocks']),
            'dokuwiki.getTitle' => (new ApiCall([$this, 'getTitle']))
                ->setPublic(),
            'dokuwiki.appendPage' => new ApiCall([$this, 'appendPage']),
            'dokuwiki.createUser' => new ApiCall([$this, 'createUser']),
            'dokuwiki.deleteUsers' => new ApiCall([$this, 'deleteUsers']),
            'wiki.getPage' => (new ApiCall([$this, 'rawPage']))
                ->limitArgs(['page']),
            'wiki.getPageVersion' => (new ApiCall([$this, 'rawPage']))
                ->setSummary('Get a specific revision of a wiki page'),
            'wiki.getPageHTML' => (new ApiCall([$this, 'htmlPage']))
                ->limitArgs(['page']),
            'wiki.getPageHTMLVersion' => (new ApiCall([$this, 'htmlPage']))
                ->setSummary('Get the HTML for a specific revision of a wiki page'),
            'wiki.getAllPages' => new ApiCall([$this, 'listPages']),
            'wiki.getAttachments' => new ApiCall([$this, 'listAttachments']),
            'wiki.getBackLinks' => new ApiCall([$this, 'listBackLinks']),
            'wiki.getPageInfo' => (new ApiCall([$this, 'pageInfo']))
                ->limitArgs(['page']),
            'wiki.getPageInfoVersion' => (new ApiCall([$this, 'pageInfo']))
                ->setSummary('Get some basic data about a specific revison of a wiki page'),
            'wiki.getPageVersions' => new ApiCall([$this, 'pageVersions']),
            'wiki.putPage' => new ApiCall([$this, 'putPage']),
            'wiki.listLinks' => new ApiCall([$this, 'listLinks']),
            'wiki.getRecentChanges' => new ApiCall([$this, 'getRecentChanges']),
            'wiki.getRecentMediaChanges' => new ApiCall([$this, 'getRecentMediaChanges']),
            'wiki.aclCheck' => new ApiCall([$this, 'aclCheck']),
            'wiki.putAttachment' => new ApiCall([$this, 'putAttachment']),
            'wiki.deleteAttachment' => new ApiCall([$this, 'deleteAttachment']),
            'wiki.getAttachment' => new ApiCall([$this, 'getAttachment']),
            'wiki.getAttachmentInfo' => new ApiCall([$this, 'getAttachmentInfo']),
            'dokuwiki.getXMLRPCAPIVersion' => (new ApiCall([$this, 'getAPIVersion']))->setPublic(),
            'wiki.getRPCVersionSupported' => (new ApiCall([$this, 'wikiRpcVersion']))->setPublic(),
        ];
    }

    /**
     * Return the current server time
     *
     * Returns a Unix timestamp (seconds since 1970-01-01 00:00:00 UTC).
     *
     * You can use this to compensate for differences between your client's time and the
     * server's time when working with last modified timestamps (revisions).
     *
     * @return int A unix timestamp
     */
    public function time()
    {
        return time();
    }

    /**
     * Return a raw wiki page
     *
     * @param string $page wiki page id
     * @param int $rev revision timestamp of the page
     * @return string the syntax of the page
     * @throws AccessDeniedException if no permission for page
     */
    public function rawPage($page, $rev = '')
    {
        $page = $this->resolvePageId($page);
        if (auth_quickaclcheck($page) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this file', 111);
        }
        $text = rawWiki($page, $rev);
        if (!$text) {
            return pageTemplate($page);
        } else {
            return $text;
        }
    }

    /**
     * Return a media file
     *
     * @param string $media file id
     * @return string media file contents
     * @throws AccessDeniedException no permission for media
     * @throws RemoteException not exist
     * @author Gina Haeussge <osd@foosel.net>
     *
     */
    public function getAttachment($media)
    {
        $media = cleanID($media);
        if (auth_quickaclcheck(getNS($media) . ':*') < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this file', 211);
        }

        $file = mediaFN($media);
        if (!@ file_exists($file)) {
            throw new RemoteException('The requested file does not exist', 221);
        }

        $data = io_readFile($file, false);
        return $this->api->toFile($data);
    }

    /**
     * Return info about a media file
     *
     * @param string $media file id
     * @return array
     * @author Gina Haeussge <osd@foosel.net>
     *
     */
    public function getAttachmentInfo($media)
    {
        $media = cleanID($media);
        $info = ['lastModified' => $this->api->toDate(0), 'size' => 0];

        $file = mediaFN($media);
        if (auth_quickaclcheck(getNS($media) . ':*') >= AUTH_READ) {
            if (file_exists($file)) {
                $info['lastModified'] = $this->api->toDate(filemtime($file));
                $info['size'] = filesize($file);
            } else {
                //Is it deleted media with changelog?
                $medialog = new MediaChangeLog($media);
                $revisions = $medialog->getRevisions(0, 1);
                if (!empty($revisions)) {
                    $info['lastModified'] = $this->api->toDate($revisions[0]);
                }
            }
        }

        return $info;
    }

    /**
     * Return a wiki page rendered to HTML
     *
     * @param string $page page id
     * @param string $rev revision timestamp
     * @return string Rendered HTML for the page
     * @throws AccessDeniedException no access to page
     */
    public function htmlPage($page, $rev = '')
    {
        $page = $this->resolvePageId($page);
        if (auth_quickaclcheck($page) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this page', 111);
        }
        return p_wiki_xhtml($page, $rev, false);
    }

    /**
     * List all pages
     *
     * This uses the search index and only returns pages that have been indexed already
     *
     * @return array[] A list of all pages with id, perms, size, lastModified
     */
    public function listPages()
    {
        $list = [];
        $pages = idx_get_indexer()->getPages();
        $pages = array_filter(array_filter($pages, 'isVisiblePage'), 'page_exists');
        Sort::ksort($pages);

        foreach (array_keys($pages) as $idx) {
            $perm = auth_quickaclcheck($pages[$idx]);
            if ($perm < AUTH_READ) {
                continue;
            }
            $list[] = new Page(['id' => $pages[$idx], 'perm' => $perm]);
        }

        return $list;
    }

    /**
     * List all pages in the given namespace (and below)
     *
     * @param string $ns
     * @param array $opts
     *    $opts['depth']   recursion level, 0 for all
     *    $opts['hash']    do md5 sum of content?
     * @return Page[] A list of matching pages with id, rev, mtime, size, (hash)
     */
    public function readNamespace($ns, $opts = [])
    {
        global $conf;

        if (!is_array($opts)) $opts = [];

        $ns = cleanID($ns);
        $dir = utf8_encodeFN(str_replace(':', '/', $ns));
        $data = [];
        $opts['skipacl'] = 0; // no ACL skipping for XMLRPC
        search($data, $conf['datadir'], 'search_allpages', $opts, $dir);

        $result = array_map(fn($item) => new Page($item), $data);


        return $result;
    }

    /**
     * Do a fulltext search
     *
     * This executes a full text search and returns the results. The query uses the standard
     * DokuWiki search syntax.
     *
     * Snippets are provided for the first 15 results only. The title is either the first heading
     * or the page id depending on the wiki's configuration.
     *
     * @link https://www.dokuwiki.org/search#syntax
     * @param string $query The search query as supported by the DokuWiki search
     * @return array[] A list of matching pages with id, score, rev, mtime, size, snippet, title
     */
    public function search($query)
    {
        $regex = [];
        $data = ft_pageSearch($query, $regex);
        $pages = [];

        // prepare additional data
        $idx = 0;
        foreach ($data as $id => $score) {
            $file = wikiFN($id);

            if ($idx < FT_SNIPPET_NUMBER) {
                $snippet = ft_snippet($id, $regex);
                $idx++;
            } else {
                $snippet = '';
            }

            $pages[] = [
                'id' => $id,
                'score' => (int)$score,
                'rev' => filemtime($file),
                'mtime' => filemtime($file),
                'size' => filesize($file),
                'snippet' => $snippet,
                'title' => useHeading('navigation') ? p_get_first_heading($id) : $id
            ];
        }
        return $pages;
    }

    /**
     * Returns the wiki title
     *
     * @link https://www.dokuwiki.org/config:title
     * @return string
     */
    public function getTitle()
    {
        global $conf;
        return $conf['title'];
    }

    /**
     * List all media files.
     *
     * Available options are 'recursive' for also including the subnamespaces
     * in the listing, and 'pattern' for filtering the returned files against
     * a regular expression matching their name.
     *
     * @param string $ns
     * @param array $options
     *   $options['depth']     recursion level, 0 for all
     *   $options['showmsg']   shows message if invalid media id is used
     *   $options['pattern']   check given pattern
     *   $options['hash']      add hashes to result list
     * @return array
     * @throws AccessDeniedException no access to the media files
     * @author Gina Haeussge <osd@foosel.net>
     *
     */
    public function listAttachments($ns, $options = [])
    {
        global $conf;

        $ns = cleanID($ns);

        if (!is_array($options)) $options = [];
        $options['skipacl'] = 0; // no ACL skipping for XMLRPC

        if (auth_quickaclcheck($ns . ':*') >= AUTH_READ) {
            $dir = utf8_encodeFN(str_replace(':', '/', $ns));

            $data = [];
            search($data, $conf['mediadir'], 'search_media', $options, $dir);
            $len = count($data);
            if (!$len) return [];

            for ($i = 0; $i < $len; $i++) {
                unset($data[$i]['meta']);
                $data[$i]['perms'] = $data[$i]['perm'];
                unset($data[$i]['perm']);
                $data[$i]['lastModified'] = $this->api->toDate($data[$i]['mtime']);
            }
            return $data;
        } else {
            throw new AccessDeniedException('You are not allowed to list media files.', 215);
        }
    }

    /**
     * Return a list of backlinks
     *
     * @param string $page page id
     * @return string[]
     */
    public function listBackLinks($page)
    {
        return ft_backlinks($this->resolvePageId($page));
    }

    /**
     * Return some basic data about a page
     *
     * @param string $page page id
     * @param string|int $rev revision timestamp or empty string
     * @return array
     * @throws AccessDeniedException no access for page
     * @throws RemoteException page not exist
     */
    public function pageInfo($page, $rev = '')
    {
        $page = $this->resolvePageId($page);
        if (auth_quickaclcheck($page) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this page', 111);
        }
        $file = wikiFN($page, $rev);
        $time = @filemtime($file);
        if (!$time) {
            throw new RemoteException('The requested page does not exist', 121);
        }

        // set revision to current version if empty, use revision otherwise
        // as the timestamps of old files are not necessarily correct
        if ($rev === '') {
            $rev = $time;
        }

        $pagelog = new PageChangeLog($page, 1024);
        $info = $pagelog->getRevisionInfo($rev);

        $data = [
            'name' => $page,
            'lastModified' => $this->api->toDate($rev),
            'author' => is_array($info) ? ($info['user'] ?: $info['ip']) : null,
            'version' => $rev
        ];

        return ($data);
    }

    /**
     * Save a wiki page
     *
     * Saves the given wiki text to the given page. If the page does not exist, it will be created.
     *
     * You need write permissions for the given page.
     *
     * @param string $page page id
     * @param string $text wiki text
     * @param array $params parameters: summary, minor edit
     * @return bool
     * @throws AccessDeniedException no write access for page
     * @throws RemoteException no id, empty new page or locked
     * @author Michael Klier <chi@chimeric.de>
     */
    public function putPage($page, $text, $params = [])
    {
        global $TEXT;
        global $lang;

        $page = $this->resolvePageId($page);
        $TEXT = cleanText($text);
        $sum = $params['sum'] ?? '';
        $minor = $params['minor'] ?? false;

        if (empty($page)) {
            throw new RemoteException('Empty page ID', 131);
        }

        if (!page_exists($page) && trim($TEXT) == '') {
            throw new RemoteException('Refusing to write an empty new wiki page', 132);
        }

        if (auth_quickaclcheck($page) < AUTH_EDIT) {
            throw new AccessDeniedException('You are not allowed to edit this page', 112);
        }

        // Check, if page is locked
        if (checklock($page)) {
            throw new RemoteException('The page is currently locked', 133);
        }

        // SPAM check
        if (checkwordblock()) {
            throw new RemoteException('Positive wordblock check', 134);
        }

        // autoset summary on new pages
        if (!page_exists($page) && empty($sum)) {
            $sum = $lang['created'];
        }

        // autoset summary on deleted pages
        if (page_exists($page) && empty($TEXT) && empty($sum)) {
            $sum = $lang['deleted'];
        }

        lock($page);

        saveWikiText($page, $TEXT, $sum, $minor);

        unlock($page);

        // run the indexer if page wasn't indexed yet
        idx_addPage($page);

        return true;
    }

    /**
     * Appends text to the end of a wiki page
     *
     * If the page does not exist, it will be created. The call will create a new page revision.
     *
     * You need write permissions for the given page.
     *
     * @param string $page page id
     * @param string $text wiki text
     * @param array $params such as summary,minor
     * @return bool|string
     * @throws RemoteException
     */
    public function appendPage($page, $text, $params = [])
    {
        $currentpage = $this->rawPage($page);
        if (!is_string($currentpage)) {
            return $currentpage;
        }
        return $this->putPage($page, $currentpage . $text, $params);
    }

    /**
     * Create a new user
     *
     * If no password is provided, a password is auto generated.
     *
     * You need to be a superuser to create users.
     *
     * @param array[] $userStruct User struct with user, password, name, mail, groups, notify
     * @return boolean Was the user successfully created?
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    public function createUser($userStruct)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to create users', 114);
        }

        /** @var AuthPlugin $auth */
        global $auth;

        if (!$auth->canDo('addUser')) {
            throw new AccessDeniedException(
                sprintf('Authentication backend %s can\'t do addUser', $auth->getPluginName()),
                114
            );
        }

        $user = trim($auth->cleanUser($userStruct['user'] ?? ''));
        $password = $userStruct['password'] ?? '';
        $name = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/', '', $userStruct['name'] ?? ''));
        $mail = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/', '', $userStruct['mail'] ?? ''));
        $groups = $userStruct['groups'] ?? [];

        $notify = (bool)($userStruct['notify'] ?? false);

        if ($user === '') throw new RemoteException('empty or invalid user', 401);
        if ($name === '') throw new RemoteException('empty or invalid user name', 402);
        if (!mail_isvalid($mail)) throw new RemoteException('empty or invalid mail address', 403);

        if ((string)$password === '') {
            $password = auth_pwgen($user);
        }

        if (!is_array($groups) || $groups === []) {
            $groups = null;
        }

        $ok = $auth->triggerUserMod('create', [$user, $password, $name, $mail, $groups]);

        if ($ok !== false && $ok !== null) {
            $ok = true;
        }

        if ($ok) {
            if ($notify) {
                auth_sendPassword($user, $password);
            }
        }

        return $ok;
    }


    /**
     * Remove one or more users from the list of registered users
     *
     * You need to be a superuser to delete users.
     *
     * @param string[] $usernames List of usernames to remove
     * @return bool if the users were successfully deleted
     * @throws AccessDeniedException
     */
    public function deleteUsers($usernames)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to delete users', 114);
        }
        /** @var AuthPlugin $auth */
        global $auth;
        return (bool)$auth->triggerUserMod('delete', [$usernames]);
    }

    /**
     * Uploads a file to the wiki
     *
     * @param string $media media id
     * @param string $data file contents
     * @param array $params such as overwrite
     * @return false|string
     * @throws RemoteException
     * @author Michael Klier <chi@chimeric.de>
     */
    public function putAttachment($media, $data, $params = [])
    {
        $media = cleanID($media);
        $auth = auth_quickaclcheck(getNS($media) . ':*');

        if (!isset($media)) {
            throw new RemoteException('Filename not given.', 231);
        }

        global $conf;

        $ftmp = $conf['tmpdir'] . '/' . md5($media . clientIP());

        // save temporary file
        @unlink($ftmp);
        io_saveFile($ftmp, $data);

        $res = media_save(['name' => $ftmp], $media, $params['ow'], $auth, 'rename');
        if (is_array($res)) {
            throw new RemoteException($res[0], -$res[1]);
        } else {
            return $res;
        }
    }

    /**
     * Deletes a file from the wiki
     *
     * You need to have delete permissions for the file.
     *
     * @param string $media media id
     * @return int
     * @throws AccessDeniedException no permissions
     * @throws RemoteException file in use or not deleted
     * @author Gina Haeussge <osd@foosel.net>
     *
     */
    public function deleteAttachment($media)
    {
        $media = cleanID($media);
        $auth = auth_quickaclcheck(getNS($media) . ':*');
        $res = media_delete($media, $auth);
        if ($res & DOKU_MEDIA_DELETED) {
            return 0;
        } elseif ($res & DOKU_MEDIA_NOT_AUTH) {
            throw new AccessDeniedException('You don\'t have permissions to delete files.', 212);
        } elseif ($res & DOKU_MEDIA_INUSE) {
            throw new RemoteException('File is still referenced', 232);
        } else {
            throw new RemoteException('Could not delete file', 233);
        }
    }

    /**
     * Returns the permissions of a given wiki page for the current user or another user
     *
     * @param string $page page id
     * @param string|null $user username
     * @param array|null $groups array of groups
     * @return int permission level
     */
    public function aclCheck($page, $user = null, $groups = null)
    {
        /** @var AuthPlugin $auth */
        global $auth;

        $page = $this->resolvePageId($page);
        if ($user === null) {
            return auth_quickaclcheck($page);
        } else {
            if ($groups === null) {
                $userinfo = $auth->getUserData($user);
                if ($userinfo === false) {
                    $groups = [];
                } else {
                    $groups = $userinfo['grps'];
                }
            }
            return auth_aclcheck($page, $user, $groups);
        }
    }

    /**
     * Lists all links contained in a wiki page
     *
     * @param string $page page id
     * @return array
     * @throws AccessDeniedException  no read access for page
     * @author Michael Klier <chi@chimeric.de>
     *
     */
    public function listLinks($page)
    {
        $page = $this->resolvePageId($page);
        if (auth_quickaclcheck($page) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this page', 111);
        }
        $links = [];

        // resolve page instructions
        $ins = p_cached_instructions(wikiFN($page));

        // instantiate new Renderer - needed for interwiki links
        $Renderer = new Doku_Renderer_xhtml();
        $Renderer->interwiki = getInterwiki();

        // parse parse instructions
        foreach ($ins as $in) {
            $link = [];
            switch ($in[0]) {
                case 'internallink':
                    $link['type'] = 'local';
                    $link['page'] = $in[1][0];
                    $link['href'] = wl($in[1][0]);
                    $links[] = $link;
                    break;
                case 'externallink':
                    $link['type'] = 'extern';
                    $link['page'] = $in[1][0];
                    $link['href'] = $in[1][0];
                    $links[] = $link;
                    break;
                case 'interwikilink':
                    $url = $Renderer->_resolveInterWiki($in[1][2], $in[1][3]);
                    $link['type'] = 'extern';
                    $link['page'] = $url;
                    $link['href'] = $url;
                    $links[] = $link;
                    break;
            }
        }

        return ($links);
    }

    /**
     * Returns a list of recent changes since given timestamp
     *
     * The results are limited to date range configured in $conf['recent']
     *
     * @link https://www.dokuwiki.org/config:recent
     * @param int $timestamp unix timestamp
     * @return array
     * @throws RemoteException no valid timestamp
     * @author Michael Klier <chi@chimeric.de>
     *
     * @author Michael Hamann <michael@content-space.de>
     */
    public function getRecentChanges($timestamp)
    {
        if (strlen($timestamp) != 10) {
            throw new RemoteException('The provided value is not a valid timestamp', 311);
        }

        $recents = getRecentsSince($timestamp);

        $changes = [];

        foreach ($recents as $recent) {
            $change = [];
            $change['name'] = $recent['id'];
            $change['lastModified'] = $this->api->toDate($recent['date']);
            $change['author'] = $recent['user'];
            $change['version'] = $recent['date'];
            $change['perms'] = $recent['perms'];
            $change['size'] = @filesize(wikiFN($recent['id']));
            $changes[] = $change;
        }

        if ($changes !== []) {
            return $changes;
        } else {
            // in case we still have nothing at this point
            throw new RemoteException('There are no changes in the specified timeframe', 321);
        }
    }

    /**
     * Returns a list of recent media changes since given timestamp
     *
     * @param int $timestamp unix timestamp
     * @return array
     * @throws RemoteException no valid timestamp
     * @author Michael Klier <chi@chimeric.de>
     *
     * @author Michael Hamann <michael@content-space.de>
     */
    public function getRecentMediaChanges($timestamp)
    {
        if (strlen($timestamp) != 10)
            throw new RemoteException('The provided value is not a valid timestamp', 311);

        $recents = getRecentsSince($timestamp, null, '', RECENTS_MEDIA_CHANGES);

        $changes = [];

        foreach ($recents as $recent) {
            $change = [];
            $change['name'] = $recent['id'];
            $change['lastModified'] = $this->api->toDate($recent['date']);
            $change['author'] = $recent['user'];
            $change['version'] = $recent['date'];
            $change['perms'] = $recent['perms'];
            $change['size'] = @filesize(mediaFN($recent['id']));
            $changes[] = $change;
        }

        if ($changes !== []) {
            return $changes;
        } else {
            // in case we still have nothing at this point
            throw new RemoteException('There are no changes in the specified timeframe', 321);
        }
    }

    /**
     * Returns a list of available revisions of a given wiki page
     * Number of returned pages is set by $conf['recent']
     * However not accessible pages are skipped, so less than $conf['recent'] could be returned
     *
     * @param string $page page id
     * @param int $first skip the first n changelog lines
     *                      0 = from current(if exists)
     *                      1 = from 1st old rev
     *                      2 = from 2nd old rev, etc
     * @return array
     * @throws AccessDeniedException no read access for page
     * @throws RemoteException empty id
     * @author Michael Klier <chi@chimeric.de>
     *
     */
    public function pageVersions($page, $first = 0)
    {
        $page = $this->resolvePageId($page);
        if (auth_quickaclcheck($page) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this page', 111);
        }
        global $conf;

        $versions = [];

        if (empty($page)) {
            throw new RemoteException('Empty page ID', 131);
        }

        $first = (int)$first;
        $first_rev = $first - 1;
        $first_rev = max(0, $first_rev);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions($first_rev, $conf['recent']);

        if ($first == 0) {
            array_unshift($revisions, '');  // include current revision
            if (count($revisions) > $conf['recent']) {
                array_pop($revisions);          // remove extra log entry
            }
        }

        if (!empty($revisions)) {
            foreach ($revisions as $rev) {
                $file = wikiFN($page, $rev);
                $time = @filemtime($file);
                // we check if the page actually exists, if this is not the
                // case this can lead to less pages being returned than
                // specified via $conf['recent']
                if ($time) {
                    $pagelog->setChunkSize(1024);
                    $info = $pagelog->getRevisionInfo($rev ?: $time);
                    if (!empty($info)) {
                        $data = [];
                        $data['user'] = $info['user'];
                        $data['ip'] = $info['ip'];
                        $data['type'] = $info['type'];
                        $data['sum'] = $info['sum'];
                        $data['modified'] = $this->api->toDate($info['date']);
                        $data['version'] = $info['date'];
                        $versions[] = $data;
                    }
                }
            }
            return $versions;
        } else {
            return [];
        }
    }

    /**
     * The version of Wiki RPC API supported
     *
     * This is the version of the Wiki RPC specification implemented. Since that specification
     * is no longer maintained, this will always return 2
     *
     * You probably want to look at dokuwiki.getXMLRPCAPIVersion instead
     *
     * @return int
     */
    public function wikiRpcVersion()
    {
        return 2;
    }

    /**
     * Locks or unlocks a given batch of pages
     *
     * Give an associative array with two keys: lock and unlock. Both should contain a
     * list of pages to lock or unlock
     *
     * Returns an associative array with the keys locked, lockfail, unlocked and
     * unlockfail, each containing lists of pages.
     *
     * @param array[] $set list pages with ['lock' => [], 'unlock' => []]
     * @return array[] list of pages with ['locked' => [], 'lockfail' => [], 'unlocked' => [], 'unlockfail' => []]
     */
    public function setLocks($set)
    {
        $locked = [];
        $lockfail = [];
        $unlocked = [];
        $unlockfail = [];

        foreach ($set['lock'] as $id) {
            $id = $this->resolvePageId($id);
            if (auth_quickaclcheck($id) < AUTH_EDIT || checklock($id)) {
                $lockfail[] = $id;
            } else {
                lock($id);
                $locked[] = $id;
            }
        }

        foreach ($set['unlock'] as $id) {
            $id = $this->resolvePageId($id);
            if (auth_quickaclcheck($id) < AUTH_EDIT || !unlock($id)) {
                $unlockfail[] = $id;
            } else {
                $unlocked[] = $id;
            }
        }

        return [
            'locked' => $locked,
            'lockfail' => $lockfail,
            'unlocked' => $unlocked,
            'unlockfail' => $unlockfail
        ];
    }

    /**
     * Return the API version
     *
     * This is the version of the DokuWiki API. It increases whenever the API definition changes.
     *
     * When developing a client, you should check this version and make sure you can handle it.
     *
     * @return int
     */
    public function getAPIVersion()
    {
        return self::API_VERSION;
    }

    /**
     * Login
     *
     * This will use the given credentials and attempt to login the user. This will set the
     * appropriate cookies, which can be used for subsequent requests.
     *
     * Use of this mechanism is discouraged. Using token authentication is preferred.
     *
     * @param string $user The user name
     * @param string $pass The password
     * @return int If the login was successful
     */
    public function login($user, $pass)
    {
        global $conf;
        /** @var AuthPlugin $auth */
        global $auth;

        if (!$conf['useacl']) return 0;
        if (!$auth instanceof AuthPlugin) return 0;

        @session_start(); // reopen session for login
        $ok = null;
        if ($auth->canDo('external')) {
            $ok = $auth->trustExternal($user, $pass, false);
        }
        if ($ok === null) {
            $evdata = [
                'user' => $user,
                'password' => $pass,
                'sticky' => false,
                'silent' => true
            ];
            $ok = Event::createAndTrigger('AUTH_LOGIN_CHECK', $evdata, 'auth_login_wrapper');
        }
        session_write_close(); // we're done with the session

        return $ok;
    }

    /**
     * Log off
     *
     * Attempt to log out the current user, deleting the appropriate cookies
     *
     * @return int 0 on failure, 1 on success
     */
    public function logoff()
    {
        global $conf;
        global $auth;
        if (!$conf['useacl']) return 0;
        if (!$auth instanceof AuthPlugin) return 0;

        auth_logoff();

        return 1;
    }

    /**
     * Resolve page id
     *
     * @param string $id page id
     * @return string
     */
    private function resolvePageId($id)
    {
        $id = cleanID($id);
        if (empty($id)) {
            global $conf;
            $id = cleanID($conf['start']);
        }
        return $id;
    }
}
