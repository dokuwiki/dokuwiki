<?php

namespace dokuwiki\Parsing\Handler;

class Lists extends AbstractRewriter
{
    protected $listCalls = array();
    protected $listStack = array();

    protected $initialDepth = 0;

    const NODE = 1;

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall(array('list_close',array(), $last_call[2]));

        $this->process();
        $this->callWriter->finalise();
        unset($this->callWriter);
    }

    /** @inheritdoc */
    public function process()
    {

        foreach ($this->calls as $call) {
            switch ($call[0]) {
                case 'list_item':
                    $this->listOpen($call);
                    break;
                case 'list_open':
                    $this->listStart($call);
                    break;
                case 'list_close':
                    $this->listEnd($call);
                    break;
                default:
                    $this->listContent($call);
                    break;
            }
        }

        $this->callWriter->writeCalls($this->listCalls);
        return $this->callWriter;
    }

    protected function listStart($call)
    {
        $depth = $this->interpretSyntax($call[1][0], $listType);

        $this->initialDepth = $depth;
        //                   array(list type, current depth, index of current listitem_open)
        $this->listStack[] = array($listType, $depth, 1);

        $this->listCalls[] = array('list'.$listType.'_open',array(),$call[2]);
        $this->listCalls[] = array('listitem_open',array(1),$call[2]);
        $this->listCalls[] = array('listcontent_open',array(),$call[2]);
    }


    protected function listEnd($call)
    {
        $closeContent = true;

        while ($list = array_pop($this->listStack)) {
            if ($closeContent) {
                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $closeContent = false;
            }
            $this->listCalls[] = array('listitem_close',array(),$call[2]);
            $this->listCalls[] = array('list'.$list[0].'_close', array(), $call[2]);
        }
    }

    protected function listOpen($call)
    {
        $depth = $this->interpretSyntax($call[1][0], $listType);
        $end = end($this->listStack);
        $key = key($this->listStack);

        // Not allowed to be shallower than initialDepth
        if ($depth < $this->initialDepth) {
            $depth = $this->initialDepth;
        }

        if ($depth == $end[1]) {
            // Just another item in the list...
            if ($listType == $end[0]) {
                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $this->listCalls[] = array('listitem_close',array(),$call[2]);
                $this->listCalls[] = array('listitem_open',array($depth-1),$call[2]);
                $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                // new list item, update list stack's index into current listitem_open
                $this->listStack[$key][2] = count($this->listCalls) - 2;

                // Switched list type...
            } else {
                $this->listCalls[] = array('listcontent_close',array(),$call[2]);
                $this->listCalls[] = array('listitem_close',array(),$call[2]);
                $this->listCalls[] = array('list'.$end[0].'_close', array(), $call[2]);
                $this->listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
                $this->listCalls[] = array('listitem_open', array($depth-1), $call[2]);
                $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                array_pop($this->listStack);
                $this->listStack[] = array($listType, $depth, count($this->listCalls) - 2);
            }
        } elseif ($depth > $end[1]) { // Getting deeper...
            $this->listCalls[] = array('listcontent_close',array(),$call[2]);
            $this->listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
            $this->listCalls[] = array('listitem_open', array($depth-1), $call[2]);
            $this->listCalls[] = array('listcontent_open',array(),$call[2]);

            // set the node/leaf state of this item's parent listitem_open to NODE
            $this->listCalls[$this->listStack[$key][2]][1][1] = self::NODE;

            $this->listStack[] = array($listType, $depth, count($this->listCalls) - 2);
        } else { // Getting shallower ( $depth < $end[1] )
            $this->listCalls[] = array('listcontent_close',array(),$call[2]);
            $this->listCalls[] = array('listitem_close',array(),$call[2]);
            $this->listCalls[] = array('list'.$end[0].'_close',array(),$call[2]);

            // Throw away the end - done
            array_pop($this->listStack);

            while (1) {
                $end = end($this->listStack);
                $key = key($this->listStack);

                if ($end[1] <= $depth) {
                    // Normalize depths
                    $depth = $end[1];

                    $this->listCalls[] = array('listitem_close',array(),$call[2]);

                    if ($end[0] == $listType) {
                        $this->listCalls[] = array('listitem_open',array($depth-1),$call[2]);
                        $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                        // new list item, update list stack's index into current listitem_open
                        $this->listStack[$key][2] = count($this->listCalls) - 2;
                    } else {
                        // Switching list type...
                        $this->listCalls[] = array('list'.$end[0].'_close', array(), $call[2]);
                        $this->listCalls[] = array('list'.$listType.'_open', array(), $call[2]);
                        $this->listCalls[] = array('listitem_open', array($depth-1), $call[2]);
                        $this->listCalls[] = array('listcontent_open',array(),$call[2]);

                        array_pop($this->listStack);
                        $this->listStack[] = array($listType, $depth, count($this->listCalls) - 2);
                    }

                    break;

                    // Haven't dropped down far enough yet.... ( $end[1] > $depth )
                } else {
                    $this->listCalls[] = array('listitem_close',array(),$call[2]);
                    $this->listCalls[] = array('list'.$end[0].'_close',array(),$call[2]);

                    array_pop($this->listStack);
                }
            }
        }
    }

    protected function listContent($call)
    {
        $this->listCalls[] = $call;
    }

    protected function interpretSyntax($match, & $type)
    {
        if (substr($match, -1) == '*') {
            $type = 'u';
        } else {
            $type = 'o';
        }
        // Is the +1 needed? It used to be count(explode(...))
        // but I don't think the number is seen outside this handler
        return substr_count(str_replace("\t", '  ', $match), '  ') + 1;
    }
}
