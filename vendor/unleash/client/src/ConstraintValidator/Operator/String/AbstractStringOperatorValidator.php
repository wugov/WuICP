<?php

namespace Unleash\Client\ConstraintValidator\Operator\String;

use Override;
use Unleash\Client\ConstraintValidator\Operator\AbstractOperatorValidator;

/**
 * @internal
 */
abstract class AbstractStringOperatorValidator extends AbstractOperatorValidator
{
    #[Override]
    protected function acceptsValues(array|string $values): bool
    {
        return is_string($values);
    }
}
