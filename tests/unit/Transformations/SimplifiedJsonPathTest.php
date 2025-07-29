<?php

declare(strict_types=1);

namespace Grano22\TestKit\Tests\Unit\Transformations;

use Grano22\TestKit\Transformations\SimplifiedJsonPath;
use Monolog\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class SimplifiedJsonPathTest extends TestCase
{
    public static function provideValidJsonPaths(): iterable
    {
        yield 'Just root indicator' => ['$'];
        yield 'Just separator' => ['.'];
        yield 'Starting with prop while omitting the root' => ['.someProp'];
        yield 'Just wildcard index' => ['[*]'];
        yield 'Just wildcard property' => ['.*'];
        yield 'Just numeric index' => ['[12]'];
        yield 'Just some property name' => ['.id'];

        yield 'Combination of any index and any id' => ['[*].id'];
        yield 'Mixed correctly' => ['$.sth[12]'];
        yield 'Quotes in the properties' => ['$."quotes are allowed * <3"'];
        yield 'Quotes on the second level' => ['$.first."staring at space"'];
        yield 'Quotes in the properties with escaped double quote' => ['$."quotes \" escaped"'];
        yield 'Quote escaped on the second level' => ['$.first."quoted \" middle"'];
        yield 'Complex expression that contains all' => ['$."\" escaped".name[*][12]."quotes * allowed".someText']; //"escapier\""
    }

    #[DataProvider('provideValidJsonPaths')]
    #[Test]
    public function jsonPathsAreValidatedCorrectly(string $validJsonPath): void
    {
        self::assertTrue(SimplifiedJsonPath::validate($validJsonPath));
    }

    public static function provideInvalidSimplifiedJsonPathsForValidation(): iterable
    {
        yield 'Doubled root indicator' => ['$$'];
        yield 'Empty' => [''];
        yield 'Just some text' => ['abc'];
        yield 'Separator between parentheses' => ['[.]'];
        yield 'Separator used twice' => ['..'];
        yield 'Just text index' => ['[awesome]'];
        yield 'Just text index surrounded with quotes' => ['["complex text here"]'];

        yield 'Separator with wildcard and text' => ['.*abc'];
        yield 'Separator with wildcard and text prefix' => ['.abc*'];
        yield 'Separator with wildcard and text in the middle' => ['.ab*cd'];

        yield 'Separator between parentheses with word' => ['[a.z]'];
        yield 'Empty parenthesis' => ['[]'];
        yield 'Parenthesis with quotes' => ['["abc"]'];
        yield 'Separator given twice' => ['.dfdd..'];
        yield 'Mixed wrongly' => ['$.[abc]'];
        yield 'Wildcard has something after it' => ['[*d]'];
        yield 'Wildcard has something before it' => ['[a*]'];
        yield 'Wildcard not closed' => ['[*'];
        yield 'Just an opened parenthesis' => ['$['];

        yield 'Mixed incorrectly' => ['$.sth[abc]'];
        yield 'Empty parenthesis in the middle' => ['$.someProp[ok][].else'];
        yield 'Ends with period' => ['.endsWithPeriod.'];
        yield 'Missing closing quote' => ['$["Oh no missed * it]'];
        yield 'Missing second closing quote' => ['$["Oh no missed * it]'];
        yield 'Path property without a leading period between array tokens' => ['[1]sthBetween["sth"]'];
        yield 'Only dot between brackets with wildcard left' => ['$.name[*].[sth]'];
        yield 'Only dot between brackets' => ['$.name[sth1].[sth]'];
        yield 'Only dot between with wildcard right' => ['$.name[sth1].[*]'];
        yield 'Spaced property is not allowed' => ['$.some name.some second name'];
        yield 'Spaced array key is not allowed' => ['$[some name][some second name]'];
        yield 'Doubled double quotes in the property' => ['$."sth""sth2"'];
        yield 'Doubled double quotes in the property, one escaped' => ['$."sth"\"sth2"'];
        yield 'Doubled double quotes in the array key' => ['$["sth""sth2"]'];
        yield 'Double quotes in the property combined with text' => ['$."sth"sth2'];
        yield 'Double quotes in the array key combined with text' => ['$["sth"sth2]'];
        yield 'Doubled double quotes in the array key, one escaped' => ['$["sth"\"sth2"].sth3'];
        yield 'Wildcard expression mixed with string inside property, not yet allowed' => ['$["sth"*"ish"]'];
        yield 'Wildcard expression mixed with string inside array key, not yet allowed' => ['$."sth"*"ish"'];
    }

    #[DataProvider('provideInvalidSimplifiedJsonPathsForValidation')]
    #[Test]
    public function jsonPathsAreValidatedCorrectlyForWrongCases(string $validJsonPath): void
    {
        self::assertFalse(SimplifiedJsonPath::validate($validJsonPath));
    }

    public static function provideValidSimplifiedJsonPathsToGenerateSegments(): iterable
    {
        yield 'Without quotes' => [
            'validJsonPath' => '$.first.second[3].fourth[*]',
            'expected' => [
                'first',
                'second',
                3,
                'fourth',
                '*',
            ]
        ];
        yield 'Most complex one' => [
            'validJsonPath' => '$.first."quoted \" middle"."quoted$ \""."\""."\" $sth*"[3].*.fourth[*]',
            'expected' => [
                'first',
                'quoted " middle',
                'quoted$ "',
                '"',
                '" $sth*',
                3,
                '*',
                'fourth',
                '*'
            ]
        ];
    }

    #[Test]
    #[DataProvider('provideValidSimplifiedJsonPathsToGenerateSegments')]
    public function segmentsAreGeneratedCorrectly(string $validJsonPath, array $expected): void
    {
        // Arrange
        $simplifiedJsonPath = SimplifiedJsonPath::fromString($validJsonPath);

        // Act
        $segments = $simplifiedJsonPath->toSegments();

        // Assert
        self::assertSame($expected, $segments);
    }
}
