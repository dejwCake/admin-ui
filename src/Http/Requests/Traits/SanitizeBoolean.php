<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Http\Requests\Traits;

trait SanitizeBoolean
{
    protected function sanitizeBoolean(string|int|float|bool|null $value): bool
    {
        return $value === 'true' ||
            $value === 1 ||
            $value === '1' ||
            $value === true;
    }
}
