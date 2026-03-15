<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Http\Requests\Traits;

use Brackets\AdminUI\Http\Requests\Traits\SanitizeBoolean;
use Override;
use PHPUnit\Framework\TestCase;

final class SanitizeBooleanTest extends TestCase
{
    private object $instance;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new class {
            use SanitizeBoolean;

            public function callSanitizeBoolean(string|int|float|bool|null $value): bool
            {
                return $this->sanitizeBoolean($value);
            }
        };
    }

    public function testStringTrueReturnsTrue(): void
    {
        self::assertTrue($this->instance->callSanitizeBoolean('true'));
    }

    public function testIntegerOneReturnsTrue(): void
    {
        self::assertTrue($this->instance->callSanitizeBoolean(1));
    }

    public function testStringOneReturnsTrue(): void
    {
        self::assertTrue($this->instance->callSanitizeBoolean('1'));
    }

    public function testBooleanTrueReturnsTrue(): void
    {
        self::assertTrue($this->instance->callSanitizeBoolean(true));
    }

    public function testFalsyValuesReturnFalse(): void
    {
        self::assertFalse($this->instance->callSanitizeBoolean('false'));
        self::assertFalse($this->instance->callSanitizeBoolean(0));
        self::assertFalse($this->instance->callSanitizeBoolean(null));
        self::assertFalse($this->instance->callSanitizeBoolean(''));
    }
}
