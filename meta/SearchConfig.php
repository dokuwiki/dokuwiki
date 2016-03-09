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

    protected $config;

    protected $dynamicParameters;

    /**
     * SearchConfig constructor.
     * @param array $config The parsed configuration for this search
     */
    public function __construct($config) {
        $this->config = $config;

        parent::__construct();

        // setup schemas and columns
        foreach($config['schemas'] as $schema) {
            $this->addSchema($schema[0], $schema[1]);
        }
        foreach($config['cols'] as $col) {
            $this->addColumn($col);
        }

        // apply dynamic paramters
        $this->dynamicParameters = new SearchConfigParameters($this);
        $this->config = $this->dynamicParameters->updateConfig($config);

        // configure search from configuration
        if(!empty($config['filter'])) foreach($config['filter'] as $filter) {
            $this->addFilter($filter[0], $filter[2], $filter[1], $filter[3]);
        }

        if(!empty($config['sort'])) foreach($config['sort'] as $sort) {
            $this->addSort($sort[0], $sort[1] === 'ASC');
        }

        if(!empty($config['limit'])) {
            $this->setLimit($config['limit']);
        }

        if(!empty($config['offset'])) {
            $this->setLimit($config['offset']);
        }
    }

    /**
     * Access the dynamic paramters of this search
     *
     * Note: This call retruns a clone of the parameters as they were initialized
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
