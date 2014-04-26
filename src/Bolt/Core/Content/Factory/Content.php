<?php

namespace Bolt\Core\Content\Factory;

class Content
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($model = array(), $type = 'array')
    {
        $contentClass = $this->getContentClass($type);

        return new $contentClass($this->app, $model);
    }

    public function validateConfig($key, $config)
    {
        // @todo implement
    }

    protected function getContentClass($type)
    {
        return $this->app['config']->get('app/classes/content/' . $type . '/content', 'Bolt\Core\Content\Content');
    }

}
