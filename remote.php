<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\ConfigParser;
use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\StructException;

if(!defined('DOKU_INC')) die();


class remote_plugin_struct extends DokuWiki_Remote_Plugin {
    /** @var helper_plugin_struct hlp */
    protected $hlp;

    /**
     * remote_plugin_struct constructor.
     */
    public function __construct() {
        parent::__construct();

        /** @var helper_plugin_struct hlp */
        $this->hlp = plugin_load('helper', 'struct');
    }

    /**
     * Get the structured data of a given page
     *
     * @param string $page The page to get data for
     * @param string $schema The schema to use empty for all
     * @param int $time A timestamp if you want historic data (0 for now)
     * @return array ('schema' => ( 'fieldlabel' => 'value', ...))
     * @throws RemoteAccessDeniedException
     * @throws RemoteException
     */
    public function getData($page, $schema, $time) {
        $page = cleanID($page);

        if(!auth_quickaclcheck($page) < AUTH_READ) {
            throw new RemoteAccessDeniedException('no permissions to access data of that page');
        }

        if(!$schema) $schema = null;

        try {
            return $this->hlp->getData($page, $schema, $time);
        } catch (StructException $e) {
            throw new RemoteException($e->getMessage(), 0, $e);
        }
    }


    /**
     * Saves data for a given page (creates a new revision)
     *
     * If this call succeeds you can assume your data has either been saved or it was
     * not necessary to save it because the data already existed in the wanted form or
     * the given schemas are no longer assigned to that page.
     *
     * @param string $page
     * @param array $data ('schema' => ( 'fieldlabel' => 'value', ...))
     * @param string $summary
     * @return bool returns always true
     * @throws RemoteAccessDeniedException
     * @throws RemoteException
     */
    public function saveData($page, $data, $summary) {
        $page = cleanID($page);

        if(!auth_quickaclcheck($page) < AUTH_EDIT) {
            throw new RemoteAccessDeniedException('no permissions to save data for that page');
        }

        try {
            $this->hlp->saveData($page, $data, $summary);
            return true;
        } catch (StructException $e) {
            throw new RemoteException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Get info about existing schemas columns
     *
     * Returns only current, enabled columns
     *
     * @param string $schema the schema to query, empty for all
     * @return array
     * @throws RemoteAccessDeniedException
     * @throws RemoteException
     */
    public function getSchema($schema) {
        if(!auth_ismanager()) {
            throw new RemoteAccessDeniedException('you need to be manager to access schema info');
        }

        if(!$schema) $schema = null;
        try {
            $result = array();
            $schemas = $this->hlp->getSchema($schema);
            foreach($schemas as $name => $schema) {
                $result[$name] = array();
                foreach ($schema->getColumns(false) as $column) {
                    $result[$name][] = array(
                        'name' => $column->getLabel(),
                        'type' =>  array_pop(explode('\\', get_class($column->getType()))),
                        'ismulti' => $column->isMulti()
                    );
                }
            }
            return $result;
        } catch (StructException $e) {
            throw new RemoteException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the data that would be shown in an aggregation
     *
     * @param array  $schemas array of strings with the schema-names
     * @param array  $cols array of strings with the columns
     * @param array  $filter array of arrays with ['logic'=> 'and'|'or', 'condition' => 'your condition']
     * @param string $sort string indicating the column to sort by
     *
     * @return array array of rows, each row is an array of the column values
     * @throws RemoteException
     */
    public function getAggregationData(array $schemas, array $cols, array $filter = [], $sort = '') {
        $schemaLine = 'schema: ' . implode(', ', $schemas);
        $columnLine = 'cols: ' . implode(', ', $cols);
        $filterLines = array_map(function ($filter) {
            return 'filter' . $filter['logic'] . ': ' . $filter['condition'];
        }, $filter);
        $sortLine = 'sort: ' . $sort;
        // schemas, cols, REV?, filter, order

        try {
            $parser = new ConfigParser(array_merge([$schemaLine, $columnLine, $sortLine], $filterLines));
            $config = $parser->getConfig();
            $search = new SearchConfig($config);
            $results = $search->execute();
            $data = [];
            /** @var \dokuwiki\plugin\struct\meta\Value[] $rowValues */
            foreach ($results as $rowValues) {
                $row = [];
                foreach ($rowValues as $value) {
                    $row[$value->getColumn()->getFullQualifiedLabel()] = $value->getDisplayValue();
                }
                $data[] = $row;
            }
            return $data;
        } catch (StructException $e) {
            throw new RemoteException($e->getMessage(), 0, $e);
        }
    }
}
