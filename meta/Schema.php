<?php

namespace plugin\struct\meta;

use plugin\struct\types\AbstractBaseType;
use plugin\struct\types\Text;

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

    /** @var AbstractBaseType[] all the colums */
    protected $columns = array();

    /** @var int */
    protected $maxsort = 0;

    /**
     * Schema constructor
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
            $result = $this->sqlite->res2arr($res);
            $this->id = $result['id'];
            $this->chksum = $result['chksum'];

        }
        $this->sqlite->res_close($res);
        if(!$this->id) return;

        // load existing columns
        $sql = "SELECT SC.*, T.*
                  FROM schema_cols SC,
                       types T
                 WHERE SC.schema_id = ?
                   AND SC.type_id = T.id
              ORDER BY SC.sort";
        $res = $this->sqlite->query($sql, $opt);
        $rows = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        foreach($rows as $row) {
            $class = 'plugin\\struct\\type\\' . $row['class'];
            $config = json_decode($row['config'], true);
            $this->columns[$row['col']] = new $class($config, $row['label'], $row['ismulti']);
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
        $table = preg_replace('/[^a-z0-9_]+/', '', $table);
        $table = preg_replace('/^[0-9_]+/', '', $table);
        $table = trim($table);
        return $table;
    }

    /**
     * Returns a table to edit the schema
     *
     * @todo should this include the form?
     *
     * @return string
     */
    public function adminEditor() {
        $html = '';

        $html .= '<input type="hidden" name="table" value="' . hsc($this->table) . '">';

        $html .= '<table class="inline">';
        $html .= '<tr><th>Sort</th><th>Label</th><th>Multi-Input?</th><th>Configuration</th><th>Type</th></tr>'; // FIXME localize
        foreach($this->columns as $key => $obj) {
            $html .= $this->adminColumn($key, $obj);
        }

        // FIXME new one needs to be added dynamically, this is just for testing
        $html .= $this->adminColumn('new1', new Text($this->maxsort+10));

        $html .= '</table>';
        return $html;
    }

    /**
     * Returns the HTML to edit a single column definition of the schema
     *
     * @param string $column_id
     * @param AbstractBaseType $type
     * @return string
     * @todo this should probably be reused for adding new columns via AJAX later?
     */
    protected function adminColumn($column_id, AbstractBaseType $type) {
        $base = 'schema[' . $column_id . ']'; // base name for all fields

        $html = '<tr>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[sort]" value="' . hsc($type->getSort()) . '" size="3">';
        $html .= '</td>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[label]" value="' . hsc($type->getLabel()) . '">';
        $html .= '</td>';

        $html .= '<td>';
        $checked = $type->isMulti() ? 'checked="checked"' : '';
        $html .= '<input type="checkbox" name="' . $base . '[ismulti]" value="1" ' . $checked . '>';
        $html .= '</td>';

        $html .= '<td>';
        $config = json_encode($type->getConfig(), JSON_PRETTY_PRINT);
        $html .= '<textarea name="' . $base . '[config]" cols="45" rows="10">' . hsc($config) . '</textarea>';
        $html .= '</td>';

        $html .= '<td>';
        $html .= substr(get_class($type), 20); //FIXME this needs to be a dropdown
        $html .= '</td>';

        $html .= '</tr>';

        return $html;
    }

}
