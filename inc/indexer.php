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


/* For compatibility */


/** @deprecated 2020-01-30 */
function idx_get_indexer() {
    dbg_deprecated(Indexer::class.'::getInstance()');
    return Indexer::getInstance();
}

/** @deprecated 2019-12-16 */
function idx_get_version() {
    dbg_deprecated(Indexer::class.'::getVersion()');
    $Indexer = Indexer::getInstance();
    return $Indexer->getVersion();
}

/** @deprecated 2019-12-16 */
function idx_addPage($page, $verbose=false, $force=false) {
    dbg_deprecated(Indexer::class.'::addPage()');
    $Indexer = Indexer::getInstance();
    return $Indexer->addPage($page, $verbose, $force);
}

/** @deprecated 2019-12-16 */
function idx_getIndex($idx, $suffix) {
    dbg_deprecated(Indexer::class.'::getIndex()');
    $Indexer = Indexer::getInstance();
    return $Indexer->getIndex($idx, $suffix);
}

/** @deprecated 2019-12-16 */
function idx_listIndexLengths() {
    dbg_deprecated(PagewordIndex::class.'listIndexLengths()');
    $PagewordIndex = PagewordIndex::getInstance();
    return $PagewordIndex->listIndexLengths();
}

//Setup VIM: ex: et ts=4 :
