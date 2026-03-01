<?php

namespace dokuwiki\Remote\Response;

/**
 * Represents a page found by a search
 */
class PageHit extends Page
{
    /** @var int The number of hits this result got */
    public $score;

    /** @var string The HTML formatted snippet in which the search term was found (if available) */
    public $snippet;

    /** @var string Not available for search results */
    public $hash;

    /** @var string Not available for search results */
    public $author;

    /**
     * PageHit constructor.
     *
     * @param string $id
     * @param string $snippet
     * @param int $score
     * @param string $title
     */
    public function __construct($id, $snippet = '', $score = 0, $title = '')
    {
        parent::__construct($id, 0, 0, $title);

        $this->snippet = $snippet;
        $this->score = $score;
    }
}
