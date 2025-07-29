<?php

declare(strict_types=1);

namespace Grano22\TestKit\Tests\Unit\TestDoubles;

use Grano22\TestKit\TestDoubles\CallSpy;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CallSpyTest extends TestCase
{
    #[Test]
    public function callsAreCollectedAndAssertedCorrectly(): void
    {
        // Arrange
        $someClass = new class() extends Assert {
            use CallSpy;

            public function someMethod(): void
            {
                $this->trackEach();
            }
        };

        // Act & Assert
        try {
            $someClass->setMaxExpectedCalls(2);
            $someClass->someMethod();
            $someClass->someMethod();
            $someClass->someMethod();
        } catch (AssertionFailedError $error) {
            self::assertSame(
                "[Grano22\TestKit\TestDoubles\CallSpy] Number of calls exceeded (3 of 2 allowed) in method someMethod and location PHPUnit\Framework\Assert@anonymous\0/mnt/d/projects/test-kit/tests/unit/TestDoubles/CallSpyTest.php:19\$b6",
                $error->getMessage()
            );
        }
    }
}
