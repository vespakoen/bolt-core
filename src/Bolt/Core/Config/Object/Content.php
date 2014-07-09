<?php

namespace Bolt\Core\Config\Object;

use Illuminate\Support\Contracts\ArrayableInterface;

class Content implements ArrayableInterface
{
    public function __construct($app, $attributes, $contentType)
    {
        $this->app = $app;
        $this->attributes = $attributes;
        $this->contentType = $contentType;
    }

    public function getAttribute($key, $default = null)
    {
        return array_get($this->attributes, $key, $default);
    }

    public function setAttribute($key, $value)
    {
        array_set($this->attributes, $key, $value);
    }

    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->attributes;
        }

        return $this->getAttribute($key, $default);
    }

    public function getTitle()
    {
        $titleField = $this->contentType->getTitleField();
        if ( ! $titleField->empty()) {
            return $this->get($titleField);
        }

        return '-';
    }

    public function __call($method, $arguments)
    {
        return $this->get($method);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function toArray()
    {
        $attributes = $this->attributes;

        if (array_key_exists('incoming', $attributes)) {
            foreach ($attributes['incoming'] as $type => $contents) {
                $attributes['incoming'][$type] = $contents->toArray();
            }
        }

        if (array_key_exists('outgoing', $attributes)) {
            foreach ($attributes['outgoing'] as $type => $contents) {
                $attributes['outgoing'][$type] = $contents->toArray();
            }
        }

        return $attributes;
    }

}
