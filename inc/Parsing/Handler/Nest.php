<?php

namespace dokuwiki\Parsing\Handler;

/**
 * Generic call writer class to handle nesting of rendering instructions
 * within a render instruction. Also see nest() method of renderer base class
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */
class Nest extends AbstractRewriter
{
    protected $closingInstruction;

    /**
     * @inheritdoc
     *
     * @param  CallWriterInterface $CallWriter     the parser's current call writer, i.e. the one above us in the chain
     * @param  string     $close          closing instruction name, this is required to properly terminate the
     *                                    syntax mode if the document ends without a closing pattern
     */
    public function __construct(CallWriterInterface $CallWriter, $close = "nest_close")
    {
        parent::__construct($CallWriter);
        $this->closingInstruction = $close;
    }

    /** @inheritdoc */
    public function writeCall($call)
    {
        $this->calls[] = $call;
    }

    /** @inheritdoc */
    public function writeCalls($calls)
    {
        $this->calls = array_merge($this->calls, $calls);
    }

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall(array($this->closingInstruction,array(), $last_call[2]));

        $this->process();
        $this->callWriter->finalise();
        unset($this->callWriter);
    }

    /** @inheritdoc */
    public function process()
    {
        // merge consecutive cdata
        $unmerged_calls = $this->calls;
        $this->calls = array();

        foreach ($unmerged_calls as $call) $this->addCall($call);

        $first_call = reset($this->calls);
        $this->callWriter->writeCall(array("nest", array($this->calls), $first_call[2]));

        return $this->callWriter;
    }

    /**
     * @param array $call
     */
    protected function addCall($call)
    {
        $key = count($this->calls);
        if ($key and ($call[0] == 'cdata') and ($this->calls[$key-1][0] == 'cdata')) {
            $this->calls[$key-1][1][0] .= $call[1][0];
        } elseif ($call[0] == 'eol') {
            // do nothing (eol shouldn't be allowed, to counter preformatted fix in #1652 & #1699)
        } else {
            $this->calls[] = $call;
        }
    }


}
