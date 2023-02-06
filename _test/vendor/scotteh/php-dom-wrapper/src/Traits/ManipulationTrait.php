<?php declare(strict_types=1);

namespace DOMWrap\Traits;

use DOMWrap\{
    Text,
    Element,
    NodeList
};

/**
 * Manipulation Trait
 *
 * @package DOMWrap\Traits
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
trait ManipulationTrait
{
    /**
     * Magic method - Trap function names using reserved keyword (empty, clone, etc..)
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments) {
        if (!method_exists($this, '_' . $name)) {
            throw new \BadMethodCallException("Call to undefined method " . get_class($this) . '::' . $name . "()");
        }

        return call_user_func_array([$this, '_' . $name], $arguments);
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->getOuterHtml(true);
    }

    /**
     * @param string|NodeList|\DOMNode $input
     *
     * @return iterable
     */
    protected function inputPrepareAsTraversable($input): iterable {
        if ($input instanceof \DOMNode) {
            // Handle raw \DOMNode elements and 'convert' them into their DOMWrap/* counterpart
            if (!method_exists($input, 'inputPrepareAsTraversable')) {
                $input = $this->document()->importNode($input, true);
            }

            $nodes = [$input];
        } else if (is_string($input)) {
            $nodes = $this->nodesFromHtml($input);
        } else if (is_iterable($input)) {
            $nodes = $input;
        } else {
            throw new \InvalidArgumentException();
        }

        return $nodes;
    }

    /**
     * @param string|NodeList|\DOMNode $input
     * @param bool $cloneForManipulate
     *
     * @return NodeList
     */
    protected function inputAsNodeList($input, $cloneForManipulate = true): NodeList {
        $nodes = $this->inputPrepareAsTraversable($input);

        $newNodes = $this->newNodeList();

        foreach ($nodes as $node) {
            if ($node->document() !== $this->document()) {
                 $node = $this->document()->importNode($node, true);
            }

            if ($cloneForManipulate && $node->parentNode !== null) {
                $node = $node->cloneNode(true);
            }

            $newNodes[] = $node;
        }

        return $newNodes;
    }

    /**
     * @param string|NodeList|\DOMNode $input
     *
     * @return \DOMNode|null
     */
    protected function inputAsFirstNode($input): ?\DOMNode {
        $nodes = $this->inputAsNodeList($input);

        return $nodes->findXPath('self::*')->first();
    }

    /**
     * @param string $html
     *
     * @return NodeList
     */
    protected function nodesFromHtml($html): NodeList {
        $class = get_class($this->document());
        $doc = new $class();
        $doc->setEncoding($this->document()->getEncoding());
        $nodes = $doc->html($html)->find('body > *');

        return $nodes;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     * @param callable $callback
     *
     * @return self
     */
    protected function manipulateNodesWithInput($input, callable $callback): self {
        $this->collection()->each(function($node, $index) use ($input, $callback) {
            $html = $input;

            /*if ($input instanceof \DOMNode) {
                if ($input->parentNode !== null) {
                    $html = $input->cloneNode(true);
                }
            } else*/if (is_callable($input)) {
                $html = $input($node, $index);
            }

            $newNodes = $this->inputAsNodeList($html);

            $callback($node, $newNodes);
        });

        return $this;
    }

    /**
     * @param string|null $selector
     *
     * @return NodeList
     */
    public function detach(string $selector = null): NodeList {
        if (!is_null($selector)) {
            $nodes = $this->find($selector, 'self::');
        } else {
            $nodes = $this->collection();
        }

        $nodeList = $this->newNodeList();

        $nodes->each(function($node) use($nodeList) {
            if ($node->parent() instanceof \DOMNode) {
                $nodeList[] = $node->parent()->removeChild($node);
            }
        });

        $nodes->fromArray([]);

        return $nodeList;
    }

    /**
     * @param string|null $selector
     *
     * @return self
     */
    public function destroy(string $selector = null): self {
        $this->detach($selector);

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function substituteWith($input): self {
        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            foreach ($newNodes as $newNode) {
                $node->parent()->replaceChild($newNode, $node);
            }
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return string|self
     */
    public function text($input = null) {
        if (is_null($input)) {
            return $this->getText();
        } else {
            return $this->setText($input);
        }
    }

    /**
     * @return string
     */
    public function getText(): string {
        return (string)$this->collection()->reduce(function($carry, $node) {
            return $carry . $node->textContent;
        }, '');
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function setText($input): self {
        if (is_string($input)) {
            $input = new Text($input);
        }

        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            // Remove old contents from the current node.
            $node->contents()->destroy();

            // Add new contents in it's place.
            $node->appendWith(new Text($newNodes->getText()));
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function precede($input): self {
        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            foreach ($newNodes as $newNode) {
                $node->parent()->insertBefore($newNode, $node);
            }
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function follow($input): self {
        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            foreach ($newNodes as $newNode) {
                if (is_null($node->following())) {
                    $node->parent()->appendChild($newNode);
                } else {
                    $node->parent()->insertBefore($newNode, $node->following());
                }
            }
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function prependWith($input): self {
        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            foreach ($newNodes as $newNode) {
                $node->insertBefore($newNode, $node->contents()->first());
            }
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function appendWith($input): self {
        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            foreach ($newNodes as $newNode) {
                $node->appendChild($newNode);
            }
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode $selector
     *
     * @return self
     */
    public function prependTo($selector): self {
        if ($selector instanceof \DOMNode || $selector instanceof NodeList) {
            $nodes = $this->inputAsNodeList($selector);
        } else {
            $nodes = $this->document()->find($selector);
        }

        $nodes->prependWith($this);

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode $selector
     *
     * @return self
     */
    public function appendTo($selector): self {
        if ($selector instanceof \DOMNode || $selector instanceof NodeList) {
            $nodes = $this->inputAsNodeList($selector);
        } else {
            $nodes = $this->document()->find($selector);
        }

        $nodes->appendWith($this);

        return $this;
    }

    /**
     * @return self
     */
    public function _empty(): self {
        $this->collection()->each(function($node) {
            $node->contents()->destroy();
        });

        return $this;
    }

    /**
     * @return NodeList|\DOMNode
     */
    public function _clone() {
        $clonedNodes = $this->newNodeList();

        $this->collection()->each(function($node) use($clonedNodes) {
            $clonedNodes[] = $node->cloneNode(true);
        });

        return $this->result($clonedNodes);
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function removeAttr(string $name): self {
        $this->collection()->each(function($node) use($name) {
            if ($node instanceof \DOMElement) {
                $node->removeAttribute($name);
            }
        });

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAttr(string $name): bool {
        return (bool)$this->collection()->reduce(function($carry, $node) use ($name) {
            if ($node->hasAttribute($name)) {
                return true;
            }

            return $carry;
        }, false);
    }

    /**
     * @internal
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttr(string $name): string {
        $node = $this->collection()->first();

        if (!($node instanceof \DOMElement)) {
            return '';
        }

        return $node->getAttribute($name);
    }

    /**
     * @internal
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function setAttr(string $name, $value): self {
        $this->collection()->each(function($node) use($name, $value) {
            if ($node instanceof \DOMElement) {
                $node->setAttribute($name, (string)$value);
            }
        });

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return self|string
     */
    public function attr(string $name, $value = null) {
        if (is_null($value)) {
            return $this->getAttr($name);
        } else {
            return $this->setAttr($name, $value);
        }
    }

    /**
     * @internal
     *
     * @param string $name
     * @param string|callable $value
     * @param bool $addValue
     */
    protected function _pushAttrValue(string $name, $value, bool $addValue = false): void {
        $this->collection()->each(function($node, $index) use($name, $value, $addValue) {
            if ($node instanceof \DOMElement) {
                $attr = $node->getAttribute($name);

                if (is_callable($value)) {
                    $value = $value($node, $index, $attr);
                }

                // Remove any existing instances of the value, or empty values.
                $values = array_filter(explode(' ', $attr), function($_value) use($value) {
                    if (strcasecmp($_value, $value) == 0 || empty($_value)) {
                        return false;
                    }

                    return true;
                });

                // If required add attr value to array
                if ($addValue) {
                    $values[] = $value;
                }

                // Set the attr if we either have values, or the attr already
                //  existed (we might be removing classes).
                //
                // Don't set the attr if it doesn't already exist.
                if (!empty($values) || $node->hasAttribute($name)) {
                    $node->setAttribute($name, implode(' ', $values));
                }
            }
        });
    }

    /**
     * @param string|callable $class
     *
     * @return self
     */
    public function addClass($class): self {
        $this->_pushAttrValue('class', $class, true);

        return $this;
    }

    /**
     * @param string|callable $class
     *
     * @return self
     */
    public function removeClass($class): self {
        $this->_pushAttrValue('class', $class);

        return $this;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasClass(string $class): bool {
        return (bool)$this->collection()->reduce(function($carry, $node) use ($class) {
            $attr = $node->getAttr('class');

            return array_reduce(explode(' ', (string)$attr), function($carry, $item) use ($class) {
                if (strcasecmp($item, $class) == 0) {
                    return true;
                }

                return $carry;
            }, false);
        }, false);
    }

    /**
     * @param Element $node
     *
     * @return \SplStack
     */
    protected function _getFirstChildWrapStack(Element $node): \SplStack {
        $stack = new \SplStack;

        do {
            // Push our current node onto the stack
            $stack->push($node);

            // Get the first element child node
            $node = $node->children()->first();
        } while ($node instanceof Element);

        // Get the top most node.
        return $stack;
    }

    /**
     * @param Element $node
     *
     * @return \SplStack
     */
    protected function _prepareWrapStack(Element $node): \SplStack {
        // Generate a stack (root to leaf) of the wrapper.
        // Includes only first element nodes / first element children.
        $stackNodes = $this->_getFirstChildWrapStack($node);

        // Only using the first element, remove any siblings.
        foreach ($stackNodes as $stackNode) {
            $stackNode->siblings()->destroy();
        }

        return $stackNodes;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     * @param callable $callback
     */
    protected function wrapWithInputByCallback($input, callable $callback): void {
        $this->collection()->each(function($node, $index) use ($input, $callback) {
            $html = $input;

            if (is_callable($input)) {
                $html = $input($node, $index);
            }

            $inputNode = $this->inputAsFirstNode($html);

            if ($inputNode instanceof Element) {
                // Pre-process wrapper into a stack of first element nodes.
                $stackNodes = $this->_prepareWrapStack($inputNode);

                $callback($node, $stackNodes);
            }
        });
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function wrapInner($input): self {
        $this->wrapWithInputByCallback($input, function($node, $stackNodes) {
            foreach ($node->contents() as $child) {
                // Remove child from the current node
                $oldChild = $child->detach()->first();

                // Add it back as a child of the top (leaf) node on the stack
                $stackNodes->top()->appendWith($oldChild);
            }

            // Add the bottom (root) node on the stack
            $node->appendWith($stackNodes->bottom());
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function wrap($input): self {
        $this->wrapWithInputByCallback($input, function($node, $stackNodes) {
            // Add the new bottom (root) node after the current node
            $node->follow($stackNodes->bottom());

            // Remove the current node
            $oldNode = $node->detach()->first();

            // Add the 'current node' back inside the new top (leaf) node.
            $stackNodes->top()->appendWith($oldNode);
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function wrapAll($input): self {
        if (!$this->collection()->count()) {
            return $this;
        }

        if (is_callable($input)) {
            $input = $input($this->collection()->first());
        }

        $inputNode = $this->inputAsFirstNode($input);

        if (!($inputNode instanceof Element)) {
            return $this;
        }

        $stackNodes = $this->_prepareWrapStack($inputNode);

        // Add the new bottom (root) node before the first matched node
        $this->collection()->first()->precede($stackNodes->bottom());

        $this->collection()->each(function($node) use ($stackNodes) {
            // Detach and add node back inside the new wrappers top (leaf) node.
            $stackNodes->top()->appendWith($node->detach());
        });

        return $this;
    }

    /**
     * @return self
     */
    public function unwrap(): self {
        $this->collection()->each(function($node) {
            $parent = $node->parent();

            // Replace parent node (the one we're unwrapping) with it's children.
            $parent->contents()->each(function($childNode) use($parent) {
                $oldChildNode = $childNode->detach()->first();

                $parent->precede($oldChildNode);
            });

            $parent->destroy();
        });

        return $this;
    }

    /**
     * @param int $isIncludeAll
     *
     * @return string
     */
    public function getOuterHtml(bool $isIncludeAll = false): string {
        $nodes = $this->collection();

        if (!$isIncludeAll) {
            $nodes = $this->newNodeList([$nodes->first()]);
        }

        return $nodes->reduce(function($carry, $node) {
            return $carry . $this->document()->saveHTML($node);
        }, '');
    }

    /**
     * @param int $isIncludeAll
     *
     * @return string
     */
    public function getHtml(bool $isIncludeAll = false): string {
        $nodes = $this->collection();

        if (!$isIncludeAll) {
            $nodes = $this->newNodeList([$nodes->first()]);
        }

        return $nodes->contents()->reduce(function($carry, $node) {
            return $carry . $this->document()->saveHTML($node);
        }, '');
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return self
     */
    public function setHtml($input): self {
        $this->manipulateNodesWithInput($input, function($node, $newNodes) {
            // Remove old contents from the current node.
            $node->contents()->destroy();

            // Add new contents in it's place.
            $node->appendWith($newNodes);
        });

        return $this;
    }

    /**
     * @param string|NodeList|\DOMNode|callable $input
     *
     * @return string|self
     */
    public function html($input = null) {
        if (is_null($input)) {
            return $this->getHtml();
        } else {
            return $this->setHtml($input);
        }
    }

    /**
     * @param string|NodeList|\DOMNode $input
     *
     * @return NodeList
     */
    public function create($input): NodeList {
        return $this->inputAsNodeList($input);
    }
}