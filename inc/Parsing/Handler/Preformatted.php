<?php

namespace dokuwiki\Parsing\Handler;

class Preformatted extends AbstractRewriter
{
    protected $pos;
    protected $text = '';

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall(['preformatted_end', [], $last_call[2]]);

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
                        $this->callWriter->writeCall(['preformatted', [$this->text], $this->pos]);
                    }
                    // see FS#1699 & FS#1652, add 'eol' instructions to ensure proper triggering of following p_open
                    $this->callWriter->writeCall(['eol', [], $this->pos]);
                    $this->callWriter->writeCall(['eol', [], $this->pos]);
                    break;
            }
        }

        return $this->callWriter;
    }
}
