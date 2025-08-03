<?php

declare(strict_types=1);

namespace Grano22\TestKit\Tests\Unit\Transformations;

use Grano22\TestKit\Transformations\ReferenceNode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReferenceNodeTest extends TestCase
{
    #[Test]
    public function arrayNodesAreTraversedAndDeletedCorrectly(): void
    {
        // Arrange
        $structure = [
            [
                'title' => 'First',
                'children' => [
                    [
                        'title' => 'First.1',
                        'children' => [
                            [
                                'title' => 'First.1.1',
                                'children' => []
                            ],
                            [
                                'title' => 'First.1.2',
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'title' => 'First.2',
                        'children' => []
                    ]
                ]
            ]
        ];

        // Act
        ReferenceNode::traverseByNodePath(
            static fn (ReferenceNode $node) => $node->remove(),
            $structure,
            '[*].children[*].children[*].title'
        );

        // Assert
        self::assertSame(
            [
                [
                    'title' => 'First',
                    'children' => [
                        [
                            'title' => 'First.1',
                            'children' => [
                                [
                                    'children' => []
                                ],
                                [
                                    'children' => []
                                ]
                            ]
                        ],
                        [
                            'title' => 'First.2',
                            'children' => []
                        ]
                    ]
                ]
            ],
            $structure
        );
    }

    #[Test]
    public function arrayNodesAreMappedAndTruncatedCorrectly(): void
    {
        // Arrange
        $structure = [
            'items' => [
                [ 'title' => 'First', 'description' => 'First description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ],
                [ 'title' => 'Second', 'description' => 'Second description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ]
            ]
        ];

        // Act
        $mappedArray = ReferenceNode::mapByNodePath(
            static fn (ReferenceNode $node) => $node->trunc(18),
            $structure,
            '.items[*].description'
        );

        // Assert
        self::assertSame(
            [
                'items' => [
                    [ 'title' => 'First', 'description' => 'First description ...' ],
                    [ 'title' => 'Second', 'description' => 'Second description...' ]
                ]
            ],
            $mappedArray
        );
    }

    #[Test]
    public function arrayNodesAreMappedAndReplacedCorrectly(): void
    {
        // Arrange
        $structure = [
            'items' => [
                [ 'title' => 'First', 'description' => 'First description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ],
                [ 'title' => 'Second', 'description' => 'Second description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ]
            ]
        ];

        // Act
        $mappedArray = ReferenceNode::mapByNodePath(
            static fn (ReferenceNode $node) => $node->modify(
                str_replace(
                    'lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu',
                    'abc',
                    $node->getValue()
                )
            ),
            $structure,
            '.items[*].description'
        );

        // Assert
        self::assertSame(
            [
                'items' => [
                    [ 'title' => 'First', 'description' => 'First description abc.' ],
                    [ 'title' => 'Second', 'description' => 'Second description abc.' ]
                ]
            ],
            $mappedArray
        );
    }
}
