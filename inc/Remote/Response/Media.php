<?php

namespace dokuwiki\Remote\Response;

use dokuwiki\ChangeLog\MediaChangeLog;

/**
 * Represents a single media revision in the wiki.
 */
class Media extends ApiResponse
{
    /** @var string The media ID */
    public $id;
    /** @var int The media revision aka last modified timestamp */
    public $revision;
    /** @var int The page size in bytes */
    public $size;
    /** @var int The current user's permissions for this file */
    public $permission;
    /** @var bool Wether this is an image file */
    public $isimage;
    /** @var string MD5 sum over the file's content (if available and requested) */
    public $hash;
    /** @var string The author of this page revision (if available and requested) */
    public $author;

    /** @var string The file path to this media revision */
    protected $file;

    /**
     * Media constructor.
     *
     * @param string $id The media ID
     * @param int $revision The media revision aka last modified timestamp
     * @param int $mtime The media revision aka last modified timestamp
     * @param int|null $size The page size in bytes
     * @param int|null $perms The current user's permissions for this file
     * @param bool|null $isimage Wether this is an image file
     * @param string $hash MD5 sum over the file's content
     */
    public function __construct(
        $id,
        $revision = 0,
        $mtime = 0,
        $size = null,
        $perms = null,
        $isimage = null,
        $hash = '',
        $author = ''
    ) {
        $this->id = $id;
        $this->file = mediaFN($this->id, $revision);
        $this->revision = $revision ?: $mtime ?: filemtime($this->file);
        $this->size = $size ?? filesize($this->file);
        $this->permission = $perms ?? auth_quickaclcheck($this->id);
        ;
        $this->isimage = (bool)($isimage ?? preg_match("/\.(jpe?g|gif|png)$/", $id));
        $this->hash = $hash;
        $this->author = $author;
    }

    /**
     * Calculate the hash for this page
     *
     * This is a heavy operation and should only be called when needed.
     */
    public function calculateHash()
    {
        $this->hash = md5(io_readFile($this->file, false));
    }

    /**
     * Retrieve the author of this page
     */
    public function retrieveAuthor()
    {
        $pagelog = new MediaChangeLog($this->id, 1024);
        $info = $pagelog->getRevisionInfo($this->revision);
        $this->author = is_array($info) ? ($info['user'] ?: $info['ip']) : '';
    }

    /** @inheritdoc */
    public function __toString()
    {
        return $this->id . '@' . $this->revision;
    }
}
