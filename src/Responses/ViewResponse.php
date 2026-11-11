<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Responses;

use ArrayObject;
use Rkov\ViewModel\Contracts\ViewInterface;

final class ViewResponse extends JsonResponse
{
    protected bool $showNull = false;

    /**
     * @param  iterable<object>|object|null  $resource
     */
    public function __construct(
        protected ViewInterface $view,
        protected iterable|object|null $resource,
    ) {
    }

    public function whenEmptyShowNull(): static
    {
        $this->showNull = true;

        return $this;
    }

    /**
     * Check if the resource is empty (null, empty array or empty countable collection).
     *
     * Non-countable iterables (e.g. generators) are never treated as empty:
     * detecting their emptiness would consume them, making them unusable afterwards.
     *
     * @phpstan-assert-if-false !null $this->resource
     */
    protected function isResourceEmpty(): bool
    {
        if ($this->resource === null) {
            return true;
        }

        if (is_array($this->resource)) {
            return $this->resource === [];
        }

        if ($this->resource instanceof \Countable) {
            return count($this->resource) === 0;
        }

        return false;
    }

    /**
     * Value returned for an empty resource.
     *
     * @return array{}|ArrayObject<object>|null
     */
    private function emptyValue(): array|object|null
    {
        if ($this->showNull) {
            return null;
        }

        // An empty collection renders as "[]", a missing object as "{}"
        return is_iterable($this->resource) ? [] : new ArrayObject();
    }

    /**
     * @return array<string, mixed>|array<int, array<string, mixed>>|ArrayObject<object>|null
     */
    public function jsonSerialize(): array|object|null
    {
        if ($this->isResourceEmpty()) {
            return $this->emptyValue();
        }

        return is_iterable($this->resource)
            ? $this->mapCollection($this->resource)
            : $this->view->toArray($this->resource);
    }

    /**
     * Map every item of a collection through the view.
     *
     * @param  iterable<object>  $items
     * @return array<int, array<string, mixed>>
     */
    private function mapCollection(iterable $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = $this->view->toArray($item);
        }

        return $result;
    }
}
