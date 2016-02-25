<?php

namespace plugin\struct\meta;

/**
 * Class Schema
 *
 * Represents the schema of a single data table and all its properties. It defines what can be stored in
 * the represented data table and how those contents are formatted.
 *
 * It can be initialized with a timestamp to access the schema as it looked at that particular point in time.
 *
 * @package plugin\struct\meta
 */
class Schema {

    /** @var \helper_plugin_sqlite|null */
    protected $sqlite;

    /** @var int The ID of this schema */
    protected $id = 0;

    /** @var string name of the associated table */
    protected $table = '';

    /**
     * @var string the current checksum of this schema
     */
    protected $chksum = '';

    /** @var Column[] all the colums */
    protected $columns = array();

    /** @var int */
    protected $maxsort = 0;

    /** @var int */
    protected $ts = 0;

    /**
     * Schema constructor
     *
     * @param string $table The table this schema is for
     * @param int $ts The timestamp for when this schema was valid, 0 for current
     */
    public function __construct($table, $ts = 0) {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();
        if(!$this->sqlite) return;

        $table = self::cleanTableName($table);
        $this->table = $table;
        $this->ts = $ts;

        // load info about the schema itself
        if($ts) {
            $sql = "SELECT *
                      FROM schemas
                     WHERE tbl = ?
                       AND ts <= ?
                  ORDER BY ts DESC
                     LIMIT 1";
            $opt = array($table, $ts);
        } else {
            $sql = "SELECT *
                      FROM schemas
                     WHERE tbl = ?
                  ORDER BY ts DESC
                     LIMIT 1";
            $opt = array($table);
        }
        $res = $this->sqlite->query($sql, $opt);
        if($this->sqlite->res2count($res)) {
            $schema = $this->sqlite->res2arr($res);
            $result = array_shift($schema);
            $this->id = $result['id'];
            $this->chksum = $result['chksum'];

        }
        $this->sqlite->res_close($res);
        if(!$this->id) return;

        // load existing columns
        $sql = "SELECT SC.*, T.*
                  FROM schema_cols SC,
                       types T
                 WHERE SC.sid = ?
                   AND SC.tid = T.id
              ORDER BY SC.sort";
        $res = $this->sqlite->query($sql, $this->id);
        $rows = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        foreach($rows as $row) {
            $class = 'plugin\\struct\\types\\' . $row['class'];
            $config = json_decode($row['config'], true);
            $this->columns[$row['colref']] =
                new Column(
                    $row['sort'],
                    new $class($config, $row['label'], $row['ismulti'], $row['tid']),
                    $row['colref'],
                    $row['enabled'],
                    $table
                );

            if($row['sort'] > $this->maxsort) $this->maxsort = $row['sort'];
        }
    }

    /**
     * Cleans any unwanted stuff from table names
     *
     * @param string $table
     * @return string
     */
    static public function cleanTableName($table) {
        $table = strtolower($table);
        $table = preg_replace('/[^a-z0-9_]+/', '', $table);
        $table = preg_replace('/^[0-9_]+/', '', $table);
        $table = trim($table);
        return $table;
    }

    /**
     * @return string
     */
    public function getChksum() {
        return $this->chksum;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns a list of columns in this schema
     *
     * @param bool $withDisabled if false, disabled columns will not be returned
     * @return Column[]
     */
    public function getColumns($withDisabled = true) {
        if(!$withDisabled) {
            return array_filter(
                $this->columns,
                function (Column $col) {
                    return $col->isEnabled();
                }
            );
        }

        return $this->columns;
    }

    /**
     * Find a column in the schema by its label
     *
     * Only enabled columns are returned!
     *
     * @param $name
     * @return bool|Column
     */
    public function findColumn($name) {
        foreach($this->columns as $col) {
            if($col->isEnabled() && utf8_strtolower($col->getLabel()) == utf8_strtolower($name)) {
                return $col;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @return int the highest sort number used in this schema
     */
    public function getMaxsort() {
        return $this->maxsort;
    }

}
