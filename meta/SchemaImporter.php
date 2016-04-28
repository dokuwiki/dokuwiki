<?php

namespace dokuwiki\plugin\struct\meta;

if(!defined('JSON_PRETTY_PRINT')) define('JSON_PRETTY_PRINT', 0); // PHP 5.3 compatibility

/**
 * Class SchemaImporter
 *
 * This works just like the schema builder, except that it expects a JSON structure as input
 *
 * @package dokuwiki\plugin\struct\meta
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
        if($input === null) {
            switch(json_last_error()) {
                case JSON_ERROR_NONE:
                    $error = 'No errors';
                    break;
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error = 'Unknown error';
                    break;
            }

            throw new StructException('JSON couldn\'t be decoded: ' . $error);
        }
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
