<?php

namespace Bolt\Core\Config\Object\Factory;

use Bolt\Core\App;

class Field
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($key, $type, $options = array())
    {
        $fieldClass = $this->getFieldClass();

        if (is_string($type)) {
            $type = $this->app['fieldtypes']->get($type);
        }

        return new $fieldClass($this->app, $key, $type, $options);
    }

    public function fromConfig($key, $config = array())
    {
        $this->validateConfig($key, $config);

        $type = array_get($config, 'type', 'string');

        return $this->create($key, $type, $config);
    }

    public function validateConfig($key, $config)
    {
        $app = $this->app;

        if ( ! array_key_exists('type', $config)) {
            $app['notify']->error(sprintf('Not type given for field with key: "%s"', $key));
        }

        $registeredFieldTypes = $app['fieldtypes']->keys();
        if (!in_array($config['type'], $registeredFieldTypes)) {
            $app['notify']->error(sprintf('Invalid "type" key (%s) in field options for "%s" field. It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', $config['type'], $key));
        }
    }

    protected function getFieldClass()
    {
        return $this->app['config']->get('app/classes/field', 'Bolt\Core\Config\Object\Field');
    }

}
