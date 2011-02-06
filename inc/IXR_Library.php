<?php
/**
 * IXR - The Inutio XML-RPC Library - (c) Incutio Ltd 2002
 *
 * @version 1.61
 * @author  Simon Willison
 * @date    11th July 2003
 * @link    http://scripts.incutio.com/xmlrpc/
 * @link    http://scripts.incutio.com/xmlrpc/manual.php
 * @license Artistic License http://www.opensource.org/licenses/artistic-license.php
 *
 * Modified for DokuWiki
 * @author  Andreas Gohr <andi@splitbrain.org>
 */


class IXR_Value {
    var $data;
    var $type;
    function IXR_Value ($data, $type = false) {
        $this->data = $data;
        if (!$type) {
            $type = $this->calculateType();
        }
        $this->type = $type;
        if ($type == 'struct') {
            /* Turn all the values in the array in to new IXR_Value objects */
            foreach ($this->data as $key => $value) {
                $this->data[$key] = new IXR_Value($value);
            }
        }
        if ($type == 'array') {
            for ($i = 0, $j = count($this->data); $i < $j; $i++) {
                $this->data[$i] = new IXR_Value($this->data[$i]);
            }
        }
    }
    function calculateType() {
        if ($this->data === true || $this->data === false) {
            return 'boolean';
        }
        if (is_integer($this->data)) {
            return 'int';
        }
        if (is_double($this->data)) {
            return 'double';
        }
        // Deal with IXR object types base64 and date
        if (is_object($this->data) && is_a($this->data, 'IXR_Date')) {
            return 'date';
        }
        if (is_object($this->data) && is_a($this->data, 'IXR_Base64')) {
            return 'base64';
        }
        // If it is a normal PHP object convert it in to a struct
        if (is_object($this->data)) {

            $this->data = get_object_vars($this->data);
            return 'struct';
        }
        if (!is_array($this->data)) {
            return 'string';
        }
        /* We have an array - is it an array or a struct ? */
        if ($this->isStruct($this->data)) {
            return 'struct';
        } else {
            return 'array';
        }
    }
    function getXml() {
        /* Return XML for this value */
        switch ($this->type) {
            case 'boolean':
                return '<boolean>'.(($this->data) ? '1' : '0').'</boolean>';
                break;
            case 'int':
                return '<int>'.$this->data.'</int>';
                break;
            case 'double':
                return '<double>'.$this->data.'</double>';
                break;
            case 'string':
                return '<string>'.htmlspecialchars($this->data).'</string>';
                break;
            case 'array':
                $return = '<array><data>'."\n";
                foreach ($this->data as $item) {
                    $return .= '  <value>'.$item->getXml()."</value>\n";
                }
                $return .= '</data></array>';
                return $return;
                break;
            case 'struct':
                $return = '<struct>'."\n";
                foreach ($this->data as $name => $value) {
                    $return .= "  <member><name>$name</name><value>";
                    $return .= $value->getXml()."</value></member>\n";
                }
                $return .= '</struct>';
                return $return;
                break;
            case 'date':
            case 'base64':
                return $this->data->getXml();
                break;
        }
        return false;
    }
    function isStruct($array) {
        /* Nasty function to check if an array is a struct or not */
        $expected = 0;
        foreach ($array as $key => $value) {
            if ((string)$key != (string)$expected) {
                return true;
            }
            $expected++;
        }
        return false;
    }
}


