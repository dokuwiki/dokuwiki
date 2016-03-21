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
     * @var array hold the configuration as parsed and extended by dynamic params
     */
    protected $config;

    /**
     * @var SearchConfigParameters manages dynamic parameters
     */
    protected $dynamicParameters;

    /**
     * SearchConfig constructor.
     * @param array $config The parsed configuration for this search
     */
    public function __construct($config) {
        parent::__construct();

        // setup schemas and columns
        if(!empty($config['schemas'])) foreach($config['schemas'] as $schema) {
            $this->addSchema($schema[0], $schema[1]);
        }
        if(!empty($config['cols'])) foreach($config['cols'] as $col) {
            $this->addColumn($col);
        }

        // apply dynamic paramters
        $this->dynamicParameters = new SearchConfigParameters($this);
        $config = $this->dynamicParameters->updateConfig($config);

        // configure search from configuration
        if(!empty($config['filter'])) foreach($config['filter'] as $filter) {
            $this->addFilter($filter[0], $filter[2], $filter[1], $filter[3]);
        }

        if(!empty($config['sort'])) foreach($config['sort'] as $sort) {
            $this->addSort($sort[0], $sort[1]);
        }

        if(!empty($config['limit'])) {
            $this->setLimit($config['limit']);
        }

        if(!empty($config['offset'])) {
            $this->setLimit($config['offset']);
        }

        $this->config = $config;
    }

    /**
     * Access the dynamic paramters of this search
     *
     * Note: This call returns a clone of the parameters as they were initialized
     *
     * @return SearchConfigParameters
     */
    public function getDynamicParameters() {
        return clone $this->dynamicParameters;
    }

    /**
     * @return array the current config
     */
    public function getConf() {
        return $this->config;
    }

}
