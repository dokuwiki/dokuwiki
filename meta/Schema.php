<?php

namespace plugin\struct\meta;

use dokuwiki\Form\Form;
use plugin\struct\types\AbstractBaseType;
use plugin\struct\types\Text;

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
            $result = array_shift($this->sqlite->res2arr($res));
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
                    new $class($config, $row['label'], $row['ismulti']),
                    $row['tid'],
                    $row['colref'],
                    $row['enabled']
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
        $table = preg_replace('/[^a-z0-9_]+/', '', $table);
        $table = preg_replace('/^[0-9_]+/', '', $table);
        $table = trim($table);
        return $table;
    }

    /**
     * Returns the Admin Form to edit the schema
     *
     * This data is processed by the SchemaBuilder class
     *
     * @return string
     * @see SchemaBuilder
     * @todo it could be discussed if this editor should be part of the schema class it self or if that should be in a SchemaEditor class
     */
    public function adminEditor() {
        $form = new Form(array('method' => 'POST'));
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'struct');
        $form->setHiddenField('table', $this->table);
        $form->setHiddenField('schema[id]', $this->id);

        $form->addHTML('<table class="inline">');
        $form->addHTML('<tr><th>Sort</th><th>Label</th><th>Multi-Input?</th><th>Configuration</th><th>Type</th></tr>'); // FIXME localize

        foreach($this->columns as $key => $obj) {
            $form->addHTML($this->adminColumn($key, $obj));
        }

        // FIXME new one needs to be added dynamically, this is just for testing
        $form->addHTML($this->adminColumn('new1', new Column($this->maxsort+10, new Text()), 'new'));

        $form->addHTML('</table>');
        $form->addButton('save', 'Save')->attr('type','submit');
        return $form->toHTML();
    }

    /**
     * Returns the HTML to edit a single column definition of the schema
     *
     * @param string $column_id
     * @param Column $col
     * @param string $key The key to use in the form
     * @return string
     * @todo this should probably be reused for adding new columns via AJAX later?
     * @todo as above this might be better fitted to a SchemaEditor class
     */
    protected function adminColumn($column_id, Column $col, $key='cols') {
        $base = 'schema['.$key.'][' . $column_id . ']'; // base name for all fields

        $html = '<tr>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[sort]" value="' . hsc($col->getSort()) . '" size="3">';
        $html .= '</td>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[label]" value="' . hsc($col->getType()->getLabel()) . '">';
        $html .= '</td>';

        $html .= '<td>';
        $checked = $col->getType()->isMulti() ? 'checked="checked"' : '';
        $html .= '<input type="checkbox" name="' . $base . '[ismulti]" value="1" ' . $checked . '>';
        $html .= '</td>';

        $html .= '<td>';
        $config = json_encode($col->getType()->getConfig(), JSON_PRETTY_PRINT);
        $html .= '<textarea name="' . $base . '[config]" cols="45" rows="10">' . hsc($config) . '</textarea>';
        $html .= '</td>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[class]" value="' . hsc($col->getType()->getClass()) . '">'; //FIXME this needs to be a dropdown
        $html .= '</td>';

        $html .= '</tr>';

        return $html;
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
     * @return \plugin\struct\meta\Column[]
     */
    public function getColumns() {
        return $this->columns;
    }




}
