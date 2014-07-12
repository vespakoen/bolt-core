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

    public function findBy($key, $value, $default = null)
    {
        return $this->filterBy($key, $value, $default)
            ->first();
    }

    public function filterByMethod($method, $value)
    {
        return $this->filter(function($item) use ($method, $value) {
            return $item->$method() == $value;
        });
    }

    public function findByMethod($method, $value)
    {
        return $this->filterByMethod($method, $value)
            ->first();
    }

    /**
     * Get an array with the values of a given key.
     *
     * @param  string  $value
     * @param  string  $key
     * @return array
     */
    public function listsOption($value, $key = null)
    {
        $results = array();

        $counter = 0;
        foreach ($this as $item) {
            $resultKey = $key == null ? $counter : $item->get($key);
            $resultValue = $item->get($value);
            $results[$resultKey] = $resultValue;
            $counter++;
        }

        return $results;
    }

}
