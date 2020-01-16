<?php
/**
 * Functions to create the fulltext search index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Tom N Harris <tnharris@whoopdedo.org>
 */

use dokuwiki\Extension\Event;
use dokuwiki\Search\Indexer;

// Version tag used to force rebuild on upgrade
define('INDEXER_VERSION', 8);

// set the minimum token length to use in the index (note, this doesn't apply to numeric tokens)
if (!defined('IDX_MINWORDLENGTH')) define('IDX_MINWORDLENGTH',2);

/**
 * Version of the indexer taking into consideration the external tokenizer.
 * The indexer is only compatible with data written by the same version.
 *
 * @triggers INDEXER_VERSION_GET
 * Plugins that modify what gets indexed should hook this event and
 * add their version info to the event data like so:
 *     $data[$plugin_name] = $plugin_version;
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @author Michael Hamann <michael@content-space.de>
 *
 * @return int|string
 */
function idx_get_version(){
    static $indexer_version = null;
    if ($indexer_version == null) {
        $version = INDEXER_VERSION;

        // DokuWiki version is included for the convenience of plugins
        $data = array('dokuwiki'=>$version);
        Event::createAndTrigger('INDEXER_VERSION_GET', $data, null, false);
        unset($data['dokuwiki']); // this needs to be first
        ksort($data);
        foreach ($data as $plugin=>$vers)
            $version .= '+'.$plugin.'='.$vers;
        $indexer_version = $version;
    }
    return $indexer_version;
}

/**
 * Measure the length of a string.
 * Differs from strlen in handling of asian characters.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 *
 * @param string $w
 * @return int
 */
function wordlen($w){
    $l = strlen($w);
    // If left alone, all chinese "words" will get put into w3.idx
    // So the "length" of a "word" is faked
    if(preg_match_all('/[\xE2-\xEF]/',$w,$leadbytes)) {
        foreach($leadbytes[0] as $b)
            $l += ord($b) - 0xE1;
    }
    return $l;
}

/**
 * Create an instance of the indexer.
 *
 * @return Indexer    an Indexer
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_get_indexer() {
    static $Indexer;
    if (!isset($Indexer)) {
        $Indexer = new Indexer();
    }
    return $Indexer;
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
