<?php declare(strict_types=1);

namespace DOMWrap\Traits;

use DOMWrap\NodeList;

/**
 * Common Trait
 *
 * @package DOMWrap\Traits
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
trait CommonTrait
{
    /**
     * @return NodeList
     */
    abstract public function collection(): NodeList;

    /**
     * @return \DOMDocument
     */
    abstract public function document(): ?\DOMDocument;

    /**
     * @param NodeList $nodeList
     *
     * @return NodeList|\DOMNode
     */
    abstract public function result(NodeList $nodeList);

    /**
     * @return bool
     */
    public function isRemoved(): bool {
        return !isset($this->nodeType);
    }
}