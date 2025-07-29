<?php

declare(strict_types=1);

namespace Grano22\TestKit\TestDoubles;

use LogicException;
use PHPUnit\Framework\Assert;

// TODO: Move in the PHP 8.2 to the trait
const LOG_TAGS = [CallSpy::class];

/** @mixin Assert */
trait CallSpy
{
    private int $trackedCalls = 0;
    private int $howManyCallsExpected;

    public function __construct()
    {
        $this->ensureAssertIsAvailable();
    }

    protected function trackEach(): void
    {
        $this->trackedCalls++;

        if (isset($this->howManyCallsExpected) && $this->howManyCallsExpected < $this->trackedCalls) {
            Assert::fail(
                sprintf(
                    '[' . implode('][', LOG_TAGS) . '] ' .
                    'Number of calls exceeded (%d of %d allowed) in method %s and location %s',
                    $this->trackedCalls,
                    $this->howManyCallsExpected,
                    debug_backtrace()[1]['function'],
                    debug_backtrace()[1]['class']
                )
            );
        }
    }

    public function setMaxExpectedCalls(int $howMany): self
    {
        $this->howManyCallsExpected = $howMany;

        return $this;
    }

    protected function getTrackedCalls(): int
    {
        return $this->trackedCalls;
    }

    private function ensureAssertIsAvailable(): void
    {
        if (!$this instanceof Assert) {
            throw new LogicException('Class ' . self::class . ' must extend Assert class');
        }
    }
}
