<?php

/**
 * Class DokuWiki_Remote_Plugin
 */
abstract class DokuWiki_Remote_Plugin extends DokuWiki_Plugin {

    private  $api;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new RemoteAPI();
    }

    /**
     * Get all available methods with remote access.
     *
     * By default it exports all public methods of a remote plugin. Methods beginning
     * with an underscore are skipped.
     *
     * @return array Information about all provided methods. {@see RemoteAPI}.
     */
    public function _getMethods() {
        $result = array();

        $reflection = new \ReflectionClass($this);
        foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // skip parent methods, only methods further down are exported
            $declaredin = $method->getDeclaringClass()->name;
            if($declaredin == 'DokuWiki_Plugin' || $declaredin == 'DokuWiki_Remote_Plugin') continue;
            $method_name = $method->name;
            if(substr($method_name, 0, 1) == '_') continue;

            // strip asterisks
            $doc = $method->getDocComment();
            $doc = preg_replace(
                array('/^[ \t]*\/\*+[ \t]*/m', '/[ \t]*\*+[ \t]*/m', '/\*+\/\s*$/m','/\s*\/\s*$/m'),
                array('', '', '', ''),
                $doc
            );

            // prepare data
            $data = array();
            $data['name'] = $method_name;
            $data['public'] = 0;
            $data['doc'] = $doc;
            $data['args'] = array();

            // get parameter type from doc block type hint
            foreach($method->getParameters() as $parameter) {
                $name = $parameter->name;
                $type = 'string'; // we default to string
                if(preg_match('/^@param[ \t]+([\w|\[\]]+)[ \t]\$'.$name.'/m', $doc, $m)){
                    $type = $this->cleanTypeHint($m[1]);
                }
                $data['args'][] = $type;
            }

            // get return type from doc block type hint
            if(preg_match('/^@return[ \t]+([\w|\[\]]+)/m', $doc, $m)){
                $data['return'] = $this->cleanTypeHint($m[1]);
            } else {
                $data['return'] = 'string';
            }

            // add to result
            $result[$method_name] = $data;
        }

        return $result;
    }

    /**
     * Matches the given type hint against the valid options for the remote API
     *
     * @param string $hint
     * @return string
     */
    protected function cleanTypeHint($hint) {
        $types = explode('|', $hint);
        foreach($types as $t) {
            if(substr($t, -2) == '[]') {
                return 'array';
            }
            if($t == 'boolean') {
                return 'bool';
            }
            if(in_array($t, array('array', 'string', 'int', 'double', 'bool', 'null', 'date', 'file'))) {
                return $t;
            }
        }
        return 'string';
    }

    /**
     * @return RemoteAPI
     */
    protected function getApi() {
        return $this->api;
    }

}