class IXR_Message {
    var $message;
    var $messageType;  // methodCall / methodResponse / fault
    var $faultCode;
    var $faultString;
    var $methodName;
    var $params;
    // Current variable stacks
    var $_arraystructs = array();   // The stack used to keep track of the current array/struct
    var $_arraystructstypes = array(); // Stack keeping track of if things are structs or array
    var $_currentStructName = array();  // A stack as well
    var $_param;
    var $_value;
    var $_currentTag;
    var $_currentTagContents;
    var $_lastseen;
    // The XML parser
    var $_parser;
    function IXR_Message ($message) {
        $this->message = $message;
    }
    function parse() {
        // first remove the XML declaration
        $this->message = preg_replace('/<\?xml(.*)?\?'.'>/', '', $this->message);
        // workaround for a bug in PHP/libxml2, see http://bugs.php.net/bug.php?id=45996
        $this->message = str_replace('&lt;', '&#60;', $this->message);
        $this->message = str_replace('&gt;', '&#62;', $this->message);
        $this->message = str_replace('&amp;', '&#38;', $this->message);
        $this->message = str_replace('&apos;', '&#39;', $this->message);
        $this->message = str_replace('&quot;', '&#34;', $this->message);
        $this->message = str_replace("\x0b", ' ', $this->message); //vertical tab
        if (trim($this->message) == '') {
            return false;
        }
        $this->_parser = xml_parser_create();
        // Set XML parser to take the case of tags in to account
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        // Set XML parser callback functions
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($this->_parser, 'cdata');
        if (!xml_parse($this->_parser, $this->message)) {
            /* die(sprintf('XML error: %s at line %d',
                xml_error_string(xml_get_error_code($this->_parser)),
                xml_get_current_line_number($this->_parser))); */
            return false;
        }
        xml_parser_free($this->_parser);
        // Grab the error messages, if any
        if ($this->messageType == 'fault') {
            $this->faultCode = $this->params[0]['faultCode'];
            $this->faultString = $this->params[0]['faultString'];
        }
        return true;
    }
    function tag_open($parser, $tag, $attr) {
        $this->currentTag = $tag;
        $this->_currentTagContents = '';
        switch($tag) {
            case 'methodCall':
            case 'methodResponse':
            case 'fault':
                $this->messageType = $tag;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':    // data is to all intents and puposes more interesting than array
                $this->_arraystructstypes[] = 'array';
                $this->_arraystructs[] = array();
                break;
            case 'struct':
                $this->_arraystructstypes[] = 'struct';
                $this->_arraystructs[] = array();
                break;
        }
        $this->_lastseen = $tag;
    }
    function cdata($parser, $cdata) {
        $this->_currentTagContents .= $cdata;
    }
    function tag_close($parser, $tag) {
        $valueFlag = false;
        switch($tag) {
            case 'int':
            case 'i4':
                $value = (int)trim($this->_currentTagContents);
                $this->_currentTagContents = '';
                $valueFlag = true;
                break;
            case 'double':
                $value = (double)trim($this->_currentTagContents);
                $this->_currentTagContents = '';
                $valueFlag = true;
                break;
            case 'string':
                $value = (string)$this->_currentTagContents;
                $this->_currentTagContents = '';
                $valueFlag = true;
                break;
            case 'dateTime.iso8601':
                $value = new IXR_Date(trim($this->_currentTagContents));
                // $value = $iso->getTimestamp();
                $this->_currentTagContents = '';
                $valueFlag = true;
                break;
            case 'value':
                // "If no type is indicated, the type is string."
                if($this->_lastseen == 'value'){
                    $value = (string)$this->_currentTagContents;
                    $this->_currentTagContents = '';
                    $valueFlag = true;
                }
                break;
            case 'boolean':
                $value = (boolean)trim($this->_currentTagContents);
                $this->_currentTagContents = '';
                $valueFlag = true;
                break;
            case 'base64':
                $value = base64_decode($this->_currentTagContents);
                $this->_currentTagContents = '';
                $valueFlag = true;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':
            case 'struct':
                $value = array_pop($this->_arraystructs);
                array_pop($this->_arraystructstypes);
                $valueFlag = true;
                break;
            case 'member':
                array_pop($this->_currentStructName);
                break;
            case 'name':
                $this->_currentStructName[] = trim($this->_currentTagContents);
                $this->_currentTagContents = '';
                break;
            case 'methodName':
                $this->methodName = trim($this->_currentTagContents);
                $this->_currentTagContents = '';
                break;
        }
        if ($valueFlag) {
            /*
            if (!is_array($value) && !is_object($value)) {
                $value = trim($value);
            }
            */
            if (count($this->_arraystructs) > 0) {
                // Add value to struct or array
                if ($this->_arraystructstypes[count($this->_arraystructstypes)-1] == 'struct') {
                    // Add to struct
                    $this->_arraystructs[count($this->_arraystructs)-1][$this->_currentStructName[count($this->_currentStructName)-1]] = $value;
                } else {
                    // Add to array
                    $this->_arraystructs[count($this->_arraystructs)-1][] = $value;
                }
            } else {
                // Just add as a paramater
                $this->params[] = $value;
            }
        }
        $this->_lastseen = $tag;
    }
}


