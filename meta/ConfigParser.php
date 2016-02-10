<?php

namespace plugin\struct\meta;

/**
 * Class ConfigParser
 *
 * Utilities to parse the configuration syntax into an array
 *
 * @package plugin\struct\meta
 */
class ConfigParser {


    protected $config = array();

    /**
     * Parser constructor.
     *
     * parses the given configuration lines
     *
     * @param $lines
     */
    public function __construct($lines) {
        $data = array(
            'limit' => 0,
            'dynfilters' => false,
            'summarize' => false,
            'rownumbers' => false,
            'sepbyheaders' => false,
            'headers' => array(),
            'widths' => array(),
            'filter' => array(),
            'schemas' => array()
        );
        // parse info
        foreach($lines as $line) {
            list($key, $val) = $this->splitLine($line);
            if(!$key) continue;

            $logic = 'OR';
            // handle line commands (we allow various aliases here)
            switch($key) {
                case 'from':
                case 'schema':
                case 'tables':
                    $this->config['schemas'] = array_merge($this->config['schemas'], $this->parseSchema($val));
                    break;
                case 'select':
                case 'cols':
                case 'field':
                case 'col':
                    $this->config['cols'] = $this->parseValues($val);
                    break;
                case 'title':
                    $this->config['title'] = $val;
                    break;
                case 'head':
                case 'header':
                case 'headers':
                    $this->config['headers'] = $this->parseValues($val);
                    break;
                case 'align':
                    $this->config['align'] = $this->parseAlignments($val);
                    break;
                case 'widths':
                    $this->config['width'] = $this->parseValues($val);
                    break;
                case 'min':
                    $this->config['min'] = abs((int) $val);
                    break;
                case 'limit':
                case 'max':
                    $this->config['limit'] = abs((int) $val);
                    break;
                case 'order':
                case 'sort':
                    // FIXME multiple values!?
                    if(substr($val, 0, 1) == '^') {
                        $this->config['sort'] = array(substr($val, 1), 'DESC');
                    } else {
                        $this->config['sort'] = array($val, 'ASC');
                    }
                    break;
                case 'where':
                case 'filter':
                case 'filterand':
                    /** @noinspection PhpMissingBreakStatementInspection */
                case 'and':
                    $logic = 'AND';
                case 'filteror':
                case 'or':
                    $flt = $this->parseFilter($val);
                    if($flt) {
                        $flt[] = $logic;
                        $this->config['filter'][] = $flt;
                    }
                    break;
                case 'page':
                case 'target':
                    $this->config['page'] = cleanID($val);
                    break;
                case 'dynfilters':
                    $this->config['dynfilters'] = (bool) $val;
                    break;
                case 'rownumbers':
                    $this->config['rownumbers'] = (bool) $val;
                    break;
                case 'summarize':
                    $this->config['summarize'] = (bool) $val;
                    break;
                case 'sepbyheaders':
                    $this->config['sepbyheaders'] = (bool) $val;
                    break;
                default:
                    throw new StructException("unknown option '%s'", hsc($val));
            }
        }
        // we need at least one column to display
        // fill up headers with field names if necessary
        $this->config['headers'] = (array) $this->config['headers'];
        $cnth = count($this->config['headers']);
        $cntf = count($this->config['cols']);
        for($i = $cnth; $i < $cntf; $i++) {
            $column = array_slice($this->config['cols'], $i, 1);
            $columnprops = array_pop($column);
            $this->config['headers'][] = $columnprops['title'];
        }

        return $data;
    }

    /**
     * Get the parsed configuration
     *
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Splits the given line into key and value
     *
     * @param $line
     * @return bool|array returns false for empty lines
     */
    protected function splitLine($line) {
        // ignore comments
        $line = preg_replace('/(?<![&\\\\])#.*$/', '', $line);
        $line = str_replace('\\#', '#', $line);
        $line = trim($line);
        if(empty($line)) return false;

        $line = preg_split('/\s*:\s*/', $line, 2);
        $line[0] = strtolower($line[0]);

        return $line;
    }

    /**
     * parses schema config and aliases
     *
     * @param $val
     * @return array
     */
    protected function parseSchema($val){
        $schemas = array();
        $parts = explode(',', $val);
        foreach($parts as $part) {
            list($table, $alias) = explode(' ', $part);
            $table = trim($table);
            $alias = trim($alias);
            if(!$table) continue;

            $schemas[] = array($table, $alias);
        }
        return $schemas;
    }

    /**
     * Parse a filter
     *
     * @param string $val
     * @return array ($col, $comp, $value)
     */
    protected function parseFilter($val) {

        $comps = Search::$COMPARATORS;
        $comps = array_map('preg_quote_cb', $comps);
        $comps = join('|', $comps);

        if(!preg_match('/^(.*?)('.$comps.')(.*)$/', $val, $match)) {
            throw new StructException('Invalid search filter %s', hsc($val));
        }
        array_shift($match); // we don't need the zeroth match
        return $match;
    }


    /**
     * Parse alignment data
     *
     * @param string $val
     * @return string[]
     */
    protected function parseAlignments($val) {
        $cols = explode(',', $val);
        $data = array();
        foreach($cols as $col) {
            $col = trim(strtolower($col));
            if($col[0] == 'c') {
                $align = 'center';
            } elseif($col[0] == 'r') {
                $align = 'right';
            } else {
                $align = 'left';
            }
            $data[] = $align;
        }

        return $data;
    }

    /**
     * Split values at the commas,
     * - Wrap with quotes to escape comma, quotes escaped by two quotes
     * - Within quotes spaces are stored.
     *
     * @param string $line
     * @return array
     */
    protected function parseValues($line) {
        $values = array();
        $inQuote = false;
        $escapedQuote = false;
        $value = '';
        $len = strlen($line);
        for($i = 0; $i < $len; $i++) {
            if($line{$i} == '"') {
                if($inQuote) {
                    if($escapedQuote) {
                        $value .= '"';
                        $escapedQuote = false;
                        continue;
                    }
                    if($line{$i + 1} == '"') {
                        $escapedQuote = true;
                        continue;
                    }
                    array_push($values, $value);
                    $inQuote = false;
                    $value = '';
                    continue;
                } else {
                    $inQuote = true;
                    $value = ''; //don't store stuff before the opening quote
                    continue;
                }
            } else if($line{$i} == ',') {
                if($inQuote) {
                    $value .= ',';
                    continue;
                } else {
                    if(strlen($value) < 1) {
                        continue;
                    }
                    array_push($values, trim($value));
                    $value = '';
                    continue;
                }
            }
            $value .= $line{$i};
        }
        if(strlen($value) > 0) {
            array_push($values, trim($value));
        }
        return $values;
    }

}
