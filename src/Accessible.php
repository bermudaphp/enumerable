<?php


namespace Bermuda\Enumerable;


/**
 * Trait Accessible
 * @package Bermuda\Enumerable
 */
trait Accessible
{
    protected $items = [];

    public function __construct(iterable $items = [])
    {
        foreach ($items as $offset => $value)
        {
            $this->offsetSet($offset, $value);
        }
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed|static Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if(!$this->offsetExists($offset))
        {
            $this->offsetSet($offset, $value = $this->newStatic());
            return $value;
        }

        return $this->data[$offset];
    }

    /**
     * @param $offset
     * @param $value
     * @return static
     */
    public function offsetSet($offset, $value)
    {
        if(is_array($value))
        {
            $value = $this->newStatic($value);
        }

        $offset === null ? $this->items[] = $value : $this->items[$offset] = $value;

        return $this;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @param ... $arguments
     * @return static
     */
    protected function newStatic(... $arguments): \ArrayAccess
    {
        return new static($arguments[0]);
    }

}
