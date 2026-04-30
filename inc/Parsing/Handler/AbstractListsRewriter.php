<?php

namespace dokuwiki\Parsing\Handler;

/**
 * Shared state machine for list-block CallWriter rewriters.
 *
 * Buffers flat list_open / list_item / list_close calls and reorganises them
 * into the nested listu_open / listo_open / listitem_open / listcontent_*
 * structure that DokuWiki renderers consume. Subclasses supply the
 * syntax-specific marker parser via {@see interpretSyntax}; the depth/level
 * math and the list_open argument shape are uniform across DW and GFM.
 */
abstract class AbstractListsRewriter extends AbstractRewriter
{
    /** @var array[] flat list of calls produced by this rewriter */
    protected $listCalls = [];

    /** @var array[] each entry: [listType, depth, listitemOpenIndex] */
    protected $listStack = [];

    /** @var int depth of the very first item in the block; used to normalise level numbers */
    protected $initialDepth = 0;

    /** Marker value for a listitem_open's second argument when the item has child lists */
    public const NODE = 1;

    //region CallWriter integration

    /** @inheritdoc */
    protected function getClosingCall(): string
    {
        return 'list_close';
    }

    /** @inheritdoc */
    public function process()
    {
        foreach ($this->calls as $call) {
            match ($call[0]) {
                'list_open'  => $this->handleListOpen($call),
                'list_item'  => $this->handleListItem($call),
                'list_close' => $this->handleListClose($call),
                default      => $this->listContent($call),
            };
        }

        $this->callWriter->writeCalls($this->listCalls);
        return $this->callWriter;
    }

    //endregion

    //region Event handlers

    /**
     * Open the list and the first item in response to a list_open event.
     *
     * @param array $call buffered call: [name, args, pos]
     */
    protected function handleListOpen($call)
    {
        ['depth' => $depth, 'type' => $type, 'start' => $start] = $this->parseMarker($call[1][0]);

        $this->initialDepth = $depth;
        $this->emitOpenList($type, $start, $depth, $call[2]);
    }

    /**
     * Handle a list_item event: close the previous item, open the next one,
     * adjusting the listStack for type changes and depth transitions.
     *
     * @param array $call buffered call: [name, args, pos]
     */
    protected function handleListItem($call)
    {
        ['depth' => $depth, 'type' => $type, 'start' => $start] = $this->parseMarker($call[1][0]);
        $top = end($this->listStack);
        $pos = $call[2];

        if ($depth < $this->initialDepth) {
            $depth = $this->initialDepth;
        }

        if ($depth == $top[1]) {
            // Same depth: either a sibling item or a type switch.
            $this->emitCloseItem($pos);
            if ($type === $top[0]) {
                $this->emitOpenItem($depth, $pos);
            } else {
                $this->emitCloseList($pos);
                $this->emitOpenList($type, $start, $depth, $pos);
            }
        } elseif ($depth > $top[1]) {
            // Deeper: open a nested list, mark the parent item as a node.
            $this->listCalls[] = ['listcontent_close', [], $pos];
            $this->markCurrentItemAsNode();
            $this->emitOpenList($type, $start, $depth, $pos);
        } else {
            // Shallower: close the current item and list, unwind to the
            // first list whose depth is <= target, then open at that depth.
            $this->emitCloseItem($pos);
            $this->emitCloseList($pos);

            while (($top = end($this->listStack)) && $top[1] > $depth) {
                $this->listCalls[] = ['listitem_close', [], $pos];
                $this->emitCloseList($pos);
            }

            $depth = $top[1];
            $this->listCalls[] = ['listitem_close', [], $pos];
            if ($top[0] === $type) {
                $this->emitOpenItem($depth, $pos);
            } else {
                $this->emitCloseList($pos);
                $this->emitOpenList($type, $start, $depth, $pos);
            }
        }
    }

    /**
     * Pass through any non-list call (the inline / block content emitted
     * inside an item) into the buffered call stream untouched.
     *
     * @param array $call buffered call: [name, args, pos]
     */
    protected function listContent($call)
    {
        $this->listCalls[] = $call;
    }

    /**
     * Close all open items and lists in response to a list_close event.
     *
     * @param array $call buffered call: [name, args, pos]
     */
    protected function handleListClose($call)
    {
        $first = true;
        while (!empty($this->listStack)) {
            if ($first) {
                $this->listCalls[] = ['listcontent_close', [], $call[2]];
                $first = false;
            }
            $this->listCalls[] = ['listitem_close', [], $call[2]];
            $this->emitCloseList($call[2]);
        }
    }

