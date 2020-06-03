<?php

namespace dokuwiki\Parsing\Handler;

class Quote extends AbstractRewriter
{
    protected $quoteCalls = array();

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall(array('quote_end',array(), $last_call[2]));

        $this->process();
        $this->callWriter->finalise();
        unset($this->callWriter);
    }

    /** @inheritdoc */
    public function process()
    {

        $quoteDepth = 1;

        foreach ($this->calls as $call) {
            switch ($call[0]) {

                /** @noinspection PhpMissingBreakStatementInspection */
                case 'quote_start':
                    $this->quoteCalls[] = array('quote_open',array(),$call[2]);
                    // fallthrough
                case 'quote_newline':
                    $quoteLength = $this->getDepth($call[1][0]);

                    if ($quoteLength > $quoteDepth) {
                        $quoteDiff = $quoteLength - $quoteDepth;
                        for ($i = 1; $i <= $quoteDiff; $i++) {
                            $this->quoteCalls[] = array('quote_open',array(),$call[2]);
                        }
                    } elseif ($quoteLength < $quoteDepth) {
                        $quoteDiff = $quoteDepth - $quoteLength;
                        for ($i = 1; $i <= $quoteDiff; $i++) {
                            $this->quoteCalls[] = array('quote_close',array(),$call[2]);
                        }
                    } else {
                        if ($call[0] != 'quote_start') $this->quoteCalls[] = array('linebreak',array(),$call[2]);
                    }

                    $quoteDepth = $quoteLength;

                    break;

                case 'quote_end':
                    if ($quoteDepth > 1) {
                        $quoteDiff = $quoteDepth - 1;
                        for ($i = 1; $i <= $quoteDiff; $i++) {
                            $this->quoteCalls[] = array('quote_close',array(),$call[2]);
                        }
                    }

                    $this->quoteCalls[] = array('quote_close',array(),$call[2]);

                    $this->callWriter->writeCalls($this->quoteCalls);
                    break;

                default:
                    $this->quoteCalls[] = $call;
                    break;
            }
        }

        return $this->callWriter;
    }

    /**
     * @param string $marker
     * @return int
     */
    protected function getDepth($marker)
    {
        preg_match('/>{1,}/', $marker, $matches);
        $quoteLength = strlen($matches[0]);
        return $quoteLength;
    }
}
