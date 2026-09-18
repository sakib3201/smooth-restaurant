<?php

/**
 * Container resolution exception.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Exceptions;

/**
 * Class UnresolvableException
 *
 * Thrown when the DI container cannot resolve an abstract or one of its
 * class dependencies.
 */
final class UnresolvableException extends SmoothException
{
}
