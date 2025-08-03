<div align="center">

# PHP Test Kit Pack
[![Latest Version](https://img.shields.io/packagist/v/grano22/test-kit.svg?style=flat-square)](https://packagist.org/packages/grano22/test-kit)
[![Total Downloads](https://img.shields.io/packagist/dt/grano22/test-kit.svg?style=flat-square)](https://packagist.org/packages/grano22/test-kit)
[![License](https://img.shields.io/packagist/l/grano22/test-kit.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/grano22/test-kit.svg?style=flat-square)](composer.json)

Reduce testing boilerplate and make your PHP tests more elegant and maintainable 🚀

[Installation](#-installation) •
[Features](#-features) •
[Documentation](#-documentation) •
[Contributing](#-contributing)

</div>

## 💡 Motivation

Writing high-quality, readable tests can be time-consuming, especially without proper tooling.
This package provides elegant solutions to common testing challenges, helping you write better tests with less boilerplate.

## 🚀 Installation

Install via Composer:

```bash
composer require grano22/test-kit --dev
```

## 📚 Documentation

> 🚧 **Coming Soon!** I work on comprehensive documentation.

In the meantime, you can:
- Check the [examples](#-features) below
- Browse the [source code](https://github.com/grano22/test-kit) for implementation details
- [Open an issue](https://github.com/grano22/test-kit/issues) if you have questions

## ✨ Features

### 🎯 Operating on the array with an object navigation path

#### Remove Elements

Sometimes, in the test you need to strip something from an array (most likely dates that you don't control).
It frequently creates a lot of boilerplate code in the private methods. Now you can use:

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
```

<details>

<summary>📝 View Result</summary>
```php
[
  'items' => [
      [ 'title' => 'First' ],
      [ 'title' => 'Second' ]
  ]
]
```

</details>

#### Truncate Content

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
```

<details>

<summary>📝 View Result</summary>
```php
[
    'items' => [
        [ 'title' => 'First', 'description' => 'First description ...' ],
        [ 'title' => 'Second', 'description' => 'Second description...' ]
    ]
]
```

</details>

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
```

<details>

<summary>📝 View Result</summary>
```php
[
    'items' => [
        [ 'title' => 'First', 'description' => 'First test.' ],
        [ 'title' => 'Second', 'description' => 'Second test.' ]
    ]
]
```

</details>

### 🕵️ Test Doubles - CallSpy

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

### 🏗️ DDD Tactical Patterns

Create a universal, in-memory repository in your kit/testDriver, use it in the unit test

```php
class ExampleEntity
{
    public function __construct(public readonly string $id)
    {
    }
}

$repository = InMemoryRepository::createOfType(ExampleEntity::class);

$repository->add($entity);
$repository->add(new ExampleEntity('2'));

$foundEntity = $repository->findById('1');
```

## 🤝 Contributing

Contributions are welcome! Feel free to:

- 🐛 Report bugs
- 💡 Suggest features
- 🔧 Submit pull requests

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

<div align="center">
Made with ❤️ for the PHP testing community
</div>