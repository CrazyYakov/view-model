<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Contracts;

interface ViewInterface
{
    /**
     * Convert a resource object to its array representation.
     *
     * @param  object  $model  The resource to transform
     * @return array<string, mixed> The array representation
     *
     * @throws \InvalidArgumentException If the model type is not supported
     */
    public function toArray(object $model): array;
}
