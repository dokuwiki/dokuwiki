<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\SchemaData;
use dokuwiki\plugin\struct\meta\SchemaImporter;

/**
 * Base class for all struct tests
 *
 * It cleans up the database in teardown and provides some useful helper methods
 *
 * @package dokuwiki\plugin\struct\test
 */
abstract class StructTest extends \DokuWikiTest {

    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = array('struct', 'sqlite');

    /**
     * Default teardown
     *
     * we always make sure the database is clear
     */
    protected function tearDown() {
        parent::tearDown();
        /** @var \helper_plugin_struct_db $db */
        $db = plugin_load('helper', 'struct_db');
        $db->resetDB();
    }

    /**
     * Creates a schema from one of the available schema files
     *
     * @param string $schema
     * @param string $json base name of the JSON file optional, defaults to $schema
     * @param int $rev allows to create schemas back in time
     */
    protected function loadSchemaJSON($schema, $json = '', $rev = 0) {
        if(!$json) $json = $schema;
        $file = __DIR__ . "/json/$json.struct.json";
        if(!file_exists($file)) {
            throw new \RuntimeException("$file does not exist");
        }

        $importer = new SchemaImporter($schema, file_get_contents($file));

        if(!$importer->build($rev)) {
            throw new \RuntimeException("build of $schema from $file failed");
        }
    }

    /**
     * This waits until a new second has passed
     *
     * The very first call will return immeadiately, proceeding calls will return
     * only after at least 1 second after the last call has passed.
     *
     * When passing $init=true it will not return immeadiately but use the current
     * second as initialization. It might still return faster than a second.
     *
     * @param bool $init wait from now on, not from last time
     * @return int new timestamp
     */
    protected function waitForTick($init = false) {
        static $last = 0;
        if($init) $last = time();

        while($last === $now = time()) {
            usleep(100000); //recheck in a 10th of a second
        }
        $last = $now;
        return $now;
    }

    /**
     * Saves struct data for given page and schema
     *
     * Please note that setting the $rev only influences the struct data timestamp,
     * not the page and changelog entries.
     *
     * @param string $page
     * @param string $schema
     * @param array $data
     * @param int $rev allows to override the revision timestamp
     */
    protected function saveData($page, $schema, $data, $rev = 0) {
        if(!$rev) $rev = time();

        saveWikiText($page, "test for $page", "saved for testing");
        $schemaData = new SchemaData($schema, $page, $rev);
        $schemaData->saveData($data);
        $assignments = new Assignments();
        $assignments->assignPageSchema($page, $schema);
    }

    /**
     * Access the plugin's english language strings
     *
     * @param string $key
     * @return string
     */
    protected function getLang($key) {
        static $lang = null;
        if(is_null($lang)) {
            $lang = array();
            include(DOKU_PLUGIN . 'struct/lang/en/lang.php');
        }
        return $lang[$key];
    }

    /**
     * Removes Whitespace
     *
     * Makes comparing sql statements a bit simpler as it ignores formatting
     *
     * @param $string
     * @return string
     */
    protected function cleanWS($string) {
        return preg_replace('/\s+/s', '', $string);
    }
}
