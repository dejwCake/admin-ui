<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Http\Requests\Sanitizers;

use ArondeParon\RequestSanitizer\Contracts\Sanitizer;

class BooleanValue implements Sanitizer
{
    /**
     * @param string|bool|int|null $input
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function sanitize($input): bool
    {
        return $input === 'true' ||
            $input === 1 ||
            $input === '1' ||
            $input === true;
    }
}
