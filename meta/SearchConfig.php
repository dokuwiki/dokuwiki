<?php

namespace plugin\struct\meta;

/**
 * Class SearchConfig
 *
 * The same as @see Search but can be initialized by a configuration array
 *
 * @package plugin\struct\meta
 */
class SearchConfig extends Search {

    /**
     * SearchConfig constructor.
     * @param $config
     */
    public function __construct($config) {
        parent::__construct();

        foreach($config['schemas'] as $schema) {
            $this->addSchema($schema[0], $schema[1]);
        }

        foreach($config['cols'] as $col) {
            $this->addColumn($col);
        }

        foreach($config['filter'] as $filter) {
            $this->addFilter($filter[0], $filter[2], $filter[1], $filter[3]);
        }

        // FIXME add additional stuff

    }

}
