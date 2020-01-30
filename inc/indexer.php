<?php
/**
 * Functions to create the fulltext search index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Tom N Harris <tnharris@whoopdedo.org>
 */

use dokuwiki\Search\Indexer;
use dokuwiki\Search\PagewordIndex;

/**
 * Create an instance of the indexer.
 *
 * @return Indexer    an Indexer
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_get_indexer() {
    return Indexer::getInstance();
}

/* For compatibility */

/** @deprecated 2019-12-16 */
function idx_get_version() {
    dbg_deprecated('idx_get_version');
    $Indexer = Indexer::getInstance();
    return $Indexer->getVersion();
}

/** @deprecated 2019-12-16 */
function idx_addPage($page, $verbose=false, $force=false) {
    dbg_deprecated('idx_addPage');
    $Indexer = Indexer::getInstance();
    return $Indexer->addPage($page, $verbose, $force);
}

/** @deprecated 2019-12-16 */
function idx_getIndex($idx, $suffix) {
    dbg_deprecated('idx_getIndex');
    $Indexer = Indexer::getInstance();
    return $Indexer->getIndex($idx, $suffix);
}

/** @deprecated 2019-12-16 */
function idx_listIndexLengths() {
    dbg_deprecated('idx_listIndexLengths');
    $PagewordIndex = PagewordIndex::getInstance();
    return $PagewordIndex->listIndexLengths();
}

//Setup VIM: ex: et ts=4 :
