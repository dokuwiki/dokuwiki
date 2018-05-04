<?php

namespace dokuwiki\Parsing\Handler;

interface CallWriterInterface
{
    public function writeCall($call);
    public function writeCalls($calls);
    public function finalise();
}
