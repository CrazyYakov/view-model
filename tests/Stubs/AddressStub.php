<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

final class AddressStub
{
    public function __construct(
        public string $city,
        public string $street,
        public string $homeNumber,
        public ?string $flatNumber = null,
    ) {
    }
}
