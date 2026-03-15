<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Http\Requests\Sanitizers;

use Brackets\AdminUI\Http\Requests\Sanitizers\BooleanValue;
use Override;
use PHPUnit\Framework\TestCase;

final class BooleanValueTest extends TestCase
{
    private BooleanValue $sanitizer;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitizer = new BooleanValue();
    }

    public function testStringTrueReturnsTrue(): void
    {
        self::assertTrue($this->sanitizer->sanitize('true'));
    }

    public function testIntegerOneReturnsTrue(): void
    {
        self::assertTrue($this->sanitizer->sanitize(1));
    }

    public function testStringOneReturnsTrue(): void
    {
        self::assertTrue($this->sanitizer->sanitize('1'));
    }

    public function testBooleanTrueReturnsTrue(): void
    {
        self::assertTrue($this->sanitizer->sanitize(true));
    }

    public function testStringFalseReturnsFalse(): void
    {
        self::assertFalse($this->sanitizer->sanitize('false'));
    }

    public function testIntegerZeroReturnsFalse(): void
    {
        self::assertFalse($this->sanitizer->sanitize(0));
    }

    public function testNullReturnsFalse(): void
    {
        self::assertFalse($this->sanitizer->sanitize(null));
    }

    public function testEmptyStringReturnsFalse(): void
    {
        self::assertFalse($this->sanitizer->sanitize(''));
    }

    public function testArbitraryStringReturnsFalse(): void
    {
        self::assertFalse($this->sanitizer->sanitize('yes'));
    }
}
