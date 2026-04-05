<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Index\AbstractIndex;

/**
 * Collection for page titles
 *
 * Stores the title of each page as a direct 1:1 mapping.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class PageTitleCollection extends DirectCollection
{
    /** @inheritdoc */
    public function __construct(?AbstractIndex $pageIndex = null)
    {
        parent::__construct($pageIndex ?? 'page', 'title');
    }
}