    //endregion

    //region Emit helpers

    /**
     * Open a new list and its first item; push a new stack frame.
     *
     * Ordered lists with a non-default start number are emitted as the
     * sibling instruction listo_open_start to keep the listo_open signature
     * unchanged for plugin renderers that override it.
     *
     * @param string $type list type - 'u' (unordered) or 'o' (ordered)
     * @param int $start ordered-list start number; 1 means default
     * @param int $depth absolute nesting depth of the new list
     * @param int $pos byte position to attach to the emitted calls
     */
    protected function emitOpenList(string $type, int $start, int $depth, int $pos): void
    {
        if ($type === 'o' && $start !== 1) {
            $this->listCalls[] = ['listo_open_start', [$start], $pos];
        } else {
            $this->listCalls[] = ['list' . $type . '_open', [], $pos];
        }
        $this->listCalls[] = ['listitem_open', $this->levelArgs($depth), $pos];
        $this->listCalls[] = ['listcontent_open', [], $pos];
        $this->listStack[] = [$type, $depth, count($this->listCalls) - 2];
    }

    /**
     * Open a new sibling item in the current list; update the current
     * stack frame's listitem_open index so a later child can mark it as
     * a node.
     *
     * @param int $depth absolute nesting depth of the new item
     * @param int $pos byte position to attach to the emitted calls
     */
    protected function emitOpenItem(int $depth, int $pos): void
    {
        $this->listCalls[] = ['listitem_open', $this->levelArgs($depth), $pos];
        $this->listCalls[] = ['listcontent_open', [], $pos];
        $key = array_key_last($this->listStack);
        $this->listStack[$key][2] = count($this->listCalls) - 2;
    }

    /**
     * Mark the current top-of-stack item as a node (i.e. it has a child
     * list). Sets the second arg of its listitem_open call to NODE.
     *
     * Whether an item is a node or a leaf is information from the future:
     * we only learn it when the next list_item arrives at a deeper depth.
     * So listitem_open is emitted eagerly as a leaf, and this method
     * patches the already-buffered call when a child shows up. Each stack
     * frame caches the buffer index of its listitem_open ($listStack[$key][2])
     * so the patch is a single random-access write.
     */
    protected function markCurrentItemAsNode(): void
    {
        $key = array_key_last($this->listStack);
        $this->listCalls[$this->listStack[$key][2]][1][1] = self::NODE;
    }

    /**
     * Emit a complete item close: listcontent_close + listitem_close.
     *
     * @param int $pos byte position to attach to the emitted calls
     */
    protected function emitCloseItem(int $pos): void
    {
        $this->listCalls[] = ['listcontent_close', [], $pos];
        $this->listCalls[] = ['listitem_close', [], $pos];
    }

    /**
     * Emit a list close (list_X_close) for the current top of the stack
     * and pop the stack frame.
     *
     * @param int $pos byte position to attach to the emitted call
     */
    protected function emitCloseList(int $pos): void
    {
        $top = end($this->listStack);
        $this->listCalls[] = ['list' . $top[0] . '_close', [], $pos];
        array_pop($this->listStack);
    }

    //endregion

    //region Subclass hooks

    /**
     * Translate an absolute depth into a 1-based level number, normalised
     * for lists that start at non-1 depth (DokuWiki's 2-space-indent rule
     * gives initialDepth=2, GFM gives initialDepth=1).
     *
     * @param int $depth absolute nesting depth
     * @return array single-element argument array for listitem_open
     */
    protected function levelArgs(int $depth): array
    {
        return [max(1, $depth - $this->initialDepth + 1)];
    }

    /**
     * Parse a marker match into the values driving the state machine.
     *
     * Subclasses may omit `start`; it defaults to 1. Syntaxes whose ordered
     * lists do not carry a start number (DokuWiki) take the default; GFM
     * supplies the explicit value parsed from the marker.
     *
     * @param string $match the indent + marker string captured by the parser
     * @return array{depth: int, type: string, start?: int}
     *   depth is 1-based, type is 'u' (unordered) or 'o' (ordered),
     *   start is the ordered list's start number; default 1 (no attribute
     *   emitted) for unordered lists or ordered lists that begin at 1.
     */
    abstract protected function interpretSyntax(string $match): array;

    /**
     * Wrap interpretSyntax to apply the default for omitted keys.
     */
    private function parseMarker(string $match): array
    {
        return $this->interpretSyntax($match) + ['start' => 1];
    }

    //endregion
}
