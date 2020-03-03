<?php


namespace Lobster\Arrayzy;


/**
 * Class Collect
 * @package Lobster\Arrayzy
 */
final class Collect {

    /**
     * @var callable|null
     */
    private static $factory = null;

    /**
     * @param callable $factory
     */
    public static function setFactory(callable $factory) {
        static::$factory = function () use ($factory) : Enumerable {
            return $factory();
        };
    }

    /**
     * @param iterable $data
     * @return Enumerable
     */
    public static function get(iterable $data) : Enumerable {

        if(static::$factory == null){
            static::$factory = function (iterable $data) : Enumerable {
                return new Arrayzy($data);
            };
        }
        
        return (static::$factory)($data);
    }
}
