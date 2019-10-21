<?php

namespace dokuwiki\Parsing\Handler;

class Preformatted implements ReWriterInterface
{

    /** @var CallWriterInterface original call writer */
    protected $callWriter;

    protected $calls = array();
    protected $pos;
    protected $text ='';

    /**
     * @inheritdoc
     */
    public function __construct(CallWriterInterface $CallWriter)
    {
        $this->callWriter = $CallWriter;
    }

    /** @inheritdoc */
    public function writeCall($call)
    {
        $this->calls[] = $call;
    }

    /**
     * @inheritdoc
     * Probably not needed but just in case...
     */
    public function writeCalls($calls)
    {
        $this->calls = array_merge($this->calls, $calls);
    }

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall(array('preformatted_end',array(), $last_call[2]));

        $this->process();
        $this->callWriter->finalise();
        unset($this->callWriter);
    }

    /** @inheritdoc */
    public function process()
    {
        foreach ($this->calls as $call) {
            switch ($call[0]) {
                case 'preformatted_start':
                    $this->pos = $call[2];
                    break;
                case 'preformatted_newline':
                    $this->text .= "\n";
                    break;
                case 'preformatted_content':
                    $this->text .= $call[1][0];
                    break;
                case 'preformatted_end':
                    if (trim($this->text)) {
                        $this->callWriter->writeCall(array('preformatted', array($this->text), $this->pos));
                    }
                    // see FS#1699 & FS#1652, add 'eol' instructions to ensure proper triggering of following p_open
                    $this->callWriter->writeCall(array('eol', array(), $this->pos));
                    $this->callWriter->writeCall(array('eol', array(), $this->pos));
                    break;
            }
        }

        return $this->callWriter;
    }
}
