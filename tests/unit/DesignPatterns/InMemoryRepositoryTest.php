<?php

declare(strict_types=1);

namespace Grano22\TestKit\Tests\Unit\DesignPatterns;

use Grano22\TestKit\DesignPatterns\InMemoryRepository;
use Grano22\TestKit\Tests\Unit\DesignPatterns\data\ExampleEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InMemoryRepositoryTest extends TestCase
{
    #[Test]
    public function repositoryOperationsWorks(): void
    {
        // Arrange
        $entity = new ExampleEntity('1');
        $repository = InMemoryRepository::createOfType(ExampleEntity::class);

        // Act
        $repository->add($entity);
        $repository->add(new ExampleEntity('2'));

        // Assert
        self::assertSame($entity, $repository->findById('1'));
    }
}
