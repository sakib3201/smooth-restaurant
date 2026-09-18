<?php

/**
 * Migration exception.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Exceptions;

/**
 * Class MigrationException
 *
 * Thrown when the `MigrationRunner` cannot read, run, or persist schema
 * migrations. Reserved for follow-up migration steps; no throw sites yet.
 */
final class MigrationException extends SmoothException
{
}
