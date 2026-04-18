<?php

namespace dokuwiki\Parsing\Handler;

use dokuwiki\Parsing\Handler;

class CallWriter implements CallWriterInterface
{
    /** @var Handler $Handler */
    protected $Handler;

    /**
     * @param Handler $Handler
     */
    public function __construct(Handler $Handler)
    {
        $this->Handler = $Handler;
    }

    /** @inheritdoc */
    public function writeCall($call)
    {
        $this->Handler->calls[] = $call;
    }

    /** @inheritdoc */
    public function writeCalls($calls)
    {
        $this->Handler->calls = array_merge($this->Handler->calls, $calls);
    }

    /**
     * @inheritdoc
     * function is required, but since this call writer is first/highest in
     * the chain it is not required to do anything
     */
    public function finalise()
    {
        unset($this->Handler);
    }
}
