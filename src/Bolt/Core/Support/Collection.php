<?php

namespace Bolt\Core\Support;

use Illuminate\Support\Collection as IlluminateCollection;

class Collection extends IlluminateCollection
{
    public function keys()
    {
        return array_keys($this->items);
    }

    public function filterBy($key, $value, $default = null)
    {
        return $this->filter(function($item) use ($key, $value, $default) {
            return $item->get($key, $default) == $value;
        });
    }

}
