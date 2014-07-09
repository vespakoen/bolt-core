<?php

namespace Bolt\Core\Config\Object\Collection\Factory;

class RelationCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array())
    {
        $collectionClass = $this->getRelationCollectionClass();

        $collection = new $collectionClass($items);

        $this->validateRelations($collection);

        return $collection;
    }

    public function fromConfig($config)
    {
        $this->validateConfig($config);

        $relationCollectionClass = $this->getRelationCollectionClass();

        $collection = new $relationCollectionClass;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        $this->validateRelations($collection);

        return $collection;
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error(sprintf('Invalid "relations" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

    protected function validateRelations($collection)
    {
        foreach($collection as $relation) {
            $other = $relation->getOther();
            if(is_null($other)) continue;

            if(!$collection->has($other))
            {
                $this->app['notify']->error(sprintf('Invalid relation configuration given, the "other" key (%s) for the relation "%s" does not exist.', $other, $relation->getKey()));
            }
        }
    }

    protected function getRelationCollectionClass()
    {
        return $this->app['config']->get('app/classes/relationcollection', 'Bolt\Core\Config\Object\Collection\RelationCollection');
    }

}
