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
            $this->addFilter($filter[0], $this->applyFilterVars($filter[2]), $filter[1], $filter[3]);
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
     * Replaces placeholders in the given filter value by the proper value
     *
     * @param string $filter
     * @return string
     */
    protected function applyFilterVars($filter) {
        global $ID;

        // apply inexpensive filters first
        $filter = str_replace(
            array(
                '$ID$',
                '$NS$',
                '$PAGE$',
                '$USER$',
                '$TODAY$'
            ),
            array(
                $ID,
                getNS($ID),
                noNS($ID),
                isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : '',
                date('Y-m-d')
            ),
            $filter
        );

        // apply struct filter
        while(preg_match('/\$STRUCT\.(.*?)\$/', $filter, $match)) {
            $key = $match[1];
            $column = $this->findColumn($key);

            if($column) {
                $label = $column->getLabel();
                $table = $column->getTable();
                $schemaData = new SchemaData($table, $ID, 0);
                $data = $schemaData->getDataArray();
                $value = $data[$label];
                if(is_array($value)) $value = array_shift($value);
            } else {
                $value = '';
            }
            $key = preg_quote_cb($key);
            $filter = preg_replace('/\$STRUCT\.' . $key . '\$/', $value, $filter, 1);

        }

        return $filter;
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
