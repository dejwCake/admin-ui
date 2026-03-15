<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Http\Requests\Traits;

use Brackets\AdminUI\Http\Requests\Traits\Validated;

class CountingValidatable
{
    use Validated;

    private int $callCount = 0;

    /**
     * @param array<string, string> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }

    /**
     * @return array<string, string>
     */
    public function validated(): array
    {
        $this->callCount++;

        return $this->data;
    }
}
