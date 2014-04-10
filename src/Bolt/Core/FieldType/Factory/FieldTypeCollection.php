<?php

namespace Bolt\Core\FieldType\Factory;

class FieldTypeCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array())
    {
        $fieldTypeCollectionClass = $this->getFieldTypeCollectionClass();

        return new $fieldTypeCollectionClass($items);
    }

    public function fromConfig($config)
    {
        $this->validateConfig($config);

        $fieldTypeCollectionClass = $this->getFieldTypeCollectionClass();

        $collection = new $fieldTypeCollectionClass;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error(sprintf('Invalid "fieldtypes" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

    protected function getFieldTypeCollectionClass()
    {
        return $this->app['config']->getRaw('app/classes/fieldtypecollection', 'Bolt\Core\FieldType\FieldTypeCollection');
    }

}
