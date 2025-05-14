<?php

namespace dokuwiki\Remote;

use dokuwiki\Utf8\PhpString;
use IXR\DataType\Base64;
use IXR\DataType\Date;

/**
 * Provides wrappers for the API calls as they existed in API Version 11
 *
 * No guarantees are made about the exact compatibility of the return values.
 *
 * @deprecated
 */
class LegacyApiCore extends ApiCore
{
    /** @inheritdoc */
    public function getMethods()
    {
        $methods = parent::getMethods();

        return array_merge(
            $methods,
            [
                'dokuwiki.getVersion' => new ApiCall([$this, 'legacyGetVersion'], 'legacy'),
                'dokuwiki.login' => (new ApiCall([$this, 'legacyLogin'], 'legacy'))->setPublic(),
                'dokuwiki.logoff' => new ApiCall([$this, 'legacyLogoff'], 'legacy'),
                'dokuwiki.getPagelist' => new ApiCall([$this, 'legacyGetPagelist'], 'legacy'),
                'dokuwiki.search' => new ApiCall([$this, 'legacySearch'], 'legacy'),
                'dokuwiki.getTime' => new ApiCall([$this, 'legacyGetTime'], 'legacy'),
                'dokuwiki.setLocks' => new ApiCall([$this, 'legacySetLocks'], 'legacy'),
                'dokuwiki.getTitle' => (new ApiCall([$this, 'legacyGetTitle'], 'legacy'))->setPublic(),
                'dokuwiki.appendPage' => new ApiCall([$this, 'legacyAppendPage'], 'legacy'),
                'dokuwiki.createUser' => new ApiCall([$this, 'legacyCreateUser'], 'legacy'),
                'dokuwiki.deleteUsers' => new ApiCall([$this, 'legacyDeleteUsers'], 'legacy'),
                'wiki.getPage' => new ApiCall([$this, 'legacyGetPage'], 'legacy'),
                'wiki.getPageVersion' => new ApiCall([$this, 'legacyGetPageVersion'], 'legacy'),
                'wiki.getPageHTML' => new ApiCall([$this, 'legacyGetPageHTML'], 'legacy'),
                'wiki.getPageHTMLVersion' => new ApiCall([$this, 'legacyGetPageHTMLVersion'], 'legacy'),
                'wiki.getAllPages' => new ApiCall([$this, 'legacyGetAllPages'], 'legacy'),
                'wiki.getAttachments' => new ApiCall([$this, 'legacyGetAttachments'], 'legacy'),
                'wiki.getBackLinks' => new ApiCall([$this, 'legacyGetBackLinks'], 'legacy'),
                'wiki.getPageInfo' => new ApiCall([$this, 'legacyGetPageInfo'], 'legacy'),
                'wiki.getPageInfoVersion' => new ApiCall([$this, 'legacyGetPageInfoVersion'], 'legacy'),
                'wiki.getPageVersions' => new ApiCall([$this, 'legacyGetPageVersions'], 'legacy'),
                'wiki.putPage' => new ApiCall([$this, 'legacyPutPage'], 'legacy'),
                'wiki.listLinks' => new ApiCall([$this, 'legacyListLinks'], 'legacy'),
                'wiki.getRecentChanges' => new ApiCall([$this, 'legacyGetRecentChanges'], 'legacy'),
                'wiki.getRecentMediaChanges' => new ApiCall([$this, 'legacyGetRecentMediaChanges'], 'legacy'),
                'wiki.aclCheck' => new ApiCall([$this, 'legacyAclCheck'], 'legacy'),
                'wiki.putAttachment' => new ApiCall([$this, 'legacyPutAttachment'], 'legacy'),
                'wiki.deleteAttachment' => new ApiCall([$this, 'legacyDeleteAttachment'], 'legacy'),
                'wiki.getAttachment' => new ApiCall([$this, 'legacyGetAttachment'], 'legacy'),
                'wiki.getAttachmentInfo' => new ApiCall([$this, 'legacyGetAttachmentInfo'], 'legacy'),
                'dokuwiki.getXMLRPCAPIVersion' => (new ApiCall([$this, 'legacyGetXMLRPCAPIVersion'], 'legacy'))
                    ->setPublic(),
                'wiki.getRPCVersionSupported' => (new ApiCall([$this, 'legacyGetRPCVersionSupported'], 'legacy'))
                    ->setPublic(),
            ]
        );
    }

    /**
     * This returns a XMLRPC object that will not work for the new JSONRPC API
     *
     * @param int $ts
     * @return Date
     */
    protected function toDate($ts)
    {
        return new Date($ts);
    }


    /**
     * @deprecated use core.getWikiVersion instead
     */
    public function legacyGetVersion()
    {
        return getVersion();
    }

    /**
     * @deprecated use core.getWikiTime instead
     */
    public function legacyGetTime()
    {
        return $this->getWikiTime();
    }


