<?php

namespace dokuwiki\Parsing\Handler;

/**
 * Basic implementation of the rewriter interface to be specialized by children
 */
abstract class AbstractRewriter implements ReWriterInterface
{
    /** @var CallWriterInterface original CallWriter */
    protected $callWriter;

    /** @var array[] list of calls */
    public $calls = array();

    /** @inheritdoc */
    public function __construct(CallWriterInterface $callWriter)
    {
        $this->callWriter = $callWriter;
    }

    /** @inheritdoc */
    public function writeCall($call)
    {
        $this->calls[] = $call;
    }

    /** * @inheritdoc */
    public function writeCalls($calls)
    {
        $this->calls = array_merge($this->calls, $calls);
    }

    /** @inheritDoc */
    public function getCallWriter()
    {
        return $this->callWriter;
    }
}
