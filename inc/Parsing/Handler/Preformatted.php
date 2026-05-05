<?php

namespace dokuwiki\Parsing\Handler;

class Preformatted extends AbstractRewriter
{
    protected $pos;
    protected $text = '';

    /** @inheritdoc */
    protected function getClosingCall(): string
    {
        return 'preformatted_end';
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
                    // Skip blocks whose only content is whitespace. For
                    // the rest, strip leading/trailing newline runs
                    if (trim($this->text)) {
                        $text = trim($this->text, "\n");
                        $this->callWriter->writeCall(['preformatted', [$text], $this->pos]);
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
