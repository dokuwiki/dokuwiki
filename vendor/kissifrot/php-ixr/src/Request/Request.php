<?php

namespace IXR\Request;

use IXR\DataType\Value;

/**
 * IXR_Request
 *
 * @package IXR
 * @since 1.5.0
 */
class Request
{
    private $method;
    private $args;
    private $xml;

    public function __construct($method, $args)
    {
        $this->method = $method;
        $this->args = $args;
        $this->xml = <<<EOD
<?xml version="1.0"?>
<methodCall>
<methodName>{$this->method}</methodName>
<params>

EOD;
        foreach ($this->args as $arg) {
            $this->xml .= '<param><value>';
            $v = new Value($arg);
            $this->xml .= $v->getXml();
            $this->xml .= "</value></param>\n";
        }
        $this->xml .= '</params></methodCall>';
    }

    public function getLength()
    {
        return strlen($this->xml);
    }

    public function getXml()
    {
        return $this->xml;
    }
}
