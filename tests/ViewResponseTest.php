<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests;

use ArrayObject;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rkov\ViewModel\Contracts\ViewInterface;
use Rkov\ViewModel\Responses\JsonResponse;
use Rkov\ViewModel\Responses\ViewResponse;
use Rkov\ViewModel\Tests\Stubs\CollectionStub;

#[CoversClass(ViewResponse::class)]
#[CoversClass(JsonResponse::class)]
final class ViewResponseTest extends TestCase
{
    private function makeView(): ViewInterface
    {
        return new class () implements ViewInterface {
            public function toArray(object $model): array
            {
                return ['id' => $model->id ?? null];
            }
        };
    }

    /**
     * Every way an empty resource can be passed.
     *
     * @return array<string, array{array<never>|object|null}>
     */
    public static function emptyResourceProvider(): array
    {
        return [
            'null' => [null],
            'empty array' => [[]],
            'empty ArrayIterator' => [new \ArrayIterator([])],
            'empty collection' => [new CollectionStub()],
        ];
    }

    #[DataProvider('emptyResourceProvider')]
    public function test_serializes_empty_resource_as_empty_value(array|object|null $resource): void
    {
        $response = new ViewResponse($this->makeView(), $resource);

        // A missing object renders as "{}", an empty collection as "[]"
        $expected = $resource === null ? '{}' : '[]';

        $this->assertSame($expected, json_encode($response));
    }

    #[DataProvider('emptyResourceProvider')]
    public function test_serializes_empty_resource_as_null_with_when_empty_show_null(array|object|null $resource): void
    {
        $response = (new ViewResponse($this->makeView(), $resource))->whenEmptyShowNull();

        $this->assertSame('null', json_encode($response));
    }

    public function test_null_resource_serializes_to_array_object(): void
    {
        $response = new ViewResponse($this->makeView(), null);

        $this->assertInstanceOf(ArrayObject::class, $response->jsonSerialize());
    }

    public function test_serializes_single_object(): void
    {
        $view = $this->makeView();
        $obj = new \stdClass();
        $obj->id = 42;

        $response = new ViewResponse($view, $obj);
        $result = json_decode(json_encode($response), true);

        $this->assertSame(['id' => 42], $result);
    }

    public function test_serializes_list_of_objects(): void
    {
        $view = $this->makeView();
        $objs = [
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 3],
        ];

        $response = new ViewResponse($view, $objs);
        $result = json_decode(json_encode($response), true);

        $this->assertSame([
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ], $result);
    }

    public function test_serializes_associative_array_of_objects_as_collection(): void
    {
        $view = $this->makeView();
        $items = [
            'first' => (object) ['id' => 1],
            'second' => (object) ['id' => 2],
        ];

        $response = new ViewResponse($view, $items);
        $result = json_decode(json_encode($response), true);

        $this->assertSame([
            ['id' => 1],
            ['id' => 2],
        ], $result);
    }

    public function test_serializes_non_empty_collection(): void
    {
        $view = $this->makeView();
        $collection = new CollectionStub([
            (object) ['id' => 1],
            (object) ['id' => 2],
        ]);

        $response = (new ViewResponse($view, $collection))->whenEmptyShowNull();

        $this->assertSame('[{"id":1},{"id":2}]', json_encode($response));
    }

    public function test_empty_generator_is_not_treated_as_empty_resource(): void
    {
        $view = $this->makeView();

        $generator = (function (): \Generator {
            yield from [];
        })();

        $response = (new ViewResponse($view, $generator))->whenEmptyShowNull();

        // Emptiness of a generator cannot be detected without consuming it
        $this->assertSame('[]', json_encode($response));
    }

    public function test_when_empty_show_null_returns_self(): void
    {
        $view = $this->makeView();
        $response = new ViewResponse($view, []);

        $result = $response->whenEmptyShowNull();

        $this->assertSame($response, $result);
    }

    public function test_json_response_implements_json_serializable(): void
    {
        $response = new ViewResponse($this->makeView(), new \stdClass());

        $this->assertInstanceOf(JsonSerializable::class, $response);
    }
}
