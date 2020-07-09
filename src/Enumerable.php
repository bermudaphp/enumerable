<?php


namespace Bermuda\Enumerable;


/**
 * Class Enumerable
 * @package Bermuda\Enumerable
 */
class Enumerable implements EnumerableInterface
{
    use Accessible;

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param $var
     * @return bool
     */
    protected function isStatic($var): bool
    {
        return $var instanceof static;
    }
    
    /**
     * @param bool $recursive
     * @return int
     */
    public function count(bool $recursive = false): int 
    {
        if($recursive)
        {   
           $count = 0;
           
           foreach($this->items as $item)
           {
               $count += $this->isStatic($item) ? $item->count(true) : 1;
           }
        }
       
        return count($this->items);
    }

    /**
     * @param string|int|null $offset
     * @param $value
     * @return static
     */
    public function set($offset, $value): EnumerableInterface 
    {
        return $this->offsetSet($offset, $value);
    }
    
    /**
     * @param int|string $offset
     * @param mixed $value
     * @return static
     */
    public function offsetSet($offset, $value): EnumerableInterface 
    {
        if(\is_array($value))
        {
            $value = $this->newStatic($value);
        }

        $offset === null ? $this->items[] = $value : $this->items[$offset] = $value;

        return $this;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): EnumerableInterface 
    {
        $copy = clone $this;

        foreach ($copy->items as &$item)
        {
            $item = $callable($item);
        }

        return $copy;
    }

    /**
     * Dump $this and die
     */
    public function dd(): void 
    {
        dd($this);
    }

    /**
     * @return static
     */
    public function collapse(): EnumerableInterface 
    {
        $self = $this->newStatic();

        foreach ($this->items as $item)
        {
            if($this->isStatic($item))
            {
                $self = $self->merge($item);
            }
        }

        return $self;
    }

    /**
     * @param Arrayable|array $first
     * @param Arrayable|array ... $other
     * @throws InvalidArhumentException
     * @return static
     */
    public function diff($first, ... $other): EnumerableIneterface 
    {
        if(!$other != [])
        {
            $arrays = [];
            
            foreach($other as $item)
            {
                $arrays[] = $this->varToArray($item);
            }
            
            return $this->newStatic(array_diff($this->items, $this->varToArray($first), ... $arrays));
        }
        
        return $this->newStatic(array_diff($this->items, $this->varToArray($first)));
    }
    
    /**
     * @param mixed $var
     * @throws InvalidArhumentException
     * @return array
     */
    protected function varToArray($var): array
    {
        if(is_array($var))
        {
            return $this->newStatic($var)->items;
        }
        
        if($var instanceof static)
        {
            return $var->items;
        }
        
        if($var instanceof Arrayable)
        {
            return  $this->newStatic($var->toArray())->items;
        }
        
        throw new \InvalidArgumentException('Ожидается array|Arrayable');
    }

    /**
     * @param array|Arrayable $values
     * @return static
     * @throws \InvalidArgumentException
     */
    public function combine($values): EnumerableInterface 
    {
        $values = $this->varToArray($values);
        
        if($this->count() !== \count($values))
        {
            throw new \InvalidArgumentException(
                'The number of elements in the array must 
                be equal to the number of elements in the collection'
            );
        }

        return $this->newStatic(\array_combine($this->items, $values));
    }

