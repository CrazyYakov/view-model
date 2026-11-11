<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rkov\ViewModel\Contracts\ViewInterface;
use Rkov\ViewModel\Responses\ViewResponse;
use Rkov\ViewModel\Tests\Stubs\AddressStub;
use Rkov\ViewModel\Tests\Stubs\AddressView;
use Rkov\ViewModel\Tests\Stubs\PostStub;
use Rkov\ViewModel\Tests\Stubs\PostView;
use Rkov\ViewModel\Tests\Stubs\UserStub;
use Rkov\ViewModel\Tests\Stubs\UserView;
use Rkov\ViewModel\View;

#[CoversClass(View::class)]
#[CoversClass(ViewInterface::class)]
final class ViewTest extends TestCase
{
    /**
     * Non-empty post collections in every supported iterable form.
     *
     * @return array<string, array{iterable<PostStub>}>
     */
    public static function postsProvider(): array
    {
        return [
            'array' => [[
                new PostStub(10, 'First Post'),
                new PostStub(20, 'Second Post'),
            ]],
            'ArrayIterator' => [new \ArrayIterator([
                new PostStub(10, 'First Post'),
                new PostStub(20, 'Second Post'),
            ])],
            'generator' => [(static function (): \Generator {
                yield new PostStub(10, 'First Post');
                yield new PostStub(20, 'Second Post');
            })()],
        ];
    }

    /**
     * Empty post collections in every supported iterable form.
     *
     * @return array<string, array{iterable<PostStub>}>
     */
    public static function emptyPostsProvider(): array
    {
        return [
            'empty array' => [[]],
            'empty ArrayIterator' => [new \ArrayIterator([])],
        ];
    }

    public function test_object_returns_view_response(): void
    {
        $view = new class () extends View {
            public function toArray(object $resource): array
            {
                return ['type' => get_class($resource)];
            }
        };

        $response = $view->object(new PostStub(1, 'Test'));

        $this->assertInstanceOf(ViewResponse::class, $response);
        $this->assertSame(
            ['type' => PostStub::class],
            json_decode(json_encode($response), true)
        );
    }

    public function test_collection_returns_view_response(): void
    {
        $view = new class () extends View {
            public function toArray(object $resource): array
            {
                return ['id' => $resource->id];
            }
        };

        $posts = [
            new PostStub(1, 'First'),
            new PostStub(2, 'Second'),
        ];

        $response = $view->collection($posts);

        $this->assertInstanceOf(ViewResponse::class, $response);
        $result = json_decode(json_encode($response), true);
        $this->assertSame([
            ['id' => 1],
            ['id' => 2],
        ], $result);
    }

    public function test_user_view_with_nested_posts(): void
    {
        $userView = new UserView(new PostView(), new AddressView());

        $user = new UserStub(
            id: 1,
            name: 'John',
            email: 'john@example.com',
            posts: [
                new PostStub(10, 'First Post'),
                new PostStub(20, 'Second Post'),
            ],
            address: new AddressStub(
                city: 'Moscow',
                street: 'Tverskaya',
                homeNumber: '1',
                flatNumber: '42',
            ),
        );

        $response = $userView->object($user);
        $result = json_decode(json_encode($response), true);

        $this->assertSame([
            'id' => 1,
            'name' => 'John',
            'email' => 'john@example.com',
            'posts' => [
                ['id' => 10, 'title' => 'First Post'],
                ['id' => 20, 'title' => 'Second Post'],
            ],
            'address' => [
                'city' => 'Moscow',
                'street' => 'Tverskaya',
                'home_number' => '1',
                'flat_number' => '42',
            ],
        ], $result);
    }

    /**
     * @param  iterable<PostStub>  $posts
     */
    #[DataProvider('postsProvider')]
    public function test_user_view_accepts_any_iterable_posts(iterable $posts): void
    {
        $userView = new UserView(new PostView(), new AddressView());

        $user = new UserStub(
            id: 1,
            name: 'John',
            email: 'john@example.com',
            posts: $posts,
        );

        $result = json_decode(json_encode($userView->object($user)), true);

        $this->assertSame([
            ['id' => 10, 'title' => 'First Post'],
            ['id' => 20, 'title' => 'Second Post'],
        ], $result['posts']);
    }

    /**
     * @param  iterable<PostStub>  $posts
     */
    #[DataProvider('emptyPostsProvider')]
    public function test_user_view_with_empty_posts_shows_null(iterable $posts): void
    {
        $userView = new UserView(new PostView(), new AddressView());

        $user = new UserStub(
            id: 1,
            name: 'Jane',
            email: 'jane@example.com',
            posts: $posts,
        );

        $result = json_decode(json_encode($userView->object($user)), true);

        $this->assertNull($result['posts']);
    }

    public function test_readme_example_result(): void
    {
        $userView = new UserView(new PostView(), new AddressView());

        $user = new UserStub(
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            posts: [],
            address: null,
        );

        $response = $userView->object($user);

        // Empty posts render as null, a missing address as an empty object
        $this->assertSame(
            '{"id":1,"name":"John Doe","email":"john@example.com","posts":null,"address":{}}',
            json_encode($response)
        );
    }

    public function test_address_view_renders_all_fields(): void
    {
        $addressView = new AddressView();

        $address = new AddressStub(
            city: 'Kazan',
            street: 'Bauman',
            homeNumber: '12',
            flatNumber: null,
        );

        $response = $addressView->object($address);

        $this->assertSame(
            '{"city":"Kazan","street":"Bauman","home_number":"12","flat_number":null}',
            json_encode($response)
        );
    }

    public function test_address_view_rejects_foreign_resource(): void
    {
        $addressView = new AddressView();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected AddressStub, got ' . PostStub::class);

        // The resource is validated lazily, on serialization
        json_encode($addressView->object(new PostStub(1, 'Test')));
    }
}
