<?php

namespace Bolt\Core\Config\Object\Collection\Factory;

class FieldCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array())
    {
        $fieldCollectionClass = $this->getFieldCollectionClass();

        return new $fieldCollectionClass($items);
    }

    public function fromConfig($config)
    {
        $this->validateConfig($config);

        $fieldCollectionClass = $this->getFieldCollectionClass();
        $collection = new $fieldCollectionClass;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error('Invalid "fields" configuration given, configuration\'s root value must be of type array.');
        }
    }

    protected function getFieldCollectionClass()
    {
        return $this->app['config']->get('app/classes/fieldcollection', 'Bolt\Core\Config\Object\Collection\FieldCollection');
    }

}
