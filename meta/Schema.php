<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;

if(!defined('JSON_PRETTY_PRINT')) define('JSON_PRETTY_PRINT', 0); // PHP 5.3 compatibility

/**
 * Class Schema
 *
 * Represents the schema of a single data table and all its properties. It defines what can be stored in
 * the represented data table and how those contents are formatted.
 *
 * It can be initialized with a timestamp to access the schema as it looked at that particular point in time.
 *
 * @package dokuwiki\plugin\struct\meta
 */
class Schema {

    use TranslationUtilities;

    /** @var \helper_plugin_sqlite|null */
    protected $sqlite;

    /** @var int The ID of this schema */
    protected $id = 0;

    /** @var string the user who last edited this schema */
    protected $user = '';

    /** @var string name of the associated table */
    protected $table = '';

    /** @var bool is this a lookup schema? */
    protected $islookup = false;

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

    /** @var string struct version info */
    protected $structversion = '?';

    /** @var array config array with label translations */
    protected $config = array();

    /**
     * Schema constructor
     *
     * @param string $table The table this schema is for
     * @param int $ts The timestamp for when this schema was valid, 0 for current
     * @param bool $islookup only used when creating a new schema, makes the new schema a lookup
     */
    public function __construct($table, $ts = 0, $islookup = false) {
        $baseconfig = array('allowed editors' => '');

        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $info = $helper->getInfo();
        $this->structversion = $info['date'];
        $this->sqlite = $helper->getDB();
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
        $config = array();
        if($this->sqlite->res2count($res)) {
            $schema = $this->sqlite->res2arr($res);
            $result = array_shift($schema);
            $this->id = $result['id'];
            $this->user = $result['user'];
            $this->chksum = $result['chksum'];
            $this->islookup = $result['islookup'];
            $this->ts = $result['ts'];
            $config = json_decode($result['config'], true);
        } else {
            $this->islookup = $islookup;
        }
        $this->sqlite->res_close($res);
        $this->config = array_merge($baseconfig, $config);
        $this->initTransConfig(array('label'));
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

        $typeclasses = Column::allTypes();
        foreach($rows as $row) {
            if($row['class'] == 'Integer') {
                $row['class'] = 'Decimal';
            }

            $class = $typeclasses[$row['class']];
            if(!class_exists($class)) {
                // This usually never happens, except during development
                msg('Unknown type "' . hsc($row['class']) . '" falling back to Text', -1);
                $class = 'dokuwiki\\plugin\\struct\\types\\Text';
            }

            $config = json_decode($row['config'], true);
            /** @var AbstractBaseType $type */
            $type = new $class($config, $row['label'], $row['ismulti'], $row['tid']);
            $column = new Column(
                $row['sort'],
                $type,
                $row['colref'],
                $row['enabled'],
                $table
            );
            $type->setContext($column);

            $this->columns[] = $column;
            if($row['sort'] > $this->maxsort) $this->maxsort = $row['sort'];
        }
    }

