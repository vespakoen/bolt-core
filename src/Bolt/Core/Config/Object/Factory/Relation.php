<?php

namespace Bolt\Core\Config\Object\Factory;

use Bolt\Core\App;

class Relation
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($app, $key, $other, $type, $options = array())
    {
        $relationClass = $this->getRelationClass();

        return new $relationClass($this->app, $key, $other, $type, $options);
    }

    public function fromConfig($key, $config = array())
    {
        $relationClass = $this->getRelationClass();

        $this->validateConfig($key, $config);

        $app = $this->app;

        $other = array_get($config, 'other');
        $type = array_get($config, 'type');

        $options = array_except($config, array('other', 'type'));

        return new $relationClass($app, $key, $other, $type, $options);
    }

    public function validateConfig($key, $config)
    {
        $app = $this->app;

        if ( ! array_key_exists('type', $config)) {
            $app['notify']->error(sprintf('No type given for relation with key: "%s"', $key));
        }
    }

    protected function getRelationClass()
    {
        return $this->app['config']->get('app/classes/relation', 'Bolt\Core\Config\Object\Relation');
    }

}