class IXR_Server {
    var $data;
    var $callbacks = array();
    var $message;
    var $capabilities;
    function IXR_Server($callbacks = false, $data = false) {
        $this->setCapabilities();
        if ($callbacks) {
            $this->callbacks = $callbacks;
        }
        $this->setCallbacks();
        $this->serve($data);
    }
    function serve($data = false) {
        if (!$data) {
            global $HTTP_RAW_POST_DATA;
            if (!$HTTP_RAW_POST_DATA) {
                die('XML-RPC server accepts POST requests only.');
            }
            $data = $HTTP_RAW_POST_DATA;
        }
        $this->message = new IXR_Message($data);
        if (!$this->message->parse()) {
            $this->error(-32700, 'parse error. not well formed');
        }
        if ($this->message->messageType != 'methodCall') {
            $this->error(-32600, 'server error. invalid xml-rpc. not conforming to spec. Request must be a methodCall');
        }
        $result = $this->call($this->message->methodName, $this->message->params);
        // Is the result an error?
        if (is_a($result, 'IXR_Error')) {
            $this->error($result);
        }
        // Encode the result
        $r = new IXR_Value($result);
        $resultxml = $r->getXml();
        // Create the XML
        $xml = <<<EOD
<methodResponse>
  <params>
    <param>
      <value>
        $resultxml
      </value>
    </param>
  </params>
</methodResponse>

EOD;
        // Send it
        $this->output($xml);
    }
    function call($methodname, $args) {
        if (!$this->hasMethod($methodname)) {
            return new IXR_Error(-32601, 'server error. requested method '.$methodname.' does not exist.');
        }
        $method = $this->callbacks[$methodname];
        // Perform the callback and send the response

        # Removed for DokuWiki to have a more consistent interface
        #        if (count($args) == 1) {
        #            // If only one paramater just send that instead of the whole array
        #            $args = $args[0];
        #        }

        # Adjusted for DokuWiki to use call_user_func_array

        // args need to be an array
        $args = (array) $args;

        // Are we dealing with a function or a method?
        if (substr($method, 0, 5) == 'this:') {
            // It's a class method - check it exists
            $method = substr($method, 5);
            if (!method_exists($this, $method)) {
                return new IXR_Error(-32601, 'server error. requested class method "'.$method.'" does not exist.');
            }
            // Call the method
            #$result = $this->$method($args);
            $result = call_user_func_array(array(&$this,$method),$args);
        } elseif (substr($method, 0, 7) == 'plugin:') {
            list($pluginname, $callback) = explode(':', substr($method, 7), 2);
            if(!plugin_isdisabled($pluginname)) {
                $plugin = plugin_load('action', $pluginname);
                return call_user_func_array(array($plugin, $callback), $args);
            } else {
                return new IXR_Error(-99999, 'server error');
            }
        } else {
            // It's a function - does it exist?
            if (!function_exists($method)) {
                return new IXR_Error(-32601, 'server error. requested function "'.$method.'" does not exist.');
            }
            // Call the function
            #$result = $method($args);
            $result = call_user_func_array($method,$args);
        }
        return $result;
    }

    function error($error, $message = false) {
        // Accepts either an error object or an error code and message
        if ($message && !is_object($error)) {
            $error = new IXR_Error($error, $message);
        }
        $this->output($error->getXml());
    }
    function output($xml) {
        header('Content-Type: text/xml; charset=utf-8');
        echo '<?xml version="1.0"?>', "\n", $xml;
        exit;
    }
    function hasMethod($method) {
        return in_array($method, array_keys($this->callbacks));
    }
    function setCapabilities() {
        // Initialises capabilities array
        $this->capabilities = array(
            'xmlrpc' => array(
                'specUrl' => 'http://www.xmlrpc.com/spec',
                'specVersion' => 1
            ),
            'faults_interop' => array(
                'specUrl' => 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php',
                'specVersion' => 20010516
            ),
            'system.multicall' => array(
                'specUrl' => 'http://www.xmlrpc.com/discuss/msgReader$1208',
                'specVersion' => 1
            ),
        );
    }
    function getCapabilities() {
        return $this->capabilities;
    }
    function setCallbacks() {
        $this->callbacks['system.getCapabilities'] = 'this:getCapabilities';
        $this->callbacks['system.listMethods'] = 'this:listMethods';
        $this->callbacks['system.multicall'] = 'this:multiCall';
    }
    function listMethods() {
        // Returns a list of methods - uses array_reverse to ensure user defined
        // methods are listed before server defined methods
        return array_reverse(array_keys($this->callbacks));
    }
    function multiCall($methodcalls) {
        // See http://www.xmlrpc.com/discuss/msgReader$1208
        $return = array();
        foreach ($methodcalls as $call) {
            $method = $call['methodName'];
            $params = $call['params'];
            if ($method == 'system.multicall') {
                $result = new IXR_Error(-32600, 'Recursive calls to system.multicall are forbidden');
            } else {
                $result = $this->call($method, $params);
            }
            if (is_a($result, 'IXR_Error')) {
                $return[] = array(
                    'faultCode' => $result->code,
                    'faultString' => $result->message
                );
            } else {
                $return[] = array($result);
            }
        }
        return $return;
    }
}

