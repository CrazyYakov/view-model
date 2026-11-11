<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

use Rkov\ViewModel\View;

final class PostView extends View
{
    public function toArray(object $resource): array
    {
        if (! $resource instanceof PostStub) {
            throw new \InvalidArgumentException(
                sprintf('Expected PostStub, got %s', get_class($resource))
            );
        }

        return [
            'id' => $resource->id,
            'title' => $resource->title,
        ];
    }
}
