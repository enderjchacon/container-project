<?php

namespace Ender\Container;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class Container
{
    protected $bindings =[]; 
    protected $shared = [];
    public static $instance;

    public static function getInstance(): Container
    {
        if(static::$instance == null){
            static::$instance = new Container;
        }

        return static::$instance;
    }

    public static function setInstance(Container $container): void
    {
        static::$instance = $container;
    }

    public function bind($name, $resolver, bool $shared = false)
    {
        $this->bindings[$name] = [
            'resolver' => $resolver,
            'shared'   => $shared
        ];
    }

    public function make($name,array $arguments = [])
    {
        if (isset($this->shared[$name])) {
            return $this->shared[$name];
        }

        $resolver = $name;
        $shared = false;

        if (isset($this->bindings[$name])) {
            $resolver = $this->bindings[$name]['resolver'];
            $shared   = $this->bindings[$name]['shared'];
        }

        if ($resolver instanceof Closure) {
            $object = $resolver($this);
        } else {
            $object = $this->build($resolver,$arguments);
        }

        if($shared){
            $this->shared[$name] = $object;
        }

        return $object;
    }

    public function instance($name, $object)
    {
        $this->shared[$name] = $object;
    }

    public function build($name,$arguments = [])
    {
        try {
            $reflection = new ReflectionClass($name);
        } catch (ReflectionException $e) {
            throw new ContainerException("Unable to build [$name]: " . $e->getMessage(), 0, $e);
        }

        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException;
        }

        $construct = $reflection->getConstructor();


        if (is_null($construct)) {
            return new $name;
        }

        $constructParameters = $construct->getParameters();

        $dependencies = [];

        foreach ($constructParameters as $constructParameter) {

            $nameParameter = $constructParameter->getName();

            
            if(isset($arguments[$nameParameter])){
                $dependencies[] = $arguments[$nameParameter];
                continue;
            }

            if($constructParameter->isDefaultValueAvailable()){
                $dependencies[] = $constructParameter->getDefaultValue();
                continue;
            }


            try {
                $parameterType = $constructParameter->getType();
            } catch (ReflectionException $e) {
                throw new ContainerException("Unable to build [$nameParameter]: " . $e->getMessage(), 0, $e);
            }

            if (is_null($parameterType)) {
                throw new ContainerException("Please Provide the value of the parameter [$nameParameter]");
            }

            $parameterClassName = $parameterType->getName();
            $dependencies[] = $this->build($parameterClassName,$arguments);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function singleton($name, $resolver)
    {
       $this->bind($name,$resolver,shared:true);
    }
}
