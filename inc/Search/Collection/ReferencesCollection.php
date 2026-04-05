<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Index\AbstractIndex;

/**
 * Collection for page-to-page reference relationships
 *
 * Tracks which pages link to which other pages. Each reference appears at most once per page.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class ReferencesCollection extends LookupCollection
{
    /** @inheritdoc */
    public function __construct(?AbstractIndex $pageIndex = null)
    {
        parent::__construct($pageIndex ?? 'page', 'relation_references_w', 'relation_references_i', 'relation_references_p');
    }
}
