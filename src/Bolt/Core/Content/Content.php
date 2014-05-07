<?php

namespace Bolt\Core\Content;

use ArrayAccess;

class Content implements ArrayAccess
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

    public function get($key, $default = null)
    {
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

    // BC
    public function contenttype()
    {
        return $this->contentType;
    }

    public function __call($method, $params = array())
    {
        return $this->attributes[$method];
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key))
        {
            $this->attributes[] = $value;
        }
        else
        {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->attributes[$key]);
    }


}
