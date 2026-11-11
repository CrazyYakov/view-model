<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

use Rkov\ViewModel\View;

final class UserView extends View
{
    public function __construct(
        private PostView $postView,
        private AddressView $addressView,
    ) {
    }

    public function toArray(object $resource): array
    {
        if (! $resource instanceof UserStub) {
            throw new \InvalidArgumentException(
                sprintf('Expected UserStub, got %s', get_class($resource))
            );
        }

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'email' => $resource->email,
            // Posts are rendered as null when the resource has none
            'posts' => $this->postView
                ->collection($resource->posts)
                ->whenEmptyShowNull(),
            // Missing address is rendered as an empty object "{}"
            'address' => $this->addressView
                ->object($resource->address),
        ];
    }
}
