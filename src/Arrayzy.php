<?php


namespace Lobster\Arrayzy;


/**
 * Class Arrayzy
 * @package Lobster\Arrayzy
 */
class Arrayzy implements Enumerable {

    use Accessible, Countable;

    /**
     * @return \Traversable
     */
    public function getIterator() : \Traversable {
        return new \ArrayIterator($this->data);
    }

    /**
     * @param $var
     * @return bool
     */
    public function isStatic($var) : bool {
        return $var instanceof static;
    }

    /**
     * @param string|int|null $offset
     * @param $value
     * @return static
     */
    public function set($offset, $value) : Enumerable {
        return $this->offsetSet($offset, $value);
    }

    /**
     * @param callable $callable
     * @return static
     */
    public function map(callable $callable) : Enumerable {

        $copy = clone $this;

        foreach ($copy->data as &$datum){
            $datum = $callable($datum);
        }

        return $copy;
    }

    /**
     * Dump $this and die
     */
    public function dd() : void {
        dd($this);
    }

    /**
     * @return static
     */
    public function collapse() : Enumerable {

        $self = $this->newStatic();

        foreach ($this->data as $datum){
            if($this->isStatic($datum)){
                $self = $self->merge($datum->data);
            }
        }

        return $self;
    }

    /**
     * @param array $array
     * @param array ...$arrays
     * @return static
     */
    public function diff(array $array, array ...$arrays) : Enumerable {
        return $this->newStatic(array_diff($this->data, $array, ...$arrays));
    }

    /**
     * @param array $values
     * @return static
     * @throws InvalidArgumentException
     */
    public function combine(array $values) : Enumerable {

        if($this->count() !== count($values)){
            throw new InvalidArgumentException(
                'The number of elements in the array must 
                be equal to the number of elements in the collection'
            );
        }

        return $this->newStatic(array_combine($this->data, $values));
    }

