<?php

namespace plugin\struct\meta;

/**
 * Class SchemaImporter
 *
 * This works just like the schema builder, except that it expects a JSON structure as input
 *
 * @package plugin\struct\meta
 */
class SchemaImporter extends SchemaBuilder {

    /**
     * Import a schema using JSON
     *
     * @todo sanity checking of the input data should be added
     *
     * @param string $table
     * @param string $json
     */
    public function __construct($table, $json) {
        parent::__construct($table, array());

        // number of existing columns
        $existing = count($this->oldschema->getColumns());

        $input = json_decode($json, true);
        $data = array(
            'cols' => array(),
            'new' => array()
        );

        foreach($input['columns'] as $column) {
            // config has to stay json
            $column['config'] = json_encode($column['config'], JSON_PRETTY_PRINT);

            if(!empty($column['colref']) && $column['colref'] <= $existing) {
                // update existing column
                $data['cols'][$column['colref']] = $column;
            } else {
                // add new column
                $data['new'][] = $column;
            }
        }

        $this->data = $data;
    }

}
