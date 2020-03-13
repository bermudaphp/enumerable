<?php


namespace Lobster\Arrayzy;


use Lobster\Reducible\Arrayble;
use Lobster\Countable\Countable;


/**
 * Interface Enumerable
 * @package Halcyon\Collection
 */
interface Enumerable extends Arrayble, Countable, \ArrayAccess, \IteratorAggregate {

    /**
     * @param callable $callable
     * @return static
     */
    public function map(callable $callable): Enumerable;

    /**
     * @return static
     */
    public function collapse(): Enumerable;

    /**
     * Dump and die
     */
    public function dd() : void ;

    /**
     * @param array $array
     * @param array ...$arrays
     * @return Enumerable
     *
     */
    public function diff(array $array, array ...$arrays): Enumerable;

    /**
     * @param array $values
     * @return static
     * @throws InvalidArgumentException
     */
    public function combine(array $values): Enumerable;

    /**
     * @param $limit
     * @return static
     */
    public function take($limit): Enumerable;
    
    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return static
     */
    public function flip(): Enumerable;

    /**
     * @param $value
     * @return bool
     */
    public function contains($value, bool $strict = false): bool;

    /**
     * @param $value
     * @param bool $strict
     * @return int|string|null
     */
    public function value($value, bool $strict = false);

    /**
     * @return static
     */
    public function flatten(): Enumerable;

    /**
     * @param callable $callback
     * @return static
     */
    public function reject(callable $callback): Enumerable;

    /**
     * @param callable $callback
     * @return mixed|null
     */
    public function first(callable $callback);

    /**
     * @param callable $callback
     * @return mixed|null
     */
    public function last(callable $callback);

    /**
     * @param callable $callback
     * @return int|string|null
     */
    public function search(callable $callback);

    /**
     * @param callable $callback
     * @return array
     */
    public function searchAll(callable $callback): array;
    
    /**
     * @param string|int|null $offset
     * @param $value
     * @return static
     */
    public function set($offset, $value) : Enumerable ;

    /**
     * @param string|int $offset
     * @param null $default
     * @return mixed|null
     */
    public function get($offset, $default = null);

    /**
     * @param string|int $offset
     * @return bool
     */
    public function has($offset): bool;

    /**
     * @param $offset
     * @return static
     */
    public function remove($offset): Enumerable;

    /**
     * @return int|float|null
     */
    public function sum();

    /**
     * @return float|int
     */
    public function median();

    /**
     * @param int $size
     * @param bool $preserveKeys
     * @return static
     */
    public function chunk(int $size, bool $preserveKeys = false): Enumerable;

    /**
     * @param string $glue
     * @return string
     */
    public function implode(string $glue = '.'): string;

    /**
     * @param int $sort_flags
     * @return static
     */
    public function sort(int $sort_flags = SORT_REGULAR): Enumerable;

    /**
     * @param int $sort_flags
     * @return static
     */
    public function ksort(int $sort_flags = SORT_REGULAR): Enumerable;

    /**
     * @param int $sort_flags
     * @return static
     */
    public function krsort(int $sort_flags = SORT_REGULAR): Enumerable;

    /**
     * @param callable $callback
     * @return static
     */
    public function usort(callable $callback) : Enumerable;

    /**
     * @param callable $callback
     * @return static
     */
    public function uasort(callable $callback) : Enumerable;

    /**
     * @param callable $callback
     * @return static
     */
    public function uksort(callable $callback) : Enumerable;

    /**
     * @param int $sort_flags
     * @return static
     */
    public function rsort(int $sort_flags = SORT_REGULAR): Enumerable;

    /**
     * @return float|int|null
     */
    public function avg();

    /**
     * @return mixed
     */
    public function min();

    /**
     * @return mixed
     */
    public function max();

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): Enumerable;

    /**
     * @return mixed
     */
    public function shift();

    /**
     * @param $value
     * @param mixed ...$values
     * @return $this
     */
    public function unshift($value, ...$values): Enumerable;

    /**
     * @param $value
     * @param mixed ...$values
     * @return $this
     */
    public function push($value, ...$values): Enumerable;

    /**
     * @param int $sort_flags
     * @return static
     */
    public function unique(int $sort_flags = SORT_STRING): Enumerable;

    /**
     * @return array
     */
    public function values(): array;

    /**
     * @return mixed
     */
    public function pop();

    /**
     * Shuffles the array randomly
     * @return static
     */
    public function shuffle(): Enumerable;

    /**
     * @param int $offset
     * @param int|null $len
     * @param bool $preserveKeys
     * @return static
     */
    public function slice(int $offset, int $len = null, bool $preserveKeys = false): Enumerable;

    /**
     * @param bool $preserveKeys
     * @return static
     */
    public function reverse($preserveKeys = false): Enumerable;

    /**
     * @param mixed ...$values
     * @return Enumerable
     */
    public function add(...$values): Enumerable;

    /**
     * @return static
     */
    public function clear(): Enumerable;

    /**
     * @param array $data
     * @return static
     */
    public function replace(array $data): Enumerable;

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function pull($offset);

    /**
     * @param string|int|array $offset
     * @param string|int ...$offsets
     * @return static
     */
    public function only($offset, ...$offsets): Enumerable;

    /**
     * Return first collection element or null if collection is empty
     * @return mixed
     */
    public function start();

    /**
     * Return last collection element or null if collection is empty
     * @return mixed
     */
    public function end();

    /**
     * Return array of collection keys
     * @return array
     */
    public function keys(): array;

    /**
     * Return collection first key
     * @return string|int|null
     */
    public function firstKey();

    /**
     * Return collection first key
     * @return string|int|null
     */
    public function lastKey();

    /**
     * @param string|int|array $offset
     * @param string|int ...$offsets
     * @return static
     */
    public function except($offset, ...$offsets): Enumerable;

    /**
     * @param int|string $offset
     * @param mixed $value
     * @return static
     */
    public function offsetSet($offset, $value) : Enumerable ;

    /**
     * @param array $data
     * @param array ...$arrays
     * @return static
     */
    public function merge(array $data, array ...$arrays): Enumerable;

    /**
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback): Enumerable;

    /**
     * @param string|int $key
     * @param null|string|int $indexKey
     * @return static
     */
    public function pluck($key, $indexKey = null): Enumerable;

    /**
     * @param callable $callback
     * @return $this
     */
    public function transform(callable $callback): Enumerable;

    /**
     * @param array $array
     * @param array ...$arrays
     * @return static
     */
    public function intersect(array $array, array ...$arrays): Enumerable;

    /**
     * @param callable $callback
     * @return mixed
     */
    public function callback(callable $callback);

    /**
     * @param int $num
     * @return static
     */
    public function split(int $num): Enumerable;

    /**
     * @param int $num
     * @return mixed|Enumerable|null
     */
    public function rand(int $num = 1);

    /**
     * @param callable $callback
     * @param null $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null);
}
