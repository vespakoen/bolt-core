<?php

namespace Bolt\Core\Content\Factory;

class ContentCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array())
    {
        $contentCollectionClass = $this->getContentCollectionClass();

        return new $contentCollectionClass($items);
    }

    public function fromConfig($config)
    {
        $this->validateConfig($config);

        $contentCollectionClass = $this->getContentCollectionClass();

        $collection = new $contentCollectionClass;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error(sprintf('Invalid "content" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

    protected function getContentCollectionClass()
    {
        return $this->app['config']->getRaw('app/classes/content/array/collection', 'Bolt\Core\Content\ContentCollection');
    }

}
