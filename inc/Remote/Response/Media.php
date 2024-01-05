<?php

namespace dokuwiki\Remote\Response;

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
    public $perms;
    /** @var bool Wether this is an image file */
    public $isimage;
    /** @var string MD5 sum over the file's content (if available and requested) */
    public $hash;

    /** @inheritdoc */
    public function __construct($data)
    {
        $this->id = cleanID($data['id'] ?? '');
        if ($this->id === '') {
            throw new \InvalidArgumentException('Missing id');
        }
        if (!media_exists($this->id)) {
            throw new \InvalidArgumentException('Media does not exist');
        }

        // FIXME this isn't really managing the difference between old and current revs correctly

        $this->revision = (int)($data['rev'] ?? $data['mtime'] ?? @filemtime(mediaFN($this->id)));
        $this->size = (int)($data['size'] ?? @filesize(mediaFN($this->id)));
        $this->perms = $data['perm'] ?? auth_quickaclcheck($this->id);
        $this->isimage = (bool)($data['isimg'] ?? false);
        $this->hash = $data['hash'] ?? '';
    }

    /**
     * Calculate the hash for this page
     *
     * This is a heavy operation and should only be called when needed.
     */
    public function calculateHash()
    {
        if (!media_exists($this->id)) return;
        $this->hash = md5(io_readFile(mediaFN($this->id)));
    }
}
