<?php

namespace Lobster\Arrayzy;


/**
 * Class Collection
 * @package Lobster\Arrayzy
 */
abstract class Collection extends Arrayzy {

    /**
     * @param $var
     * @return bool
     */
    abstract public function is($var) : bool ;

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
    public function offsetSet($offset, $value) {

        if($this->is($value)){
            return parent::offsetSet($offset, $value);
        }

        throw $this->getException($value);
    }
}
