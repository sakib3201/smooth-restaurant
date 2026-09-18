<?php

/**
 * Repository exception.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Exceptions;

/**
 * Class RepositoryException
 *
 * Thrown when a `BaseRepository` subclass cannot talk to its database
 * connection (missing upgrade API, unprepareable query).
 */
final class RepositoryException extends SmoothException
{
}
