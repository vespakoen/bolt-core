<?php

namespace Bolt\Core\FieldType\Factory;

class FieldType
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($key, $type, $options = array())
    {
        $fieldTypeClass = $this->getFieldTypeClass();

        return new $fieldTypeClass($this->app, $key, $type, $options);
    }

    public function fromConfig($key, $config = array())
    {
        $fieldTypeClass = $this->getFieldTypeClass();

        $type = array_get($config, 'doctrine.type');
        $options = array_except(array_get($config, 'doctrine'), array('type'));

        return new $fieldTypeClass($this->app, $key, $type, $options);
    }

    protected function getFieldTypeClass()
    {
        return $this->app['config']->get('app/classes/fieldtype', 'Bolt\Core\FieldType\FieldType');
    }

}
