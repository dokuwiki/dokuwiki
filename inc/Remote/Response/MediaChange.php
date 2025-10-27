<?php

namespace dokuwiki\Remote\Response;

/**
 * Represents a single change in a media file
 */
class MediaChange extends ApiResponse
{
    /** @var string The media ID */
    public $id;
    /** @var int The revision (timestamp) of this change */
    public $revision;
    /** @var string The author of this change */
    public $author;
    /** @var string The IP address from where this change was made */
    public $ip;
    /** @var string The summary of this change */
    public $summary;
    /** @var string The type of this change */
    public $type;
    /** @var int The change in bytes */
    public $sizechange;

    /**
     * MediaChange constructor.
     *
     * @param string $id
     * @param int $revision
     * @param string $author
     * @param string $ip
     * @param string $summary
     * @param string $type
     * @param int $sizechange
     */
    public function __construct($id, $revision, $author, $ip, $summary, $type, $sizechange)
    {
        $this->id = $id;
        $this->revision = $revision;
        $this->author = $author;
        $this->ip = $ip;
        $this->summary = $summary;
        $this->type = $type;
        $this->sizechange = $sizechange;
    }

    /** @inheritdoc */
    public function __toString()
    {
        return $this->id . '@' . $this->revision;
    }
}
