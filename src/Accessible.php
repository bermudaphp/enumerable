<?php


namespace Lobster\Arrayzy;


/**
 * Trait Accessible
 * @package Lobster\Arrayzy
 */
trait Accessible {

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Accessible constructor.
     * @param iterable $data
     */
    public function __construct(iterable $data = []) {
        foreach ($data as $offset => $value){
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
    public function offsetExists($offset) : bool {
        return array_key_exists($offset, $this->data);
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
    public function offsetGet($offset) {

        if(!$this->offsetExists($offset)){
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
    public function offsetSet($offset, $value){

        if(is_array($value)){
            $value = $this->newStatic($value);
        }

        $offset === null ? $this->data[] = $value : $this->data[$offset] = $value;

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
    public function offsetUnset($offset) : void {
        unset($this->data[$offset]);
    }

    /**
     * @param array $data
     * @return static
     */
    protected function newStatic(iterable $data = []) : \ArrayAccess {
        return new static($data);
    }

}
