<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Http\Requests\Sanitizers;

use ArondeParon\RequestSanitizer\Contracts\Sanitizer;

use function explode;
use function is_string;

class StringToArray implements Sanitizer
{
    /**
     * @param string|array|null $input
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     */
    public function sanitize($input)
    {
        if (is_string($input)) {
            return explode(',', $input);
        }

        return $input;
    }
}
