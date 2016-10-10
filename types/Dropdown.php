<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\struct\meta\Search;
use dokuwiki\plugin\struct\meta\Value;

class Dropdown extends AbstractBaseType {

    protected $config = array(
        'values' => 'one, two, three',
        'schema' => '',
        'field' => ''
    );

    /** @var Schema */
    protected $schema = null;
    /** @var Column */
    protected $column = null;

    /**
     * Dropdown constructor.
     *
     * @param array|null $config
     * @param string $label
     * @param bool $ismulti
     * @param int $tid
     */
    public function __construct($config = null, $label = '', $ismulti = false, $tid = 0) {
        global $conf;

        parent::__construct($config, $label, $ismulti, $tid);
        $this->config['schema'] = Schema::cleanTableName($this->config['schema']);

        if($this->usesLookup()) {
            $this->schema = new Schema($this->config['schema']);
            if(!$this->schema->getId()) {
                // schema does not exist
                msg(sprintf('Schema %s does not exist', $this->config['schema']), -1);
                $this->schema = null;
                $this->config['schema'] = '';
                return;
            }

            // apply language replacement
            $field = str_replace('$LANG', $conf['lang'], $this->config['field']);
            $this->column = $this->schema->findColumn($field);
            if(!$this->column) {
                $field = str_replace('$LANG', 'en', $this->config['field']); // fallback to en
                $this->column = $this->schema->findColumn($field);
            }
            if(!$this->column) {
                // field does not exist
                msg(sprintf('Field %s.%s does not exist', $this->config['schema'], $this->config['field']), -1);
                $this->column = null;
                $this->config['field'] = '';
                return;
            }

            if($this->column->isMulti()) {
                // field is multi
                msg(sprintf('Field %s.%s is a multi field - not allowed for lookup', $this->config['schema'], $this->config['field']), -1);
                $this->column = null;
                $this->config['field'] = '';
                return;
            }
        }
    }

    /**
     * @return bool is this dropdown configured to use a lookup?
     */
    protected function usesLookup() {
        return !blank($this->config['schema']) && !blank($this->config['field']);
    }

    /**
     * Creates the options array
     *
     * @return array
     */
    protected function getOptions() {
        if($this->usesLookup()) {
            $options = $this->loadLookupData();
        } else {
            $options = explode(',', $this->config['values']);
            $options = array_map('trim', $options);
            $options = array_filter($options);
            array_unshift($options, '');
            $options = array_combine($options, $options);
        }
        return $options;
    }

    /**
     * Loads all available lookup values
     *
     * @return array
     */
    protected function loadLookupData() {
        $schema = $this->schema->getTable();
        $field = $this->column->getLabel();

        $search = new Search();
        $search->addSchema($schema);
        $search->addColumn($field);
        $search->addSort($field);
        $result = $search->execute();
        $pids = $search->getPids();
        $len = count($result);

        /** @var Value[][] $result */
        $options = array('' => '');
        for($i = 0; $i < $len; $i++) {
            $options[$pids[$i]] = $result[$i][0]->getDisplayValue();
        }
        return $options;
    }

    /**
     * Render using linked field
     *
     * @param int|string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if(!$this->usesLookup()) {
            return parent::renderValue($value, $R, $mode);
        } else {
            list(, $value) = json_decode($value);
            return $this->column->getType()->renderValue($value, $R, $mode);
        }
    }

    /**
     * Render using linked field
     *
     * @param \int[]|\string[] $values
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderMultiValue($values, \Doku_Renderer $R, $mode) {
        if(!$this->usesLookup()) {
            return parent::renderMultiValue($values, $R, $mode);
        } else {
            $values = array_map(
                function ($val) {
                    list(, $val) = json_decode($val);
                    return $val;
                }, $values
            );
            return $this->column->getType()->renderMultiValue($values, $R, $mode);
        }
    }

    /**
     * A Dropdown with a single value to pick
     *
     * @param string $name
     * @param string $rawvalue
     * @return string
     */
    public function valueEditor($name, $rawvalue) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = "<select name=\"$name\" class=\"$class\">";
        foreach($this->getOptions() as $opt => $val) {
            if($opt == $rawvalue) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($opt) . "\">" . hsc($val) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * A dropdown that allows to pick multiple values
     *
     * @param string $name
     * @param \string[] $rawvalues
     * @return string
     */
    public function multiValueEditor($name, $rawvalues) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = "<select name=\"{$name}[]\" class=\"$class\" multiple=\"multiple\" size=\"5\">";
        foreach($this->getOptions() as $opt) {
            if(in_array($opt, $rawvalues)) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($opt) . "\">" . hsc($opt) . '</option>';

        }
        $html .= '</select> ';
        $html .= '<small>' . $this->getLang('multidropdown') . '</small>';
        return $html;
    }

    /**
     * @param string $value
     * @return string
     */
    public function rawValue($value) {
        if($this->usesLookup()) {
            list($value) = json_decode($value);
        }
        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    public function displayValue($value) {
        if($this->usesLookup()) {
            list(, $value) = json_decode($value);
            $value = $this->column->getType()->displayValue($value);
        }
        return $value;
    }

    /**
     * Merge with lookup table
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $alias
     */
    public function select(QueryBuilder $QB, $tablealias, $colname, $alias) {
        if(!$this->usesLookup()) {
            parent::select($QB, $tablealias, $colname, $alias);
            return;
        }

        $schema = 'data_' . $this->schema->getTable();
        $field = $this->column->getColName();

        $rightalias = $QB->generateTableAlias();
        $QB->addLeftJoin($tablealias, $schema, $rightalias, "$tablealias.$colname = $rightalias.pid");
        $this->column->getType()->select($QB, $rightalias, $field, $alias);
        $sql = $QB->getSelectStatement($alias);
        $QB->addSelectStatement("STRUCT_JSON($tablealias.$colname, $sql)", $alias);
    }

    /**
     * Compare against lookup table
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilder $QB, $tablealias, $colname, $comp, $value, $op) {
        if(!$this->usesLookup()) {
            parent::filter($QB, $tablealias, $colname, $comp, $value, $op);
            return;
        }

        $schema = 'data_' . $this->schema->getTable();
        $field = $this->column->getColName();

        // compare against lookup field
        $rightalias = $QB->generateTableAlias();
        $QB->addLeftJoin($tablealias, $schema, $rightalias, "$tablealias.$colname = $rightalias.pid");
        $this->column->getType()->filter($QB, $rightalias, $field, $comp, $value, $op);
    }

    /**
     * Sort by lookup table
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $order
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order) {
        if(!$this->usesLookup()) {
            parent::sort($QB, $tablealias, $colname, $order);
            return;
        }

        $schema = 'data_' . $this->schema->getTable();
        $field = $this->column->getColName();

        $rightalias = $QB->generateTableAlias();
        $QB->addLeftJoin($tablealias, $schema, $rightalias, "$tablealias.$colname = $rightalias.pid");
        $this->column->getType()->sort($QB, $rightalias, $field, $order);
    }

}
