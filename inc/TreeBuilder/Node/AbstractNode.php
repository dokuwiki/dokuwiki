<?php

namespace dokuwiki\TreeBuilder\Node;

/**
 * A node represents one entry in the tree. It can have a parent and children.
 */
abstract class AbstractNode
{
    /** @var AbstractNode|null parent node */
    protected ?AbstractNode $parent = null;
    /** @var string unique ID for this node, usually the page id or external URL */
    protected string $id = '';
    /** @var string|null title of the node, may be null */
    protected ?string $title = null;

    /** @var AbstractNode[] */
    protected array $parents = [];
    /** @var AbstractNode[] */
    protected array $children = [];
    /** @var array */
    protected array $properties = [];

    /**
     * @param string $id The pageID or the external URL
     * @param string|null $title The title as given in the link
     */
    public function __construct(string $id, ?string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the namespace of this node
     *
     * @return string
     */
    public function getNs(): string
    {
        return getNS($this->id);
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get all nodes on the same level
     * @return AbstractNode[]
     */
    public function getSiblings(): array
    {
        return $this->getParent()->getChildren();
    }

    /**
     * Get all sub nodes, may return an empty array
     *
     * @return AbstractNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Does this node have children?
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    /**
     * Get all sub nodes and their sub nodes and so on
     *
     * @return AbstractNode[]
     */
    public function getDescendants(): array
    {
        $descendants = [];
        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getDescendants());
        }
        return $descendants;
    }

    /**
     * Get all parent nodes in reverse order
     *
     * This list is cached, so it will only be calculated once.
     *
     * @return AbstractNode[]
     */
    public function getParents(): array
    {
        if (!$this->parents) {
            $parent = $this->parent;
            while ($parent) {
                $this->parents[] = $parent;
                $parent = $parent->getParent();
            }
        }

        return $this->parents;
    }

    /**
     * Set the direct parent node
     */
    public function setParent(AbstractNode $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Get the direct parent node
     *
     * @return AbstractNode|null
     */
    public function getParent(): ?AbstractNode
    {
        return $this->parent;
    }

    /**
     * @param AbstractNode $child
     * @return void
     */
    public function addChild(AbstractNode $child): void
    {
        $child->setParent($this);
        $this->children[] = $child;
    }

    /**
     * Allows to attach an arbitrary property to the page
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setProperty(string $name, $value): void
    {
        $this->properties[$name] = $value;
    }

    /**
     * Get the named property, default is returned when the property is not set
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getProperty(string $name, $default = null)
    {
        return $this->properties[$name] ?? $default;
    }

    /**
     * Sort the children of this node and all its descendants
     *
     * The given comparator function will be called with two nodes as arguments and needs to
     * return an integer less than, equal to, or greater than zero if the first argument is considered
     * to be respectively less than, equal to, or greater than the second.
     *
     * @param callable $comparator
     * @return void
     */
    public function sort(callable $comparator): void
    {
        usort($this->children, $comparator);
        foreach ($this->children as $child) {
            $child->sort($comparator);
        }
    }

    /**
     * Get the string representation of the node
     *
     * Uses plus char to show the depth of the node in the tree
     *
     * @return string
     */
    public function __toString(): string
    {
        return str_pad('', count($this->getParents()), '+') . $this->id;
    }
}
