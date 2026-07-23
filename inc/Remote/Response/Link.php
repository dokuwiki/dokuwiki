<?php

namespace dokuwiki\Remote\Response;

class Link extends ApiResponse
{
    /** @var string The type of this link: `local`, `extern` or `interwiki` */
    public $type;
    /** @var string The wiki page this link points to, same as `href` for external links */
    public $page;
    /** @var string A hyperlink pointing to the linked target */
    public $href;
    /** @var string The title of this link */
    public $title;

    /**
     * @param string $type One of `local`, `extern` or `interwiki`
     * @param string $page The wiki page this link points to, same as `href` for external links
     * @param string $href A hyperlink pointing to the linked target
     * @param string $title The title of this link
     */
    public function __construct($type, $page, $href, $title = '')
    {
        $this->type = $type;
        $this->page = $page;
        $this->href = $href;
        $this->title = $title;
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        return $this->href;
    }
}
