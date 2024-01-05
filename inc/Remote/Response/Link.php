<?php

namespace dokuwiki\Remote\Response;

class Link extends ApiResponse
{
    /** @var string The type of this link: `internal`, `external` or `interwiki` */
    public $type;
    /** @var string The wiki page this link points to, same as `href` for external links */
    public $page;
    /** @var string A hyperlink pointing to the linked target */
    public $href;



    public function __construct($data)
    {
        $this->type = $data['type'] ?? '';
        $this->page = $data['page'] ?? '';
        $this->href = $data['href'] ?? '';
    }
}
