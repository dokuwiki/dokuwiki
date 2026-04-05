<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Index\AbstractIndex;

/**
 * Collection for page-to-media relationships
 *
 * Tracks which media files are used on which pages. Each media reference appears at most once per page.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class MediaCollection extends LookupCollection
{
    /** @inheritdoc */
    public function __construct(?AbstractIndex $pageIndex = null)
    {
        parent::__construct($pageIndex ?? 'page', 'relation_media_w', 'relation_media_i', 'relation_media_p');
    }
}
