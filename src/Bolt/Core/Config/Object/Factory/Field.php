<?php

namespace Bolt\Core\Config\Object\Factory;

use Bolt\Core\App;

class Field
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($key, $options = array())
    {
        $fieldClass = $this->getFieldClass();

        $type = $options['type'];
        if (is_string($type)) {
            $type = $this->app['fieldtypes']->get($type);
        }

        return new $fieldClass($this->app, $key, $type, $options);
    }

    public function fromConfig($key, $config = array())
    {
        $this->validateConfig($key, $config);

        $options = $this->getDefaultOptions($config);

        return $this->create($key, $options);
    }

    public function validateConfig($key, $config)
    {
        $app = $this->app;

        if ( ! array_key_exists('type', $config)) {
            $app['notify']->error(sprintf('Not type given for field with key: "%s"', $key));
        }

        $registeredFieldTypes = $app['fieldtypes']->keys();
        if ( ! in_array($config['type'], $registeredFieldTypes)) {
            $app['notify']->error(sprintf('Invalid "type" key (%s) in field options for "%s" field. It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', $config['type'], $key));
        }
    }

    protected function getDefaultOptions($options)
    {
        $defaults = $this->app['config']->get('defaults/field', array());

        return array_replace_recursive($defaults, $options);
    }

    protected function getFieldClass()
    {
        return $this->app['config']->get('app/classes/field', 'Bolt\Core\Config\Object\Field');
    }

}