    /**
     * @param $limit
     * @return static
     */
    public function take(int $limit): EnumerableInterface
    {
        if($limit < 0)
        {
            return $this->slice($limit, \abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * @return static
     */
    public function flip(): EnumerableInterface 
    {
        $filtered = $this->filter(static function ($v)
        {
            return \is_numeric($v) || \is_string($v);
        });

        $filtered->items = \array_flip($filtered->items);

        return $filtered;
    }

    /**
     * @param $value
     * @param bool $strict
     * @return bool
     */
    public function contains($value, bool $strict = false): bool 
    {
        return $this->value($value, $strict) !== null;
    }

    /**
     * @param $value
     * @param bool $strict
     * @return int|string|null
     */
    public function value($value, bool $strict = false)
    {
        return $this->search(static function ($v) use ($value, $strict)
        {
            return $strict ? $v === $value : $v == $value ;
        });
    }

    /**
     * @return static
     */
    public function flatten(): EnumerableInterface 
    {
        $self = $this->newStatic();

        $copy = clone $this;

        foreach ($copy->items as $item)
        {
            if($this->isStatic($item))
            {
                $self = $self->merge($item->flatten()->items);
                continue;
            }

            $self->add($item);
        }

        return $self;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function reject(callable $callback): EnumerableInterface
    {
        $copy = clone $this;

        foreach ($this->items as $offset => $v)
        {
            if((bool) $callback($v, $offset))
            {
                $copy->offsetUnset($offset);
            }
        }

        return $copy;
    }

    /**
     * @param callable $callback
     * @return mixed|null
     */
    public function first(callable $callback)
    {
        return $this->items[$this->search($callback)] ?? null;
    }

    /**
     * @param callable $callback
     * @return mixed|null
     */
    public function last(callable $callback)
    {
        return ($filtered = $this->filter($callback))->isEmpty() ? null : $filtered->end();
    }

    /**
     * @param callable $callback
     * @return int|string|null
     */
    public function search(callable $callback)
    {
        foreach ($this->items as $key => $v)
        {
            if((bool) $callback($v, $key))
            {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param callable $callback
     * @return array
     */
    public function searchAll(callable $callback): EnumerableInterface 
    {
        $keys = [];

        foreach ($this->items as $key => $v)
        {
            if((bool) $callback($v, $key))
            {
                $keys[] = $key;
            }
        }

        return $this->newStatic($keys);
    }

    /**
     * @param $offset
     * @param null $default
     * @return mixed|null
     */
    public function get($offset, $default = null) 
    {
        return $this->items[$offset] ?? $default;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool 
    {
        return $this->items === [];
    }

    /**
     * @param $offset
     * @return bool
     */
    public function has($offset): bool 
    {
        return $this->offsetExists($offset);
    }

    /**
     * @param $offset
     * @return static
     */
    public function remove($offset): EnumerableInterface
    {
        $this->offsetUnset($offset);
        return $this;
    }

    /**
     * @return int|float|null
     */
    public function sum()
    {
        $filtered = $this->filter(static function ($v)
        {
            return \is_numeric($v);
        });

        if($filtered->isEmpty())
        {
            return null;
        }

        $sum = 0;

        foreach ($filtered as $item)
        {
            $sum += $item;
        }

        return $sum;
    }

    /**
     * @return float|int|null
     */
    public function median()
    {
        $filtered = $this->filter(static function ($v)
        {
            return \is_numeric($v);
        })->sort(SORT_NUMERIC);

        if($filtered->isEmpty())
        {
            return null;
        }

        $middle = (int) (($count = $filtered->count()) / 2);

        if($count % 2 === 0)
        {
            return ($filtered->offsetGet($middle - 1) + $filtered->offsetGet($middle)) / 2 + 0;
        }

        return $filtered->offsetGet($middle) + 0;
    }

    /**
     * @param int $size
     * @param bool $preserveKeys
     * @return static
     */
    public function chunk(int $size, bool $preserveKeys = false): EnumerableInterface
    {
        return $this->newStatic(\array_chunk($this->items, $size, $preserveKeys));
    }

    /**
     * @param string $glue
     * @return string
     */
    public function implode(string $glue = '.'): string 
    {
        $filtered = $this->filter(static function ($v)
        {
            return \is_string($v) || \is_numeric($v);
        });

        return \implode($glue, $filtered->items);
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function sort(int $sort_flags = SORT_REGULAR ): EnumerableInterface
    {
        $copy = clone $this;
        \sort($copy->items, $sort_flags);

        return $copy;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function ksort(int $sort_flags = SORT_REGULAR ): EnumerableInterface
    {
        $copy = clone $this;
        \ksort($copy->items, $sort_flags);

        return $copy;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function krsort(int $sort_flags = SORT_REGULAR ): EnumerableInterface
    {
        $copy = clone $this;
        \krsort($copy->items, $sort_flags);

        return $copy;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function usort(callable $callback): EnumerableInterface
    {
        $copy = clone $this;
        \usort($copy->items, $callback);

        return $copy;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function uasort(callable $callback): EnumerableInterface
    {
        $copy = clone $this;
        \uasort($copy->items, $callback);

        return $copy;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function uksort(callable $callback): EnumerableInterface
    {
        $copy = clone $this;
        \uksort($copy->items, $callback);

        return $copy;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function rsort(int $sort_flags = SORT_REGULAR ): EnumerableInterface
    {
        $copy = clone $this;
        \rsort($copy->items, $sort_flags);

        return $copy;
    }

    /**
     * @return float|int|null
     */
    public function avg()
    {
        $filtered = $this->filter(static function ($v)
        {
            return \is_numeric($v);
        });

        if(($count = $filtered->count()) === 0)
        {
            return null;
        }

        $sum = 0;

        foreach ($filtered as $item)
        {
            $sum += $item;
        }

        return $sum / $count;

    }

    /**
     * @return mixed
     */
    public function min()
    {
        return \min($this->items);
    }

    /**
     * @return mixed
     */
    public function max()
    {
        return \max($this->items);
    }


    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback) : EnumerableInterface
    {
        $self = $this->newStatic();

        foreach ($this->items as $offset => $value)
        {
            if((bool) $callback($value, $offset))
            {
                $self->set($offset, $value);
            }
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function shift()
    {
        return \array_shift($this->items);
    }

    /**
     * @param $value
     * @param mixed ... $values
     * @return $this
     */
    public function unshift($value, ... $values): EnumerableInterface
    {
        \array_unshift($values, $value);

        foreach ($values as $value)
        {
            !\is_array($value) ?: $value = $this->newStatic($value);
            \array_unshift($this->items, $value);
        }

        return $this;
    }

    /**
     * @param $value
     * @param mixed ... $values
     * @return $this
     */
    public function push($value, ... $values): EnumerableInterface
    {
        \array_unshift($values, $value);

        foreach ($values as $value)
        {
            !\is_array($value) ?: $value = $this->newStatic($value);
            \array_push($this->items, $value);
        }

        return $this;
    }

    /**
     * @param int $sort_flags
     * @return static
     */
    public function unique(int $sort_flags = SORT_STRING): EnumerableInterface
    {
        return $this->newStatic(\array_unique($this->items, $sort_flags));
    }

    /**
     * @return array
     */
    public function values(): EnumerableInterface
    {
        return $this->newStatic(\array_values($this->items));
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return \array_pop($this->items);
    }

    /**
     * Shuffles the array randomly
     * @return static
     */
    public function shuffle(): EnumerableInterface
    {
        $items = $this->items;
        shuffle($items);

        return $this->newStatic($items);
    }

    /**
     * @param int $offset
     * @param int|null $len
     * @param bool $preserveKeys
     * @return static
     */
    public function slice(int $offset, int $len = null, bool $preserveKeys = false): EnumerableInterface
    {
        return $this->newStatic(\array_slice($items = $this->items, $offset, $len, $preserveKeys));
    }

    /**
     * @param bool $preserveKeys
     * @return static
     */
    public function reverse($preserveKeys = false): EnumerableInterface
    {
        return $this->newStatic(\array_reverse($items = $this->items, $preserveKeys));
    }

    /**
     * @param mixed ... $values
     * @return static
     */
    public function add(... $values): EnumerableInterface
    {
        foreach ($values as $value
        {
            $this->offsetSet(null, $value);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function clear(): EnumerableInterface
    {
        return $this->replace([]);
    }

    /**
     * @param array|Arrayable $items
     * @return static
     */
    public function replace($items): EnumerableInterface
    {
        $this->items = $this->varToArray($items);
        return $this;
    }

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function pull($offset)
    {
        $v = $this->get($offset);
        $this->remove($offset);

        return $v;
    }

    /**
     * @param string|int|array $offset
     * @param string|int ...$offsets
     * @return static
     */
    public function only($offset, ... $offsets): EnumerableInterface
    {
        if(\is_array($offset))
        {
            $offsets = $offset;
        } 
        
        else 
        {
            \array_unshift($offsets, $offset);
        }

        $values = $this->newStatic();

        foreach ($offsets as $offset)
        {
            if($this->offsetExists($offset))
            {
                $values->offsetSet($offset, $this->offsetGet($offset));
            }
        }

        return $values;
    }

    /**
     * Return first collection element or null if collection is empty
     * @return mixed
     */
    public function start()
    {
        return $this->items[$this->firstKey()] ?? null ;
    }

    /**
     * Return last collection element or null if collection is empty
     * @return mixed
     */
    public function end()
    {
        return $this->items[$this->lastKey()] ?? null ;
    }

    /**
     * Return array of collection keys
     * @return array
     */
    public function keys() : EnumerableInterface
    {
        return $this->newStatic(\array_keys($this->items));
    }

    /**
     * Return collection first key
     * @return string|int|null
     */
    public function firstKey()
    {
        return \array_key_first($this->items);
    }

    /**
     * Return collection first key
     * @return string|int|null
     */
    public function lastKey()
    {
        return \array_key_last($this->items);
    }

    /**
     * @param string|int|array $offset
     * @param string|int ...$offsets
     * @return static
     */
    public function except($offset, ... $offsets): EnumerableInterface 
    {
        if(\is_array($offset))
        {
            $offsets = $offset;
        } 
        
        else 
        {
            \array_unshift($offsets, $offset);
        }

        $values = clone $this;

        foreach ($offsets as $offset)
        {
            $values->remove($offset);
        }

        return $values;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->items as $offset => $value)
        {
            if($this->isStatic($value))
            {
                $value = $value->toArray();
            }

            $array[$offset] = $value;
        }

        return $array;
    }

    /**
     * @param array|Arrayable $first
     * @param array|Arrayable ... $other
     * @return static
     */
    public function merge($first, ... $other): EnumerableInterface
    {
        $first = $this->varToArray($first);
        
        $copy = clone $this;
        
        if($other != [])
        {
            $arrays = [];
             
            foreach($other as $item)
            {
                $arrays[] = $this->varToArray($item);
            }
            
            $copy->items = \array_merge($this->items, $first, ... $arrays)
            
            return $copy;
        }        

        $copy->items = \array_merge($this->items, $first);
        
        return $copy;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback): EnumerableInterface
    {
        $copy = clone $this;
        
        foreach ($copy->items as $key => $v)
        {
            if ((bool) $callback($v, $key) === false)
            {
                break;
            }
        }

        return $copy;
    }

    /**
     * @param string|int $key
     * @param null|string|int $indexKey
     * @return static
     */
    public function pluck($key, $indexKey = null) : EnumerableInterface
    {
        return $this->newStatic(\array_column($this->items, $key, $indexKey));
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function transform(callable $callback): EnumerableInterface
    {
        foreach ($this->items as $key => $value)
        {
            $this->items[$key] = $callback($value, $key);
        }

        return $this;
    }

    /**
     * @param array|Arrayable $first
     * @param array ... $other
     * @return static
     */
    public function intersect($first, ... $other): EnumerableInterface
    {
        return $this->newStatic(\array_intersect($this->items, $first, ... $other));
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    public function callback(callable $callback)
    {
        return $callback($this);
    }

    /**
     * @param int $num
     * @return static
     */
    public function split(int $num): EnumerableInterface
    {
        if(($count = $this->count()) === 0)
        {
            return $this->newStatic();
        }

        $self = $this->newStatic();

        $start = 0;

        for ($i = 0; $i < $num; $i++)
        {
            $size = (int) \floor($count / $num);

            if($i < $count % $num)
            {
                $size++;
            }

            if($size)
            {
                $self->push($this->newStatic(\array_slice($this->items, $start, $size)));
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
    public function rand(int $num = 1)
    {
        if($this->isEmpty())
        {
            return null;
        }

        if($num > ($count = $this->count()) || 0 >= $num)
        {
            throw new \LogicException('Argument $num has to be between 1 and ' . $count);
        }

        $keys = (array) \array_rand($this->items, $num);

        $values = [];

        foreach ($keys as $key)
        {
            $values[] = $this->offsetGet($key);
        }

        if(count($values) > 1)
        {
            return $this->newStatic($values);
        }

        return $values[0];
    }

    /**
     * @param callable $callback
     * @param null $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return \array_reduce($this->items, $callback, $initial);
    }
}