class IXR_Request {
    var $method;
    var $args;
    var $xml;
    function IXR_Request($method, $args) {
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
            $v = new IXR_Value($arg);
            $this->xml .= $v->getXml();
            $this->xml .= "</value></param>\n";
        }
        $this->xml .= '</params></methodCall>';
    }
    function getLength() {
        return strlen($this->xml);
    }
    function getXml() {
        return $this->xml;
    }
}

/**
 * Changed for DokuWiki to use DokuHTTPClient
 *
 * This should be compatible to the original class, but uses DokuWiki's
 * HTTP client library which will respect proxy settings
 *
 * Because the XMLRPC client is not used in DokuWiki currently this is completely
 * untested
 */
class IXR_Client extends DokuHTTPClient {
    var $posturl = '';
    var $message = false;
    var $xmlerror = false;

    function IXR_Client($server, $path = false, $port = 80) {
        $this->DokuHTTPClient();
        if (!$path) {
            // Assume we have been given a URL instead
            $this->posturl = $server;
        }else{
            $this->posturl = 'http://'.$server.':'.$port.$path;
        }
    }

    function query() {
        $args = func_get_args();
        $method = array_shift($args);
        $request = new IXR_Request($method, $args);
        $xml = $request->getXml();

        $this->headers['Content-Type'] = 'text/xml';
        if(!$this->sendRequest($this->posturl,$xml,'POST')){
            $this->xmlerror = new IXR_Error(-32300, 'transport error - '.$this->error);
            return false;
        }

        // Check HTTP Response code
        if($this->status < 200 || $this->status > 206){
            $this->xmlerror = new IXR_Error(-32300, 'transport error - HTTP status '.$this->status);
            return false;
        }

        // Now parse what we've got back
        $this->message = new IXR_Message($this->resp_body);
        if (!$this->message->parse()) {
            // XML error
            $this->xmlerror = new IXR_Error(-32700, 'parse error. not well formed');
            return false;
        }
        // Is the message a fault?
        if ($this->message->messageType == 'fault') {
            $this->xmlerror = new IXR_Error($this->message->faultCode, $this->message->faultString);
            return false;
        }
        // Message must be OK
        return true;
    }
    function getResponse() {
        // methodResponses can only have one param - return that
        return $this->message->params[0];
    }
    function isError() {
        return (is_object($this->xmlerror));
    }
    function getErrorCode() {
        return $this->xmlerror->code;
    }
    function getErrorMessage() {
        return $this->xmlerror->message;
    }
}


