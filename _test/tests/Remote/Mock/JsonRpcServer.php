<?php

namespace dokuwiki\test\Remote\Mock;

class JsonRpcServer extends \dokuwiki\Remote\JsonRpcServer
{

    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();
        $this->remote->getCoreMethods(new ApiCore()); // use the mock API core
    }

}
