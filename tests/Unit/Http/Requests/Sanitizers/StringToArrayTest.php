<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Http\Requests\Sanitizers;

use Brackets\AdminUI\Http\Requests\Sanitizers\StringToArray;
use Override;
use PHPUnit\Framework\TestCase;

final class StringToArrayTest extends TestCase
{
    private StringToArray $sanitizer;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitizer = new StringToArray();
    }

    public function testConvertsCommaSeparatedStringToArray(): void
    {
        self::assertSame(['a', 'b', 'c'], $this->sanitizer->sanitize('a,b,c'));
    }

    public function testSingleStringReturnsArrayWithOneElement(): void
    {
        self::assertSame(['a'], $this->sanitizer->sanitize('a'));
    }

    public function testArrayInputIsReturnedAsIs(): void
    {
        self::assertSame(['a', 'b'], $this->sanitizer->sanitize(['a', 'b']));
    }

    public function testNullInputIsReturnedAsIs(): void
    {
        self::assertNull($this->sanitizer->sanitize(null));
    }

    public function testEmptyStringReturnsArrayWithEmptyString(): void
    {
        self::assertSame([''], $this->sanitizer->sanitize(''));
    }
}
