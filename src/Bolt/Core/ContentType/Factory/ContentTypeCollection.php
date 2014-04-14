<?php

namespace Bolt\Core\ContentType\Factory;

class ContentTypeCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array())
    {
        $contentTypeCollectionClass = $this->getContentTypeCollectionClass();

        return new $contentTypeCollectionClass($items);
    }

    public function fromConfig($config)
    {
        $this->validateConfig($config);

        $contentTypeCollectionClass = $this->getContentTypeCollectionClass();

        $collection = new $contentTypeCollectionClass;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error(sprintf('Invalid "contenttypes" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

    protected function getContentTypeCollectionClass()
    {
        return $this->app['config']->get('app/classes/contenttypecollection', 'Bolt\Core\ContentType\ContentTypeCollection');
    }

}
