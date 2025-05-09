<?php

namespace Unleash\Client\DTO;

use Override;

final class DefaultVariantOverride implements VariantOverride
{
    /**
     * @param array<string> $values
     */
    public function __construct(
        private readonly string $field,
        private readonly array $values,
    ) {
    }

    #[Override]
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return array<string>
     */
    #[Override]
    public function getValues(): array
    {
        return $this->values;
    }
}
