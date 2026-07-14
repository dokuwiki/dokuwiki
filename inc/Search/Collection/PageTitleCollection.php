<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Index\AbstractIndex;
use dokuwiki\Search\Index\FileIndex;

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

    /**
     * Use FileIndex for titles since each page has exactly one title
     * accessed by RID — no need to load the entire index into memory
     *
     * @inheritdoc
     */
    public function getTokenIndex(int $group = 0): AbstractIndex
    {
        if ($this->idxToken instanceof AbstractIndex) {
            return $this->idxToken;
        }
        return new FileIndex($this->idxToken, '', $this->isWritable);
    }
}
