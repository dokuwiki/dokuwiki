<?php
namespace IXR\Message;

/**
 * IXR_Error
 *
 * @package IXR
 * @since 1.5.0
 */
class Error
{
    public $code;
    public $message;

    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = htmlspecialchars($message);
    }

    public function getXml()
    {
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
