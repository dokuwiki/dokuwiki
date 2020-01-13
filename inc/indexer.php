<?php
/**
 * Functions to create the fulltext search index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Tom N Harris <tnharris@whoopdedo.org>
 */

use dokuwiki\Search\PageIndex;
use dokuwiki\Search\PagewordIndex;


/**
 * Create an instance of the indexer.
 *
 * @return Doku_Indexer    a Doku_Indexer
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_get_indexer() {
    return PageIndex::getInstance();
}

/* For compatibility */

/** @deprecated 2019-12-16 */
function idx_get_version() {
    dbg_deprecated('idx_get_version');
    $PageIndex = PageIndex::getInstance();
    return $PageIndex->getVersion();
}

/** @deprecated 2019-12-16 */
function idx_addPage($page, $verbose=false, $force=false) {
    dbg_deprecated('idx_addPage');
    $PageIndex = PageIndex::getInstance();
    return $PageIndex->addPage($page, $verbose, $force);
}

/** @deprecated 2019-12-16 */
function idx_getIndex($idx, $suffix) {
    dbg_deprecated('idx_getIndex');
    $PageIndex = PageIndex::getInstance();
    return $PageIndex->getIndex($idx, $suffix);
}

/** @deprecated 2019-12-16 */
function idx_listIndexLengths() {
    dbg_deprecated('idx_listIndexLengths');
    $PageIndex = PageIndex::getInstance();
    return $PageIndex->PagewordIndex->listIndexLengths();
}


/**
 * Class that encapsulates operations on the indexer database.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @@deprecated 2019-12-20
 */
class Doku_Indexer extends \dokuwiki\Search\AbstractIndex
{
    public function __construct()
    {
        dbg_deprecated(\Indexer::class);
        parent::__construct();
    }
    public function clear() {}
}
//Setup VIM: ex: et ts=4 :
