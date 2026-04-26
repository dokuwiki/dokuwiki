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
    public $calls = [];

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

    /**
     * Return the instruction name for this block's closing call.
     */
    abstract protected function getClosingCall(): string;

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall([$this->getClosingCall(), [], $last_call[2]]);

        $this->process();
        $this->callWriter->finalise();
        unset($this->callWriter);
    }
}