    /**
     * @deprecated use core.getPage instead
     */
    public function legacyGetPage($id)
    {
        try {
            return $this->getPage($id);
        } catch (RemoteException $e) {
            if ($e->getCode() === 121) {
                return '';
            }
            throw $e;
        }
    }

    /**
     * @deprecated use core.getPage instead
     */
    public function legacyGetPageVersion($id, $rev = '')
    {
        try {
            return $this->getPage($id, $rev);
        } catch (RemoteException $e) {
            if ($e->getCode() === 121) {
                return '';
            }
            throw $e;
        }
    }

    /**
     * @deprecated use core.getMedia instead
     */
    public function legacyGetAttachment($id)
    {
        return new Base64(base64_decode($this->getMedia($id)));
    }

    /**
     * @deprecated use core.getMediaInfo instead
     */
    public function legacygetAttachmentInfo($id)
    {
        $info = $this->getMediaInfo($id);
        return [
            'lastModified' => $this->toDate($info->revision),
            'size' => $info->size,
        ];
    }

    /**
     * @deprecated use core.getPageHTML instead
     */
    public function legacyGetPageHTML($id)
    {
        try {
            return $this->getPageHTML($id);
        } catch (RemoteException $e) {
            if ($e->getCode() === 121) {
                return '';
            }
            throw $e;
        }
    }

    /**
     * @deprecated use core.getPageHTML instead
     */
    public function legacyGetPageHTMLVersion($id, $rev = '')
    {
        try {
            return $this->getPageHTML($id, (int)$rev);
        } catch (RemoteException $e) {
            if ($e->getCode() === 121) {
                return '';
            }
            throw $e;
        }
    }

    /**
     * @deprecated use core.listPages instead
     */
    public function legacyGetAllPages()
    {
        $pages = $this->listPages('', 0);

        $result = [];
        foreach ($pages as $page) {
            $result[] = [
                'id' => $page->id,
                'perms' => $page->permission,
                'size' => $page->size,
                'lastModified' => $this->toDate($page->revision),
            ];
        }
        return $result;
    }

    /**
     * @deprecated use core.listPages instead
     */
    public function legacyGetPagelist($ns, $opts = [])
    {
        $data = $this->listPages($ns, $opts['depth'] ?? 0, $opts['hash'] ?? false);
        $result = [];

        foreach ($data as $page) {
            $result[] = [
                'id' => $page->id,
                'perms' => $page->permission,
                'size' => $page->size,
                'rev' => $page->revision,
                'mtime' => $page->revision,
                'hash' => $page->hash,

            ];
        }

        return $result;
    }

    /**
     * @deprecated use core.searchPages instead
     */
    public function legacySearch($query)
    {
        $this->searchPages($query);
        $pages = [];

        foreach ($this->searchPages($query) as $page) {
            $pages[] = [
                'id' => $page->id,
                'score' => $page->score,
                'rev' => $page->revision,
                'lastModified' => $this->toDate($page->revision),
                'size' => $page->size,
                'snippet' => $page->snippet,
                'title' => $page->title
            ];
        }

        return $pages;
    }

    /**
     * @deprecated use core.getWikiTitle instead
     */
    public function legacyGetTitle()
    {
        return $this->getWikiTitle();
    }

    /**
     * @deprecated use core.listMedia instead
     */
    public function legacyGetAttachments($ns, $options = [])
    {
        $files = $this->listMedia($ns, $options['pattern'] ?? '', $options['depth'] ?? 0, $options['hash'] ?? false);
        $result = [];
        foreach ($files as $file) {
            $result[] = [
                'id' => $file->id,
                'perms' => $file->permission,
                'size' => $file->size,
                'rev' => $file->revision,
                'lastModified' => $this->toDate($file->revision),
                'mtime' => $this->toDate($file->revision),
                'hash' => $file->hash,
                'file' => PhpString::basename(mediaFN($file->id)),
                'writable' => is_writable(mediaFN($file->id)),
                'isimg' => $file->isimage,

            ];
        }
        return $result;
    }

    /**
     * @deprecated use core.getPageBackLinks instead
     */
    public function legacyGetBackLinks($id)
    {
        return $this->getPageBackLinks($id);
    }

    /**
     * @deprecated use core.getPageInfo instead
     */
    public function legacyGetPageInfo($id)
    {
        $info = $this->getPageInfo($id, 0);
        return [
            'name' => $info->id,
            'lastModified' => $this->toDate($info->revision),
            'author' => $info->author,
            'version' => $info->revision,
        ];
    }

    /**
     * @deprecated use core.getPageInfo instead
     */
    public function legacyGetPageInfoVersion($id, $rev = '')
    {
        $info = $this->getPageInfo($id, $rev);
        return [
            'name' => $info->id,
            'lastModified' => $this->toDate($info->revision),
            'author' => $info->author,
            'version' => $info->revision,
        ];
    }

