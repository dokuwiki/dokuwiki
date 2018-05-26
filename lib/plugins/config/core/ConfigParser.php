<?php

namespace dokuwiki\plugin\config\core;

/**
 * A naive PHP file parser
 *
 * This parses our very simple config file in PHP format. We use this instead of simply including
 * the file, because we want to keep expressions such as 24*60*60 as is.
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 */
class ConfigParser {
    /** @var string variable to parse from the file */
    protected $varname = 'conf';
    /** @var string the key to mark sub arrays */
    protected $keymarker = Configuration::KEYMARKER;

    /**
     * Parse the given PHP file into an array
     *
     * When the given files does not exist, this returns an empty array
     *
     * @param string $file
     * @return array
     */
    public function parse($file) {
        if(!file_exists($file)) return array();

        $config = array();
        $contents = @php_strip_whitespace($file);
        $pattern = '/\$' . $this->varname . '\[[\'"]([^=]+)[\'"]\] ?= ?(.*?);(?=[^;]*(?:\$' . $this->varname . '|$))/s';
        $matches = array();
        preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);

        for($i = 0; $i < count($matches); $i++) {
            $value = $matches[$i][2];

            // merge multi-dimensional array indices using the keymarker
            $key = preg_replace('/.\]\[./', $this->keymarker, $matches[$i][1]);

            // handle arrays
            if(preg_match('/^array ?\((.*)\)/', $value, $match)) {
                $arr = explode(',', $match[1]);

                // remove quotes from quoted strings & unescape escaped data
                $len = count($arr);
                for($j = 0; $j < $len; $j++) {
                    $arr[$j] = trim($arr[$j]);
                    $arr[$j] = $this->readValue($arr[$j]);
                }

                $value = $arr;
            } else {
                $value = $this->readValue($value);
            }

            $config[$key] = $value;
        }

        return $config;
    }

    /**
     * Convert php string into value
     *
     * @param string $value
     * @return bool|string
     */
    protected function readValue($value) {
        $removequotes_pattern = '/^(\'|")(.*)(?<!\\\\)\1$/s';
        $unescape_pairs = array(
            '\\\\' => '\\',
            '\\\'' => '\'',
            '\\"' => '"'
        );

        if($value == 'true') {
            $value = true;
        } elseif($value == 'false') {
            $value = false;
        } else {
            // remove quotes from quoted strings & unescape escaped data
            $value = preg_replace($removequotes_pattern, '$2', $value);
            $value = strtr($value, $unescape_pairs);
        }
        return $value;
    }

}
