<?php

namespace dokuwiki\Remote;

use Doku_Renderer_xhtml;
use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Remote\Response\Link;
use dokuwiki\Remote\Response\Media;
use dokuwiki\Remote\Response\MediaChange;
use dokuwiki\Remote\Response\Page;
use dokuwiki\Remote\Response\PageChange;
use dokuwiki\Remote\Response\PageHit;
use dokuwiki\Remote\Response\User;
use dokuwiki\Utf8\Sort;

/**
 * Provides the core methods for the remote API.
 * The methods are ordered in 'wiki.<method>' and 'dokuwiki.<method>' namespaces
 */
class ApiCore
{
    /** @var int Increased whenever the API is changed */
    public const API_VERSION = 14;

    /**
     * Returns details about the core methods
     *
     * @return array
     */
    public function getMethods()
    {
        return [
            'core.getAPIVersion' => (new ApiCall([$this, 'getAPIVersion'], 'info'))->setPublic(),

            'core.getWikiVersion' => new ApiCall('getVersion', 'info'),
            'core.getWikiTitle' => (new ApiCall([$this, 'getWikiTitle'], 'info'))->setPublic(),
            'core.getWikiTime' => (new ApiCall([$this, 'getWikiTime'], 'info')),

            'core.login' => (new ApiCall([$this, 'login'], 'user'))->setPublic(),
            'core.logoff' => new ApiCall([$this, 'logoff'], 'user'),
            'core.whoAmI' => (new ApiCall([$this, 'whoAmI'], 'user')),
            'core.aclCheck' => new ApiCall([$this, 'aclCheck'], 'user'),

            'core.listPages' => new ApiCall([$this, 'listPages'], 'pages'),
            'core.searchPages' => new ApiCall([$this, 'searchPages'], 'pages'),
            'core.getRecentPageChanges' => new ApiCall([$this, 'getRecentPageChanges'], 'pages'),

            'core.getPage' => (new ApiCall([$this, 'getPage'], 'pages')),
            'core.getPageHTML' => (new ApiCall([$this, 'getPageHTML'], 'pages')),
            'core.getPageInfo' => (new ApiCall([$this, 'getPageInfo'], 'pages')),
            'core.getPageHistory' => new ApiCall([$this, 'getPageHistory'], 'pages'),
            'core.getPageLinks' => new ApiCall([$this, 'getPageLinks'], 'pages'),
            'core.getPageBackLinks' => new ApiCall([$this, 'getPageBackLinks'], 'pages'),

            'core.lockPages' => new ApiCall([$this, 'lockPages'], 'pages'),
            'core.unlockPages' => new ApiCall([$this, 'unlockPages'], 'pages'),
            'core.savePage' => new ApiCall([$this, 'savePage'], 'pages'),
            'core.appendPage' => new ApiCall([$this, 'appendPage'], 'pages'),

            'core.listMedia' => new ApiCall([$this, 'listMedia'], 'media'),
            'core.getRecentMediaChanges' => new ApiCall([$this, 'getRecentMediaChanges'], 'media'),

            'core.getMedia' => new ApiCall([$this, 'getMedia'], 'media'),
            'core.getMediaInfo' => new ApiCall([$this, 'getMediaInfo'], 'media'),
            'core.getMediaUsage' => new ApiCall([$this, 'getMediaUsage'], 'media'),
            'core.getMediaHistory' => new ApiCall([$this, 'getMediaHistory'], 'media'),

            'core.saveMedia' => new ApiCall([$this, 'saveMedia'], 'media'),
            'core.deleteMedia' => new ApiCall([$this, 'deleteMedia'], 'media'),
        ];
    }

