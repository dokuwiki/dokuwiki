<?php

namespace dokuwiki\Parsing\Handler;

interface CallWriterInterface
{
    /**
     * Add a call to our call list
     *
     * @param array $call the call to be added
     */
    public function writeCall($call);

    /**
     * Append a list of calls to our call list
     *
     * @param array[] $calls list of calls to be appended
     */
    public function writeCalls($calls);

    /**
     * Explicit request to finish up and clean up NOW!
     * (probably because document end has been reached)
     *
     * If part of a CallWriter chain, call finalise on
     * the original call writer
     *
     */
    public function finalise();
}
