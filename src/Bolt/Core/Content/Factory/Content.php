<?php

namespace Bolt\Core\Content\Factory;

class Content
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($attributes = array())
    {
        $contentClass = $this->getContentClass();

        return new $contentClass($this->app, $attributes);
    }

    public function validateConfig($key, $config)
    {
        // @todo implement
    }

    protected function getContentClass()
    {
        return $this->app['config']->get('app/classes/content', 'Bolt\Core\Content\Content');
    }

}
