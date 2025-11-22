<?php

namespace easywiki\test\Remote\Mock;

class XmlRpcServer extends \easywiki\Remote\XmlRpcServer
{
    public $output;

    /** @inheritdoc */
    public function __construct($wait = false)
    {
        parent::__construct($wait);
        $this->remote->getCoreMethods(new ApiCore()); // use the mock API core
    }

    /**
     * Make output available for testing
     *
     * @param string $xml
     * @return void
     */
    public function output($xml) {
        $this->output = $xml;
    }
}
