<?php

namespace Ender\Container;

use Ender\Container\Container;

abstract class Facade
{
    public static $container;

    public static function __callStatic($method, $arguments)
    {
        $object = static::getInstance();

        return $object->$method(...$arguments);
    }

    public static function setContainer(Container $container)
    {
        static::$container = $container;
    }

    public static function getContainer(): Container
    {
        return static::$container;
    }

    public static function getAccesor()
    {
        throw new \Exception("Please define the getAccesor method");
        
    }

    public static function getInstance()
    {
        return static::getContainer()->make(static::getAccesor());
    }
}
