<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Http\Requests\Traits;

/**
 * @method validated()
 */
trait Validated
{
    /** @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingTraversableTypeHintSpecification */
    protected ?array $validated = null;

    public function getValidated(): array
    {
        if ($this->validated === null) {
            $validated = $this->validated();
            $this->validated = $this->filterValidated($validated);
        }

        return $this->validated;
    }

    protected function filterValidated(array $validated): array
    {
        return $validated;
    }
}
