<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

final class PostStub
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
