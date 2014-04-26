<?php

namespace Bolt\Core\Content;

class Content
{
    public function __construct($app, $model)
    {
        $this->app = $app;
        $this->model = $model;
    }

    public function getAttribute($key, $default = null)
    {
        return array_get($this->model, $key, $default);
    }

    public function get($key, $default = null)
    {
        return $this->getAttribute($key, $default);
    }

}
