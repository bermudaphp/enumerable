<?php


namespace Bermuda\Enumerable;


/**
 * Class Collection
 * @package Bermuda\Enumerable
 */
abstract class Collection extends Enumerable
{
    /**
     * @param $var
     * @return bool
     */
    abstract public function is($var): bool ;

    /**
     * @param $value
     * @return \InvalidArgumentException
     */
    abstract protected function getException($value) : \InvalidArgumentException ;

    /**
     * @param $offset
     * @param $value
     * @return static
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value) : EnumerableInterface
    {
        if($this->is($value))
        {
            return parent::offsetSet($offset, $value);
        }

        throw $this->getException($value);
    }
}
