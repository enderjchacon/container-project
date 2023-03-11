<?php

namespace Ender\Container;

use Ender\Container;

abstract class Provider 
{
    public function __construct(protected Container $container){}

    abstract public function register ();
}
