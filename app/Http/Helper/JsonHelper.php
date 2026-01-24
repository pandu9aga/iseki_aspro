<?php

namespace App\Http\Helper;

use Illuminate\Contracts\Support\Jsonable;

/**
 * JsonHelper - Flexible JSON array wrapper with dot-path notation support
 *
 * A convenient interface for working with JSON data and nested arrays.
 * Supports both magic property access and explicit dot-path methods for
 * reading and writing nested values.
 *
 * @property JsonHelper|mixed|string|null $Photo_PDF
 *
 * @author tunbudi06
 */
class JsonHelper implements \JsonSerializable, \Stringable, Jsonable
{
    /**
     * Internal array storage for all data.
     */
    private array $data = [];

    /**
     * Create a new JsonHelper instance.
     *
     * Accepts a JSON string (validated and decoded) or an array (used directly).
     * Invalid JSON falls back to an empty array.
     *
     * @param  string|array  $input  JSON string or array data
     */
    public function __construct(string|array $input = '')
    {
        if (is_string($input)) {
            if ($input !== '' && function_exists('json_validate') && json_validate($input)) {
                $this->data = json_decode($input, true);
            } elseif ($input !== '') {
                // Fallback: try decode, use empty array if invalid
                $decoded = json_decode($input, true);
                $this->data = is_array($decoded) ? $decoded : [];
            } else {
                $this->data = [];
            }
        } else {
            $this->data = $input;
        }
    }

    /**
     * Magic getter for property access.
     *
     * Returns scalar values directly. For nested arrays, returns a new JsonHelper
     * instance. Note: the returned instance is separate, use set() for nested writes.
     *
     * @param  string  $name  Property name
     * @return mixed|null|JsonHelper|$this->data
     */
    public function __get(string $name)
    {
        if (! array_key_exists($name, $this->data)) {
            return null;
        }

        $value = $this->data[$name];

        if (is_array($value)) {
            return new self($value);
        }

        return $value;
    }

    /**
     * Magic setter for property assignment.
     *
     * Converts JsonHelper values to arrays. Nested writes via chaining won't
     * update the parent - use set() method instead.
     *
     * @param  string  $name  Property name
     * @param  mixed  $value  Value to set
     */
    public function __set(string $name, $value): void
    {
        if ($value instanceof self) {
            $this->data[$name] = $value->toArray();

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * Convert data to JSON string.
     *
     * @param  int  $options  JSON encoding options
     * @return false|string JSON string or false on failure
     */
    public function toJson($options = 0): false|string
    {
        return json_encode($this->data, $options);
    }

    /**
     * Convert to string (JSON representation).
     */
    public function __toString(): string
    {
        return (string) $this->toJson();
    }

    /**
     * Get the underlying array.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * JsonSerializable interface implementation.
     *
     * Allows json_encode($helper) to work correctly.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get a value using dot-path notation.
     *
     * Examples: 'user.name', 'settings.theme.color'
     * Returns default value if path doesn't exist.
     *
     * @param  string  $path  Dot-separated path (e.g., 'user.profile.name')
     * @param  mixed  $default  Default value if path not found
     */
    public function get(string $path, $default = null): mixed
    {
        if ($path === '') {
            return $this->data;
        }

        $segments = explode('.', $path);
        $cursor = $this->data;

        foreach ($segments as $seg) {
            if (! is_array($cursor) || ! array_key_exists($seg, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$seg];
        }

        return $cursor;
    }

    /**
     * Set a value using dot-path notation.
     *
     * Automatically creates intermediate arrays as needed.
     * Example: set('user.profile.name', 'John') creates nested structure.
     *
     * @param  string  $path  Dot-separated path (e.g., 'user.profile.name')
     * @param  mixed  $value  Value to set
     */
    public function set(string $path, $value): void
    {
        if ($path === '') {
            $this->data = is_array($value) ? $value : [$value];

            return;
        }

        $segments = explode('.', $path);
        $cursor = &$this->data;

        foreach ($segments as $seg) {
            if (! isset($cursor[$seg]) || ! is_array($cursor[$seg])) {
                $cursor[$seg] = [];
            }
            $cursor = &$cursor[$seg];
        }

        $cursor = $value;
    }

    /**
     * Check if a dot-path exists in the data.
     *
     * @param  string  $path  Dot-separated path (e.g., 'user.profile.name')
     * @return bool True if path exists, false otherwise
     */
    public function has(string $path): bool
    {
        if ($path === '') {
            return ! empty($this->data);
        }

        $segments = explode('.', $path);
        $cursor = $this->data;

        foreach ($segments as $seg) {
            if (! is_array($cursor) || ! array_key_exists($seg, $cursor)) {
                return false;
            }
            $cursor = $cursor[$seg];
        }

        return true;
    }

    /**
     * Remove value at dot-path if exists.
     *
     * @param  string  $path  Dot-separated path (e.g., 'user.profile.name')
     */
    public function remove(string $path): void
    {
        if ($path === '') {
            $this->data = [];

            return;
        }

        $segments = explode('.', $path);
        $last = array_pop($segments);
        $cursor = &$this->data;

        foreach ($segments as $seg) {
            if (! is_array($cursor) || ! array_key_exists($seg, $cursor)) {
                return;
            }
            $cursor = &$cursor[$seg];
        }

        if (is_array($cursor) && array_key_exists($last, $cursor)) {
            unset($cursor[$last]);
        }
    }
}
