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

    /**
     * @param string $type One of `internal`, `external` or `interwiki`
     * @param string $page The wiki page this link points to, same as `href` for external links
     * @param string $href A hyperlink pointing to the linked target
     */
    public function __construct($type, $page, $href)
    {
        $this->type = $type;
        $this->page = $page;
        $this->href = $href;
    }

    /** @inheritdoc */
    public function __toString()
    {
        return $this->href;
    }
}
