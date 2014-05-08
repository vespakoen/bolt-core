<?php

namespace Bolt\Core\Content;

class Content
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

    public function __call($method, $arguments)
    {
        return $this->get($method);
    }

}
