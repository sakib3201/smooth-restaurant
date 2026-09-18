<?php

/**
 * Base plugin exception.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Exceptions;

/**
 * Class SmoothException
 *
 * Base of the plugin exception hierarchy. Extends the SPL
 * `RuntimeException` so existing `expectException(\RuntimeException::class)`
 * assertions keep passing while call sites can catch narrower subtypes.
 */
class SmoothException extends \RuntimeException
{
}
