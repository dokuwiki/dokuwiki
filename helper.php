<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use plugin\struct\meta\StructException;

if(!defined('DOKU_INC')) die();

/**
 * The public interface for the struct plugin
 *
 * 3rd party developer should always interact with struct data through this
 * helper plugin only.
 *
 * All functions will throw StructExceptions when something goes wrong.
 */
class helper_plugin_struct extends DokuWiki_Plugin {

    /**
     * Get the structured data of a given page
     *
     * @param string $page The page to get data for
     * @param string|null $schema The schema to use null for all
     * @param int $time A timestamp if you want historic data (0 for now)
     * @return array ('schema' => ( 'fieldlabel' => 'value', ...))
     * @throws StructException
     */
    public function getData($page, $schema=null, $time=0) {

    }

    /**
     * Saves data for a given page (creates a new revision)
     *
     * @param string $page
     * @param string $summary
     * @throws StructException
     */
    public function saveData($page, $summary='') {

    }

    /**
     * Get info about existing schemas
     *
     * @param string|null $schema the schema to query, null for all
     * @return array ('schema' => ( (sort, label, type, ...), ...))
     * @throws StructException
     */
    public function getSchema($schema=null) {

    }

    /**
     * Returns all pages known to the struct plugin
     *
     * That means all pages that have or had once struct data saved
     *
     * @param string|null $schema limit the result to a given schema
     * @return array ((page, schema), ...)
     * @throws StructException
     */
    public function getPages($schema=null) {

    }

}