    // region info

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
     * Returns the wiki title
     *
     * @link https://www.dokuwiki.org/config:title
     * @return string
     */
    public function getWikiTitle()
    {
        global $conf;
        return $conf['title'];
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
    public function getWikiTime()
    {
        return time();
    }

    // endregion

    // region user

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
     * Use of this mechanism is discouraged. Using token authentication is preferred.
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
     * Info about the currently authenticated user
     *
     * @return User
     */
    public function whoAmI()
    {
        return new User();
    }

    /**
     * Check ACL Permissions
     *
     * This call allows to check the permissions for a given page/media and user/group combination.
     * If no user/group is given, the current user is used.
     *
     * Read the link below to learn more about the permission levels.
     *
     * @link https://www.dokuwiki.org/acl#background_info
     * @param string $page A page or media ID
     * @param string $user username
     * @param string[] $groups array of groups
     * @return int permission level
     * @throws RemoteException
     */
    public function aclCheck($page, $user = '', $groups = [])
    {
        /** @var AuthPlugin $auth */
        global $auth;

        $page = $this->checkPage($page, 0, false, AUTH_NONE);

        if ($user === '') {
            return auth_quickaclcheck($page);
        } else {
            if ($groups === []) {
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

    // endregion

    // region pages

    /**
     * List all pages in the given namespace (and below)
     *
     * Setting the `depth` to `0` and the `namespace` to `""` will return all pages in the wiki.
     *
     * Note: author information is not available in this call.
     *
     * @param string $namespace The namespace to search. Empty string for root namespace
     * @param int $depth How deep to search. 0 for all subnamespaces
     * @param bool $hash Whether to include a MD5 hash of the page content
     * @return Page[] A list of matching pages
     * @todo might be a good idea to replace search_allpages with search_universal
     */
    public function listPages($namespace = '', $depth = 1, $hash = false)
    {
        global $conf;

        $namespace = cleanID($namespace);

        // shortcut for all pages
        if ($namespace === '' && $depth === 0) {
            return $this->getAllPages($hash);
        }

        // search_allpages handles depth weird, we need to add the given namespace depth
        if ($depth) {
            $depth += substr_count($namespace, ':') + 1;
        }

        // run our search iterator to get the pages
        $dir = utf8_encodeFN(str_replace(':', '/', $namespace));
        $data = [];
        $opts['skipacl'] = 0;
        $opts['depth'] = $depth;
        $opts['hash'] = $hash;
        search($data, $conf['datadir'], 'search_allpages', $opts, $dir);

        return array_map(static fn($item) => new Page(
            $item['id'],
            0, // we're searching current revisions only
            $item['mtime'],
            '', // not returned by search_allpages
            $item['size'],
            null, // not returned by search_allpages
            $item['hash'] ?? ''
        ), $data);
    }

    /**
     * Get all pages at once
     *
     * This is uses the page index and is quicker than iterating which is done in listPages()
     *
     * @return Page[] A list of all pages
     * @see listPages()
     */
    protected function getAllPages($hash = false)
    {
        $list = [];
        $pages = idx_get_indexer()->getPages();
        Sort::ksort($pages);

        foreach (array_keys($pages) as $idx) {
            $perm = auth_quickaclcheck($pages[$idx]);
            if ($perm < AUTH_READ || isHiddenPage($pages[$idx]) || !page_exists($pages[$idx])) {
                continue;
            }

            $page = new Page($pages[$idx], 0, 0, '', null, $perm);
            if ($hash) $page->calculateHash();

            $list[] = $page;
        }

        return $list;
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
     * @return PageHit[] A list of matching pages
     */
    public function searchPages($query)
    {
        $regex = [];
        $data = ft_pageSearch($query, $regex);
        $pages = [];

        // prepare additional data
        $idx = 0;
        foreach ($data as $id => $score) {
            if ($idx < FT_SNIPPET_NUMBER) {
                $snippet = ft_snippet($id, $regex);
                $idx++;
            } else {
                $snippet = '';
            }

            $pages[] = new PageHit(
                $id,
                $snippet,
                $score,
                useHeading('navigation') ? p_get_first_heading($id) : $id
            );
        }
        return $pages;
    }

    /**
     * Get recent page changes
     *
     * Returns a list of recent changes to wiki pages. The results can be limited to changes newer than
     * a given timestamp.
     *
     * Only changes within the configured `$conf['recent']` range are returned. This is the default
     * when no timestamp is given.
     *
     * @link https://www.dokuwiki.org/config:recent
     * @param int $timestamp Only show changes newer than this unix timestamp
     * @return PageChange[]
     * @author Michael Klier <chi@chimeric.de>
     * @author Michael Hamann <michael@content-space.de>
     */
    public function getRecentPageChanges($timestamp = 0)
    {
        $recents = getRecentsSince($timestamp);

        $changes = [];
        foreach ($recents as $recent) {
            $changes[] = new PageChange(
                $recent['id'],
                $recent['date'],
                $recent['user'],
                $recent['ip'],
                $recent['sum'],
                $recent['type'],
                $recent['sizechange']
            );
        }

        return $changes;
    }

    /**
     * Get a wiki page's syntax
     *
     * Returns the syntax of the given page. When no revision is given, the current revision is returned.
     *
     * A non-existing page (or revision) will return an empty string usually. For the current revision
     * a page template will be returned if configured.
     *
     * Read access is required for the page.
     *
     * @param string $page wiki page id
     * @param int $rev Revision timestamp to access an older revision
     * @return string the syntax of the page
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    public function getPage($page, $rev = 0)
    {
        $page = $this->checkPage($page, $rev, false);

        $text = rawWiki($page, $rev);
        if (!$text && !$rev) {
            return pageTemplate($page);
        } else {
            return $text;
        }
    }

    /**
     * Return a wiki page rendered to HTML
     *
     * The page is rendered to HTML as it would be in the wiki. The HTML consist only of the data for the page
     * content itself, no surrounding structural tags, header, footers, sidebars etc are returned.
     *
     * References in the HTML are relative to the wiki base URL unless the `canonical` configuration is set.
     *
     * If the page does not exist, an error is returned.
     *
     * @link https://www.dokuwiki.org/config:canonical
     * @param string $page page id
     * @param int $rev revision timestamp
     * @return string Rendered HTML for the page
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    public function getPageHTML($page, $rev = 0)
    {
        $page = $this->checkPage($page, $rev);

        return (string)p_wiki_xhtml($page, $rev, false);
    }

    /**
     * Return some basic data about a page
     *
     * The call will return an error if the requested page does not exist.
     *
     * Read access is required for the page.
     *
     * @param string $page page id
     * @param int $rev revision timestamp
     * @param bool $author whether to include the author information
     * @param bool $hash whether to include the MD5 hash of the page content
     * @return Page
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    public function getPageInfo($page, $rev = 0, $author = false, $hash = false)
    {
        $page = $this->checkPage($page, $rev);

        $result = new Page($page, $rev);
        if ($author) $result->retrieveAuthor();
        if ($hash) $result->calculateHash();

        return $result;
    }

    /**
     * Returns a list of available revisions of a given wiki page
     *
     * The number of returned pages is set by `$conf['recent']`, but non accessible revisions
     * are skipped, so less than that may be returned.
     *
     * @link https://www.dokuwiki.org/config:recent
     * @param string $page page id
     * @param int $first skip the first n changelog lines, 0 starts at the current revision
     * @return PageChange[]
     * @throws AccessDeniedException
     * @throws RemoteException
     * @author Michael Klier <chi@chimeric.de>
     */
    public function getPageHistory($page, $first = 0)
    {
        global $conf;

        $page = $this->checkPage($page, 0, false);

        $pagelog = new PageChangeLog($page);
        $pagelog->setChunkSize(1024);
        // old revisions are counted from 0, so we need to subtract 1 for the current one
        $revisions = $pagelog->getRevisions($first - 1, $conf['recent']);

        $result = [];
        foreach ($revisions as $rev) {
            if (!page_exists($page, $rev)) continue; // skip non-existing revisions
            $info = $pagelog->getRevisionInfo($rev);

            $result[] = new PageChange(
                $page,
                $rev,
                $info['user'],
                $info['ip'],
                $info['sum'],
                $info['type'],
                $info['sizechange']
            );
        }

        return $result;
    }

    /**
     * Get a page's links
     *
     * This returns a list of links found in the given page. This includes internal, external and interwiki links
     *
     * If a link occurs multiple times on the page, it will be returned multiple times.
     *
     * Read access for the given page is needed and page has to exist.
     *
     * @param string $page page id
     * @return Link[] A list of links found on the given page
     * @throws AccessDeniedException
     * @throws RemoteException
     * @todo returning link titles would be a nice addition
     * @todo hash handling seems not to be correct
     * @todo maybe return the same link only once?
     * @author Michael Klier <chi@chimeric.de>
     */
    public function getPageLinks($page)
    {
        $page = $this->checkPage($page);

        // resolve page instructions
        $ins = p_cached_instructions(wikiFN($page), false, $page);

        // instantiate new Renderer - needed for interwiki links
        $Renderer = new Doku_Renderer_xhtml();
        $Renderer->interwiki = getInterwiki();

        // parse instructions
        $links = [];
        foreach ($ins as $in) {
            switch ($in[0]) {
                case 'internallink':
                    $links[] = new Link('local', $in[1][0], wl($in[1][0]));
                    break;
                case 'externallink':
                    $links[] = new Link('extern', $in[1][0], $in[1][0]);
                    break;
                case 'interwikilink':
                    $url = $Renderer->_resolveInterWiki($in[1][2], $in[1][3]);
                    $links[] = new Link('interwiki', $in[1][0], $url);
                    break;
            }
        }

        return ($links);
    }

    /**
     * Get a page's backlinks
     *
     * A backlink is a wiki link on another page that links to the given page.
     *
     * Only links from pages readable by the current user are returned. The page itself
     * needs to be readable. Otherwise an error is returned.
     *
     * @param string $page page id
     * @return string[] A list of pages linking to the given page
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    public function getPageBackLinks($page)
    {
        $page = $this->checkPage($page, 0, false);

        return ft_backlinks($page);
    }

    /**
     * Lock the given set of pages
     *
     * This call will try to lock all given pages. It will return a list of pages that were
     * successfully locked. If a page could not be locked, eg. because a different user is
     * currently holding a lock, that page will be missing from the returned list.
     *
     * You should always ensure that the list of returned pages matches the given list of
     * pages. It's up to you to decide how to handle failed locking.
     *
     * Note: you can only lock pages that you have write access for. It is possible to create
     * a lock for a page that does not exist, yet.
     *
     * Note: it is not necessary to lock a page before saving it. The `savePage()` call will
     * automatically lock and unlock the page for you. However if you plan to do related
     * operations on multiple pages, locking them all at once beforehand can be useful.
     *
     * @param string[] $pages A list of pages to lock
     * @return string[] A list of pages that were successfully locked
     */
    public function lockPages($pages)
    {
        $locked = [];

        foreach ($pages as $id) {
            $id = cleanID($id);
            if ($id === '') continue;
            if (auth_quickaclcheck($id) < AUTH_EDIT || checklock($id)) {
                continue;
            }
            lock($id);
            $locked[] = $id;
        }
        return $locked;
    }

    /**
     * Unlock the given set of pages
     *
     * This call will try to unlock all given pages. It will return a list of pages that were
     * successfully unlocked. If a page could not be unlocked, eg. because a different user is
     * currently holding a lock, that page will be missing from the returned list.
     *
     * You should always ensure that the list of returned pages matches the given list of
     * pages. It's up to you to decide how to handle failed unlocking.
     *
     * Note: you can only unlock pages that you have write access for.
     *
     * @param string[] $pages A list of pages to unlock
     * @return string[] A list of pages that were successfully unlocked
     */
    public function unlockPages($pages)
    {
        $unlocked = [];

        foreach ($pages as $id) {
            $id = cleanID($id);
            if ($id === '') continue;
            if (auth_quickaclcheck($id) < AUTH_EDIT || !unlock($id)) {
                continue;
            }
            $unlocked[] = $id;
        }

        return $unlocked;
    }

    /**
     * Save a wiki page
     *
     * Saves the given wiki text to the given page. If the page does not exist, it will be created.
     * Just like in the wiki, saving an empty text will delete the page.
     *
     * You need write permissions for the given page and the page may not be locked by another user.
     *
     * @param string $page page id
     * @param string $text wiki text
     * @param string $summary edit summary
     * @param bool $isminor whether this is a minor edit
     * @return bool Returns true on success
     * @throws AccessDeniedException no write access for page
     * @throws RemoteException no id, empty new page or locked
     * @author Michael Klier <chi@chimeric.de>
     */
    public function savePage($page, $text, $summary = '', $isminor = false)
    {
        global $TEXT;
        global $lang;

        $page = $this->checkPage($page, 0, false, AUTH_EDIT);
        $TEXT = cleanText($text);


        if (!page_exists($page) && trim($TEXT) == '') {
            throw new RemoteException('Refusing to write an empty new wiki page', 132);
        }

        // Check, if page is locked
        if (checklock($page)) {
            throw new RemoteException('The page is currently locked', 133);
        }

        // SPAM check
        if (checkwordblock()) {
            throw new RemoteException('The page content was blocked', 134);
        }

        // autoset summary on new pages
        if (!page_exists($page) && empty($summary)) {
            $summary = $lang['created'];
        }

        // autoset summary on deleted pages
        if (page_exists($page) && empty($TEXT) && empty($summary)) {
            $summary = $lang['deleted'];
        }

        // FIXME auto set a summary in other cases "API Edit" might be a good idea?

        lock($page);
        saveWikiText($page, $TEXT, $summary, $isminor);
        unlock($page);

        // run the indexer if page wasn't indexed yet
        idx_addPage($page);

        return true;
    }

    /**
     * Appends text to the end of a wiki page
     *
     * If the page does not exist, it will be created. If a page template for the non-existant
     * page is configured, the given text will appended to that template.
     *
     * The call will create a new page revision.
     *
     * You need write permissions for the given page.
     *
     * @param string $page page id
     * @param string $text wiki text
     * @param string $summary edit summary
     * @param bool $isminor whether this is a minor edit
     * @return bool Returns true on success
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    public function appendPage($page, $text, $summary = '', $isminor = false)
    {
        $currentpage = $this->getPage($page);
        if (!is_string($currentpage)) {
            $currentpage = '';
        }
        return $this->savePage($page, $currentpage . $text, $summary, $isminor);
    }

    // endregion

    // region media

    /**
     * List all media files in the given namespace (and below)
     *
     * Setting the `depth` to `0` and the `namespace` to `""` will return all media files in the wiki.
     *
     * When `pattern` is given, it needs to be a valid regular expression as understood by PHP's
     * `preg_match()` including delimiters.
     * The pattern is matched against the full media ID, including the namespace.
     *
     * @link https://www.php.net/manual/en/reference.pcre.pattern.syntax.php
     * @param string $namespace The namespace to search. Empty string for root namespace
     * @param string $pattern A regular expression to filter the returned files
     * @param int $depth How deep to search. 0 for all subnamespaces
     * @param bool $hash Whether to include a MD5 hash of the media content
     * @return Media[]
     * @author Gina Haeussge <osd@foosel.net>
     */
    public function listMedia($namespace = '', $pattern = '', $depth = 1, $hash = false)
    {
        global $conf;

        $namespace = cleanID($namespace);

        $options = [
            'skipacl' => 0,
            'depth' => $depth,
            'hash' => $hash,
            'pattern' => $pattern,
        ];

        $dir = utf8_encodeFN(str_replace(':', '/', $namespace));
        $data = [];
        search($data, $conf['mediadir'], 'search_media', $options, $dir);
        return array_map(static fn($item) => new Media(
            $item['id'],
            0, // we're searching current revisions only
            $item['mtime'],
            $item['size'],
            $item['perm'],
            $item['isimg'],
            $item['hash'] ?? ''
        ), $data);
    }

    /**
     * Get recent media changes
     *
     * Returns a list of recent changes to media files. The results can be limited to changes newer than
     * a given timestamp.
     *
     * Only changes within the configured `$conf['recent']` range are returned. This is the default
     * when no timestamp is given.
     *
     * @link https://www.dokuwiki.org/config:recent
     * @param int $timestamp Only show changes newer than this unix timestamp
     * @return MediaChange[]
     * @author Michael Klier <chi@chimeric.de>
     * @author Michael Hamann <michael@content-space.de>
     */
    public function getRecentMediaChanges($timestamp = 0)
    {

        $recents = getRecentsSince($timestamp, null, '', RECENTS_MEDIA_CHANGES);

        $changes = [];
        foreach ($recents as $recent) {
            $changes[] = new MediaChange(
                $recent['id'],
                $recent['date'],
                $recent['user'],
                $recent['ip'],
                $recent['sum'],
                $recent['type'],
                $recent['sizechange']
            );
        }

        return $changes;
    }

    /**
     * Get a media file's content
     *
     * Returns the content of the given media file. When no revision is given, the current revision is returned.
     *
     * @link https://en.wikipedia.org/wiki/Base64
     * @param string $media file id
     * @param int $rev revision timestamp
     * @return string Base64 encoded media file contents
     * @throws AccessDeniedException no permission for media
     * @throws RemoteException not exist
     * @author Gina Haeussge <osd@foosel.net>
     *
     */
    public function getMedia($media, $rev = 0)
    {
        $media = cleanID($media);
        if (auth_quickaclcheck($media) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this media file', 211);
        }

        // was the current revision requested?
        if ($this->isCurrentMediaRev($media, $rev)) {
            $rev = 0;
        }

        $file = mediaFN($media, $rev);
        if (!@ file_exists($file)) {
            throw new RemoteException('The requested media file (revision) does not exist', 221);
        }

        $data = io_readFile($file, false);
        return base64_encode($data);
    }

    /**
     * Return info about a media file
     *
     * The call will return an error if the requested media file does not exist.
     *
     * Read access is required for the media file.
     *
     * @param string $media file id
     * @param int $rev revision timestamp
     * @param bool $author whether to include the author information
     * @param bool $hash whether to include the MD5 hash of the media content
     * @return Media
     * @throws AccessDeniedException no permission for media
     * @throws RemoteException if not exist
     * @author Gina Haeussge <osd@foosel.net>
     */
    public function getMediaInfo($media, $rev = 0, $author = false, $hash = false)
    {
        $media = cleanID($media);
        if (auth_quickaclcheck($media) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this media file', 211);
        }

        // was the current revision requested?
        if ($this->isCurrentMediaRev($media, $rev)) {
            $rev = 0;
        }

        if (!media_exists($media, $rev)) {
            throw new RemoteException('The requested media file does not exist', 221);
        }

        $info = new Media($media, $rev);
        if ($hash) $info->calculateHash();
        if ($author) $info->retrieveAuthor();

        return $info;
    }

    /**
     * Returns the pages that use a given media file
     *
     * The call will return an error if the requested media file does not exist.
     *
     * Read access is required for the media file.
     *
     * Since API Version 13
     *
     * @param string $media file id
     * @return string[] A list of pages linking to the given page
     * @throws AccessDeniedException no permission for media
     * @throws RemoteException if not exist
     */
    public function getMediaUsage($media)
    {
        $media = cleanID($media);
        if (auth_quickaclcheck($media) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this media file', 211);
        }
        if (!media_exists($media)) {
            throw new RemoteException('The requested media file does not exist', 221);
        }

        return ft_mediause($media);
    }

    /**
     * Returns a list of available revisions of a given media file
     *
     * The number of returned files is set by `$conf['recent']`, but non accessible revisions
     * are skipped, so less than that may be returned.
     *
     * Since API Version 14
     *
     * @link https://www.dokuwiki.org/config:recent
     * @param string $media file id
     * @param int $first skip the first n changelog lines, 0 starts at the current revision
     * @return MediaChange[]
     * @throws AccessDeniedException
     * @throws RemoteException
     * @author
     */
    public function getMediaHistory($media, $first = 0)
    {
        global $conf;

        $media = cleanID($media);
        // check that this media exists
        if (auth_quickaclcheck($media) < AUTH_READ) {
            throw new AccessDeniedException('You are not allowed to read this media file', 211);
        }
        if (!media_exists($media, 0)) {
            throw new RemoteException('The requested media file does not exist', 221);
        }

        $medialog = new MediaChangeLog($media);
        $medialog->setChunkSize(1024);
        // old revisions are counted from 0, so we need to subtract 1 for the current one
        $revisions = $medialog->getRevisions($first - 1, $conf['recent']);

        $result = [];
        foreach ($revisions as $rev) {
            // the current revision needs to be checked against the current file path
            $check = $this->isCurrentMediaRev($media, $rev) ? '' : $rev;
            if (!media_exists($media, $check)) continue; // skip non-existing revisions

            $info = $medialog->getRevisionInfo($rev);

            $result[] = new MediaChange(
                $media,
                $rev,
                $info['user'],
                $info['ip'],
                $info['sum'],
                $info['type'],
                $info['sizechange']
            );
        }

        return $result;
    }

    /**
     * Uploads a file to the wiki
     *
     * The file data has to be passed as a base64 encoded string.
     *
     * @link https://en.wikipedia.org/wiki/Base64
     * @param string $media media id
     * @param string $base64 Base64 encoded file contents
     * @param bool $overwrite Should an existing file be overwritten?
     * @return bool Should always be true
     * @throws RemoteException
     * @author Michael Klier <chi@chimeric.de>
     */
    public function saveMedia($media, $base64, $overwrite = false)
    {
        $media = cleanID($media);
        $auth = auth_quickaclcheck(getNS($media) . ':*');

        if ($media === '') {
            throw new RemoteException('Empty or invalid media ID given', 231);
        }

        // clean up base64 encoded data
        $base64 = strtr($base64, [
            "\n" => '', // strip newlines
            "\r" => '', // strip carriage returns
            '-' => '+', // RFC4648 base64url
            '_' => '/', // RFC4648 base64url
            ' ' => '+', // JavaScript data uri
        ]);

        $data = base64_decode($base64, true);
        if ($data === false) {
            throw new RemoteException('Invalid base64 encoded data', 234);
        }

        if ($data === '') {
            throw new RemoteException('Empty file given', 235);
        }

        // save temporary file
        global $conf;
        $ftmp = $conf['tmpdir'] . '/' . md5($media . clientIP());
        @unlink($ftmp);
        io_saveFile($ftmp, $data);

        $res = media_save(['name' => $ftmp], $media, $overwrite, $auth, 'rename');
        if (is_array($res)) {
            throw new RemoteException('Failed to save media: ' . $res[0], 236);
        }
        return (bool)$res; // should always be true at this point
    }

    /**
     * Deletes a file from the wiki
     *
     * You need to have delete permissions for the file.
     *
     * @param string $media media id
     * @return bool Should always be true
     * @throws AccessDeniedException no permissions
     * @throws RemoteException file in use or not deleted
     * @author Gina Haeussge <osd@foosel.net>
     *
     */
    public function deleteMedia($media)
    {
        $media = cleanID($media);

        $auth = auth_quickaclcheck($media);
        $res = media_delete($media, $auth);
        if ($res & DOKU_MEDIA_DELETED) {
            return true;
        } elseif ($res & DOKU_MEDIA_NOT_AUTH) {
            throw new AccessDeniedException('You are not allowed to delete this media file', 212);
        } elseif ($res & DOKU_MEDIA_INUSE) {
            throw new RemoteException('Media file is still referenced', 232);
        } elseif (!media_exists($media)) {
            throw new RemoteException('The media file requested to delete does not exist', 221);
        } else {
            throw new RemoteException('Failed to delete media file', 233);
        }
    }

    /**
     * Check if the given revision is the current revision of this file
     *
     * @param string $id
     * @param int $rev
     * @return bool
     */
    protected function isCurrentMediaRev(string $id, int $rev)
    {
        $current = @filemtime(mediaFN($id));
        if ($current === $rev) return true;
        return false;
    }

    // endregion


    /**
     * Convenience method for page checks
     *
     * This method will perform multiple tasks:
     *
     * - clean the given page id
     * - disallow an empty page id
     * - check if the page exists (unless disabled)
     * - check if the user has the required access level (pass AUTH_NONE to skip)
     *
     * @param string $id page id
     * @param int $rev page revision
     * @param bool $existCheck
     * @param int $minAccess
     * @return string the cleaned page id
     * @throws AccessDeniedException
     * @throws RemoteException
     */
    private function checkPage($id, $rev = 0, $existCheck = true, $minAccess = AUTH_READ)
    {
        $id = cleanID($id);
        if ($id === '') {
            throw new RemoteException('Empty or invalid page ID given', 131);
        }

        if ($existCheck && !page_exists($id, $rev)) {
            throw new RemoteException('The requested page (revision) does not exist', 121);
        }

        if ($minAccess && auth_quickaclcheck($id) < $minAccess) {
            throw new AccessDeniedException('You are not allowed to read this page', 111);
        }

        return $id;
    }
}
