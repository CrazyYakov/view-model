<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Tests\Stubs;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Simulates a framework collection (e.g. Doctrine ArrayCollection):
 * iterable and countable, but neither an array nor an ArrayObject.
 *
 * @implements IteratorAggregate<int, object>
 */
final class CollectionStub implements IteratorAggregate, Countable
{
    /**
     * @param  array<int, object>  $items
     */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
