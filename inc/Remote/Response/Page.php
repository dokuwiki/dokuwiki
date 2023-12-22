<?php

namespace dokuwiki\Remote\Response;

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
    /** @var string MD5 sum over the page's content (only if requested) */
    public $hash;

    /** @inheritdoc */
    public function __construct($data)
    {
        $this->id = cleanID($data['id'] ?? '');
        if ($this->id === '') {
            throw new \InvalidArgumentException('Missing id');
        }

        $this->revision = (int)($data['rev'] ?? $data['lastModified'] ?? @filemtime(wikiFN($this->id)));
        $this->size = (int)($data['size'] ?? @filesize(wikiFN($this->id)));
        $this->title = $data['title'] ?? $this->retrieveTitle();
        $this->perms = $data['perm'] ?? auth_quickaclcheck($this->id);
        $this->hash = $data['hash'] ?? '';
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

}