    /**
     * @param $limit
     * @return static
     */
    public function take($limit) : Enumerable {

        if($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * @return static
     */
    public function flip() : Enumerable {

        $self = $this->filter(function ($v){
            return is_numeric($v) || is_string($v);
        });

        $self->data = array_flip($self->data);

        return $self;
    }

    /**
     * @param $value
     * @param bool $strict
     * @return bool
     */
    public function contains($value, bool $strict = false) : bool {
        return $this->value($value, $strict) !== null;
    }

    /**
     * @param $value
     * @param bool $strict
     * @return int|string|null
     */
    public function value($value, bool $strict = false){
        return $this->search(static function ($v) use ($value, $strict){
            return $strict ? $v === $value : $v == $value ;
        });
    }

    /**
     * @return static
     */
    public function flatten() : Enumerable {

        $self = $this->newStatic();

        $copy = clone $this;

        foreach ($copy->data as $datum){

            if($this->isStatic($datum)){

                /**
                 * @var static $datum
                 */
                $self = $self->merge(
                    $datum->flatten()->data
                );

                continue;
            }

            $self->add($datum);
        }

        return $self;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function reject(callable $callback) : Enumerable {

        $self = clone $this;

        foreach ($this->data as $offset => $v){
            if((bool) $callback($v, $offset)){
                $self->offsetUnset($offset);
            }
        }

        return $self;
    }

    /**
     * @param callable $callback
     * @return mixed|null
     */
    public function first(callable $callback){
        return $this->data[$this->search($callback)] ?? null;
    }

    /**
     * @param callable $callback
     * @return mixed|null
     */
    public function last(callable $callback){
        return ($self = $this->filter($callback))->isEmpty()
            ? null : $self->end();
    }

    /**
     * @param callable $callback
     * @return int|string|null
     */
    public function search(callable $callback){

        foreach ($this->data as $key => $v){
            if((bool) $callback($v, $key)){
                return $key;
            }
        }

        return null;
    }

    /**
     * @param callable $callback
     * @return array
     */
    public function searchAll(callable $callback) : array {

        $keys = [];

        foreach ($this->data as $key => $v){
            if((bool) $callback($v, $key)){
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @param $offset
     * @param null $default
     * @return mixed|null
     */
    public function get($offset, $default = null) {
        return $this->data[$offset] ?? $default;
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool {
        return $this->data === [];
    }

    /**
     * @param $offset
     * @return bool
     */
    public function has($offset): bool {
        return $this->offsetExists($offset);
    }

    /**
     * @param $offset
     * @return static
     */
    public function remove($offset): Enumerable {
        $this->offsetUnset($offset);
        return $this;
    }


    /**
     * @return int|float|null
     */
    public function sum(){

        $self = $this->filter(function ($v){
            return is_numeric($v);
        });

        if($self->isEmpty()){
            return null;
        }

        $sum = 0;

        foreach ($self as $item){
            $sum += $item;
        }

        return $sum;
    }

    /**
     * @return float|int
     */
    public function median(){

        $filtered = $this->filter(function ($v){
            return is_numeric($v);
        })->sort(SORT_NUMERIC);

        if($filtered->isEmpty()){
            return null;
        }

        $middle = (int) (($count = $filtered->count()) / 2);

        if($count % 2 === 0){
            return ($filtered->offsetGet($middle - 1) + $filtered->offsetGet($middle)) / 2 + 0;
        }

        return $filtered->offsetGet($middle) + 0;
    }

    /**
     * @param int $size
     * @param bool $preserveKeys
     * @return static
     */
    public function chunk(int $size, bool $preserveKeys = false) : Enumerable {
        return new static(array_chunk($this->data, $size, $preserveKeys));
    }

    /**
     * @param string $glue
     * @return string
     */
    public function implode(string $glue = '.') : string {
        $self = $this->filter(function ($v){
            return is_string($v) || is_numeric($v);
        });

        return implode($glue, $self->data);
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function sort(int $sort_flags = SORT_REGULAR ) : Enumerable {

        $self = clone $this;

        sort($self->data, $sort_flags);

        return $self;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function ksort(int $sort_flags = SORT_REGULAR ) : Enumerable {

        $self = clone $this;

        ksort($self->data, $sort_flags);

        return $self;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function krsort(int $sort_flags = SORT_REGULAR ) : Enumerable {

        $self = clone $this;

        krsort($self->data, $sort_flags);

        return $self;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function usort(callable $callback) : Enumerable {

        $self = clone $this;

        usort($self->data, $callback);

        return $self;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function uasort(callable $callback) : Enumerable{
        $self = clone $this;

        uasort($self->data, $callback);

        return $self;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function uksort(callable $callback) : Enumerable{
        $self = clone $this;

        uksort($self->data, $callback);

        return $self;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function rsort(int $sort_flags = SORT_REGULAR ) : Enumerable {

        $self = clone $this;

        rsort($self->data, $sort_flags);

        return $self;
    }


    /**
     * @return float|int|null
     */
    public function avg(){
        $self = $this->filter(function ($v){
            return is_numeric($v);
        });

        if(($count = $self->count()) === 0){
            return null;
        }

        $sum = 0;

        foreach ($self as $item){
            $sum += $item;
        }

        return $sum / $count;

    }

    /**
     * @return mixed
     */
    public function min(){
        return min($this->data);
    }

    /**
     * @return mixed
     */
    public function max(){
        return max($this->data);
    }


    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback) : Enumerable {

        $self = $this->newStatic();

        foreach ($this->data as $offset => $value){
            if((bool)$callback($value, $offset)){
                $self->set($offset, $value);
            }
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function shift() {
        return array_shift($this->data);
    }

    /**
     * @param $value
     * @param mixed ...$values
     * @return $this
     */
    public function unshift($value, ...$values) : Enumerable {
        array_unshift($values, $value);

        foreach ($values as $value){
            !is_array($value) ?: $value = $this->newStatic($value);

            array_unshift($this->data, $value);
        }

        return $this;
    }

    /**
     * @param $value
     * @param mixed ...$values
     * @return $this
     */
    public function push($value, ...$values) : Enumerable {
        array_unshift($values, $value);

        foreach ($values as $value){
            !is_array($value) ?: $value = $this->newStatic($value);

            array_push($this->data, $value);
        }

        return $this;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function unique(int $sort_flags = SORT_STRING) : Enumerable {
        return $this->newStatic(array_unique($this->data, $sort_flags));
    }

    /**
     * @return array
     */
    public function values() : array {
        return array_values($this->data);
    }

    /**
     * @return mixed
     */
    public function pop() {
        return array_pop($this->data);
    }

    /**
     * Shuffles the array randomly
     * @return static
     */
    public function shuffle() : Enumerable {
        $data = $this->data;

        shuffle($data);

        return $this->newStatic($data);
    }

    /**
     * @param int $offset
     * @param int|null $len
     * @param bool $preserveKeys
     * @return static
     */
    public function slice(int $offset, int $len = null, bool $preserveKeys = false) : Enumerable {
        $data = $this->data;
        return $this->newStatic(array_slice($data, $offset, $len, $preserveKeys));
    }

    /**
     * @param bool $preserveKeys
     * @return static
     */
    public function reverse($preserveKeys = false) : Enumerable {
        return $this->newStatic(array_reverse($data = $this->data, $preserveKeys));
    }

    /**
     * @param mixed ...$values
     * @return static
     */
    public function add(...$values): Enumerable {

        foreach ($values as $value){
            $this->offsetSet(null, $value);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function clear(): Enumerable {
        return $this->replace([]);
    }

    /**
     * @param array $data
     * @return static
     */
    public function replace(array $data) : Enumerable {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function pull($offset) {
        $v = $this->get($offset);

        $this->remove($offset);

        return $v;
    }

    /**
     * @param string|int|array $offset
     * @param string|int ...$offsets
     * @return static
     */
    public function only($offset, ...$offsets): Enumerable {

        if(is_array($offset)){
            $offsets = $offset;
        } else {
            array_unshift($offsets, $offset);
        }

        $values = $this->newStatic();

        foreach ($offsets as $offset){
            if($this->offsetExists($offset)){
                $values->offsetSet($offset, $this->offsetGet($offset));
            }
        }

        return $values;
    }

    /**
     * Return first collection element or null if collection is empty
     * @return mixed
     */
    public function start() {
        return $this->data[$this->firstKey()] ?? null ;
    }

    /**
     * Return last collection element or null if collection is empty
     * @return mixed
     */
    public function end() {
        return $this->data[$this->lastKey()] ?? null ;
    }

    /**
     * Return array of collection keys
     * @return array
     */
    public function keys() : array {
        return array_keys($this->data);
    }

    /**
     * Return collection first key
     * @return string|int|null
     */
    public function firstKey() {
        return array_key_first($this->data);
    }

    /**
     * Return collection first key
     * @return string|int|null
     */
    public function lastKey() {
        return array_key_last($this->data);
    }

    /**
     * @param string|int|array $offset
     * @param string|int ...$offsets
     * @return static
     */
    public function except($offset, ...$offsets): Enumerable {

        if(is_array($offset)){
            $offsets = $offset;
        } else {
            array_unshift($offsets, $offset);
        }

        $values = clone $this;

        foreach ($offsets as $offset){
            $values->remove($offset);
        }

        return $values;
    }

    /**
     * @return array
     */
    public function toArray(): array {

        $array = [];

        foreach ($this->data as $offset => $value){

            if($this->isStatic($value)){

                /**
                 * @var static $value
                 */
                $value = $value->toArray();
            }

            $array[$offset] = $value;
        }

        return $array;
    }

    /**
     * @param array $data
     * @param array ...$arrays
     * @return static
     */
    public function merge(array $data, array ...$arrays): Enumerable {

        array_unshift($arrays, $data);

        $self = clone $this;

        foreach ($arrays as $array){
            $self->data = array_merge($this->data, $this->newStatic($array)->data);
        }

        return $self;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback) : Enumerable {

        $self = clone $this;

        foreach ($self->data as $key => $v) {
            if ((bool) $callback($v, $key) === false) {
                break;
            }
        }

        return $self;
    }

    /**
     * @param string|int $key
     * @param null|string|int $indexKey
     * @return static
     */
    public function pluck($key, $indexKey = null) : Enumerable {
        return $this->newStatic(array_column($this->toArray(), $key, $indexKey));
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function transform(callable $callback) : Enumerable {

        $data = $this->data;

        foreach ($data as $key => $value){
            $this->data[$key] = $callback($value, $key);
        }

        return $this;
    }

    /**
     * @param array $array
     * @param array ...$arrays
     * @return static
     */
    public function intersect(array $array, array ...$arrays) : Enumerable {
        return $this->newStatic(array_intersect($this->data, $array, ...$arrays));
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    public function callback(callable $callback){
        return $callback($this);
    }

    /**
     * @param int $num
     * @return static
     */
    public function split(int $num) : Enumerable {

        if(($count = $this->count()) === 0){
            return $this->newStatic();
        }

        $self = $this->newStatic();

        $start = 0;

        for ($i = 0; $i < $num; $i++) {

            $size = (int) floor($count / $num);

            if($i < $count % $num) {
                $size++;
            }

            if($size) {
                $self->push(
                    $this->newStatic(
                        array_slice($this->data, $start, $size)
                    )
                );

                $start += $size;
            }
        }

        return $self;
    }

    /**
     * @param int $num
     * @return mixed|Enumerable|null
     * @throws \LogicException
     */
    public function rand(int $num = 1){

        if($this->isEmpty()){
            return null;
        }

        if($num > ($count = $this->count()) || 0 >= $num){
            throw new \LogicException('Argument $num has to be between 1 and ' . $count);
        }

        $keys = (array) array_rand($this->toArray(), $num);

        $values = [];

        foreach ($keys as $key){
            $values[] = $this->offsetGet($key);
        }

        if(count($values) > 1){
            return $this->newStatic($values);
        }

        return $values[0];
    }

    /**
     * @param callable $callback
     * @param null $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null){
        return array_reduce($this->data, $callback, $initial);
    }
}
