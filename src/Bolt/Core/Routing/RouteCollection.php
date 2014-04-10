<?php

namespace Bolt\Core\Routing;

use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

use Illuminate\Support\Contracts\ArrayableInterface;

class RouteCollection extends SymfonyRouteCollection implements ArrayableInterface
{
    public function toArray()
    {
        $results = array();
        foreach ($this->all() as $key => $route) {
            $results[$key] = unserialize($route->serialize());
        }

        return $results;
    }

}
