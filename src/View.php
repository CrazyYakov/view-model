<?php

declare(strict_types=1);

namespace Rkov\ViewModel;

use Rkov\ViewModel\Contracts\ViewInterface;
use Rkov\ViewModel\Responses\ViewResponse;

abstract class View implements ViewInterface
{
    public function object(?object $resource): ViewResponse
    {
        return new ViewResponse($this, $resource);
    }

    /**
     * @param  iterable<object>  $resource
     */
    public function collection(iterable $resource): ViewResponse
    {
        return new ViewResponse($this, $resource);
    }
}
