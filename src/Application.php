<?php

namespace Ender\Container;

class Application
{
    public function __construct(protected Container $container){}

    public function registerProviders(array $providers) : void
    {
        foreach ($providers as $provider) {
           $provider = new $provider($this->container);

           $provider->register();
        }
    }

}
