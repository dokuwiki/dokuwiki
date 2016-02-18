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

    /**
     * SearchConfig constructor.
     * @param $config
     */
    public function __construct($config) {
        global $INPUT;
        /** @var \helper_plugin_struct_config $confHlp */
        $confHlp = plugin_load('helper','struct_config');
        $this->config = $config;
        $this->config['current_params'] = array();

        parent::__construct();

        foreach($config['schemas'] as $schema) {
            $this->addSchema($schema[0], $schema[1]);
        }

        foreach($config['cols'] as $col) {
            $this->addColumn($col);
        }

        if ($INPUT->has('datasrt')) {
            list($colname, $sort) = $confHlp->parseSort($INPUT->str('datasrt'));
            $this->addSort($colname, $sort === 'ASC');
            $this->config['sort'] = array($colname, $sort);
            $this->config['current_params']['datasrt'] = $INPUT->str('datasrt');
        } elseif ($config['sort'][0] != '') {
            $this->addSort($config['sort'][0], $config['sort'][1] === 'ASC');
        }

        foreach($config['filter'] as $filter) {
            $this->addFilter($filter[0], $filter[2], $filter[1], $filter[3]);
        }
        if ($INPUT->has('dataflt')) {
            foreach ($INPUT->arr('dataflt') as $colcomp => $filter) {
                list($colname, $comp, $value, $logic) = $confHlp->parseFilterLine('AND', $colcomp . $filter);
                $this->addFilter($colname, $value, $comp, $logic);
                $this->config['filter'][] = array($colname, $comp, $value, $logic);
                $this->config['current_params']['dataflt'] = $INPUT->arr('dataflt');
            }
        }

        if (!empty($config['limit'])) {
            $this->setLimit($config['limit']);
        }
        if ($INPUT->has('dataofs')) {
            $this->setOffset($INPUT->int('dataofs'));
            $this->config['current_params']['dataofs'] = $INPUT->int('dataofs');
        }

        // FIXME add additional stuff

    }

    public function getConf() {
        return $this->config;
    }

}
