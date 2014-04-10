<?php

namespace Bolt\Core\Support;

use Illuminate\Support\Collection as IlluminateCollection;

class Collection extends IlluminateCollection
{
    public function keys()
    {
        return array_keys($this->items);
    }

    public function serialize($strategy = 'array')
    {
        $serialized = array();
        foreach ($this->items as $item) {
            $serialized[] = $item->serialize($strategy);
        }

        return $serialized;
    }

}
