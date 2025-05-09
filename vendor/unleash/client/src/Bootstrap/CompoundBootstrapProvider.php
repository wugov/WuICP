<?php

namespace Unleash\Client\Bootstrap;

use Exception;
use JsonSerializable;
use Override;
use Traversable;
use Unleash\Client\Exception\CompoundException;

final class CompoundBootstrapProvider implements BootstrapProvider
{
    /**
     * @var BootstrapProvider[]
     */
    private readonly array $bootstrapProviders;

    public function __construct(
        BootstrapProvider ...$bootstrapProviders
    ) {
        $this->bootstrapProviders = $bootstrapProviders;
    }

    /**
     * @return array<mixed>|JsonSerializable|Traversable<mixed>|null
     */
    #[Override]
    public function getBootstrap(): array|JsonSerializable|Traversable|null
    {
        $exceptions = [];

        foreach ($this->bootstrapProviders as $bootstrapProvider) {
            try {
                $result = $bootstrapProvider->getBootstrap();
            } catch (Exception $e) {
                $exceptions[] = $e;
                $result = null;
            }
            if ($result === null) {
                continue;
            }

            return $result;
        }

        if (count($exceptions)) {
            $this->throwExceptions($exceptions);
        }

        return null;
    }

    /**
     * @param array<Exception> $exceptions
     *
     * @throws CompoundException
     */
    private function throwExceptions(array $exceptions): never
    {
        assert(count($exceptions) > 0);
        throw new CompoundException(...$exceptions);
    }
}
