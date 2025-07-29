# PHP Test Kit Pack

**Motivation—** this package was introduced to reduce testing boilerplate and make it easier.
Writing good quality test, readable ones, it's time-consuming process, especially when you don't have
handy test tools.

## Capabilities

### Operating on the array with an object navigation path

Sometimes, in the test you need to strip something from an array (most likely dates that you don't control).
It creates frequently a lot of boilerplate code in the private methods. Now you can use:

```php
$structure = [
    'items' => [
        [ 'title' => 'First', 'description' => 'First description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ],
        [ 'title' => 'Second', 'description' => 'Second description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ]
    ]
];

ReferenceNode::traverseByNodePath(
    static fn(ReferenceNode $node) => $node->remove(),
    $structure,
    '.items[*].description'
);

//  It will produce on the original array:
//  [
//      'items' => [
//          [ 'title' => 'First' ],
//          [ 'title' => 'Second' ]
//      ]
//  ]
```

Too long meaningful description? Don't worry, you can truncate it for assertion.

```php
$structure = [
    'items' => [
        [ 'title' => 'First', 'description' => 'First description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ],
        [ 'title' => 'Second', 'description' => 'Second description lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut venenatis arcu.' ]
    ]
];

$mappedArray = ReferenceNode::mapByNodePath(
    static fn(ReferenceNode $node) => $node->trunc(18),
    $structure,
    '.items[*].description'
);

//  It will produce:
//  [
//      'items' => [
//          [ 'title' => 'First', 'description' => 'First description ...' ],
//          [ 'title' => 'Second', 'description' => 'Second description...' ]
//      ]
//  ]
```

You can also replace some content.

```php
$structure = [
    'items' => [
        [ 'title' => 'First', 'description' => 'First description.' ],
        [ 'title' => 'Second', 'description' => 'Second description.' ]
    ]
];

$mappedArray = ReferenceNode::mapByNodePath(
    static fn(ReferenceNode $node) => $node->modify(str_replace("description", "test", $node->getValue())),
    $structure,
    '.items[*].description'
);

//  It will produce:
//  [
//      'items' => [
//          [ 'title' => 'First', 'description' => 'First test.' ],
//          [ 'title' => 'Second', 'description' => 'Second test.' ]
//      ]
//  ]
```

### Test Doubles - CallSpy

Call spy is just a single test double (spy) to track your calls without using phpunit mocks.

```php
use Grano22\TestKit\TestDoubles\CallSpy;
use PHPUnit\Framework\Assert;

$someClass = new class() extends Assert {
    use CallSpy;

    public function someMethod(): void
    {
        $this->trackEach();
    }
};

$someClass->setMaxExpectedCalls(2);
$someClass->someMethod();
$someClass->someMethod();
$someClass->someMethod();

// Will throw **AssertionFailedError**, because of Assert::fail
```