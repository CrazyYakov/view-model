<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

final class UserStub
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        /** @var iterable<PostStub> */
        public iterable $posts = [],
        public ?AddressStub $address = null,
    ) {
    }
}
