<?php

/**
 * Shared REST plumbing for the menu domain controllers.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Menu;

/**
 * Trait MenuRestSupport
 *
 * Request-param normalization, integer validation, and status-coded
 * response/error helpers shared by MenuRoutes, MenuItemRoutes, and
 * ModifierRoutes. No database access and no WordPress globals beyond
 * function/class-exists guards, so domain purity holds.
 */
trait MenuRestSupport
{
    /**
     * Normalize request params from a WP_REST_Request or a unit-test array.
     *
     * @return array<string, mixed>
     */
    private static function params(mixed $request): array
    {
        if (\is_array($request)) {
            return $request;
        }
        if (\is_object($request) && \method_exists($request, 'get_params')) {
            $params = $request->get_params();
            if (\is_array($params)) {
                return $params;
            }
        }

        return [];
    }

    /**
     * Normalize the status input, defaulting to publish.
     *
     * @param array<string, mixed> $params Request params.
     */
    private function status(array $params): string
    {
        $status = (string) ($params['status'] ?? 'publish');

        return 'draft' === $status ? 'draft' : 'publish';
    }

    /**
     * Read an integer >= 0 param (price_cents, image_id).
     *
     * Accepts ints and integer-valued strings/floats; everything else (and
     * negatives) is invalid. Absent params are valid with value 0.
     *
     * @param array<string, mixed> $params Request params.
     * @param string               $key    Param key.
     * @return array{present: bool, value: int, valid: bool} Presence, value, and validity.
     */
    private function nonNegativeInt(array $params, string $key): array
    {
        if (! isset($params[$key])) {
            return ['present' => false, 'value' => 0, 'valid' => true];
        }

        $raw = $params[$key];
        if (\is_int($raw)) {
            $value = $raw;
        } elseif (\is_string($raw) && '' !== $raw && false !== \filter_var($raw, FILTER_VALIDATE_INT)) {
            $value = (int) $raw;
        } elseif (\is_float($raw) && (float) (int) $raw === $raw) {
            $value = (int) $raw;
        } else {
            return ['present' => true, 'value' => 0, 'valid' => false];
        }

        if ($value < 0) {
            return ['present' => true, 'value' => 0, 'valid' => false];
        }

        return ['present' => true, 'value' => $value, 'valid' => true];
    }

    /**
     * Normalize a bulk-reorder id list.
     *
     * @param array<string, mixed> $params Request params.
     * @return list<int>|null Ordered ids, or null when the body is unusable.
     */
    private function orderIds(array $params): ?array
    {
        $ids = $params['ids'] ?? null;
        if (! \is_array($ids)) {
            return null;
        }

        $normalized = [];
        foreach (\array_values($ids) as $id) {
            if (\is_int($id)) {
                $int = $id;
            } elseif (\is_string($id) && '' !== $id && false !== \filter_var($id, FILTER_VALIDATE_INT)) {
                $int = (int) $id;
            } elseif (\is_float($id) && (float) (int) $id === $id) {
                $int = (int) $id;
            } else {
                return null;
            }
            if ($int <= 0) {
                return null;
            }
            $normalized[] = $int;
        }

        return $normalized;
    }

    /**
     * Whether the requested order covers exactly the parent's current id set.
     *
     * Order-insensitive: the caller assigns positions after this check.
     *
     * @param list<int> $requested Ordered ids from the request body.
     * @param list<int> $current   Full current id set for the parent.
     */
    private function sameIdSet(array $requested, array $current): bool
    {
        \sort($requested);
        \sort($current);

        return $requested === $current;
    }

    /**
     * Wrap a payload in a status-coded response when WordPress is loaded.
     *
     * Unit context (no WP_REST_Response) returns the payload array, which
     * WordPress would serialize to a 200 JSON response in production.
     *
     * @param array<string, mixed> $data Payload.
     * @return mixed
     */
    private function respond(array $data, int $status): mixed
    {
        if (\class_exists('WP_REST_Response')) {
            return new \WP_REST_Response($data, $status);
        }

        return $data;
    }

    /**
     * Error payload with a machine-readable code.
     *
     * @return mixed
     */
    private function error(string $code, string $message, int $status): mixed
    {
        return $this->respond(['code' => $code, 'message' => $message, 'data' => ['status' => $status]], $status);
    }
}