    /**
     * @deprecated use core.savePage instead
     */
    public function legacyPutPage($id, $text, $params = [])
    {
        return $this->savePage($id, $text, $params['sum'] ?? '', $params['minor'] ?? false);
    }

    /**
     * @deprecated use core.appendPage instead
     */
    public function legacyAppendPage($id, $text, $params = [])
    {
        $ok = $this->appendPage($id, $text, $params['summary'] ?? '', $params['minor'] ?? false);
        if ($ok === true) {
            return cleanID($id);
        } else {
            return $ok;
        }
    }

    /**
     * @deprecated use plugin.usermanager.createUser instead
     */
    public function legacyCreateUser($userStruct)
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

        $notify = (bool)$userStruct['notify'] ?? false;

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
     * @deprecated use plugin.usermanager.deleteUser instead
     */
    public function legacyDeleteUsers($usernames)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to delete users', 114);
        }
        /** @var AuthPlugin $auth */
        global $auth;
        return (bool)$auth->triggerUserMod('delete', [$usernames]);
    }

    /**
     * @deprecated use core.saveMedia instead
     */
    public function legacyPutAttachment($id, $file, $params = [])
    {
        $ok = $this->saveMedia($id, base64_encode($file), $params['ow'] ?? false);
        if ($ok === true) {
            return cleanID($id);
        } else {
            return $ok;
        }
    }

    /**
     * @deprecated use core.deleteMedia instead
     */
    public function legacyDeleteAttachment($id)
    {
        $ok = $this->deleteMedia($id);
        if ($ok === true) {
            return 0;
        } else {
            return $ok;
        }
    }

    /**
     * @deprecated use core.aclCheck instead
     */
    public function legacyAclCheck($id, $user = null, $groups = null)
    {
        return $this->aclCheck($id, (string)$user, (string)$groups);
    }

    /**
     * @deprecated use core.listLinks instead
     */
    public function legacyListLinks($id)
    {
        $links = $this->getPageLinks($id);
        $result = [];
        foreach ($links as $link) {
            $result[] = [
                'type' => $link['type'],
                'page' => $link['page'],
                'href' => $link['href'],
            ];
        }
        return $result;
    }

    /**
     * @deprecated use core.getRecentChanges instead
     */
    public function legacyGetRecentChanges($timestamp)
    {
        $recents = $this->getRecentPageChanges($timestamp);
        $result = [];
        foreach ($recents as $recent) {
            $result[] = [
                'name' => $recent->id,
                'lastModified' => $this->toDate($recent->revision),
                'author' => $recent->author,
                'version' => $recent->revision,
                'perms' => auth_quickaclcheck($recent->id),
                'size' => @filesize(wikiFN($recent->id)),
            ];
        }
        return $result;
    }

    /**
     * @deprecated use core.getRecentMediaChanges instead
     */
    public function legacyGetRecentMediaChanges($timestamp)
    {
        $recents = $this->getRecentMediaChanges($timestamp);
        $result = [];
        foreach ($recents as $recent) {
            $result[] = [
                'name' => $recent->id,
                'lastModified' => $this->toDate($recent->revision),
                'author' => $recent->author,
                'version' => $recent->revision,
                'perms' => auth_quickaclcheck($recent->id),
                'size' => @filesize(mediaFN($recent->id)),
            ];
        }
        return $result;
    }

    /**
     * @deprecated use core.getPageHistory instead
     */
    public function legacyGetPageVersions($id, $first = 0)
    {
        $revisions = $this->getPageHistory($id, $first);
        $result = [];

        foreach ($revisions as $revision) {
            $result[] = [
                'user' => $revision->author,
                'ip' => $revision->ip,
                'type' => $revision->type,
                'sum' => $revision->summary,
                'modified' => $this->toDate($revision->revision),
                'version' => $revision->revision,
            ];
        }
        return $result;
    }

    /**
     * @deprecated Wiki RPC spec is no longer supported
     */
    public function legacyGetRPCVersionSupported()
    {
        return 2;
    }

    /**
     * @deprecated use core.lockPages and core.unlockPages instead
     */
    public function legacySetLocks($set)
    {
        $locked = $this->lockPages($set['lock']);
        $lockfail = array_diff($set['lock'], $locked);

        $unlocked = $this->unlockPages($set['unlock']);
        $unlockfail = array_diff($set['unlock'], $unlocked);

        return [
            'locked' => $locked,
            'lockfail' => $lockfail,
            'unlocked' => $unlocked,
            'unlockfail' => $unlockfail
        ];
    }

    /**
     * @deprecated use core.getAPIVersion instead
     */
    public function legacyGetXMLRPCAPIVersion()
    {
        return $this->getAPIVersion();
    }

    /**
     * @deprecated use core.login instead
     */
    public function legacyLogin($user, $pass)
    {
        return parent::login($user, $pass);
    }

    /**
     * @deprecated use core.logoff instead
     */
    public function legacyLogoff()
    {
        return parent::logoff();
    }
}
