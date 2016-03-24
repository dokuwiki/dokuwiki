<?php

namespace plugin\struct\test;

use plugin\struct\meta\SchemaData;
use plugin\struct\meta\SchemaImporter;
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Base class for all struct tests
 *
 * It cleans up the database in teardown and provides some useful helper methods
 *
 * @package plugin\struct\test
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
        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
    }

    /**
     * Creates a schema from one of the available schema files
     *
     * @param string $schema
     * @param string $json base name of the JSON file optional, defaults to $schema
     */
    protected function loadSchemaJSON($schema, $json='') {
        if(!$json) $json = $schema;
        $file = __DIR__ . "/json/$json.struct.json";
        if(!file_exists($file)) {
            throw new \RuntimeException("$file does not exist");
        }

        $importer = new SchemaImporter($schema, file_get_contents($file));

        if(!$importer->build()) {
            throw new \RuntimeException("build of $schema from $file failed");
        }
    }

    /**
     * This waits until a new second has passed
     *
     * The very first call will return immeadiately, proceeding calls will return
     * only after at least 1 second after the last call has passed
     *
     * @return int new timestamp
     */
    protected function waitForTick() {
        static $last = 0;
        while ( $last === $now = time() ) {
            usleep(100000); //recheck in a 10th of a second
        }
        $last = $now;
        return $now;
    }

    /**
     * Saves struct data for given page and schema
     *
     * @param string $page
     * @param string $schema
     * @param array $data
     */
    protected function saveData($page, $schema, $data) {
        saveWikiText($page, "test for $page", "saved for testing");
        $schemaData = new SchemaData($schema, $page, time());
        $schemaData->saveData($data);
    }
}
