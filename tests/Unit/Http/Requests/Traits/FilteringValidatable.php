<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Tests\Unit\Http\Requests\Traits;

use Brackets\AdminUI\Http\Requests\Traits\Validated;

class FilteringValidatable
{
    use Validated;

    /**
     * @return array<string, string>
     */
    public function validated(): array
    {
        return ['name' => 'test', 'secret' => 'hidden'];
    }

    /**
     * @param array<string, string> $validated
     * @return array<string, string>
     */
    protected function filterValidated(array $validated): array
    {
        unset($validated['secret']);

        return $validated;
    }
}