    /**
     * @return string identifer for debugging purposes
     */
    function __toString() {
        return __CLASS__ . ' ' . $this->table . ' (' . $this->id . ') ' . ($this->islookup ? 'LOOKUP' : 'DATA');
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
     * Gets a list of all available schemas
     *
     * @param string $filter either 'page' or 'lookup'
     * @return \string[]
     */
    static public function getAll($filter = '') {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $db = $helper->getDB(false);
        if(!$db) return array();

        if($filter == 'page') {
            $where = 'islookup = 0';
        } elseif($filter == 'lookup') {
            $where = 'islookup = 1';
        } else {
            $where = '1 = 1';
        }

        $res = $db->query("SELECT DISTINCT tbl FROM schemas WHERE $where ORDER BY tbl");
        $tables = $db->res2arr($res);
        $db->res_close($res);

        $result = array();
        foreach($tables as $row) {
            $result[] = $row['tbl'];
        }
        return $result;
    }

    /**
     * Delete all data associated with this schema
     *
     * This is really all data ever! Be careful!
     */
    public function delete() {
        if(!$this->id) throw new StructException('can not delete unsaved schema');

        $this->sqlite->query('BEGIN TRANSACTION');

        $sql = "DROP TABLE ?";
        $this->sqlite->query($sql, 'data_' . $this->table);
        $this->sqlite->query($sql, 'multi_' . $this->table);

        $sql = "DELETE FROM schema_assignments WHERE tbl = ?";
        $this->sqlite->query($sql, $this->table);

        $sql = "DELETE FROM schema_assignments_patterns WHERE tbl = ?";
        $this->sqlite->query($sql, $this->table);

        $sql = "SELECT T.id
                  FROM types T, schema_cols SC, schemas S 
                 WHERE T.id = SC.tid
                   AND SC.sid = S.id
                   AND S.tbl = ?";
        $sql = "DELETE FROM types WHERE id IN ($sql)";
        $this->sqlite->query($sql, $this->table);

        $sql = "SELECT id
                  FROM schemas 
                 WHERE tbl = ?";
        $sql = "DELETE FROM schema_cols WHERE sid IN ($sql)";
        $this->sqlite->query($sql, $this->table);

        $sql = "DELETE FROM schemas WHERE tbl = ?";
        $this->sqlite->query($sql, $this->table);

        $this->sqlite->query('COMMIT TRANSACTION');
        $this->sqlite->query('VACUUM');

        // a deleted schema should not be used anymore, but let's make sure it's somewhat sane anyway
        $this->id = 0;
        $this->chksum = '';
        $this->columns = array();
        $this->maxsort = 0;
        $this->ts = 0;
    }


    /**
     * Clear all data of a schema, but retain the schema itself
     */
    public function clear() {
        if(!$this->id) throw new StructException('can not clear data of unsaved schema');

        $this->sqlite->query('BEGIN TRANSACTION');
        $sql = 'DELETE FROM ?';
        $this->sqlite->query($sql, 'data_' . $this->table);
        $this->sqlite->query($sql, 'multi_' . $this->table);
        $this->sqlite->query('COMMIT TRANSACTION');
        $this->sqlite->query('VACUUM');
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
     * @return int returns the timestamp this Schema was created at
     */
    public function getTimeStamp() {
        return $this->ts;
    }

    /**
     * @return bool is this a lookup schema?
     */
    public function isLookup() {
        return $this->islookup;
    }

    /**
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    public function getConfig() {
        return $this->config;
    }

    /**
     * Returns the translated label for this schema
     *
     * Uses the current language as determined by $conf['lang']. Falls back to english
     * and then to the Schema label
     *
     * @return string
     */
    public function getTranslatedLabel() {
        return $this->getTranslatedKey('label', $this->table);
    }

    /**
     * Checks if the current user may edit data in this schema
     *
     * @return bool
     */
    public function isEditable() {
        global $USERINFO;
        if($this->config['allowed editors'] === '') return true;
        if(blank($_SERVER['REMOTE_USER'])) return false;
        if(auth_isadmin()) return true;
        return auth_isMember($this->config['allowed editors'], $_SERVER['REMOTE_USER'], $USERINFO['grps']);
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

    /**
     * @return string the JSON representing this schema
     */
    public function toJSON() {
        $data = array(
            'structversion' => $this->structversion,
            'schema' => $this->getTable(),
            'id' => $this->getId(),
            'user' => $this->getUser(),
            'config' => $this->getConfig(),
            'columns' => array()
        );

        foreach($this->columns as $column) {
            $data['columns'][] = array(
                'colref' => $column->getColref(),
                'ismulti' => $column->isMulti(),
                'isenabled' => $column->isEnabled(),
                'sort' => $column->getSort(),
                'label' => $column->getLabel(),
                'class' => $column->getType()->getClass(),
                'config' => $column->getType()->getConfig(),
            );
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
