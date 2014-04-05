<?php

namespace Bolt\Core\FieldType;

use Bolt\Core\App;
use Bolt\Core\Field\Field;
use Bolt\Core\Config\ConfigObject;

use Doctrine\DBAL\Schema\Table;

use Illuminate\Support\Contracts\ArrayableInterface;

class FieldType extends ConfigObject implements ArrayableInterface {

    protected $objectType = 'fieldtype';

    protected $key;

    protected $doctrineType;

    protected $serializer;

    protected $migrator;

    public function __construct($app, $key, $doctrineType = null, $serializer = null, Closure $migrator = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->doctrineType = is_null($doctrineType) ? $this->getDefaultDoctrineType() : $doctrineType;
        $this->serializer = is_null($serializer) ? $this->getDefaultSerializer() : $serializer;
        $this->migrator = is_null($migrator) ? $this->getDefaultMigrator() : $migrator;
        $this->options = $options;

        $this->validate();
    }

    public static function fromConfig($key, $config = array())
    {
        $app = App::instance();
        $doctrineType = array_get($config, 'doctrine_type');
        $serializer = array_get($config, 'serializer');
        $migrator = array_get($config, 'migrator');

        if(!is_null($migrator)) {
            $migrator = function($table, $field) use ($migrator) {
                $key = $field->getKey();

                $type = array_get($migrator, 'type', 'string');
                $options = array_get($migrator, 'options', array());

                $table->addColumn($key, $type, $options);
            };
        }

        return new static($app, $key, $doctrineType, $serializer, $migrator);
    }

    public function getSerializer()
    {
        $serializerClass = $this->serializer;

        return new $serializerClass;
    }

    public function getSerializerClass()
    {
        return $this->serializer;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getDoctrineType()
    {
        return $this->doctrineType;
    }

    public function getMigrator()
    {
        return $this->migrator;
    }

    public function validate()
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", '', $this->key);

        if($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid FieldType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

    protected function getMigratorConfig()
    {
        $table = new Table('test');

        $migrator = $this->migrator;
        $migrator($table, new Field('test'));

        foreach($table->getColumns() as $column)
        {
            $config = $column->toArray();

            $result = array(
                'type' => get_class($config['type']),
            );
            unset($config['type']);
            unset($config['name']);

            $result['options'] = $config;

            return $result;
        }

        return array();
    }

    protected function getDefaultDoctrineType()
    {
        return 'Doctrine\DBAL\Types\StringType';
    }

    protected function getDefaultSerializer()
    {
        return 'Bolt\Core\FieldType\Serializer\PassthroughSerializer';
    }

    protected function getDefaultMigrator()
    {
        return function($table, $field) {
            $table->addColumn($field->getKey(), 'string', array('length' => 256, 'default' => ''));
        };
    }

}
