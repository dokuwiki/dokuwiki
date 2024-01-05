<?php

namespace dokuwiki\Remote\Response;

use dokuwiki\ChangeLog\PageChangeLog;

/**
 * Represents a single page revision in the wiki.
 */
class Page extends ApiResponse
{
    /** @var string The page ID */
    public $id;
    /** @var int The page revision aka last modified timestamp */
    public $revision;
    /** @var int The page size in bytes */
    public $size;
    /** @var string The page title */
    public $title;
    /** @var int The current user's permissions for this page */
    public $perms;
    /** @var string MD5 sum over the page's content (if available and requested) */
    public $hash;
    /** @var string The author of this page revision (if available and requested) */
    public $author;

    /** @inheritdoc */
    public function __construct($data)
    {
        $this->id = cleanID($data['id'] ?? '');
        if ($this->id === '') {
            throw new \InvalidArgumentException('Missing id');
        }
        if (!page_exists($this->id)) {
            throw new \InvalidArgumentException('Page does not exist');
        }

        // FIXME this isn't really managing the difference between old and current revs correctly

        $this->revision = (int)($data['rev'] ?? @filemtime(wikiFN($this->id)));
        $this->size = (int)($data['size'] ?? @filesize(wikiFN($this->id)));
        $this->title = $data['title'] ?? $this->retrieveTitle();
        $this->perms = $data['perm'] ?? auth_quickaclcheck($this->id);
        $this->hash = $data['hash'] ?? '';
        $this->author = $data['author'] ?? '';
    }

    /**
     * Get the title for the page
     *
     * Honors $conf['useheading']
     *
     * @return string
     */
    protected function retrieveTitle()
    {
        global $conf;

        if ($conf['useheading']) {
            $title = p_get_first_heading($this->id);
            if ($title) {
                return $title;
            }
        }
        return $this->id;
    }

    /**
     * Calculate the hash for this page
     *
     * This is a heavy operation and should only be called when needed.
     */
    public function calculateHash()
    {
        if (!page_exists($this->id)) return;
        $this->hash = md5(io_readFile(wikiFN($this->id)));
    }

    /**
     * Retrieve the author of this page
     */
    public function retrieveAuthor()
    {
        if (!page_exists($this->id)) return;

        $pagelog = new PageChangeLog($this->id, 1024);
        $info = $pagelog->getRevisionInfo($this->revision);
        $this->author = is_array($info) ? ($info['user'] ?: $info['ip']) : null;
    }
}
