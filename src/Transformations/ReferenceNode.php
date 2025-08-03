<?php

declare(strict_types=1);

namespace Grano22\TestKit\Transformations;

use Generator;
use LogicException;

/**
 * @template T
 * @phpstan-type RefAnyArray array<int|string, mixed>
 */
final class ReferenceNode
{
    /** @var T $value */
    private mixed $value;

    /** @var array<non-empty-string|int> $path */
    private array $path;

    /** @var RefAnyArray */
    private array $connectedOwner;

    /** @return T */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Maps nested data structures using JSON-like path syntax, and for each matched path yields a ReferenceNode
     *
     * @param callable(ReferenceNode<T> $node): void $walker
     * @param array<int|string, mixed> $structuredData
     * @param string|SimplifiedJsonPath $objPath Object navigation path (e.g.: '[*].id'), use string to auto determine the most performant pathLike
     *
     * @return array<int|string, mixed>
     */
    public static function mapByNodePath(callable $walker, array $structuredData, string|SimplifiedJsonPath $objPath): array
    {
        self::traverseByNodePath($walker, $structuredData, $objPath);

        return $structuredData;
    }

    /**
     * Traverses nested data structures using JSON-like path syntax, and for each matched path yields a ReferenceNode
     *
     * @param callable(ReferenceNode<T> $node): void $walker
     * @param array<int|string, mixed> $structuredData
     * @param string|SimplifiedJsonPath $objPath Object navigation path (e.g.: '[*].id'), use string to auto determine the most performant pathLike
     */
    public static function traverseByNodePath(callable $walker, array &$structuredData, string|SimplifiedJsonPath $objPath): void
    {
        foreach (self::yieldByNodePath($structuredData, $objPath) as $node) {
            $walker($node);
        }
    }

    /**
     * @param array<int|string, mixed> $structuredData
     * @param string|SimplifiedJsonPath $objPath Object navigation path (e.g.: '[*].id'), use string to auto determine the most performant pathLike
     *
     * @return Generator<ReferenceNode<T>>
     */
    public static function yieldByNodePath(array &$structuredData, string|SimplifiedJsonPath $objPath): Generator
    {
        if (is_string($objPath)) {
            $objPath = SimplifiedJsonPath::fromString($objPath);
        }

        // TODO: Add support for more libraries such as JSONPath (RFC 9535), JSON Pointer (RFC-6901)

        $segments = $objPath->toSegments();

        yield from self::processSegment(
            $structuredData,
            $segments,
            $structuredData
        );
    }

    /**
     * @param array<non-empty-string|int> $path
     * @param array<int|string, mixed> $owner
     */
    public function __construct(mixed $value, array $path, array &$owner)
    {
        $this->value = $value;
        $this->path = $path;
        $this->connectedOwner = & $owner;
    }

    public function remove(): void
    {
        $locatedPath = $this->traverseByPath();

        unset($locatedPath['parentRef'][$locatedPath['lastSegment']]);
    }

    public function modify(mixed $newValue): void
    {
        $located = $this->traverseByPath();

        $located['parentRef'][$located['lastSegment']] = $newValue;
    }

    public function trunc(int $afterWordsCount, string $posFix = '...'): void
    {
        $located = $this->traverseByPath();

        $located['parentRef'][$located['lastSegment']] = mb_substr(
            $located['parentRef'][$located['lastSegment']],
            0,
            $afterWordsCount,
            'UTF-8'
        ) . $posFix;
    }

    /**
     * @param array<int|string, mixed> $currentRef
     * @param array<non-empty-string|int> $segments
     * @param array<int|string, mixed> $rootRef
     * @param array<int|non-empty-string> $traversedPath
     *
     * @return Generator<ReferenceNode<T>>
     */
    private static function processSegment(mixed &$currentRef, array $segments, array &$rootRef, array $traversedPath = []): Generator
    {
        if (empty($segments)) {
            yield new ReferenceNode($currentRef, $traversedPath, $rootRef);

            return;
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            /** @var non-empty-string $key */
            foreach ($currentRef as $key => $item) {
                yield from self::processSegment($item, $segments, $rootRef, [ ...$traversedPath, $key ]);
            }

            return;
        }

        if (!array_key_exists($segment, $currentRef)) {
            throw new LogicException(
                sprintf(
                    'Path unreachable, remaining segments: %s, current ref: %s, key of current level: %s',
                    implode(',', $segments),
                    var_export($currentRef, true),
                    var_export($segment, true)
                )
            );
        }

        $nextData = $currentRef[$segment];
        $traversedPath[] = $segment;

        yield from self::processSegment($nextData, $segments, $rootRef, $traversedPath);
    }

    /**
     * @return array{parentRef: array<int|string, mixed>, lastSegment: int|string}
     */
    private function traverseByPath(): array
    {
        $nodeRef = & $this->connectedOwner;

        $i = 0;
        for (; $i < count($this->path) - 1; $i++) {
            $segment = $this->path[$i];

            $nodeRef = & $nodeRef[$segment];
        }
        $lastSegment = $this->path[$i];

        return [ 'parentRef' => &$nodeRef, 'lastSegment' => $lastSegment ];
    }
}
