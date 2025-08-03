<?php

declare(strict_types=1);

namespace Grano22\TestKit\DesignPatterns;

use BadMethodCallException;
use Stringable;

/**
 * @template EntityType of object
 */
class InMemoryRepository
{
    /** @var class-string<EntityType>|null $type */

    /** @var array<string|int, EntityType> $indexedEntities */
    protected array $indexedEntities = [];

    /**
     * @template EntityTypeInput of object
     * @param class-string<EntityTypeInput> $type
     *
     * @return self<EntityTypeInput>
     */
    public static function createOfType(string $type): self
    {
        /** @var InMemoryRepository<EntityTypeInput> */
        return new self($type);
    }

    /**
     * @phpstan-param EntityType $entity
     * @psalm-param EntityType $entity
     */
    public function add(object $entity, int|string|Stringable|null $entityId = null): void
    {
        if ($this->type && !($entity instanceof $this->type)) {
            throw new BadMethodCallException(
                sprintf(
                    'Cannot add an entity of type %s to a repository of type %s',
                    $entity::class,
                    $this->type
                )
            );
        }

        if ($entityId === null && method_exists($entity, 'getId')) {
            $entityId = $entity->getId();
        }

        if ($entityId === null && property_exists($entity, 'id')) {
            $entityId = $entity->id;
        }

        if ($entityId === null) {
            throw new BadMethodCallException('Cannot determine the entity identifier. Please provide one explicitly or implement the getId() method on the entity or the id property on the entity.');
        }

        if ($entityId instanceof Stringable) {
            $entityId = $entityId->__toString();
        }

        $this->indexedEntities[$entityId] = $entity;
    }

    public function __construct(private readonly ?string $type = null)
    {
    }

    /**
     * @phpstan-return EntityType|null
     * @psalm-return EntityType|null
     */
    public function findById(int|string|Stringable $id): ?object
    {
        if ($id instanceof Stringable) {
            $id = $id->__toString();
        }

        return $this->indexedEntities[$id] ?? null;
    }

    /**
     * @param callable(EntityType): bool $predicate
     *
     * @return EntityType|null
     */
    public function findBy(callable $predicate)
    {
        return array_find($this->indexedEntities, $predicate);
    }

    /** @return EntityType[] */
    public function findAll(): array
    {
        return $this->indexedEntities;
    }

    public function clear(): void
    {
        $this->indexedEntities = [];
    }
}