class IXR_Error {
    var $code;
    var $message;
    function IXR_Error($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
    function getXml() {
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$this->code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$this->message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
        return $xml;
    }
}


class IXR_Date {
    var $year;
    var $month;
    var $day;
    var $hour;
    var $minute;
    var $second;
    function IXR_Date($time) {
        // $time can be a PHP timestamp or an ISO one
        if (is_numeric($time)) {
            $this->parseTimestamp($time);
        } else {
            $this->parseIso($time);
        }
    }
    function parseTimestamp($timestamp) {
        $this->year = gmdate('Y', $timestamp);
        $this->month = gmdate('m', $timestamp);
        $this->day = gmdate('d', $timestamp);
        $this->hour = gmdate('H', $timestamp);
        $this->minute = gmdate('i', $timestamp);
        $this->second = gmdate('s', $timestamp);
    }
    function parseIso($iso) {
        if(preg_match('/^(\d\d\d\d)-?(\d\d)-?(\d\d)([T ](\d\d):(\d\d)(:(\d\d))?)?/',$iso,$match)){
            $this->year   = (int) $match[1];
            $this->month  = (int) $match[2];
            $this->day    = (int) $match[3];
            $this->hour   = (int) $match[5];
            $this->minute = (int) $match[6];
            $this->second = (int) $match[8];
        }
    }
    function getIso() {
        return $this->year.$this->month.$this->day.'T'.$this->hour.':'.$this->minute.':'.$this->second;
    }
    function getXml() {
        return '<dateTime.iso8601>'.$this->getIso().'</dateTime.iso8601>';
    }
    function getTimestamp() {
        return gmmktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
    }
}


class IXR_Base64 {
    var $data;
    function IXR_Base64($data) {
        $this->data = $data;
    }
    function getXml() {
        return '<base64>'.base64_encode($this->data).'</base64>';
    }
}


class IXR_IntrospectionServer extends IXR_Server {
    var $signatures;
    var $help;
    function IXR_IntrospectionServer() {
        $this->setCallbacks();
        $this->setCapabilities();
        $this->capabilities['introspection'] = array(
            'specUrl' => 'http://xmlrpc.usefulinc.com/doc/reserved.html',
            'specVersion' => 1
        );
        $this->addCallback(
            'system.methodSignature',
            'this:methodSignature',
            array('array', 'string'),
            'Returns an array describing the return type and required parameters of a method'
        );
        $this->addCallback(
            'system.getCapabilities',
            'this:getCapabilities',
            array('struct'),
            'Returns a struct describing the XML-RPC specifications supported by this server'
        );
        $this->addCallback(
            'system.listMethods',
            'this:listMethods',
            array('array'),
            'Returns an array of available methods on this server'
        );
        $this->addCallback(
            'system.methodHelp',
            'this:methodHelp',
            array('string', 'string'),
            'Returns a documentation string for the specified method'
        );
    }
    function addCallback($method, $callback, $args, $help) {
        $this->callbacks[$method] = $callback;
        $this->signatures[$method] = $args;
        $this->help[$method] = $help;
    }
    function call($methodname, $args) {
        // Make sure it's in an array
        if ($args && !is_array($args)) {
            $args = array($args);
        }
        // Over-rides default call method, adds signature check
        if (!$this->hasMethod($methodname)) {
            return new IXR_Error(-32601, 'server error. requested method "'.$this->message->methodName.'" not specified.');
        }
        $method = $this->callbacks[$methodname];
        $signature = $this->signatures[$methodname];
        $returnType = array_shift($signature);
        // Check the number of arguments. Check only, if the minimum count of parameters is specified. More parameters are possible.
        // This is a hack to allow optional parameters...
        if (count($args) < count($signature)) {
            // print 'Num of args: '.count($args).' Num in signature: '.count($signature);
            return new IXR_Error(-32602, 'server error. wrong number of method parameters');
        }
        // Check the argument types
        $ok = true;
        $argsbackup = $args;
        for ($i = 0, $j = count($args); $i < $j; $i++) {
            $arg = array_shift($args);
            $type = array_shift($signature);
            switch ($type) {
                case 'int':
                case 'i4':
                    if (is_array($arg) || !is_int($arg)) {
                        $ok = false;
                    }
                    break;
                case 'base64':
                case 'string':
                    if (!is_string($arg)) {
                        $ok = false;
                    }
                    break;
                case 'boolean':
                    if ($arg !== false && $arg !== true) {
                        $ok = false;
                    }
                    break;
                case 'float':
                case 'double':
                    if (!is_float($arg)) {
                        $ok = false;
                    }
                    break;
                case 'date':
                case 'dateTime.iso8601':
                    if (!is_a($arg, 'IXR_Date')) {
                        $ok = false;
                    }
                    break;
            }
            if (!$ok) {
                return new IXR_Error(-32602, 'server error. invalid method parameters');
            }
        }
        // It passed the test - run the "real" method call
        return parent::call($methodname, $argsbackup);
    }
    function methodSignature($method) {
        if (!$this->hasMethod($method)) {
            return new IXR_Error(-32601, 'server error. requested method "'.$method.'" not specified.');
        }
        // We should be returning an array of types
        $types = $this->signatures[$method];
        $return = array();
        foreach ($types as $type) {
            switch ($type) {
                case 'string':
                    $return[] = 'string';
                    break;
                case 'int':
                case 'i4':
                    $return[] = 42;
                    break;
                case 'double':
                    $return[] = 3.1415;
                    break;
                case 'dateTime.iso8601':
                    $return[] = new IXR_Date(time());
                    break;
                case 'boolean':
                    $return[] = true;
                    break;
                case 'base64':
                    $return[] = new IXR_Base64('base64');
                    break;
                case 'array':
                    $return[] = array('array');
                    break;
                case 'struct':
                    $return[] = array('struct' => 'struct');
                    break;
            }
        }
        return $return;
    }
    function methodHelp($method) {
        return $this->help[$method];
    }
}


class IXR_ClientMulticall extends IXR_Client {
    var $calls = array();
    function IXR_ClientMulticall($server, $path = false, $port = 80) {
        parent::IXR_Client($server, $path, $port);
        //$this->useragent = 'The Incutio XML-RPC PHP Library (multicall client)';
    }
    function addCall() {
        $args = func_get_args();
        $methodName = array_shift($args);
        $struct = array(
            'methodName' => $methodName,
            'params' => $args
        );
        $this->calls[] = $struct;
    }
    function query() {
        // Prepare multicall, then call the parent::query() method
        return parent::query('system.multicall', $this->calls);
    }
}

