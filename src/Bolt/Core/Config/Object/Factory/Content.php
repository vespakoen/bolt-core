<?php

namespace Bolt\Core\Config\Object\Factory;

class Content
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($attributes = array(), $contentType)
    {
        $contentClass = $this->getContentClass();

        if (is_string($contentType)) {
            $contentType = $this->app['contenttypes']->get($contentType);
        }

        return new $contentClass($this->app, $attributes, $contentType);
    }

    protected function getContentClass()
    {
        return $this->app['config']->get('app/classes/content', 'Bolt\Core\Config\Object\Content');
    }

}
