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
    public $permission;
    /** @var string MD5 sum over the page's content (if available and requested) */
    public $hash;
    /** @var string The author of this page revision (if available and requested) */
    public $author;

    /** @var string The file path to this page revision */
    protected $file;

    /**
     * Page constructor.
     *
     * @param string $id The page ID
     * @param int $revision The page revision 0 for current
     * @param int $mtime Last modified timestamp
     * @param string $title The page title
     * @param int|null $size The page size in bytes
     * @param int|null $perms The current user's permissions for this page
     * @param string $hash MD5 sum over the page's content
     * @param string $author The author of this page revision
     */
    public function __construct(
        $id,
        $revision = 0,
        $mtime = 0,
        $title = '',
        $size = null,
        $perms = null,
        $hash = '',
        $author = ''
    ) {
        $this->id = $id;
        $this->file = wikiFN($this->id, $revision);
        $this->revision = $revision ?: $mtime ?: @filemtime($this->file);
        $this->size = $size ?? @filesize($this->file);
        $this->permission = $perms ?? auth_quickaclcheck($this->id);
        $this->hash = $hash;
        $this->author = $author;
        $this->title = $title ?: $this->retrieveTitle();
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
        $this->hash = md5(trim(io_readFile($this->file)));
    }

    /**
     * Retrieve the author of this page
     */
    public function retrieveAuthor()
    {
        $pagelog = new PageChangeLog($this->id, 1024);
        $info = $pagelog->getRevisionInfo($this->revision);
        $this->author = is_array($info) ? ($info['user'] ?: $info['ip']) : '';
    }

    /** @inheritdoc */
    public function __toString()
    {
        return $this->id . '@' . $this->revision;
    }
}
