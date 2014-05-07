<?php

namespace Bolt\Core\Field\Factory;

use Bolt\Core\App;
use Bolt\Core\FieldType\FieldType;

class Field
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($key, $type, $options = array())
    {
        $fieldClass = $this->getFieldClass();

        if( ! $type instanceof FieldType) {
            $type = $app['fieldtypes']->get($type);
        }

        return new $fieldClass($this->app, $key, $type, $options);
    }

    public function fromConfig($key, $config = array())
    {
        $fieldClass = $this->getFieldClass();

        $this->validateConfig($key, $config);

        $type = $this->app['fieldtypes']->get($config['type']);
        $options = array_except($config, array('type'));

        return new $fieldClass($this->app, $key, $type, $options);
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
        return $this->app['config']->get('app/classes/field', 'Bolt\Core\Field\Field');
    }

}
