<?php

declare(strict_types=1);

namespace Rkov\ViewModel\Responses;

use JsonSerializable;

/**
 * Base JSON response — provides JSON serialization foundation
 * for all view response types.
 */
abstract class JsonResponse implements JsonSerializable
{
}
