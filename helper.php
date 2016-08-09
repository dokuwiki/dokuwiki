<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\struct\meta\AccessTableData;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Validator;

if(!defined('DOKU_INC')) die();

/**
 * The public interface for the struct plugin
 *
 * 3rd party developers should always interact with struct data through this
 * helper plugin only. If additionional interface functionality is needed,
 * it should be added here.
 *
 * All functions will throw StructExceptions when something goes wrong.
 *
 * Remember to check permissions yourself!
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
        $page = cleanID($page);

        if(is_null($schema)) {
            $assignments = new Assignments();
            $schemas = $assignments->getPageAssignments($page, false);
        } else {
            $schemas = array($schema);
        }

        $result = array();
        foreach($schemas as $schema) {
            $schemaData = AccessTable::byTableName($schema, $page, $time);
            $result[$schema] = $schemaData->getDataArray();
        }

        return $result;
    }

    /**
     * Saves data for a given page (creates a new revision)
     *
     * If this call succeeds you can assume your data has either been saved or it was
     * not necessary to save it because the data already existed in the wanted form or
     * the given schemas are no longer assigned to that page.
     *
     * Important: You have to check write permissions for the given page before calling
     * this function yourself!
     *
     * this duplicates a bit of code from entry.php - we could also fake post data and let
     * entry handle it, but that would be rather unclean and might be problematic when multiple
     * calls are done within the same request.
     *
     * @todo should this try to lock the page?
     *
     *
     * @param string $page
     * @param array $data ('schema' => ( 'fieldlabel' => 'value', ...))
     * @param string $summary
     * @throws StructException
     */
    public function saveData($page, $data, $summary='') {
        $page = cleanID($page);
        $summary = trim($summary);
        if(!$summary) $summary = $this->getLang('summary');

        if(!page_exists($page)) throw new StructException("Page does not exist. You can not attach struct data");

        // validate and see if anything changes
        $validator = new Validator();
        if(!$validator->validate($data, $page)) {
            throw new StructException("Validation failed:\n%s", join("\n", $validator->getErrors()));
        }
        $data = $validator->getCleanedData();
        $tosave = $validator->getChangedSchemas();
        if(!$tosave) return;

        $newrevision = self::createPageRevision($page, $summary);

        // save the provided data
        $assignments = new Assignments();
        foreach($tosave as $table) {
            $schemaData = AccessTable::byTableName($table, $page, $newrevision);
            $schemaData->saveData($data[$table]);
            // make sure this schema is assigned
            $assignments->assignPageSchema($page, $table);
        }
    }

    /**
     * Creates a new page revision with the same page content as before
     *
     * @param string $page
     * @param string $summary
     * @param bool $minor
     * @return int the new revision
     */
    static public function createPageRevision($page, $summary='', $minor=false) {
        $summary = trim($summary);
        // force a new page revision @see action_plugin_struct_entry::handle_pagesave_before()
        $GLOBALS['struct_plugin_force_page_save'] = true;
        saveWikiText($page, rawWiki($page), $summary, $minor);
        unset($GLOBALS['struct_plugin_force_page_save']);
        $file = wikiFN($page);
        clearstatcache(false, $file);
        return filemtime($file);
    }

    /**
     * Get info about existing schemas
     *
     * @param string|null $schema the schema to query, null for all
     * @return Schema[]
     * @throws StructException
     */
    public function getSchema($schema=null) {
        if(is_null($schema)) {
            $schemas = Schema::getAll();
        } else {
            $schemas = array($schema);
        }

        $result = array();
        foreach($schemas as $table) {
            $result[$table] = new Schema($table);
        }
        return $result;
    }

    /**
     * Returns all pages known to the struct plugin
     *
     * That means all pages that have or had once struct data saved
     *
     * @param string|null $schema limit the result to a given schema
     * @return array (page => (schema => true), ...)
     * @throws StructException
     */
    public function getPages($schema=null) {
        $assignments = new Assignments();
        return $assignments->getPages($schema);
    }

}
