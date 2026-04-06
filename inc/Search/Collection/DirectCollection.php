<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\IndexAccessException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Exception\IndexWriteException;

/**
 * Abstract collection for direct 1:1 entity-token mappings
 *
 * In a direct collection each entity has exactly one token stored at the entity's position
 * in the token index (entity.RID === token.RID). No frequency or reverse indexes are used.
 *
 * Example: each page has exactly one title.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
abstract class DirectCollection extends AbstractCollection
{
    /**
     * Store a single token for the given entity
     *
     * Takes the first token from the list and writes it directly at the entity's position
     * in the token index. An empty list stores an empty string.
     *
     * @param string $entity The name of the entity
     * @param string[] $tokens The list of tokens (only the first is used)
     * @return static
     * @throws IndexLockException
     * @throws IndexAccessException
     * @throws IndexWriteException
     */
    public function addEntity(string $entity, array $tokens): static
    {
        if (!$this->isWritable) {
            throw new IndexLockException('Indexes not locked. Forgot to call lock()?');
        }

        $entityIndex = $this->getEntityIndex();
        $entityId = $entityIndex->accessCachedValue($entity);

        $token = $tokens[0] ?? '';
        $tokenIndex = $this->getTokenIndex('');
        $tokenIndex->changeRow($entityId, $token);
        $tokenIndex->save();

        return $this;
    }

    /**
     * Get the token stored for the given entity
     *
     * @param string $entity The name of the entity
     * @return string The stored token, or empty string if none
     * @throws IndexAccessException
     * @throws IndexLockException
     * @throws IndexWriteException
     */
    public function getToken(string $entity): string
    {
        $entityIndex = $this->getEntityIndex();
        $entityId = $entityIndex->accessCachedValue($entity);

        $tokenIndex = $this->getTokenIndex('');
        return $tokenIndex->retrieveRow($entityId);
    }

    /**
     * Not actually used, because we override addEntity() to directly write the token.
     * @inheritdoc
     */
    protected function countTokens(array $tokens): array
    {
        $token = $tokens[0] ?? '';
        return [$token => 1];
    }
}
