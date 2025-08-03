<?php

declare(strict_types=1);

namespace Grano22\TestKit\Tests\Unit\DesignPatterns\data;

class ExampleEntity
{
    public function __construct(public readonly string $id)
    {
    }
}
