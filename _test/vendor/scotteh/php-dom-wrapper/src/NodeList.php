<?php declare(strict_types=1);

namespace DOMWrap;

use DOMWrap\Traits\{
    CommonTrait,
    TraversalTrait,
    ManipulationTrait
};
use DOMWrap\Collections\NodeCollection;

/**
 * Node List
 *
 * @package DOMWrap
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
class NodeList extends NodeCollection
{
    use CommonTrait;
    use TraversalTrait;
    use ManipulationTrait {
        ManipulationTrait::__call as __manipulationCall;
    }

    /** @var Document */
    protected $document;

    /**
     * @param Document $document
     * @param iterable $nodes
     */
    public function __construct(Document $document = null, iterable $nodes = null) {
        parent::__construct($nodes);

        $this->document = $document;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments) {
        try {
            $result = $this->__manipulationCall($name, $arguments);
        } catch (\BadMethodCallException $e) {
            if (!$this->first() || !method_exists($this->first(), $name)) {
                throw new \BadMethodCallException("Call to undefined method " . get_class($this) . '::' . $name . "()");
            }

            $result = call_user_func_array([$this->first(), $name], $arguments);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function collection(): NodeList {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function document(): ?\DOMDocument {
        return $this->document;
    }

    /**
     * {@inheritdoc}
     */
    public function result(NodeList $nodeList) {
        return $nodeList;
    }

    /**
     * @return NodeList
     */
    public function reverse(): NodeList {
        array_reverse($this->nodes);

        return $this;
    }

    /**
     * @return mixed
     */
    public function first() {
        return !empty($this->nodes) ? $this->rewind() : null;
    }

    /**
     * @return mixed
     */
    public function last() {
        return $this->end();
    }

    /**
     * @return mixed
     */
    public function end() {
        return !empty($this->nodes) ? end($this->nodes) : null;
    }

    /**
     * @param int $key
     *
     * @return mixed
     */
    public function get(int $key) {
        if (isset($this->nodes[$key])) {
            return $this->nodes[$key];
        }

        return null;
    }

    /**
     * @param int $key
     * @param mixed $value
     *
     * @return self
     */
    public function set(int $key, $value): self {
        $this->nodes[$key] = $value;

        return $this;
    }

    /**
     * @param callable $function
     *
     * @return self
     */
    public function each(callable $function): self {
        foreach ($this->nodes as $index => $node) {
            $result = $function($node, $index);

            if ($result === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * @param callable $function
     *
     * @return NodeList
     */
    public function map(callable $function): NodeList {
        $nodes = $this->newNodeList();

        foreach ($this->nodes as $node) {
            $result = $function($node);

            if (!is_null($result) && $result !== false) {
                $nodes[] = $result;
            }
        }

        return $nodes;
    }

    /**
     * @param callable $function
     * @param mixed|null $initial
     *
     * @return iterable
     */
    public function reduce(callable $function, $initial = null) {
        return array_reduce($this->nodes, $function, $initial);
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->nodes;
    }

    /**
     * @param iterable $nodes
     */
    public function fromArray(iterable $nodes = null) {
        $this->nodes = [];

        if (is_iterable($nodes)) {
            foreach ($nodes as $node) {
                $this->nodes[] = $node;
            }
        }
    }

    /**
     * @param NodeList|array $elements
     *
     * @return NodeList
     */
    public function merge($elements = []): NodeList {
        if (!is_array($elements)) {
            $elements = $elements->toArray();
        }

        return $this->newNodeList(array_merge($this->toArray(), $elements));
    }

    /**
     * @param int $start
     * @param int $end
     *
     * @return NodeList
     */
    public function slice(int $start, int $end = null): NodeList {
        $nodeList = array_slice($this->toArray(), $start, $end);

        return $this->newNodeList($nodeList);
    }

    /**
     * @param \DOMNode $node
     *
     * @return self
     */
    public function push(\DOMNode $node): self {
        $this->nodes[] = $node;

        return $this;
    }

    /**
     * @return \DOMNode
     */
    public function pop(): \DOMNode {
        return array_pop($this->nodes);
    }

    /**
     * @param \DOMNode $node
     *
     * @return self
     */
    public function unshift(\DOMNode $node): self {
        array_unshift($this->nodes, $node);

        return $this;
    }

    /**
     * @return \DOMNode
     */
    public function shift(): \DOMNode {
        return array_shift($this->nodes);
    }

    /**
     * @param \DOMNode $node
     *
     * @return bool
     */
    public function exists(\DOMNode $node): bool {
        return in_array($node, $this->nodes, true);
    }

    /**
     * @param \DOMNode $node
     *
     * @return self
     */
    public function delete(\DOMNode $node): self {
        $index = array_search($node, $this->nodes, true);

        if ($index !== false) {
            unset($this->nodes[$index]);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isRemoved(): bool {
        return false;
    }
}