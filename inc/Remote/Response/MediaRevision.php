<?php

namespace dokuwiki\Remote\Response;

class MediaRevision extends ApiResponse
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

    /** @inheritdoc */
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->revision = (int)($data['revision'] ?? 0);
        $this->author = $data['author'] ?? '';
        $this->ip = $data['ip'] ?? '';
        $this->summary = $data['summary'] ?? '';
        $this->type = $data['type'] ?? '';
        $this->sizechange = (int)($data['sizechange'] ?? 0);
    }
}
