<?php

namespace easywiki\test\Remote\Mock;

class JsonRpcServer extends \easywiki\Remote\JsonRpcServer
{

    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();
        $this->remote->getCoreMethods(new ApiCore()); // use the mock API core
    }

}
