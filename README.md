# ViewModel

Model representations for REST API.
Now you can create “model representations” using dependency injection.
Suitable for any frameworks (Laravel, Symfony).

## Installation

```bash
composer require rkov/view-model
```

## Usage

1. Add view models

### Post Model

```php
use Rkov\ViewModel\View;

class PostView extends View
{
    public function toArray(object $resource): array
    {
        if (! $resource instanceof Post) {
            throw new \InvalidArgumentException('Expected Post, got ' . get_class($resource));
        }

        return [
            'id' => $resource->id,
            'title' => $resource->title,
        ];
    }
}
```

### Address Model
```php
use Rkov\ViewModel\View;

class AddressView extends View
{
    public function toArray(object $resource): array
    {
        if (! $resource instanceof Address) {
            throw new \InvalidArgumentException('Expected Address, got ' . get_class($resource));
        }

        return [
            'city' => $resource->city,
            'street' => $resource->street,
            'home_number' => $resource->home_number,
            'flat_number' => $resource->flat_number,
        ];
    }
}
```

### User Model
```php

use Rkov\ViewModel\View;

class UserView extends View
{
    public function __construct(
        private PostView $postView,
        private AddressView $addressView,
    ) {}

    public function toArray(object $resource): array
    {
        if (! $resource instanceof User) {
            throw new \InvalidArgumentException('Expected User, got ' . get_class($resource));
        }

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'email' => $resource->email,
            // Posts are rendered as null when the resource has none
            'posts' => $this->postView
                ->collection($resource->posts)
                ->whenEmptyShowNull(),
            // A missing address is rendered as an empty object "{}"
            'address' => $this->addressView
                ->object($resource->address),
        ];
    }
}
```

2. Next, adding a controller

```php

use Rkov\ViewModel\Responses\ViewResponse;

class GetUserController
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function __invoke(Request $request, UserView $view): ViewResponse
    {
        $user = $this->repository->get($request->id);

        return $view->object($user);
    }
}
```

3. Result

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "posts": null,
    "address": {}
}
```

## License

Copyright (c) Rochev Yakov. Distributed under the MIT.