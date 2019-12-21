<?php
/**
 * Functions to create the fulltext search index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Tom N Harris <tnharris@whoopdedo.org>
 */

use dokuwiki\Search\Indexer;

/**
 * Create an instance of the indexer.
 *
 * @return Doku_Indexer    a Doku_Indexer
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_get_indexer() {
    return Indexer::getInstance();
}

/** @deprecated 2019-12-16 */
function idx_get_version() {
    dbg_deprecated('idx_get_version');
    $Indexer = idx_get_indexer();
    return $Indexer->getVersion();
}

/** @deprecated 2019-12-17 */
function wordlen($w) {
    dbg_deprecated('wordlen');
    return Doku_Indexer::wordlen($w);
}

/** @deprecated 2019-12-16 */
function & idx_get_stopwords() {
    dbg_deprecated('idx_get_stopwords');
    $Indexer = idx_get_indexer();
    return $Indexer->getStopwords();
}

/** @deprecated 2019-12-16 */
function idx_addPage($page, $verbose=false, $force=false) {
    dbg_deprecated('idx_addPage');
    $Indexer = idx_get_indexer();
    return $Indexer->addPage($page, $verbose, $force);
}

/** @deprecated 2019-12-16 */
function idx_lookup(&$words) {
    dbg_deprecated('idx_lookup');
    $Indexer = idx_get_indexer();
    return $Indexer->lookup($words);
}

/** @deprecated 2019-12-16 */
function idx_tokenizer($string, $wc=false) {
    dbg_deprecated('idx_tokenizer');
    $Indexer = idx_get_indexer();
    return $Indexer->tokenizer($string, $wc);
}

/* For compatibility */

/** @deprecated 2019-12-16 */
function idx_getIndex($idx, $suffix) {
    dbg_deprecated('idx_getIndex');
    $Indexer = idx_get_indexer();
    return $Indexer->getIndex($idx, $suffix);
}

/** @deprecated 2019-12-16 */
function idx_listIndexLengths() {
    dbg_deprecated('idx_listIndexLengths');
    $Indexer = idx_get_indexer();
    return $Indexer->listIndexLengths();
}

/** @deprecated 2019-12-16 */
function idx_indexLengths($filter) {
    dbg_deprecated('idx_indexLengths');
    $Indexer = idx_get_indexer();
    return $Indexer->indexLengths($filter);
}

/** @deprecated 2019-12-16 */
function idx_cleanName($name) {
    dbg_deprecated('idx_cleanName');
    $Indexer = idx_get_indexer();
    return $Indexer->cleanName($name);
}

/**
 * Class that encapsulates operations on the indexer database.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @@deprecated 2019-12-20
 */
class Doku_Indexer extends \dokuwiki\Search\Indexer
{
    public function __construct()
    {
        dbg_deprecated(\Indexer::class);
        parent::__construct();
    }
}
//Setup VIM: ex: et ts=4 :
