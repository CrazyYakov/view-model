<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

use Rkov\ViewModel\View;

final class AddressView extends View
{
    public function toArray(object $resource): array
    {
        if (! $resource instanceof AddressStub) {
            throw new \InvalidArgumentException(
                sprintf('Expected AddressStub, got %s', get_class($resource))
            );
        }

        return [
            'city' => $resource->city,
            'street' => $resource->street,
            'home_number' => $resource->homeNumber,
            'flat_number' => $resource->flatNumber,
        ];
    }
}
