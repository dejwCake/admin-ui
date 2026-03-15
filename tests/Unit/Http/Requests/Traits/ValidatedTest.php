<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Http\Requests\Traits;

use PHPUnit\Framework\TestCase;

final class ValidatedTest extends TestCase
{
    public function testGetValidatedCallsValidatedOnce(): void
    {
        $instance = new CountingValidatable(['name' => 'test']);

        $instance->getValidated();
        $instance->getValidated();

        self::assertSame(1, $instance->getCallCount());
    }

    public function testGetValidatedReturnsCachedResultOnSecondCall(): void
    {
        $instance = new CountingValidatable(['name' => 'test']);

        $first = $instance->getValidated();
        $second = $instance->getValidated();

        self::assertSame($first, $second);
    }

    public function testGetValidatedReturnsValidatedData(): void
    {
        $instance = new CountingValidatable(
            ['name' => 'test', 'email' => 'test@example.com'],
        );

        self::assertSame(
            ['name' => 'test', 'email' => 'test@example.com'],
            $instance->getValidated(),
        );
    }

    public function testFilterValidatedIsApplied(): void
    {
        $instance = new FilteringValidatable();

        self::assertSame(['name' => 'test'], $instance->getValidated());
    }
}
