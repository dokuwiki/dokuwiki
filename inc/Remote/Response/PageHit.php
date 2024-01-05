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

    /** @inheritdoc */
    public function __construct($data)
    {
        parent::__construct($data);

        $this->snippet = $data['snippet'] ?? '';
        $this->score = (int)($data['score'] ?? 0);
    }
}
