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

    abstract protected function filterValidated(array $validated): array;

    public function getValidated(): array
    {
        if ($this->validated === null) {
            $validated = $this->validated();
            $this->validated = $this->filterValidated($validated);
        }

        return $this->validated;
    }
}
