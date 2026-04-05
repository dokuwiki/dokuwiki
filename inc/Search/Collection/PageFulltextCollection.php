<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Index\AbstractIndex;

/**
 * Fulltext search collection for wiki pages
 *
 * Manages the indexes used for fulltext search of page contents.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class PageFulltextCollection extends FrequencyCollection
{
    /** @inheritdoc */
    public function __construct(?AbstractIndex $pageIndex = null)
    {
        parent::__construct($pageIndex ?? 'page', 'w', 'i', 'pageword', true);
    }
}
